# Plano Estratégico Lumina — Auditoria, Refatoração e Inovação

## Contexto

A Lumina é uma plataforma web de saúde mental (Laravel 11, PHP 8.2+, PostgreSQL, Tailwind CSS, Alpine.js, Filament PHP) com 40+ controllers, 32 models, 7 services, 70 migrations, 6 policies, 4 middleware customizados, API v1 com Sanctum, WebSocket via Reverb e PWA. A presente auditoria resulta de um varrimento exaustivo a toda a codebase web (excluindo `lumina-android/`) e identifica vulnerabilidades críticas, dívida técnica, oportunidades de refatoração e inovação para os segmentos B2C, PRO e B2B.

---

## A. 🚨 Auditoria Técnica e Segurança

### A.1 — Vulnerabilidades Críticas (P0)

#### SEC-01: Lookup de terapeuta por correspondência textual de nome
- **Ficheiro:** `app/Http/Controllers/TherapistController.php:21`
- **Também afecta:** `routes/channels.php:74`, `app/Policies/SelfAssessmentPolicy.php:40`
- **Problema:** `Therapist::where('name', 'like', '%' . Auth::user()->name . '%')->first()` — identifica o terapeuta por nome parcial. Se dois utilizadores tiverem nomes semelhantes, um pode aceder ao painel clínico do outro.
- **Impacto:** Acesso não autorizado a dados de pacientes.
- **Correção:** Adicionar coluna `user_id` à tabela `therapists` (migration), alterar query para `Therapist::where('user_id', Auth::id())->firstOrFail()`.

#### SEC-02: Tokens Sanctum sem expiração
- **Ficheiro:** `config/sanctum.php:50`
- **Problema:** `'expiration' => null` — tokens de API nunca expiram. Token comprometido = acesso perpétuo.
- **Correção:** Definir `'expiration' => 60 * 24 * 7` (7 dias) e implementar refresh token rotation.

#### SEC-03: WebSocket Reverb com allowed_origins wildcard
- **Ficheiro:** `config/reverb.php:88`
- **Problema:** `'allowed_origins' => ['*']` permite Cross-Site WebSocket Hijacking (CSWSH).
- **Correção:** Substituir por `[env('APP_URL')]` ou lista explícita de domínios.

#### SEC-04: ShadowbanScope ausente no modelo Message
- **Ficheiro:** `app/Models/Message.php` (não tem scope)
- **Referência:** `app/Models/Post.php` (tem ShadowbanScope)
- **Problema:** Utilizadores shadowbanned continuam a enviar mensagens visíveis no chat e a disparar notificações de crise.
- **Correção:** Aplicar `ShadowbanScope` ao modelo `Message` ou verificar `isShadowbanned()` no `ChatController::send()`.

#### FUNC-01: Job GDPR chama relação inexistente — CRASH
- **Ficheiro:** `app/Jobs/ProcessGdprDeletion.php:44`
- **Problema:** `$user->comments()->delete()` — o modelo `User` NÃO define relação `comments()`. Resulta em `BadMethodCallException`, impedindo a eliminação dos dados.
- **Impacto:** Violação do Artigo 17 do RGPD. Dados do utilizador NÃO são eliminados.
- **Correção:** Adicionar relação `comments()` ao modelo `User.php`. Adicionar também relações em falta: `messages()`, `reactions()`, `selfAssessments()`.

#### FUNC-02: Exportação RGPD incompleta
- **Ficheiro:** `app/Http/Controllers/PrivacyController.php:27`
- **Problema:** O export carrega apenas `dailyLogs`, `posts`, `comments`, `milestones`. Faltam: mensagens de chat, reações, itens do cofre, auto-avaliações, sessões de buddy, respostas do pacto.
- **Impacto:** Violação do Artigo 15 do RGPD (direito de acesso completo).
- **Correção:** Expandir `load()` para incluir todas as relações com dados pessoais.

### A.2 — Vulnerabilidades Altas (P1)

#### SEC-05: BuddyController expõe todos os pedidos pendentes
- **Ficheiro:** `app/Http/Controllers/BuddyController.php` (~linha 28)
- **Problema:** `BuddySession::where('status', 'pending')->get()` devolve TODOS os pedidos a qualquer buddy autenticado, revelando quem pediu apoio emocional.
- **Correção:** Filtrar por disponibilidade e não expor identidade até aceitação.

