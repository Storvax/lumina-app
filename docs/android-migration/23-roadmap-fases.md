# 23 — Roadmap de Execução por Fases

## Visão geral

```
Fase 0: Backend API Layer     ← Pré-requisito (backend)
Fase 1A: Fundação Android     ← Scaffold, auth, networking, design system
Fase 1B: Core Android         ← Dashboard, diário, zona calma, perfil, gamificação
Fase 2: Comunidade            ← Fórum, biblioteca, auto-avaliação, wall, pesquisa, FCM
Fase 3: Real-time             ← Chat, buddy system, WebSocket nativo
Fase 4: Avançado              ← PRO mobile (condicional), sons, playlist, refinamentos
```

---

## Fase 0 — Backend API Layer

### Objetivo
Criar a camada API no backend Laravel que a app Android vai consumir. Sem esta fase, nenhuma funcionalidade Android é possível.

### Deliverables

| Item | Descrição | Ficheiros |
|------|-----------|----------|
| Sanctum | Instalar, configurar, migration | composer.json, config/auth.php, User model |
| routes/api.php | Rotas v1 protegidas por Sanctum | routes/api.php (novo) |
| Auth endpoints | Register, login, logout, forgot-password | Api/V1/AuthController.php |
| Dashboard endpoint | Dados agregados | Api/V1/DashboardController.php |
| Diary endpoints | GET/POST | Api/V1/DiaryController.php |
| Profile endpoints | GET/PATCH | Api/V1/ProfileController.php |
| Calm Zone endpoints | Vault CRUD, safety plan | Api/V1/CalmZoneController.php |
| Missions endpoint | GET | Api/V1/MissionController.php |
| Notifications endpoint | GET, mark-read | Api/V1/NotificationController.php |
| API Resources | UserResource, DailyLogResource, etc. | app/Http/Resources/ |
| Form Requests | Validação para cada endpoint | app/Http/Requests/Api/ |
| Error handler | JSON errors para requests API | bootstrap/app.php |
| Rate limiting | Limites para API | AppServiceProvider |
| FCM token field | Migration para users.fcm_token | database/migrations/ |

### Critérios de conclusão
- [ ] `POST /api/v1/auth/login` retorna token Sanctum
- [ ] `GET /api/v1/dashboard` retorna JSON com dados agregados
- [ ] `POST /api/v1/diary` cria entrada e retorna com CBT insight
- [ ] `GET /api/v1/profile` retorna perfil do utilizador
- [ ] `GET /api/v1/calm-zone/vault` retorna items do cofre
- [ ] `GET /api/v1/missions` retorna missões do dia
- [ ] Erros retornam JSON padronizado (401, 403, 404, 422, 500)
- [ ] Rate limiting funcional
- [ ] Testes básicos para cada endpoint

### Dependências
- Nenhuma (pode começar imediatamente)

---

## Fase 1A — Fundação Android

### Objetivo
Scaffold do projeto Android com infraestrutura técnica resolvida: auth, networking, navegação, design system, base de dados local.

### Deliverables

| Item | Descrição |
|------|-----------|
| Projeto Gradle | Multi-module com version catalog |
| core-network | Retrofit + OkHttp + interceptors + error handling |
| core-database | Room DB com schema inicial |
| core-auth | TokenManager + EncryptedSharedPreferences + SessionManager |
| core-ui | LuminaTheme (Material 3), cores, tipografia, componentes base |
| core-domain | Base entities, Result wrapper |
| core-common | Extensions, utilities, ConnectivityMonitor |
| feature-auth | Login, register, forgot-password |
| Navigation | NavHost principal com auth flow |
| CI | GitHub Actions build + test |

### Critérios de conclusão
- [ ] App compila e corre no emulador
- [ ] Login contra backend retorna e guarda token
- [ ] Registo funcional
- [ ] Navegação entre auth → main screen funciona
- [ ] Design system aplicado (cores, tipografia Lumina)
- [ ] Error handling consistente com mensagens PT-PT
- [ ] CI build passa

### Dependências
- Fase 0 (endpoints de auth devem existir)

---

## Fase 1B — Core Android

### Objetivo
Entregar uma app funcional com as funcionalidades mais importantes: dashboard, diário, zona calma, perfil, gamificação.

### Deliverables

| Item | Módulo | Descrição |
|------|--------|-----------|
| Onboarding | feature-onboarding | Wizard 3 passos com animações |
| Dashboard | feature-dashboard | Mood, flames, streak, missões, insight AI |
| Diário | feature-diary | Mood selector, tags, nota, CBT insight, histórico, auto-save |
| Zona Calma hub | feature-calm-zone | Menu de exercícios |
| Grounding | feature-calm-zone | 5-4-3-2-1 (offline) |
| Respiração | feature-calm-zone | Animação + vibração (offline) |
| Heartbeat | feature-calm-zone | Body scan + vibração (offline) |
| Plano de crise | feature-calm-zone | Contactos + chamada direta (offline) |
| Cofre | feature-calm-zone | CRUD de técnicas (sync) |
| Perfil | feature-profile | Ver + editar + logout |
| Gamificação | feature-gamification | Flames display, missões, streaks |
| Safe House | core-ui | Double-tap exit rápido |
| Night mode | core-ui | Warm colors 21h-05h |
| Bottom nav | app | 5 tabs com navegação aninhada |

