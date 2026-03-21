# 01 — Estado Atual do Produto e Arquitetura

## Stack tecnológica

| Componente | Tecnologia | Versão/Detalhe |
|-----------|-----------|----------------|
| Framework backend | Laravel | 12 |
| Linguagem backend | PHP | 8.2+ |
| Frontend templating | Blade | Integrado no Laravel |
| CSS framework | Tailwind CSS | Com PostCSS |
| Interatividade frontend | Alpine.js | Reatividade leve no browser |
| Base de dados | SQLite (dev) / PostgreSQL (prod) | WAL mode para concorrência |
| Cache | Database (dev) / Redis (prod) | Cache de comunidade, rooms, missões |
| Queue | Database driver | Jobs assíncronos (GDPR deletion) |
| Real-time | Laravel Reverb | WebSockets, 4 canais definidos |
| Frontend WebSocket | Laravel Echo | Client-side listener |
| Admin panel | Filament 5 | Backoffice completo |
| Autenticação | Laravel Breeze | Session-based, sem tokens |
| Email | SMTP (Brevo) | Notificações, verificação, summaries |
| AI/NLP | OpenAI GPT-4o-mini | Deteção de crise, sumarização, CBT |
| Música | Spotify oEmbed + iTunes API | Playlist comunitária |
| Push (web) | Web Push API (VAPID) | Parcialmente implementado |
| Build tools | Vite + npm | Asset compilation |
| Deployment | Docker + Railway | Dockerfile + nixpacks.toml |
| Icons | Remix Icons | Via CDN |
| Tipografia | Plus Jakarta Sans | Google Fonts |

## Infraestrutura e deployment

- **Docker**: Dockerfile presente com Nginx + PHP-FPM
- **Railway**: Plataforma de hosting atual
- **Supervisor**: Gestão de processos (queues, reverb)
- **GitHub Actions**: CI/CD configurado (`.github/`)
- **Nixpacks**: Configuração para deploy serverless (`nixpacks.toml`)

## Dimensão do projeto

| Métrica | Valor |
|---------|-------|
| Modelos (Eloquent) | 32 |
| Controllers | 24+ |
| Serviços | 7 |
| Events (broadcasting) | 7 |
| Notifications | 6 |
| Jobs (queue) | 1 |
| Console commands | 5 |
| Migrations | 57-68 |
| Blade views | ~90 |
| Middleware custom | 4 (CheckBanned, EnsureOnboarding, Therapist, Corporate) |
| Seeders | 6 |
| Form Requests | 2 |
| API Resources | 0 (não existem) |

## Modelos de dados (32)

### Core
- **User** — 42+ campos fillable, roles (admin/moderator/user/therapist), pseudónimo via SHA-256, acessibilidade, gamificação, privacidade
- **Company** — Entidade B2B para programas corporate
- **Therapist** — Profissionais de saúde mental (portal PRO)

### Comunidade / Fórum
- **Post** — Publicações com `title`, `content`, `audio_path`, `tag`, `is_sensitive`, `risk_level`, `sentiment`, `ai_summary`
- **Comment** — Comentários threaded (`parent_id`)
- **PostReaction** — Reações emocionais (support, hug, candle, strength)
- **CommentReaction** — Reações em comentários
- **PostCheckin** — Estado emocional após leitura de conteúdo sensível
- **Report** — Reports polimórficos (posts, mensagens)
- **ModerationLog** — Audit trail de moderação

### Chat
- **Room** — Salas de chat (públicas, privadas, crisis mode)
- **Message** — Mensagens com `content` encriptado, `is_sensitive`, `is_anonymous`, `reply_to_id`
- **MessageReaction** — Reações em mensagens (hug, candle, ear)
- **MessageRead** — Read receipts (opt-in)

### Saúde emocional
- **DailyLog** — Diário emocional: `mood_level` (1-5), `tags`, `note`, `cbt_insight`
- **SelfAssessment** — PHQ-9 e GAD-7: `type`, `answers` (JSON), `total_score`, `severity`

