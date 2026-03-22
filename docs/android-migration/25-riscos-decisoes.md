# 25 — Riscos, Dependências e Decisões em Aberto

## Contexto

Este documento consolida todos os riscos identificados ao longo dos 26 documentos do plano de migração,
acrescenta análise de compliance GDPR/health, e documenta cenários catastróficos com planos de resposta.

Refs: Todos os documentos com secção "Riscos" — docs 10 a 24.

---

## 0. Consolidated Risk Index

### Riscos Críticos e Altos (require active mitigation)

| ID | Descrição | Doc | Fase | Prob. | Impacto | Status |
|----|-----------|-----|------|-------|---------|--------|
| RISK-10-01 | Controllers web afetados ao criar API | 10 | 0 | Média | Alto | Open |
| RISK-11-01 | Sanctum guard conflita com web sessions | 11 | 0 | Média | Alto | Open |
| RISK-12-01 | Token leak via logging/crash report | 12 | 1A | Média | Alto | Open |
| RISK-13-01 | Sync loop consome bateria excessiva | 13 | 1B | Média | Alto | Open |
| RISK-14-01 | WebSocket Reverb + Sanctum token incompatível | 14 | 3 | Média | Alto | Open |
| RISK-14-02 | Crisis mode falha e mensagens não são throttled | 14 | 3 | Baixa | Crítico | Open |
| RISK-15-01 | Backend rejeita M4A (apenas aceita webm/mp3/wav/ogg) | 15 | 2 | Alta | Alto | Open |
| RISK-17-01 | Role transition não atualiza UI sem restart | 17 | 2+ | Média | Alto | Open |
| RISK-20-01 | Force push em branch partilhado perde trabalho | 20 | Todas | Baixa | Alto | Open |
| RISK-20-04 | Trabalho uncommitted perdido ao mudar de máquina | 20 | Todas | Média | Alto | Open |
| RISK-21-01 | Keystore perdido = impossível atualizar app | 21 | Release | Baixa | Crítico | Open |
| RISK-21-02 | Secret committed ao Git | 21 | Todas | Média | Alto | Open |
| RISK-23-01 | Fase 0 demora mais que esperado | 23 | 0 | Alta | Crítico | Open |
| RISK-23-03 | Fase 2 precisa moderação admin que não existe mobile | 23 | 2 | Média | Alto | Open |
| RISK-23-04 | WebSocket auth incompatível com Reverb | 23 | 3 | Média | Alto | Open |
| RISK-24-01 | Sanctum install conflita com auth middleware | 24 | 0 | Média | Alto | Open |

### Riscos Médios (monitor and mitigate)

| ID | Descrição | Doc | Fase | Prob. | Impacto | Status |
|----|-----------|-----|------|-------|---------|--------|
| RISK-10-02 | Eloquent queries ineficientes para mobile | 10 | 0 | Média | Médio | Open |
| RISK-11-02 | Rate limiting bloqueia utilizadores legítimos | 11 | 0 | Baixa | Médio | Open |
| RISK-12-02 | Biometric fallback UX confusa | 12 | 4 | Média | Médio | Open |
| RISK-13-02 | Conflict resolution UX frustrante | 13 | 1B | Baixa | Médio | Open |
| RISK-14-03 | FCM notifications duplicadas | 14 | 2 | Média | Médio | Open |
| RISK-15-02 | Audio recording falha silenciosamente | 15 | 2 | Média | Médio | Open |
| RISK-15-03 | Storage budget excedido em devices low-end | 15 | 2+ | Média | Médio | Open |
| RISK-17-02 | PRO features visíveis a B2C (confusão UI) | 17 | 2+ | Média | Médio | Open |
| RISK-18-01 | IDE settings divergem entre máquinas | 18 | Todas | Alta | Médio | Open |
| RISK-18-02 | Emulator + Docker + IDE excedem 16GB RAM | 18 | Todas | Alta | Médio | Open |
| RISK-18-03 | HAXM/WHPX conflito com Hyper-V | 18 | Todas | Média | Alto | Open |
| RISK-19-01 | Version drift entre máquinas | 19 | Todas | Alta | Alto | Open |
| RISK-19-02 | Android Studio auto-update quebra Gradle | 19 | Todas | Média | Médio | Open |
| RISK-21-03 | .env diverge entre máquinas | 21 | Todas | Média | Médio | Open |
| RISK-22-01 | Setup >2h por network issues | 22 | Setup | Média | Médio | Open |
| RISK-22-04 | Windows Defender quarantines Gradle/ADB | 22 | Setup | Baixa | Alto | Open |
| RISK-23-02 | Scope creep na Fase 1B | 23 | 1B | Média | Médio | Open |
| RISK-24-02 | Login UX não emotionally safe | 24 | 1A | Alta | Médio | Open |
| RISK-24-03 | Breathing animation jank em low-end | 24 | 1B | Média | Médio | Open |