### Critérios de conclusão
- [ ] Onboarding completo com routing contextual
- [ ] Dashboard mostra dados reais do backend
- [ ] Diário: criar, ver histórico, auto-save funcional
- [ ] Zona Calma: grounding, respiração, heartbeat funcionam offline
- [ ] Plano de crise acessível offline com botão de chamada
- [ ] Cofre: CRUD funcional com sync
- [ ] Perfil: ver e editar
- [ ] Flames e streaks atualizados após ações
- [ ] Missões visíveis com progresso
- [ ] Safe House funcional
- [ ] Offline: diário com auto-save, zona calma funcional sem rede
- [ ] Pull-to-refresh em dashboard e diário
- [ ] Acessibilidade: TalkBack navegável, touch targets 48dp+

### Dependências
- Fase 1A (infraestrutura Android)
- Fase 0 (endpoints API)

---

## Fase 2 — Comunidade

### Objetivo
Migrar o Mural da Esperança (fórum), biblioteca, auto-avaliação, The Wall, pesquisa, e implementar push notifications.

### Deliverables

| Item | Módulo | Descrição |
|------|--------|-----------|
| Feed de posts | feature-forum | LazyColumn com cursor pagination |
| Post detail | feature-forum | Post + comentários threaded |
| Criar post (texto) | feature-forum | Com draft auto-save + crisis detection |
| Criar post (áudio) | feature-forum | Gravação nativa + upload |
| Reações | feature-forum | Picker com haptic |
| Comentários | feature-forum | Threaded com reply |
| Conteúdo sensível | feature-forum | Blur + revelação intencional |
| AI summary | feature-forum | Sumarização via API |
| Report | feature-forum | Bottom sheet |
| Save/bookmark | feature-forum | Toggle com sync |
| Biblioteca | feature-library | Lista + votar + sugerir |
| Auto-avaliação | feature-assessment | PHQ-9, GAD-7 step-by-step |
| The Wall | feature-wall | Galeria StaggeredGrid + upload |
| Pesquisa global | feature-search | SearchBar com sugestões |
| FCM | core-network | Push notifications |
| Notification channels | app | Crisis, community, wellness, missions |

### Novos endpoints API necessários

| Endpoint | Método |
|----------|--------|
| `GET /api/v1/forum/posts` | GET (cursor paginated) |
| `POST /api/v1/forum/posts` | POST (multipart: text + audio) |
| `GET /api/v1/forum/posts/{id}` | GET |
| `PATCH /api/v1/forum/posts/{id}` | PATCH |
| `DELETE /api/v1/forum/posts/{id}` | DELETE |
| `POST /api/v1/forum/posts/{id}/react` | POST |
| `POST /api/v1/forum/posts/{id}/comment` | POST |
| `POST /api/v1/forum/posts/{id}/report` | POST |
| `POST /api/v1/forum/posts/{id}/save` | POST |
| `POST /api/v1/forum/posts/{id}/subscribe` | POST |
| `POST /api/v1/forum/posts/{id}/summarize` | POST |
| `GET /api/v1/library` | GET |
| `POST /api/v1/library/suggest` | POST |
| `POST /api/v1/library/{id}/vote` | POST |
| `GET /api/v1/assessments` | GET |
| `POST /api/v1/assessments/{type}` | POST |
| `GET /api/v1/assessments/{id}` | GET |
| `GET /api/v1/wall` | GET |
| `POST /api/v1/wall` | POST (multipart) |
| `GET /api/v1/search` | GET |
| `POST /api/v1/push/register` | POST |
| `POST /api/v1/push/unregister` | POST |

### Critérios de conclusão
- [ ] Feed de posts com scroll infinito e pull-to-refresh
- [ ] Criar post com texto e/ou áudio (60s)
- [ ] Reações, comentários, save, subscribe funcionais
- [ ] Conteúdo sensível com blur
- [ ] Biblioteca com votação
- [ ] PHQ-9 e GAD-7 step-by-step com resultado
- [ ] The Wall com upload de imagens
- [ ] Pesquisa global funcional
- [ ] Push notifications via FCM
- [ ] Notification channels configurados

### Dependências
- Fase 1B (app base funcional)
- Endpoints API de fórum, biblioteca, assessment, wall, search

---

## Fase 3 — Real-time

### Objetivo
Implementar chat em tempo real (A Fogueira) e buddy system.

### Deliverables

