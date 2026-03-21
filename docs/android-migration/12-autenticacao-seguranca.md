# 12 — Autenticação, Sessão, Segurança e Privacidade

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

*Próximo: [13-offline-sync.md](13-offline-sync.md) — Offline, autosave, sincronização e resiliência.*