### Riscos Baixos (accepted, monitor periodically)

| ID | Descrição | Doc | Fase | Prob. | Impacto | Status |
|----|-----------|-----|------|-------|---------|--------|
| RISK-10-03 | Scheduled commands não disponíveis em mobile | 10 | 1B | Baixa | Baixo | Accepted |
| RISK-13-03 | Stale data exibida como atual | 13 | 1B | Baixa | Baixo | Open |
| RISK-15-04 | Audio quality poor em devices baratos | 15 | 2 | Baixa | Baixo | Accepted |
| RISK-19-03 | winget/brew version lag | 19 | Setup | Baixa | Baixo | Accepted |
| RISK-20-02 | CRLF/LF phantom diffs | 20 | Todas | Média | Baixo | Open |
| RISK-20-03 | Binários grandes bloat repo | 20 | 2+ | Baixa | Médio | Open |
| RISK-21-04 | Firebase API key exposta no APK | 21 | 2 | Alta | Baixo | Open |
| RISK-22-02 | check-env.sh false positive | 22 | Setup | Média | Baixo | Open |
| RISK-22-03 | Gradle sync 1st open = 2GB+ download | 22 | Setup | Alta | Baixo | Accepted |

---

## 1. Riscos técnicos

### R-01: WebSocket compatibility com Reverb via token auth
**Severidade:** Alta
**Fase afetada:** 3 (Chat)
**Descrição:** O Laravel Reverb foi projetado para funcionar com Laravel Echo (JavaScript) e autenticação por session. A autenticação de canais via Bearer token (Sanctum) em vez de cookies de sessão pode requerer configuração adicional ou patches.
**Mitigação:** Testar early na Fase 0 com um endpoint `/broadcasting/auth` que aceite Bearer tokens. Se não funcionar out-of-the-box, pode ser necessário criar um middleware de bridge.
**Fallback:** Usar Pusher (serviço cloud) em vez de Reverb self-hosted.

### R-02: Performance do emulador em máquinas com 16 GB RAM
**Severidade:** Média
**Fase afetada:** Todas
**Descrição:** Android Studio + emulador + backend local pode consumir >12 GB RAM, deixando a máquina lenta.
**Mitigação:** Usar device físico para testing quando possível. Configurar emulador com apenas 2-3 GB RAM. Fechar backend quando não necessário.

### R-03: Complexidade de offline sync com conflitos
**Severidade:** Média
**Fase afetada:** 1B-2
**Descrição:** Se o utilizador edita o diário offline numa máquina e online noutra, pode haver conflitos de sync.
**Mitigação:** Para a Fase 1, usar "last-write-wins" com timestamps do servidor. Conflitos reais são raros (1 utilizador = tipicamente 1 dispositivo). Resolver com UX gentil se necessário.

### R-04: Tamanho do APK com exercícios e assets
**Severidade:** Baixa
**Fase afetada:** 4 (Sons)
**Descrição:** Se os sons relaxantes forem bundled no APK, o tamanho pode ultrapassar o alvo de 15 MB.
**Mitigação:** Download on-demand de sons com cache local. Manter core do APK leve.

### R-05: Breaking changes no backend ao criar API
**Severidade:** Média
**Fase afetada:** 0
**Descrição:** Ao refactorizar controllers e adicionar API Resources, há risco de introduzir bugs na web existente.
**Mitigação:** A API usa controllers separados (`Api/V1/`). Não tocar nos controllers web existentes. Testes end-to-end na web após cada mudança no backend.

### R-06: Dependência do OpenAI para funcionalidades core
**Severidade:** Média
**Fase afetada:** Todas
**Descrição:** Crisis detection (Layer 3), CBT insights, sumarização, e reflexão AI dependem da API OpenAI.
**Mitigação:** O backend já tem fallback local (Layer 1 + 2 de crisis detection). Para a app Android, garantir que funcionalidades degradam gracefully se a API falhar. Nunca bloquear uma ação do utilizador por falha da AI.