#### SEC-06: API v1 sem rate limiting
- **Ficheiro:** `routes/api.php`
- **Problema:** Nenhum middleware `throttle:` nas rotas API. Endpoints de escrita podem ser abusados por bots.
- **Correção:** Adicionar `middleware('throttle:api')` ao grupo protegido e throttles específicos para endpoints de escrita.

#### SEC-07: Ausência de 2FA para contas privilegiadas
- **Problema:** Admins, moderadores e terapeutas acedem a dados clínicos sem autenticação de dois fatores.
- **Correção:** Implementar TOTP obrigatório via `pragmarx/google2fa-laravel` para roles privilegiados.

#### SEC-08: Eliminação de perfil sem registo de auditoria
- **Ficheiro:** `app/Http/Controllers/ProfileController.php` (método `destroy`)
- **Correção:** Registar em `DataAccessLog` com tipo `account_deletion_initiated` antes do soft-delete.

#### FUNC-03: Job GDPR não elimina todos os dados
- **Ficheiro:** `app/Jobs/ProcessGdprDeletion.php`
- **Problema:** Não elimina: mensagens, reações, votos de playlist, itens do cofre, auto-avaliações, respostas do pacto, analytics, subscrições push, tokens Sanctum.
- **Correção:** Eliminar explicitamente todas as tabelas com `user_id`, ou garantir foreign keys com `ON DELETE CASCADE`.

### A.3 — Vulnerabilidades Médias (P2)

#### SEC-09: API register devolve token sem verificação de email
- **Ficheiro:** `app/Http/Controllers/Api/V1/AuthController.php`
- **Correção:** Devolver token com abilities limitadas (`['read-only']`) até verificação.

#### SEC-10: CORS não configurado explicitamente
- **Correção:** Publicar e configurar `config/cors.php` com `allowed_origins` restritos.

### A.4 — Performance (P1-P2)

#### PERF-01: N+1 Queries identificadas

| Localização | Problema | Prioridade |
|---|---|---|
| `ChatController::send()` ~linha 170 | `User::whereIn('role', ['admin', 'moderator'])->get()` em cada mensagem de crise | P1 |
| `ProfileController::show()` ~linha 52 | `Achievement::all()` em cada carregamento de perfil | P1 |
| `ForumController::comment()` ~linha 279 | `$post->subscribers()` sem eager loading | P2 |
| `ExperienceMatchingService` ~linhas 92-98 | Query de `room_visits`/`posts` por candidato (explosão quadrática) | P2 |
| `SendWeeklySummaries` ~linhas 37-42 | `PostReaction::whereHas()` por cada utilizador dentro de chunk | P2 |

**Correção global:** Cache Redis para moderadores (TTL 5min), eager loading em perfis, queries agregadas no matching.

#### PERF-02: Caching insuficiente
- Lista de moderadores, contagem de achievements, mood spiral de 30 dias, streak fallback — todos computados por request sem caching.

#### PERF-03: Índices de base de dados em falta
- `daily_logs(user_id, log_date)` composto
- `messages(user_id, room_id)` para slow mode
- `post_reactions(user_id, post_id, type)` único
- `analytics_events(event, created_at)` para conversion rate
- `notifications(notifiable_id, type, created_at)` para re-engagement

---

## B. 🛠️ Plano de Refatoração e Melhorias de Código

### B.1 — Extração de Services (Fat Controllers)

#### REF-01: Extrair ChatService + ModerationService do ChatController (401 linhas) [P1]
- `App\Services\ChatService`: envio, slow mode, deteção de crise, mute
- `App\Services\ModerationService`: toggle crisis, mute, pin, logs (reutilizável entre Forum e Chat)
- **Ficheiro:** `app/Http/Controllers/ChatController.php`

#### REF-02: Extrair ForumService do ForumController (577 linhas) [P1]
- `App\Services\ForumService`: store/update com NLP, notificações
- Migrar métodos `pact()` e `storePact()` para `PactController` dedicado
- **Ficheiro:** `app/Http/Controllers/ForumController.php`

#### REF-03: Extrair CalmZoneService do CalmZoneController (350+ linhas) [P2]
- `App\Services\PlaylistService`: suggest, vote, cover art
- `App\Services\AIReflectionService`: prompt de futuro

