# 12 — Autenticação, Sessão, Segurança e Privacidade

## Contexto

Este documento define a estratégia de autenticação, segurança e privacidade para a app
Android nativa da Lumina. A migração de session-based (web) para token-based (mobile) é
uma das reconceções mais profundas do projeto (ref. [04-fase-inicial.md](04-fase-inicial.md)
secção "O que NÃO migrar 1:1").

Referências cruzadas:
- [01-estado-atual.md](01-estado-atual.md) — auth atual (Laravel Breeze, session cookies)
- [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — componentes reutilizáveis
  (middleware, policies, rate limiters)
- [11-backend-gaps.md](11-backend-gaps.md) — Gaps 2 (Sanctum), 10 (WebSocket auth),
  11 (rate limiting), 24 (device tokens)
- [13-offline-sync.md](13-offline-sync.md) — token handling em contexto offline
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — riscos de segurança

---

## Observações do estado atual

1. **Sanctum NÃO está instalado.** Verificado: `composer.json` não inclui `laravel/sanctum`,
   `config/sanctum.php` não existe, User model não tem trait `HasApiTokens`, tabela
   `personal_access_tokens` não existe.

2. **User model `$hidden`:** `['password', 'remember_token', 'encrypted_private_key']`.
   A API Resource deve adicionalmente excluir: `shadowbanned_until` (user não deve saber),
   `onboarding_tours` (interno), `banned_at` (apenas via error response).

3. **Session driver:** database (verified). A autenticação web usa session cookies com CSRF.
   Sanctum token-based é completamente separado — não interfere com a web.

4. **EncryptionService** (`app/Services/EncryptionService.php`): Usa PHP `sodium_crypto_box`
   com `Argon2id` para key derivation. Server nunca armazena private keys em plaintext —
   apenas `encrypted_private_key` (salt + nonce + ciphertext, base64).

5. **DataAccessLog model:** Campos `user_id`, `accessed_by`, `data_type`, `purpose`,
   `ip_address`. Pouco utilizado atualmente — precisa de integração proactiva na API.

6. **Rate limiters existentes:** 8 definidos em `AppServiceProvider`, todos keyed por
   `user_id` ou IP. Reutilizáveis para API sem alterações
   (ref. [10-backend-reutilizacao.md](10-backend-reutilizacao.md) secção 11).

7. **LoginRequest** (`app/Http/Requests/Auth/LoginRequest.php`): Rate limiting integrado —
   max 5 tentativas falhadas, keyed por email + IP. Evento `Lockout` disparado.

8. **Quiet hours:** `User::isInQuietHours()` usa `now()->format('H:i')` — timezone do servidor.
   Suporta wraparound (ex: 22:00-07:00). Usado em 3 notifications para decidir canais.

---

## 1. Migração de session-based para token-based

### Estado atual (web)
- Laravel Breeze com session cookies
- CSRF tokens em forms
- Session armazenada em database
- Timeout: 120 minutos

### Estratégia Android (tokens)

**Laravel Sanctum** gera Personal Access Tokens que a app guarda de forma segura.

**Fluxo de autenticação:**

```
1. POST /api/v1/auth/login {email, password}
          │
          ▼
2. Server valida credenciais
          │
          ▼
3. Server gera Sanctum token
          │
          ▼
4. Response: {token: "1|abc...", user: {...}}
          │
          ▼
5. App guarda token em EncryptedSharedPreferences
          │
          ▼
6. Todos os requests subsequentes incluem:
   Authorization: Bearer 1|abc...
```

**Token lifecycle:**
- Token criado no login — sem expiração automática (Sanctum default)
- Token revogado no logout (`$user->currentAccessToken()->delete()`)
- Token inválido → 401 → app redireciona para login
- Opção futura: token rotation (gerar novo token periodicamente)

**Múltiplos dispositivos:**
- Cada login gera um token separado (device-specific)
- Campo `token_name` identifica o dispositivo: `"android_pixel_7"`
- Logout em um dispositivo não afeta outros

### Token expiration

**Estado atual do Sanctum:** Default sem expiração. Isto é um risco de segurança — se um
token é roubado, o atacante tem acesso indefinido.

**Estratégia Lumina:**
- Expiração de 30 dias: `'expiration' => 43200` em `config/sanctum.php`
- Token expirado → API retorna 401 → app redireciona para login (ou biometria se configurada)
- O user não precisa de fazer login frequentemente — 30 dias é suficiente para mobile
- Exceção: se o user ativa biometria, o token pode ser refreshed silenciosamente (sem prompt)

### Token rotation

- Login: novo token gerado. Tokens antigos **do mesmo dispositivo** são revogados.
- Implementação: token name com device identifier (`android_{device_id}`). Antes de criar
  novo token, `$user->tokens()->where('name', $tokenName)->delete()`.
- Garante: max 1 token por dispositivo. User com 3 dispositivos tem 3 tokens.

### Gestão de sessões concorrentes

- **Max 5 tokens ativos** por user (5 dispositivos simultâneos)
- Quando limite excedido: revogar token mais antigo (`$user->tokens()->oldest()->first()->delete()`)
- Endpoints de gestão:
  - `GET /api/v1/auth/sessions` — listar sessões ativas (device name, last used, created at)
  - `DELETE /api/v1/auth/sessions/{id}` — revogar sessão específica
- UI na app: Settings → Security → "Dispositivos ativos" com opção de logout remoto

### Identificação da app

- Header obrigatório: `X-Lumina-Client: android/1.0.0`
- Middleware valida presença deste header em todas as rotas API
- Requests sem header: rejeitados com 400 Bad Request
- **Nota:** Não é segurança forte (header spoofable). É primeira camada de defesa contra
  acesso casual + útil para analytics (diferenciar versões da app, detectar clients obsoletos)
- Ref. [25-riscos-decisoes.md](25-riscos-decisoes.md) para decisão sobre app attestation

---

## 2. Armazenamento seguro de credenciais

### EncryptedSharedPreferences

```kotlin
val masterKey = MasterKey.Builder(context)
    .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
    .build()

val securePrefs = EncryptedSharedPreferences.create(
    context,
    "lumina_secure_prefs",
    masterKey,
    EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
    EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
)
```

**O que guardar em EncryptedSharedPreferences:**
- Bearer token
- User ID (para operações offline)
- FCM token

**O que NÃO guardar:**
- Passwords (nunca armazenar)
- Dados pessoais extensos (usar Room DB encriptado se necessário)

---

## 3. Autenticação biométrica

### Cenário de uso
Após o primeiro login com email/password, o utilizador pode ativar biometria para logins futuros.

**Fluxo:**
1. Login com email/password → token guardado
2. Opção: "Ativar login com impressão digital?"
3. Se sim: BiometricPrompt confirma identidade
4. Token fica associado à autenticação biométrica
5. Próximo login: BiometricPrompt → se sucesso, usa token guardado
6. Se biometria falhar 3x: fallback para email/password

**Implementação:**
```kotlin
val biometricPrompt = BiometricPrompt(
    activity,
    executor,
    object : BiometricPrompt.AuthenticationCallback() {
        override fun onAuthenticationSucceeded(result: AuthenticationResult) {
            // Ler token de EncryptedSharedPreferences
            // Verificar se token ainda é válido (API call)
        }
    }
)

val promptInfo = BiometricPrompt.PromptInfo.Builder()
    .setTitle("Entrar na Lumina")
    .setSubtitle("Usa a tua impressão digital para entrar")
    .setNegativeButtonText("Usar password")
    .setAllowedAuthenticators(BIOMETRIC_STRONG or DEVICE_CREDENTIAL)
    .build()
```

**Prioridade:** Fase 1B (após auth básica funcionar)

---

## 4. Segurança de rede

### Certificate pinning

Para prevenir man-in-the-middle attacks:

```kotlin
val certificatePinner = CertificatePinner.Builder()
    .add("api.lumina.pt", "sha256/AAAA...=") // Pin do certificado
    .build()

val okHttpClient = OkHttpClient.Builder()
    .certificatePinner(certificatePinner)
    .build()
```

**Nota:** Requer atualização da app quando o certificado do servidor rodar. Considerar usar certificate transparency em vez de hard-pinning.

**Prioridade:** Fase 2 (após validar infraestrutura de deployment)

### HTTPS obrigatório

- Toda a comunicação API sobre HTTPS
- Configurar `network_security_config.xml` para bloquear cleartext:

```xml
<network-security-config>
    <domain-config cleartextTrafficPermitted="false">
        <domain includeSubdomains="true">api.lumina.pt</domain>
    </domain-config>
    <!-- Dev only -->
    <domain-config cleartextTrafficPermitted="true">
        <domain>10.0.2.2</domain> <!-- Android emulator localhost -->
    </domain-config>
</network-security-config>
```

### Request signing (futuro)

Para endpoints críticos (delete account, export data), considerar assinar requests com HMAC para garantir integridade.

**Prioridade:** Fase 3+

---

## 5. Proteção de dados locais

### Room DB encryption

Para dados sensíveis armazenados localmente (notas do diário, cofre):

- Opção A: **SQLCipher** (encripta toda a Room DB) — overhead de performance
- Opção B: Encriptar apenas campos sensíveis antes de guardar — mais granular

**Recomendação:** Opção B para a Lumina. Encriptar `note` e `cbt_insight` no DailyLog, e `content` no VaultItem. O resto (mood levels, tags, datas) não é sensível o suficiente para justificar o overhead.

### FLAG_SECURE

Para telas sensíveis, impedir screenshots e screen recording:

```kotlin
// Na Activity ou Composable
window.setFlags(
    WindowManager.LayoutParams.FLAG_SECURE,
    WindowManager.LayoutParams.FLAG_SECURE
)
```

**Usar em:**
- Plano de crise (contactos pessoais)
- Cofre pessoal
- Dados de perfil sensíveis
- Auto-avaliações (PHQ-9, GAD-7)

**Não usar em:**
- Fórum (conteúdo público)
- Chat (conteúdo já pseudónimo)
- Zona Calma (exercícios genéricos)

---

## 6. Safe House (Casa Segura) — versão Android

### Comportamento
1. Utilizador toca 2x rápido no botão de saída (ou shake gesture)
2. App fecha instantaneamente (finish all activities)
3. Remove entrada de "Recent Apps"
4. Cancela notificações pendentes
5. Abre browser com Google (opcional, configurável)

### Implementação
```kotlin
fun exitSafeHouse(activity: Activity) {
    // Cancelar notificações
    NotificationManagerCompat.from(activity).cancelAll()

    // Limpar back stack
    activity.finishAndRemoveTask()

    // Opcionalmente abrir browser
    val intent = Intent(Intent.ACTION_VIEW, Uri.parse("https://www.google.pt"))
    intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
    activity.startActivity(intent)
}
```

**Prioridade:** Fase 1 (funcionalidade de segurança crítica)

---

## 7. GDPR no contexto mobile

### Dados armazenados localmente na app

| Dado | Armazenamento | Retenção | Ação no delete account |
|------|--------------|----------|----------------------|
| Bearer token | EncryptedSharedPreferences | Até logout | Apagar |
| Perfil (cache) | Room DB | Até logout | Apagar |
| Diário (draft) | Room DB | Até sync | Apagar |
| Diário (cache) | Room DB | Configurável (diary_retention_days) | Apagar |
| Cofre (cache) | Room DB | Permanente localmente | Apagar |
| Missões (cache) | Room DB | 24h | Apagar |
| Preferences | DataStore | Permanente | Reset |
| Images (cache) | Disk cache (Coil) | Auto-evict | Clear |
| FCM token | Server + local | Até logout | Revogar + apagar |

### Exportar dados (GDPR Right of Access)
- Trigger: `POST /api/v1/privacy/export`
- Server compila JSON (mesmo processo que a web)
- Notificação push quando pronto
- Download link temporário (7 dias)

### Apagar conta (GDPR Right to Erasure)
1. Confirmação multi-step na app (dialog → password → confirmar)
2. `DELETE /api/v1/profile` com password no body
3. Server dispatch `ProcessGdprDeletion` job
4. App limpa todos os dados locais
5. Redireciona para tela de login

### Hibernar conta
- `POST /api/v1/privacy/hibernate`
- Server marca `hibernated_at`
- App limpa dados locais
- Permite reativação posterior

---

## 8. Autorização e roles no contexto mobile

### Estado atual
- Roles: admin, moderator, user, therapist
- Middleware: CheckBanned, EnsureOnboarding, Therapist, Corporate

### Estratégia Android
- A API retorna o role do utilizador no login e no perfil
- A app adapta a UI baseada no role:
  - `user`: Experiência standard
  - `moderator`: Botões extras de moderação em posts e chat
  - `admin`: Sem experiência mobile (web-only)
  - `therapist`: Sem experiência mobile nesta fase (web-only)

- **Nunca confiar no client-side para autorização** — o server valida sempre
- Se um user moderator tentar uma ação de admin via API, o server retorna 403

---

## 9. Proteção contra ataques comuns

| Ataque | Mitigação |
|--------|----------|
| Token theft | EncryptedSharedPreferences + biometria |
| Man-in-the-middle | HTTPS + certificate pinning (fase 2) |
| Replay attacks | Timestamps + idempotency keys (fase 2) |
| Brute force login | Rate limiting no server (throttle:api) |
| Root/jailbreak | Detecção básica (avisar, não bloquear) |
| Reverse engineering | ProGuard/R8 obfuscation em release |
| Screenshot de dados sensíveis | FLAG_SECURE em telas selecionadas |
| Data leakage via backups | `android:allowBackup="false"` no manifest |

---

## 10. Encriptação E2E no contexto Android

### Estado atual (servidor)

O `EncryptionService` (`app/Services/EncryptionService.php`) implementa E2E encryption para
buddy sessions (conversas 1:1 de suporte) usando libsodium:

- **Algoritmo:** `sodium_crypto_box` (Curve25519-XSalsa20-Poly1305)
- **Key derivation:** Argon2id (`OPSLIMIT_INTERACTIVE`, `MEMLIMIT_INTERACTIVE`)
- **Storage:** Server guarda `public_key` (base64) e `encrypted_private_key` (salt + nonce + ciphertext, base64). Server **nunca** tem acesso à private key em plaintext.
- **Per-message:** Nonce aleatório por mensagem (`CRYPTO_BOX_NONCEBYTES`)
- **Memory safety:** `sodium_memzero()` em todas as keys sensíveis após uso

### Implementação Android

**Biblioteca:** Lazysodium-Android (JNI wrapper para libsodium nativo). Mesmos algoritmos,
keys binariamente compatíveis com a implementação PHP.

**Alternativa descartada:** Google Tink — API de alto nível mas formato de key diferente,
não compatível com o formato já armazenado no servidor.

**Key bootstrap flow:**
1. Login → server retorna `encrypted_private_key` no response de auth
2. App deriva key a partir da password usando Argon2id (mesmos params que server)
3. App decripta private key
4. App guarda private key em EncryptedSharedPreferences (nunca em Room DB)
5. App pode agora encriptar/decriptar mensagens de buddy sessions

**Password change:**
- Server re-encripta private key com nova derived key
- App deteta password change (via flag no profile response)
- App re-fetches encrypted_private_key e re-deriva com nova password

**Prioridade:** Fase 3 (quando buddy system for migrado para mobile)

---

## 11. Classificação de dados e exposição via API

### Matriz de classificação — campos do User model

| Campo | Sensibilidade | Expor na API? | Encriptação at-rest? | Notas |
|-------|--------------|---------------|---------------------|-------|
| id, name, pseudonym | Baixa | Sim (UserResource) | Não | pseudonym é sha256 determinístico |
| email | Média | Sim (apenas perfil próprio) | Não | Nunca expor email de outros users |
| password | Crítica | **Nunca** | Hashed (bcrypt) | `$hidden` no model |
| remember_token | Crítica | **Nunca** | — | `$hidden` no model |
| encrypted_private_key | Crítica | Apenas no auth response | Encrypted (Argon2id) | Para E2E bootstrap |
| avatar | Baixa | Sim | Não | URL pública |
| bio | Baixa | Sim | Não | Max 160 chars |
| safety_plan | Alta | Sim (próprio, endpoint dedicado) | Considerar encriptar | 6 campos de crise |
| emotional_tags | Média | Sim (perfil próprio) | Não | Max 3 tags |
| flames, current_streak | Baixa | Sim (público) | Não | Gamificação |
| role | Baixa | Sim | Não | Para UI condicional |
| shadowbanned_until | Interna | **Nunca** | — | User não deve saber |
| banned_at | Interna | Apenas via error response 403 | — | — |
| quiet_hours_start/end | Baixa | Sim (perfil próprio) | Não | Notificação prefs |
| a11y_dyslexic_font | Baixa | Sim (perfil próprio) | Não | Sync cross-device |
| a11y_reduced_motion | Baixa | Sim (perfil próprio) | Não | Sync cross-device |
| a11y_text_size | Baixa | Sim (perfil próprio) | Não | Sync cross-device |
| diary_retention_days | Baixa | Sim (perfil próprio) | Não | Privacy setting |
| onboarding_tours | Interna | Não | — | Internal tracking |
| company_id | Interna | Não | — | B2B context (web-only) |

### Dados sensíveis em Room DB (Android)

| Dado | Encriptação | Método |
|------|------------|--------|
| DailyLog.note | Sim | Campo encriptado (AndroidX Security) |
| DailyLog.cbt_insight | Sim | Campo encriptado |
| VaultItem.content | Sim | Campo encriptado (modelo `encrypted` cast no Laravel, equivalente local) |
| Safety plan (cache) | Sim | Campo encriptado |
| Bearer token | Sim | EncryptedSharedPreferences (AES-256-GCM) |
| E2E private key | Sim | EncryptedSharedPreferences |

---

## 12. Auditoria de acesso a dados via API

### DataAccessLog — integração com API

O model `DataAccessLog` (`app/Models/DataAccessLog.php`) tem campos: `user_id`, `accessed_by`,
`data_type`, `purpose`, `ip_address`. Atualmente pouco utilizado.

**Estratégia para API:** After-middleware que loga acesso a endpoints sensíveis:

| Endpoint | data_type | purpose |
|---------|-----------|---------|
| GET /profile/safety-plan | safety_plan | user_view |
| PUT /profile/safety-plan | safety_plan | user_update |
| GET /vault | vault | user_view |
| POST /privacy/export | gdpr_export | user_request |
| DELETE /profile | account_deletion | user_request |
| GET /diary | diary | user_view |
| GET /profile/passport | emotional_passport | user_view |
| GET /assessments | assessment | user_view |

**Implementação:** Middleware `LogSensitiveAccess` que verifica route name contra lista de
endpoints sensíveis e cria `DataAccessLog` entry com IP do request.

---

## 13. Comportamento de shadowban no contexto API

O `ShadowbanScope` (`app/Models/Scopes/ShadowbanScope.php`) é um Global Scope no model
`Post`. Filtra posts de users shadowbanned — invisíveis para outros, visíveis para o
próprio user (`orWhere user_id = Auth::id()`).

**Compatibilidade API:**
- O scope usa `Auth::check()` e `Auth::user()` internamente
- Com `auth:sanctum` guard configurado nas rotas API, `Auth::user()` resolve corretamente
- O comportamento é **idêntico** ao da web sem alterações necessárias

**Regras para API:**
- `shadowbanned_until` **nunca** exposto no `UserResource` — o user não deve saber
- `User::isShadowbanned()` — disponível para lógica interna server-side
- Notificações de users shadowbanned não são enviadas a outros users
  (já implementado em `ForumInteraction`)

---

## 14. Quiet hours no contexto API

### Estado atual

`User::isInQuietHours()` verifica se a hora atual está entre `quiet_hours_start` e
`quiet_hours_end` (formato `H:i`). Suporta wraparound (ex: 22:00-07:00).

Usado em 3 notifications (`ForumInteraction`, `GentleChallengeReceived`,
`GentleReEngagement`) no método `via()` — durante quiet hours, apenas canal `database`
(sem push, sem broadcast).

### Comportamento na API

- **FCM:** Durante quiet hours, notificações não são enviadas via FCM. Apenas armazenadas
  no database. O user vê quando abrir a app.
- **Reutilização:** A lógica `isInQuietHours()` é 100% reutilizável. Apenas adicionar
  `FcmChannel::class` à lista de canais condicionais
  (ref. [10-backend-reutilizacao.md](10-backend-reutilizacao.md) secção "Transformação de notificações").

### Edge case: timezone

`isInQuietHours()` usa `now()->format('H:i')` — **timezone do servidor** (UTC ou config).
Se o user está em UTC+1 e configurou quiet hours 22:00-07:00, o servidor verifica contra
a hora UTC, não a hora local do user.

**Impacto:** Quiet hours podem estar desalinhadas em ±1-2 horas.

**Solução (Fase 2):** Adicionar campo `timezone` ao User model. `isInQuietHours()` passa
a usar `now()->setTimezone($this->timezone)->format('H:i')`. A app Android envia o timezone
do dispositivo no onboarding e pode atualizar via `PUT /api/v1/profile`.

---

## 15. Rate limiting por endpoint

### Mapeamento de limiters existentes para API

| Rate Limiter | Limite | Endpoints API | Notas |
|-------------|--------|--------------|-------|
| `password-reset` | 1/min | POST /auth/forgot-password | Já existente |
| `content-creation` | 5/min | POST /diary, /posts, /comments | Já existente |
| `buddy-actions` | 3/min | POST /buddy/* | Fase 3 |
| `gamification` | 5/min | POST /users/{id}/gentle-challenge | Já existente |
| `reports` | 3/min | POST /reports | Já existente |
| `suggestions` | 2/min | GET /recommendations | Já existente |
| `privacy-actions` | 3/hora | POST /privacy/export, DELETE /profile, POST /privacy/hibernate | Já existente |
| `ai-actions` | 10/min | POST /diary (CBT analysis) | Já existente |

### Novos limiters para API

| Rate Limiter | Limite | Endpoints API | Razão |
|-------------|--------|--------------|-------|
| `api-global` | 120/min | Todas as rotas API | Proteção geral contra abuso |
| `auth-login` | 5/min por IP | POST /auth/login, /auth/register | Proteção brute force |
| `breathing-daily` | 3 rewards/dia | POST /calm-zone/breathe | Anti-spam de flames |

### Headers de rate limit

Todas as respostas API incluem headers standard:
- `X-RateLimit-Limit: 120`
- `X-RateLimit-Remaining: 115`
- `Retry-After: 42` (apenas quando excedido, retorna 429)

---

## 16. Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-12-01 | Tokens Sanctum sem expiração permitem acesso indefinido se roubado | Alta (se não configurado) | Alto | Configurar expiração de 30 dias + token rotation por dispositivo |
| RISK-12-02 | Argon2id `OPSLIMIT_INTERACTIVE` pode ser lento em dispositivos Android low-end (~500ms-2s) | Média | Médio | Benchmark em dispositivo min-spec (API 26). Se >2s, considerar `OPSLIMIT_MIN` com salt mais longo. Cache derived key em memória durante sessão |
| RISK-12-03 | `isInQuietHours()` usa timezone do servidor — quiet hours desalinhadas para users noutros fusos | Média | Médio | Fase 1: documentar comportamento. Fase 2: adicionar campo `timezone` ao User model |
| RISK-12-04 | `DataAccessLog` pouco utilizado — audit trail GDPR com gaps | Média | Baixo | Implementar middleware `LogSensitiveAccess` desde Fase 0 |
| RISK-12-05 | E2E key derivation requer password do user — se user usa biometric login, password não está disponível | Média | Médio | Para E2E (Fase 3): na primeira vez, pedir password para derivar key e cachear derived key em EncryptedSharedPreferences. Biometric unlocks a cache, não re-deriva |
| RISK-12-06 | Certificate pinning pode causar lockout se certificado do servidor rodar e app não atualizar | Baixa | Alto | Fase 2: usar certificate transparency em vez de hard-pinning. Backup pins com hash do CA root |

---

*Próximo: [13-offline-sync.md](13-offline-sync.md) — Offline, autosave, sincronização e resiliência.*