### R-07: Play Store review para conteúdo de saúde mental
**Severidade:** Média
**Fase afetada:** Release
**Descrição:** A Google Play tem políticas específicas sobre apps de saúde e wellness. Pode haver rejeição por conteúdo sensível (menções de suicídio, auto-lesão).
**Mitigação:** Incluir disclaimers claros, emergência contactos visíveis, e seguir guidelines da Play Store para apps de saúde. Considerar classificação etária adequada.

---

## 2. Dependências externas

| Dependência | Impacto se indisponível | Mitigação |
|------------|------------------------|-----------|
| Firebase (FCM) | Sem push notifications | Polling como fallback (não ideal) |
| OpenAI API | Sem CBT insights, AI summary, reflexão | Fallback local: keywords + intent patterns |
| Spotify API | Sem metadados de músicas | iTunes API como fallback |
| iTunes API | Sem metadados alternativos | Dados manuais |
| Google Play Store | Sem distribuição | APK direto ou alternative stores |
| Laravel Reverb | Sem real-time (chat) | Pusher cloud como fallback |
| Railway (hosting) | Backend offline | Backup deployment (Fly.io, DigitalOcean) |

---

## 3. Decisões em aberto

### D-01: Nome da pasta do projeto Android
**Opções:** `lumina-android/` vs `android/` vs `mobile/`
**Recomendação:** `lumina-android/` — Explícito, sem ambiguidade.
**Status:** Proposto, aguarda validação.

### D-02: Aplicação ID (package name)
**Opções:** `pt.lumina.app` vs `pt.lumina.android` vs `com.lumina.app`
**Recomendação:** `pt.lumina.app` — Reflete o domínio português, identidade da marca.
**Status:** Proposto, aguarda validação.
**Nota:** Uma vez publicado na Play Store, o application ID não pode mudar.

### D-03: Versionamento de API — header vs URL
**Opções:**
- URL: `/api/v1/...` (recomendado)
- Header: `Accept: application/vnd.lumina.v1+json`
**Recomendação:** URL-based. Mais simples, mais visível, standard para Laravel.
**Status:** Proposto.

### D-04: Monorepo vs multi-repo
**Opções:**
- Monorepo (recomendado): Backend + Android no mesmo repositório
- Multi-repo: Repositórios separados
**Recomendação:** Monorepo. Equipa pequena, coordenação frequente.
**Status:** Proposto.

### D-05: Conta Firebase — pessoal vs organizacional
**Opções:**
- Projeto Firebase pessoal (rápido para começar)
- Projeto Firebase em conta organizacional Lumina (correto para produção)
**Recomendação:** Criar conta organizacional desde o início. Evitar migração posterior.
**Status:** Decisão necessária antes da Fase 2 (FCM).

### D-06: Conta Google Play Developer
**Opções:**
- Conta pessoal (25 USD, setup rápido)
- Conta organizacional (25 USD, requer verificação DUNS)
**Recomendação:** Conta organizacional se Lumina já tiver NIF/empresa registada. Pessoal como fallback.
**Status:** Decisão necessária antes do primeiro release.

### D-07: Backend URL de produção para API
**Opções:**
- `api.lumina.pt` (subdomínio dedicado)
- `lumina.pt/api/v1/` (path-based)
**Recomendação:** `api.lumina.pt` — Separação clara, facilita CORS/CDN no futuro.
**Status:** Proposto, depende de configuração DNS.

### D-08: Estratégia de dark mode
**Opções:**
- Seguir sistema por defeito, override manual
- Light-only inicialmente, dark mode na Fase 4
**Recomendação:** Implementar dark mode desde a Fase 1A no design system, mas manter light como default.
**Status:** Proposto.

### D-09: Idioma do código Android
**Opções:**
- Código em inglês, UI em PT-PT (alinhado com PLANO_ESTRATEGICO.md)
- Tudo em PT-PT
**Recomendação:** Código e nomes de classes/funções em inglês. Strings da UI em PT-PT via `strings.xml`. Consistente com convenções internacionais e com o backend existente.
**Status:** Proposto, alinhado com documentação existente.

---

## 4. Suposições explícitas

