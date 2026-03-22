# 26 — Recomendação Final de Abordagem

## Contexto

Este é o documento final do plano de migração Android da Lumina. Consolida as decisões tomadas
ao longo de 26 documentos e fornece o checklist de readiness para começar a implementação.

Refs: Todos os documentos de 01 a 25 — este documento serve como ponte entre planeamento e implementação.

---

## Síntese executiva

A Lumina tem um backend Laravel maduro com 32 modelos, 7 serviços, e lógica de negócio bem definida. O gap principal para mobile é a **ausência total de camada API** — não existe `routes/api.php`, não existe autenticação por tokens, e não existem API Resources.

A recomendação é uma abordagem em duas frentes paralelas:

1. **Backend:** Adicionar uma camada API (`/api/v1/`) com Sanctum, sem alterar os controllers web existentes. ~80% do backend é reutilizado diretamente (Models, Services, Events, Jobs, Commands).

2. **Android:** App nativa em Kotlin + Jetpack Compose com Clean Architecture + MVI, organizada em feature modules. App única para B2C (com módulos condicionais para moderadores e, futuramente, terapeutas).

---

## O que fazer primeiro

### Passo 1: Backend API layer (Fase 0)

Antes de escrever uma linha de Kotlin:
- Instalar Sanctum
- Criar `routes/api.php`
- Implementar 3 endpoints: login, profile, dashboard
- Padronizar error handling JSON
- Testar com Postman/Bruno

**Resultado:** A app Android tem algo contra o que autenticar e consumir.

### Passo 2: Scaffold Android + auth (Fase 1A)

- Projeto multi-module com version catalog
- Design system Lumina (cores, tipografia, componentes)
- Tela de login funcional contra a API
- Guardar token em EncryptedSharedPreferences
- CI com GitHub Actions

**Resultado:** App compila, autentica, e mostra tela com tema Lumina.

### Passo 3: Core funcional (Fase 1B)

- Onboarding, dashboard, diário, zona calma core, perfil, gamificação
- Offline: diário com auto-save, zona calma sem rede
- Safe House para saída de emergência
- Haptic feedback em exercícios e mood slider

**Resultado:** App utilizável com valor terapêutico real. Pronta para testes com utilizadores reais.

---

## Princípios a nunca esquecer

1. **A Lumina é usada por pessoas vulneráveis.** Cada decisão de UX deve considerar contexto de crise, mãos a tremer, pensamento nublado.

2. **Offline é obrigatório para a Zona Calma.** Em momento de crise, o utilizador pode não ter rede. Exercícios de regulação somática devem funcionar sempre.

3. **Toda a gamificação é server-side.** A app nunca calcula flames nem desbloqueia achievements. O servidor é a fonte de verdade.

4. **Sem mecânicas de culpa.** Streaks resetam para 1 (não 0). Missões não completadas desaparecem silenciosamente. Sem notificações agressivas.

5. **PT-PT humano e empático.** Todas as strings da UI, mensagens de erro, e estados vazios devem ser escritos com cuidado e sensibilidade.

6. **Nativo de verdade.** Touch targets ≥48dp (≥56dp em contexto de crise). Haptic feedback significativo. Gestos nativos (swipe, long-press). Não é um wrapper web.

---

## Consolidated risk summary (Top 10)

Os 10 riscos mais severos que podem bloquear ou atrasar significativamente o projeto:

| # | ID | Risco | Fase | Mitigação principal |
|---|-----|-------|------|-------------------|
| 1 | RISK-23-01 | Fase 0 demora mais que esperado | 0 | 3 endpoints mínimos primeiro. API controllers separados |
| 2 | RISK-21-01 | Keystore perdido | Release | 3 backups + Google Play App Signing |
| 3 | RISK-14-02 | Crisis mode falha | 3 | Testar extensively. Fallback: server-side throttle |
| 4 | RISK-14-01 | WebSocket + Sanctum incompatível | 3 | Testar na Fase 0. Fallback: Pusher |
| 5 | RISK-15-01 | Backend rejeita M4A do Android | 2 | Atualizar validação backend para aceitar M4A |
| 6 | RISK-21-02 | Secret committed ao Git | Todas | .gitignore robusto + pre-commit hooks + GitHub secret scanning |
| 7 | RISK-13-01 | Sync loop consome bateria | 1B | WorkManager constraints. Exponential backoff |
| 8 | RISK-24-01 | Sanctum conflita com auth web | 0 | Guard separado. Testar web após instalar |
| 9 | RISK-19-01 | Version drift entre máquinas | Todas | .sdkmanrc + .nvmrc + Gradle Wrapper |
| 10 | RISK-20-04 | Trabalho perdido ao mudar de máquina | Todas | Regra: sempre commit+push antes de fechar |

**Critical path:** RISK-23-01 → RISK-24-01 → RISK-14-01 (se Fase 0 atrasa, tudo atrasa)