### Gamificação
- **Achievement** — Badges/marcos (pivot `user_achievements`)
- **Mission** — Missões diárias (3 por dia): diary, comment, reaction, post

### Buddy system
- **BuddySession** — Sessões 1-on-1: `user_id`, `buddy_id`, `room_id`, `status`, `evaluation`
- **BuddyApplication** — Candidaturas a buddy

### Funcionalidades especializadas
- **Milestone** — Timeline pessoal de marcos
- **VaultItem** — Cofre pessoal de técnicas de coping
- **PlaylistSong** — Playlist comunitária (Spotify/iTunes)
- **Resource** — Biblioteca de recursos com votação
- **WallPost** — Galeria artística (imagens)
- **PactPrompt** / **PactAnswer** — Pacto comunitário diário
- **ExperienceConnection** — Matching por experiências partilhadas

### Infraestrutura
- **AnalyticsEvent** — Tracking de uso (opt-out)
- **DataAccessLog** — Audit trail GDPR
- **FeatureFlag** — A/B testing e feature toggles

### Scopes
- **ShadowbanScope** — Global scope que oculta conteúdo de utilizadores shadowbanned

## Controllers e métodos (resumo)

| Controller | Métodos públicos | Notas |
|-----------|-----------------|-------|
| ForumController | 19 | O mais complexo. ~30% retorna JSON para AJAX |
| CalmZoneController | 14 | Exercícios somáticos, vault, playlist, sons |
| ChatController | 12 | Real-time via Reverb, moderação, crisis mode |
| ProfileController | 11 | Perfil, energia, safety plan, milestones, passaporte |
| BuddyController | 6 | Dashboard, request, apply, accept, escalate, evaluate |
| SelfAssessmentController | 4 | PHQ-9, GAD-7 com scoring e histórico |
| OnboardingController | 3 | Wizard 3 passos com routing contextual |
| TherapistController | 3 | Dashboard PRO, missões, somatic sync |
| LibraryController | 3 | Recursos com votação comunitária |
| DailyLogController | 2 | Diário + CBT analysis |
| DashboardController | 1 | Agregação complexa de dados |
| WallController | 2 | Galeria com upload de imagens |
| SearchController | 1 (+3 privados) | Pesquisa agregada |
| PrivacyController | 3 | GDPR: export, hibernate |
| CorporateController | 1 | Dashboard B2B |

## Serviços (7)

| Serviço | Responsabilidade |
|---------|-----------------|
| CBTAnalysisService | Deteção de crise (3 camadas: keywords → intent → GPT), análise CBT, sumarização |
| GamificationService | Flames, streaks, missões, achievements, desafios gentis |
| RecommendationService | Posts e recursos recomendados por tags emocionais |
| ExperienceMatchingService | Matching de pares por experiências partilhadas |
| AnalyticsService | Logging de eventos de uso |
| EncryptionService | Encriptação AES-256 para dados sensíveis |
| FeatureFlagService | Feature toggles e A/B testing |

## Broadcasting e eventos (7)

| Evento | Canal | Propósito |
|--------|-------|-----------|
| MessageSent | `private-chat.{roomId}` | Nova mensagem no chat |
| MessageReacted | `private-chat.{roomId}` | Reação adicionada |
| MessageDeleted | `private-chat.{roomId}` | Mensagem removida |
| MessageUpdated | `private-chat.{roomId}` | Mensagem editada |
| MessageRead | `private-chat.{roomId}` | Read receipt |
| RoomStatusUpdated | `private-chat.{roomId}` | Crisis mode toggle |
| SomaticSyncTriggered | `private-session.{sessionId}` | Exercício somático B2B |

## Canais WebSocket (4)

| Canal | Tipo | Autorização |
|-------|------|------------|
| `App.Models.User.{id}` | Private | user.id === id |
| `chat.{roomId}` | Presence | Room existe + acesso permitido |
| `silent-room` | Presence | Qualquer user autenticado |
| `session.{sessionId}` | Private | Participante ou terapeuta |

## Notificações (6)