### B.2 — Model Scopes em Falta [P2]

| Pattern repetido | Scope proposto | Model |
|---|---|---|
| `->where('is_locked', false)` | `Post::unlocked()` | Post |
| `->whereNull('banned_at')` | `User::active()` | User |
| `->whereNull('hibernated_at')` | `User::notHibernated()` | User |
| `->where('is_private', false)->where('is_active', true)` | `Room::publicActive()` | Room |
| `->where('status', 'pending')` | `BuddySession::pending()` | BuddySession |

### B.3 — Relações em Falta no User Model [P1]

**Ficheiro:** `app/Models/User.php`

Relações que NÃO existem mas são chamadas em controllers/jobs:
- `comments()` — usado em `ProcessGdprDeletion` (CRASH) e `PrivacyController` (CRASH)
- `messages()` — necessário para GDPR e shadowban
- `reactions()` — necessário para GDPR export
- `selfAssessments()` — necessário para GDPR export

### B.4 — Type Hints e Return Types [P2]

Métodos sem return type em: `CalmZoneController`, `BuddyController`, `ChatController`, `ForumController`. Adicionar `declare(strict_types=1)` e return types `: View|JsonResponse|RedirectResponse` em todos os ficheiros PHP.

### B.5 — Testes Automatizados [P1]

Cobertura atual: <5% (apenas auth + profile boilerplate). Zero testes para services, API, WebSocket, notifications, jobs.

**Prioridade imediata:**
- `CBTAnalysisService` — deteção de crise e 3 camadas
- `GamificationService` — flames, streaks, missions
- `ProcessGdprDeletion` — eliminação completa
- `ChatController::send()` — slow mode, mute, crisis
- API v1 Auth — register, login, token
- Policies (todas as 6)

**Objetivo:** 80% cobertura em Services e 60% em Controllers em 3 meses.

---

## C. 💡 Inovação e Novas Funcionalidades

### C.1 — B2C (Utilizadores)

#### INOV-01: Análise de Tendências e Predição de Humor [P1]
Dashboard de insights com gráficos de tendência (7d, 30d, 90d), média móvel e avisos proativos quando tendência decrescente por 3+ dias. Usa dados existentes em `daily_logs`.
- Novo `App\Services\MoodTrendService`, rota `GET /perfil/tendencias`, view com Chart.js.

#### INOV-02: Journaling Guiado com Prompts Terapêuticos [P1]
Prompts contextuais baseados no humor: mood <= 2 (regulação emocional), mood >= 4 (gratidão), tag "ansiedade" (grounding cognitivo).
- Config `config/journal-prompts.php`. Baixa complexidade.

#### INOV-03: Auto-save de Rascunhos no Diário [P2]
O fórum já tem auto-save mas o diário não. Perder texto durante crise emocional é devastador.
- `localStorage` com Alpine.js (intervalo 10s). Apenas frontend.

#### INOV-04: Conteúdo de Meditação e Mindfulness [P2]
Secção na Zona Calma com meditações guiadas (áudio), body scans, visualizações.
- Novo modelo `Meditation`, controller, views, Filament resource.

#### INOV-05: Integração com Wearables [P3 — Fase 2]
Frequência cardíaca e sono via Apple Health / Google Fit. Fase inicial: import CSV/JSON.

### C.2 — Lumina PRO (Terapeutas)

#### INOV-06: Sistema de Notas Clínicas [P1]
Interface para notas clínicas encriptadas at-rest, com controlo de versões e timestamps. Apenas terapeuta atribuído acede.
- Novo modelo `ClinicalNote`, policy, controller. Reutiliza infraestrutura do `EncryptionService`.

#### INOV-07: Relatórios de Progresso do Paciente [P1]
Dashboard de evolução: humor, frequência, resultados PHQ-9/GAD-7, alertas de crise.
- Novo `PatientReportService`. Agrega dados existentes.

#### INOV-08: Agendamento de Consultas [P2]
Agenda básica: disponibilidade do terapeuta, marcação pelo paciente, lembretes push.
- Novo modelo `Appointment`, controller, notificações.

#### INOV-09: Videochamada Integrada [P2 — Fase 2]
Jitsi Meet ou Daily.co para sessões 1:1. Requer WebRTC.

### C.3 — B2B (Corporate)