---

## Implementation readiness checklist

Antes de escrever a primeira linha de código, verificar que tudo está pronto:

### Pré-requisitos de conta e identidade

- [ ] **Password manager** configurado (1Password ou Bitwarden) com vault "Lumina Dev"
- [ ] **Application ID** decidido: `pt.lumina.app` (proposto)
- [ ] **Conta Firebase** criada (organizacional, não pessoal)
- [ ] **Conta Google Play Developer** criada (25 USD, verificação completa)
- [ ] **Domínio API** decidido: `api.lumina.pt` (proposto)

### Pré-requisitos de repositório

- [ ] `.gitattributes` criado (doc 20 secção 11)
- [ ] `.gitignore` atualizado com entradas Android (doc 20 secção 5)
- [ ] `.github/PULL_REQUEST_TEMPLATE.md` criado (doc 20 secção 10)
- [ ] Branch protection rules no GitHub para `main` e `develop`
- [ ] `.editorconfig` criado na raiz do repo

### Pré-requisitos de ambiente

- [ ] **Android Studio** instalado e configurado (doc 18, 19)
- [ ] **JDK 17** via SDKMAN com `.sdkmanrc` na raiz
- [ ] **Android SDK** API 35 (target) + API 34 (testing) + API 26 (min)
- [ ] **Emulador** Pixel 7 API 34 configurado
- [ ] **check-env.sh** validou tudo OK (doc 22)
- [ ] **Windows Defender exclusions** adicionadas (se Windows, doc 22 secção 6.2)

### Pré-requisitos de backend

- [ ] Backend local a funcionar (`php artisan serve`)
- [ ] `.env` com todos os segredos
- [ ] `php artisan migrate --seed` executado

### Pré-requisitos de segredos

- [ ] **Release keystore** gerado e guardado em 3 locais (doc 21)
- [ ] **google-services.json** obtido do Firebase Console
- [ ] **keystore.properties** criado localmente (não no Git)
- [ ] **GitHub Secrets** configurados para CI/CD

---

## Monitoring and analytics strategy

### Firebase Crashlytics (desde a Fase 1A)

- Integrar no scaffold. Crash-free rate target: > 99.5%
- Custom keys: `user_role`, `emotional_mode`, `is_offline`
- Non-fatal errors: network failures, sync conflicts, audio recording failures

### Firebase Performance Monitoring (desde a Fase 1B)

- Custom traces: `diary_save`, `dashboard_load`, `breathing_session`, `login_flow`
- Network monitoring: automatic para Retrofit requests
- Target: p95 < targets definidos no doc 23

### Analytics (opt-in, desde a Fase 1B)

**Princípio:** Analytics é opt-in e privacy-first. Nunca rastrear conteúdo do diário,
mensagens de chat, ou dados de saúde. Apenas eventos de interação agregados.

| Evento | Parâmetros | Fase |
|--------|-----------|------|
| `diary_created` | `mood_value`, `has_note`, `has_tags`, `is_offline` | 1B |
| `breathing_completed` | `duration_seconds`, `completed_full_cycle` | 1B |
| `grounding_completed` | `duration_seconds` | 1B |
| `crisis_plan_viewed` | `is_offline` | 1B |
| `safe_house_activated` | — | 1B |
| `forum_post_created` | `has_audio`, `is_anonymous` | 2 |
| `assessment_completed` | `type` (PHQ-9/GAD-7) | 2 |
| `chat_message_sent` | `is_anonymous`, `is_sensitive` | 3 |
| `crisis_mode_activated` | `room_id` (hashed) | 3 |
| `buddy_session_started` | — | 3 |

**Nunca rastrear:** Conteúdo de texto, mood values individuais ao longo do tempo (tracking individual),
identificadores pessoais, mensagens de chat, resultados de PHQ-9/GAD-7.

---

## What NOT to do

10 anti-patterns explícitos a evitar durante toda a implementação:

### 1. Nunca usar WebView para funcionalidades core
A app é nativa. Cada ecrã é Jetpack Compose. WebView apenas para conteúdo externo (links, termos de uso).

### 2. Nunca calcular gamificação no client
Flames, streaks, achievements, XP — tudo calculado pelo servidor. A app apenas exibe.
Client-side calculation permite manipulação e causa inconsistências entre devices.

### 3. Nunca usar SharedPreferences plain para dados sensíveis
Tokens, session data, dados de saúde → sempre `EncryptedSharedPreferences` ou Room + SQLCipher.
`SharedPreferences` plain é legível por qualquer app com root access.

### 4. Nunca ignorar offline
Toda a Zona Calma funciona offline. Diário tem auto-save local. Plano de crise acessível sem rede.
Em momento de crise, o utilizador pode não ter internet.

### 5. Nunca usar vermelho para erros
Erros usam tons suaves (rose-500 com soft styling). Vermelho é agressivo para utilizadores
em estado emocional vulnerável. Usar ícones + texto descritivo em vez de cor apenas.