| Suposição | Risco se falsa |
|-----------|---------------|
| A base de utilizadores é primariamente Android | Se iOS for necessário, considerar KMP |
| O backend continua em Railway | Se migrar, URLs e deployment mudam |
| 1 developer principal | Se equipa crescer, branch strategy pode precisar de ser mais formal |
| SQLite (dev) e PostgreSQL (prod) são mantidos | Se mudar DB, migrations podem divergir |
| Reverb é compatível com token auth | Se não for, WebSocket auth precisa de workaround |
| Play Store aprova app de saúde mental | Se rejeitada, precisar de ajustar conteúdo/disclaimers |

---

## 5. Decisões já tomadas (confirmadas neste plano)

| Decisão | Escolha | Justificação |
|---------|---------|-------------|
| Linguagem | Kotlin | Oficial, moderna, null-safe |
| UI | Jetpack Compose + Material 3 | Declarativo, estado da arte |
| Arquitetura | Clean Architecture + MVI | Testável, separação clara |
| DI | Hilt | Oficial, compile-time |
| Networking | Retrofit + OkHttp | Standard da indústria |
| DB local | Room | Oficial, type-safe |
| Auth | Sanctum tokens | Leve, integra com Laravel |
| WebSocket | OkHttp WebSocket | Maduro, fiável |
| Push | Firebase Cloud Messaging | Standard Android |
| App strategy | App única, módulos condicionais | Menor manutenção |
| Repository | Monorepo | Coordenação frequente |
| B2C mobile | Sim, Fase 1-3 | Core do produto |
| PRO mobile | Condicional, Fase 4+ | Depende de procura |
| Corporate mobile | Não | Desktop-first |

---

## 6. Health app compliance e GDPR

### GDPR para dados de saúde em mobile

A Lumina processa dados de saúde (humor, diário emocional, auto-avaliação PHQ-9/GAD-7).
Sob o GDPR, estes são **dados de categoria especial** (Art. 9) e requerem proteção reforçada.

| Requisito GDPR | Implementação mobile | Doc ref |
|----------------|---------------------|---------|
| **Consentimento explícito** (Art. 9(2)(a)) | Opt-in claro no onboarding. Explicar que dados são usados para tracking pessoal. Não usar dados para marketing | 12 |
| **Encriptação em repouso** (Art. 32) | Room DB encriptada com SQLCipher. Tokens em EncryptedSharedPreferences | 12, 13 |
| **Encriptação em trânsito** (Art. 32) | HTTPS obrigatório. Certificate pinning (Fase 4). WSS para WebSocket | 12, 14 |
| **Direito ao apagamento** (Art. 17) | Endpoint `DELETE /api/v1/account` apaga todos os dados. App limpa Room DB + SharedPreferences localmente | 12 |
| **Portabilidade** (Art. 20) | Endpoint `GET /api/v1/account/export` retorna JSON com todos os dados pessoais | 11 |
| **Minimização** (Art. 5(1)(c)) | App só recolhe dados necessários. Analytics opt-in. Sem tracking de localização (exceto se feature futura) | — |
| **Retenção** (Art. 5(1)(e)) | Dados de diário mantidos enquanto conta ativa. Após delete: 30 dias de retenção para recovery, depois apagamento permanente | — |

### EU MDR (Medical Device Regulation)

**A Lumina NÃO é um dispositivo médico.** É uma app de wellness e bem-estar emocional.

**Disclaimer obrigatório (no onboarding e nas settings):**
> "A Lumina é uma ferramenta de bem-estar emocional e auto-conhecimento. Não substitui
> acompanhamento médico, psicológico ou psiquiátrico profissional. Em caso de emergência,
> contacte o 112 ou a linha SNS 24 (808 24 24 24)."

**Limites a nunca ultrapassar:**
- Nunca diagnosticar (PHQ-9/GAD-7 são apenas indicadores, não diagnósticos)
- Nunca prescrever (nem medicação, nem tratamento)
- Nunca substituir profissionais (buddy system é peer support, não terapia)
- Sempre incluir contactos de emergência visíveis

### Google Play Store — Health app policies