#### INOV-10: Dashboard Organizacional Expandido [P1]
Evolução temporal, taxa de retorno, benchmarks anónimos inter-empresa, export PDF.
- Novo `CorporateAnalyticsService`.

#### INOV-11: Programas de Bem-Estar Configuráveis [P2]
Programas com duração definida (ex: "30 Dias de Mindfulness"), missões, métricas ROI.
- Novos modelos `WellnessProgram`, `WellnessProgramParticipant`.

#### INOV-12: Integração com Sistemas de RH [P3 — Fase 3]
Webhooks para SAP SuccessFactors, Workday, BambooHR.

---

## D. 🧹 Protocolo de Clean Code e Comentários

### D.1 — Regras de Limpeza de Comentários

Ao iniciar a implementação deste plano, será executada uma limpeza sistemática de comentários em todos os ficheiros tocados:

1. **APAGAR:** Comentários óbvios, robóticos, gerados por IA que descrevem o "O QUÊ" o código faz (ex: `// Obtém o utilizador`, `// Retorna a view`, `// Valida o input`)
2. **MANTER:** Comentários profissionais que explicam o "PORQUÊ" da lógica de negócio (ex: `// Delay envio de email para evitar SMTP rate limiting`, `// Camadas 1+2 sempre sobrepõem camada 3 para garantir que crises nunca são revertidas`)
3. **ADICIONAR:** PHPDoc em métodos públicos com `@param`, `@return` e descrição funcional em PT-PT
4. **FORMATO:** `declare(strict_types=1)` em todos os ficheiros PHP

### D.2 — Convenções de Nomenclatura

- **Código** (classes, métodos, variáveis, colunas): Inglês
- **UI, flash messages, notificações**: Português de Portugal
- **Comentários PHPDoc**: Português de Portugal
- **Route slugs**: Português (já consistente: `/mural`, `/diário`, `/zona-calma`)

### D.3 — Adoção de Enums PHP 8.1+ [P3]

Substituir strings mágicas por Enums nativos:
- `App\Enums\UserRole` (admin, moderator, therapist, hr_admin, user)
- `App\Enums\PostTag` (hope, vent, anxiety)
- `App\Enums\RiskLevel` (low, medium, high)
- `App\Enums\BuddyStatus` (pending, active, escalated, completed)
- `App\Enums\CommunityTemperature` (green, yellow, red)

### D.4 — Estrutura Proposta para Services

```
app/Services/
    AI/              CBTAnalysisService, AIReflectionService (novo)
    Analytics/       AnalyticsService, MoodTrendService (novo)
    Chat/            ChatService (novo), ModerationService (novo)
    Corporate/       CorporateAnalyticsService (novo)
    Encryption/      EncryptionService
    Feature/         FeatureFlagService
    Forum/           ForumService (novo)
    Gamification/    GamificationService
    Matching/        ExperienceMatchingService
    Playlist/        PlaylistService (novo)
    Recommendation/  RecommendationService
    Therapist/       PatientReportService (novo)
```

---

## E. 📋 Matriz de Execução

### Sprint 1 — Semanas 1-2: Segurança Crítica e RGPD (~14h)

| ID | Tarefa | P | Esforço | Estado |
|---|---|---|---|---|
| SEC-01 | Corrigir lookup terapeuta (user_id) | P0 | 2h | ✅ |
| SEC-04 | Shadowban silencioso no ChatController::send() | P0 | 1h | ✅ |
| SEC-02 | Sanctum token expiration (7 dias) | P0 | 30min | ✅ |
| SEC-03 | Reverb allowed_origins via APP_URL | P0 | 30min | ✅ |
| FUNC-01 | Relações comments/messages/reactions/selfAssessments no User | P0 | 2h | ✅ |
| FUNC-02 | Expandir GDPR export completo | P0 | 3h | ✅ |
| FUNC-03 | GDPR deletion — tokens, messages, analytics, push, pivots | P1 | 3h | ✅ |
| SEC-05 | Filtrar pending buddy sessions (anónimo, sem user_id) | P1 | 1h | ✅ |
| SEC-06 | Rate limiting API v1 (throttle:20,1 + throttle:api) | P1 | 1h | ✅ |
| SEC-08 | Audit trail DataAccessLog na eliminação de perfil | P1 | 30min | ✅ |