### 6. Nunca enviar notificações de streak-break
"Não fizeste o teu diário hoje!" é uma mecânica de culpa. A Lumina nunca culpa.
Streaks resetam silenciosamente para 1. Missões não completadas desaparecem sem aviso.

### 7. Nunca priorizar PRO antes de B2C estar sólido
PRO mobile (terapeutas) é Fase 4, condicional. Só avançar com validação de procura real
(50+ terapeutas ativos, feedback positivo de 5+).

### 8. Nunca hardcodar strings de UI
Todas as strings em `strings.xml` (PT-PT). Facilita futura internacionalização e permite
revisão centralizada do tom empático.

### 9. Nunca ignorar acessibilidade
TalkBack, touch targets 44dp+, contraste AA, contentDescriptions — desde o dia 1, não como "nice to have".
Utilizadores com deficiência visual ou motora têm as mesmas necessidades de saúde mental.

### 10. Nunca commitar segredos
`.env`, `keystore.properties`, `google-services.json`, `*.jks` — nunca no Git.
Um commit é para sempre (mesmo após revert, está no history).

---

## Para o próximo prompt de implementação

Quando for altura de começar a construir:

1. Referenciar este plano como contexto: `docs/android-migration/`
2. Começar pela **Fase 0** (backend API) OU **Fase 1A** (scaffold Android) — podem correr em paralelo
3. Seguir os **quick wins** do ficheiro 24 para momentum
4. Cada feature branch deve corresponder a um deliverable do roadmap (ficheiro 23)
5. Consultar o ficheiro 11 (backend gaps) para saber exatamente o que criar na API
6. Consultar o ficheiro 09 (modularização) para a estrutura exata de packages e módulos
7. Consultar o ficheiro 07 (stack) para versões exatas de bibliotecas

---

## Estrutura final deste plano

```
docs/android-migration/
├── README.md                           ← Índice principal
├── 01-estado-atual.md                  ← Análise do produto atual
├── 02-inventario-funcional.md          ← Todas as funcionalidades mapeadas
├── 03-mapeamento-funcional.md          ← Sistema atual → Android nativo
├── 04-fase-inicial.md                  ← O que migrar primeiro
├── 05-adaptacoes-nativas.md            ← Adaptações para mobile
├── 06-web-first-admin.md               ← O que fica web-only
├── 07-stack-android.md                 ← Stack recomendada
├── 08-arquitetura-android.md           ← Clean Architecture + MVI
├── 09-modularizacao.md                 ← Feature modules e packages
├── 10-backend-reutilizacao.md          ← O que reutilizar do Laravel
├── 11-backend-gaps.md                  ← O que criar no backend
├── 12-autenticacao-seguranca.md        ← Auth, biometria, GDPR, segurança
├── 13-offline-sync.md                  ← Offline, auto-save, sync
├── 14-realtime-chat-notificacoes.md    ← WebSocket, FCM, presença
├── 15-audio-media-uploads.md           ← Gravação, reprodução, uploads
├── 16-gamificacao-servidor.md          ← Flames, streaks, server-side
├── 17-estrategia-b2c-pro-corporate.md  ← App única, módulos condicionais
├── 18-setup-ambiente.md                ← Config de máquinas
├── 19-software-ferramentas.md          ← Tudo a instalar
├── 20-git-sincronizacao.md             ← Branch strategy, commits, sync
├── 21-segredos-env.md                  ← Secrets management
├── 22-bootstrap-novas-maquinas.md      ← Setup rápido
├── 23-roadmap-fases.md                 ← Fases 0-4 com deliverables
├── 24-quick-wins.md                    ← Vitórias rápidas
├── 25-riscos-decisoes.md               ← Riscos e decisões em aberto
└── 26-recomendacao-final.md            ← Este ficheiro
```

**27 ficheiros. ~8000+ linhas de planeamento. Zero linhas de código alterado.**

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-26-01 | Documentação fica outdated durante implementação (código diverge do plano) | Alta | Médio | Atualizar docs quando implementação diverge do plano. Incluir updates na PR checklist. Usar este plano como guia, não como contrato rígido |
| RISK-26-02 | Analysis paralysis — excesso de planeamento impede o início da implementação | Média | Alto | O plano está completo. A próxima ação é implementar QW-01 (Sanctum + login endpoint). Não adicionar mais documentação — começar a construir |

---

*Este plano foi criado com base na análise completa do repositório Lumina, incluindo toda a documentação estratégica existente (PLANO_ESTRATEGICO.md, PLANO_IMPLEMENTACAO.md, CHAT_ANALYSIS.md), os 32 modelos, 24+ controllers, 7 serviços, 85+ rotas, e toda a estrutura de views, events, notifications e jobs.*

*Pronto para implementação.*