| Notificação | Canais | Propósito |
|------------|--------|-----------|
| ForumInteraction | database + mail | Reações, comentários |
| GentleChallengeReceived | database + mail | Desafio de apoio recebido |
| WeeklyEmotionalSummary | database + mail | Resumo semanal (domingos 10h) |
| ModeratorCrisisAlert | database + mail | Alerta de crise para moderadores |
| GentleReEngagement | database + mail | Reativação gentil |
| QueuedVerifyEmail | mail | Verificação de email |

## Estado de maturidade por módulo

| Módulo | Estado | Notas |
|--------|--------|-------|
| Autenticação (Breeze) | ✅ Estável | Session-based, sem tokens API |
| Onboarding | ✅ Estável | 3 passos, routing contextual |
| Dashboard | ✅ Estável | Agregação de dados, insights AI |
| Fórum (Mural) | ✅ Estável | Posts, comentários, reações, moderação, AI summary |
| Chat (Fogueira) | ✅ Estável | Real-time via Reverb, crisis mode, moderação |
| Diário | ✅ Estável | Mood tracking, CBT insights |
| Perfil (Santuário) | ✅ Estável | Settings, milestones, passaporte |
| Buddy system | ✅ Estável | Sessões, candidaturas, avaliação |
| Zona Calma | ✅ Estável | 8+ exercícios, vault, playlist, sons |
| Biblioteca | ✅ Estável | Recursos com votação |
| Self-assessment | ✅ Estável | PHQ-9, GAD-7 com scoring |
| The Wall | ✅ Estável | Galeria com upload |
| Privacidade/GDPR | ✅ Estável | Export, delete, hibernate |
| Gamificação | ✅ Estável | Flames, streaks, missions, achievements |
| Admin (Filament) | ✅ Estável | Backoffice completo |
| Acessibilidade | ⚠️ Parcial | Dyslexic font, text size, reduced motion — toggle parcial |
| NLP/Crise | ⚠️ Parcial | 3 camadas funcionais, Layer 2 pode expandir |
| PWA/Offline | ❌ Não existe | Sem service worker |
| API REST | ❌ Não existe | Sem routes/api.php, sem Resources, sem Sanctum |
| Push nativo | ❌ Não existe | VAPID parcial, sem FCM |
| E2E encryption | ❌ Não existe | Chaves existem no modelo, implementação pendente |

## Documentação existente

| Documento | Linhas | Conteúdo |
|-----------|--------|----------|
| PLANO_ESTRATEGICO.md | ~900 | Visão, 28 gaps (GAP-01 a GAP-28), melhorias arquiteturais, preparação para AI |
| PLANO_IMPLEMENTACAO.md | ~630 | Estado de cada módulo, funcionalidades em falta, 4 fases de dev |
| CHAT_ANALYSIS.md | ~360 | Análise do chat, 20 bugs, 14 melhorias propostas |

## Conclusões para a migração Android

### O que está forte
- Lógica de negócio bem definida nos Services e Controllers
- Modelos de dados maduros com 57+ migrations
- Real-time funcional via Reverb
- Gamificação e crisis detection implementados
- GDPR e privacidade considerados desde o início

### O que precisa de trabalho significativo
- **Camada API inexistente** — É o gap mais crítico. Não existe `routes/api.php`, não existem API Resources, não existe autenticação por tokens.
- **Autenticação session-based** — Incompatível com mobile nativo. Precisa de Sanctum.
- **Respostas mistas** — Controllers retornam views E JSON de forma inconsistente. Precisam de separação clara.
- **Sem paginação cursor-based** — Paginação atual é offset-based, inadequada para feeds mobile.
- **Sem versionamento de API** — Necessário para evoluir backend sem quebrar a app.
- **Uploads não preparados para mobile** — Sem pre-signed URLs, sem endpoints dedicados.
- **Push notifications** — VAPID parcial, FCM inexistente.

---

*Próximo: [02-inventario-funcional.md](02-inventario-funcional.md) — Inventário detalhado de todas as funcionalidades.*