| Política | Implicação | Ação |
|----------|-----------|------|
| **Health app data disclosure** | Declarar que a app processa dados de saúde no Data Safety form | Preencher no Play Console: "Health information → Emotional well-being data" |
| **Sensitive content** | Menções a auto-lesão e suicídio podem trigger review | Incluir disclaimer, contactos de emergência, e crisis support visível |
| **Data encryption** | Play Store favorece apps que encriptam dados sensíveis | SQLCipher + EncryptedSharedPreferences (já planeado) |
| **Age rating** | Conteúdo de saúde mental → classificação adequada | PEGI: "Parental Guidance" ou similar. IARC: selecionar "Health" |
| **Permissions** | Justificar cada permissão pedida | Microphone (gravação áudio), Notifications (FCM), Internet, Camera (futuro Wall) |

### Data retention: mobile vs server

| Dados | Retenção mobile | Retenção servidor | Sync |
|-------|----------------|------------------|------|
| Diário (entradas) | Últimos 90 dias em Room | Ilimitado enquanto conta ativa | Bidirecional |
| Cofre (técnicas) | Todos | Todos | Bidirecional |
| Plano de crise | Todos (offline-first) | Backup encriptado | Push + pull |
| Chat messages | Últimas 500 por sala | Todas | Paginação on-demand |
| Audio uploads | Cache local 7 dias | Ilimitado | Upload-only |
| PHQ-9/GAD-7 resultados | Últimos 6 meses | Ilimitado | Pull on-demand |
| Preferências | Todas | Todas | Bidirecional |

---

## 7. Catastrophic scenarios

### Cenário A: Keystore perdido

**Probabilidade:** Baixa (se seguir 3 backups). **Impacto:** Crítico.

- **Se Google Play App Signing ativo (recomendado):** Upload key pode ser reposta. A app signing key está nos servidores Google. Impacto: ~1 semana de downtime para processo de reset
- **Se Google Play App Signing NÃO ativo:** App não pode ser atualizada. Necessário republicar com novo package name, perda de todos os installs e reviews
- **Prevenção:** Ativar Google Play App Signing no primeiro upload. Guardar keystore em 3 locais (doc 21)

### Cenário B: Backend comprometido (data breach)

**Probabilidade:** Baixa. **Impacto:** Crítico.

- **Resposta imediata:** Desligar API. Rodar todas as keys (doc 21 secção 8)
- **GDPR Art. 33:** Notificar CNPD (autoridade portuguesa) em 72 horas
- **GDPR Art. 34:** Se risco alto para utilizadores, notificar utilizadores afetados
- **Mobile:** Push notification informativa. Forçar logout (invalidar todos os tokens)
- **Prevenção:** Backups encriptados. Rate limiting. Audit logging. Principle of least privilege

### Cenário C: OpenAI API deprecated ou indisponível permanentemente

**Probabilidade:** Baixa (deprecated). Média (indisponível temporariamente). **Impacto:** Médio.

- **Funcionalidades afetadas:** CBT insights, crisis detection Layer 3, AI summary, reflexão
- **Degradação graceful:** Backend já tem Layers 1+2 (keywords + patterns) como fallback
- **Longo prazo:** Migrar para modelo alternativo (Anthropic, Mistral, self-hosted)
- **Mobile:** Nunca bloquear ação do utilizador por falha de AI. Exibir insight genérico como fallback

### Cenário D: Self-harm durante utilização da app

**Probabilidade:** Média (utilizadores vulneráveis). **Impacto:** Crítico (humano, não técnico).

- **Prevenção ativa:** Crisis detection em conteúdo do diário e posts (3 layers). Plano de crise acessível offline. Contactos de emergência sempre visíveis
- **UX de crise:** FAB persistente de SOS. Safe House para saída rápida. Modo noturno automático (21h-05h). Sem mecânicas de culpa (streaks reset para 1, não 0)
- **Disclaimer:** App não substitui ajuda profissional. Referência constante a SNS 24 e 112
- **Responsabilidade legal:** Incluir Terms of Service claros. A Lumina é wellness tool, não clinical tool

---

## Riscos (meta-riscos deste documento)

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-25-01 | Risk register fica stale (novos riscos não são adicionados durante implementação) | Alta | Médio | Revisitar este documento a cada merge para `main`. Adicionar riscos descobertos durante implementação. Flag riscos como "Mitigated" ou "Closed" quando resolvidos |
| RISK-25-02 | Unknown unknowns — riscos não identificados neste plano | Média | Alto | Manter feedback loop durante implementação. Post-mortem após cada fase. Incluir "surpresas" neste documento à medida que surgem |

---

*Próximo: [26-recomendacao-final.md](26-recomendacao-final.md) — Recomendação final de abordagem.*