| Item | Módulo | Descrição |
|------|--------|-----------|
| WebSocket client | core-network | OkHttp WebSocket + reconnect |
| Broadcasting auth | core-network | Auth de canais via Sanctum |
| Room list | feature-chat | Lista de salas públicas |
| Chat UI | feature-chat | Mensagens em tempo real, lazy column invertido |
| Enviar mensagem | feature-chat | Texto + anónimo + sensível |
| Reply (swipe) | feature-chat | Swipe-to-reply |
| Editar/apagar | feature-chat | Long-press actions |
| Reações | feature-chat | Long-press picker + haptic |
| Read receipts | feature-chat | Opt-in, indicators |
| Presença | feature-chat | Online dots via presence channel |
| Typing indicator | feature-chat | Throttled |
| Crisis mode | feature-chat | 15s slow mode |
| Silent room | feature-chat | View-only mode |
| Moderação | feature-chat | Mute, pin, delete (moderators) |
| Buddy dashboard | feature-buddy | Overview + pedidos |
| Request buddy | feature-buddy | Ação + matching |
| Buddy chat | feature-buddy | Reutiliza feature-chat (sala privada) |
| Evaluar sessão | feature-buddy | Rating |

### Novos endpoints API necessários

| Endpoint | Método |
|----------|--------|
| `GET /api/v1/rooms` | GET |
| `GET /api/v1/rooms/{slug}/messages` | GET (cursor paginated) |
| `POST /api/v1/rooms/{id}/messages` | POST |
| `PATCH /api/v1/rooms/{id}/messages/{msgId}` | PATCH |
| `DELETE /api/v1/messages/{id}` | DELETE |
| `POST /api/v1/messages/{id}/react` | POST |
| `POST /api/v1/rooms/{id}/read` | POST |
| `POST /api/v1/messages/{id}/report` | POST |
| `POST /api/v1/rooms/{id}/crisis` | POST |
| `GET /api/v1/buddy/dashboard` | GET |
| `POST /api/v1/buddy/request` | POST |
| `POST /api/v1/buddy/apply` | POST |
| `POST /api/v1/buddy/{sessionId}/accept` | POST |
| `POST /api/v1/buddy/{sessionId}/escalate` | POST |
| `POST /api/v1/buddy/{sessionId}/evaluate` | POST |
| WebSocket auth endpoint | POST `/broadcasting/auth` (Sanctum) |

### Critérios de conclusão
- [ ] WebSocket conecta ao Reverb e recebe mensagens em tempo real
- [ ] Chat UI responsivo e fluido
- [ ] Enviar, editar, apagar mensagens funcional
- [ ] Reações e replies funcionais
- [ ] Crisis mode ativa slow mode 15s
- [ ] Presença online visível
- [ ] Reconexão automática após perda de rede
- [ ] Buddy system: request, accept, chat privado, evaluate
- [ ] Moderação: mute, pin, delete (para moderators)

### Dependências
- Fase 2 (forum + FCM como base de community features)
- WebSocket auth via Sanctum no backend
- Broadcasting endpoint compatível com tokens

---

## Fase 4 — Avançado

### Objetivo
Refinamentos, funcionalidades avançadas, e possível módulo PRO.

### Deliverables (candidatos, por prioridade)

| Item | Prioridade | Notas |
|------|-----------|-------|
| Sons relaxantes (mixer) | Alta | ExoPlayer multi-stream + foreground service |
| Combustão/venting | Média | Animação Canvas |
| Reflexão AI ("eu futuro") | Média | Chat-like UI |
| Playlist comunitária | Média | Spotify deep links |
| Pacto comunitário | Média | Card diário |
| Passaporte emocional (PDF) | Média | Server-side PDF + download |
| Dark mode toggle | Alta | Material 3 Dynamic Color |
| Biometria para login | Alta | BiometricPrompt |
| Certificate pinning | Média | OkHttp CertificatePinner |
| Widget Android (missões) | Baixa | Glance framework |
| PRO mobile (terapeuta) | Condicional | Depende de procura real |
| Wearable integration | Baixa | Wear OS companion (futuro distante) |

### Critérios para lançar PRO mobile
- Pelo menos 50 terapeutas ativos na plataforma
- Feedback positivo de 5+ terapeutas sobre necessidade mobile
- ROI justificável vs manter web-only

---

## Timeline estimada (referência, não compromisso)

```
Fase 0: ████████░░░░░░░░░░░░░░░░  Backend API
Fase 1A: ░░░░████████░░░░░░░░░░░░  Fundação Android
Fase 1B: ░░░░░░░░████████████░░░░  Core Android
Fase 2:  ░░░░░░░░░░░░░░████████░░  Comunidade
Fase 3:  ░░░░░░░░░░░░░░░░░░████░░  Real-time
Fase 4:  ░░░░░░░░░░░░░░░░░░░░████  Avançado
```

**Nota:** Fases podem ter overlap. Por exemplo, endpoints API da Fase 2 podem ser criados enquanto se finaliza Fase 1B.

---

*Próximo: [24-quick-wins.md](24-quick-wins.md) — Quick wins para momentum inicial.*