### Sprint 2 — Semanas 3-4: Performance e Refatoração Base (~24h) ✅

| ID | Tarefa | P | Esforço | Estado |
|---|---|---|---|---|
| PERF-01 | Cache moderadores + eager loading | P1 | 4h | ✅ |
| PERF-02 | Caching em endpoints críticos | P1 | 3h | ✅ |
| PERF-03 | Migration de índices | P2 | 2h | ✅ |
| REF-01 | Extrair ChatService + ModerationService | P1 | 8h | ✅ |
| REF-05 | Relações em falta no User | P1 | 1h | ✅ |
| TEST-01a | Testes CBTAnalysisService + GamificationService | P1 | 6h | ✅ |

### Sprint 3 — Semanas 5-6: Refatoração Profunda (~30h)

| ID | Tarefa | P | Esforço |
|---|---|---|---|
| REF-02 | Extrair ForumService + PactController | P1 | 6h |
| REF-04 | Model Scopes | P2 | 3h |
| REF-06 | Return types e strict_types | P2 | 4h |
| SEC-07 | 2FA para contas privilegiadas | P1 | 8h |
| SEC-08 | Audit trail eliminação perfil | P1 | 1h |
| TEST-01b | Testes GDPR, API Auth, BuddyController | P1 | 8h |

### Sprint 4 — Semanas 7-8: Inovação B2C (~24h)

| ID | Tarefa | P | Esforço |
|---|---|---|---|
| INOV-01 | Análise de tendências de humor | P1 | 12h |
| INOV-02 | Journaling guiado com prompts | P1 | 4h |
| INOV-03 | Auto-save no diário | P2 | 2h |
| FE-01 | Cores de erro suaves (rose em vez de red) | P2 | 2h |
| FE-02 | Fix focus styles acessibilidade | P2 | 2h |
| FE-03 | Service Worker cache dinâmico | P2 | 2h |

### Sprint 5 — Semanas 9-12: Lumina PRO e B2B (~44h)

| ID | Tarefa | P | Esforço |
|---|---|---|---|
| INOV-06 | Notas clínicas (terapeutas) | P1 | 16h |
| INOV-07 | Relatórios de progresso paciente | P1 | 12h |
| INOV-10 | Dashboard corporativo expandido | P1 | 12h |
| SEC-09 | Token abilities pré-verificação | P2 | 3h |
| SEC-10 | CORS config explícita | P2 | 1h |

### Sprint 6 — Semanas 13-16: Inovação Avançada (~64h)

| ID | Tarefa | P | Esforço |
|---|---|---|---|
| INOV-04 | Meditação e mindfulness | P2 | 16h |
| INOV-08 | Agendamento de consultas | P2 | 20h |
| INOV-11 | Programas de bem-estar B2B | P2 | 16h |
| REF-03 | Extrair CalmZoneService | P2 | 4h |
| CODE-04 | Enums PHP 8.1+ | P3 | 6h |
| CODE-03 | Reorganizar directório services | P2 | 2h |

### Backlog (Fase 2+)

- INOV-05: Integração wearables (depende de INOV-01)
- INOV-09: Videochamada integrada (depende de INOV-08)
- INOV-12: Integração HR systems (depende de INOV-11)
- Full-text search com Meilisearch
- i18n multi-idioma (EN, ES)

---

## Verificação

Para validar as correções após implementação:

1. **Segurança:** Executar `php artisan test` após cada sprint. Testar manualmente: login como terapeuta e verificar que só acede aos seus pacientes; tentar enviar mensagem com utilizador shadowbanned; verificar que tokens Sanctum expiram após 7 dias.
2. **RGPD:** Criar utilizador de teste, gerar dados em todas as tabelas, executar export (`GET /privacidade/exportar`) e confirmar que TODOS os tipos de dados aparecem. Executar eliminação e verificar que todas as tabelas foram purgadas.
3. **Performance:** Usar Laravel Debugbar para confirmar 0 N+1 queries nas páginas de perfil, chat e fórum. Verificar que endpoints cacham correctamente com `Cache::has()`.
4. **Testes:** `php artisan test --coverage` deve reportar >= 60% nos services e >= 40% nos controllers após Sprint 3.

---

*Gerado em 2026-03-31 | Auditoria completa à codebase web da Lumina*
