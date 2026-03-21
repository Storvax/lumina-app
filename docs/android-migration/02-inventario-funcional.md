# 02 — Inventário Funcional Completo

## Contexto

Este documento é um inventário rota a rota do sistema Lumina atual. Serve de referência
para decidir quais os endpoints a criar na camada API Android e para identificar o que
precisa de reconceção.

Todas as rotas listadas são de `routes/web.php` e `routes/auth.php` (session-based).
Não existe `routes/api.php` — essa camada ainda não existe.

Cada secção indica o **perfil primário** de utilizador (B2C, PRO, Corporate, Admin/Mod,
Público) para facilitar a priorização Android.

---

## Convenções deste documento

- **Resposta**: Tipo de resposta atual do controller (`view` = HTML, `json` = JSON, `redirect` = redirect, `broadcast` = WebSocket event, `file` = download)
- **Middleware**: Middleware aplicado à rota
- **Rate limit**: Grupo de throttling aplicado
- **AI**: Usa integração com OpenAI
- **RT**: Emite evento real-time via broadcasting

---

## 0. Rotas públicas (sem autenticação)

**Perfil:** Público (qualquer visitante)

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Landing page | `GET /` | HomeController@index | view | Página pública de entrada |
| Offline fallback | `GET /offline` | — | view | Placeholder PWA; service worker não implementado |
| Lista de salas de chat | `GET /fogueira` | RoomController@index | view | **Ambiguidade:** pública sem auth — ver secção de ambiguidades |
| Relatório de impacto | `GET /comunidade/impacto` | CommunityReportController@index | view | Métricas anónimas públicas |

---

## 1. Autenticação e onboarding

**Perfil:** B2C (todos os utilizadores)

### Autenticação (Laravel Breeze)

| Funcionalidade | Rota | Método | Resposta | Middleware |
|---------------|------|--------|----------|-----------|
| Registo | `POST /register` | RegisteredUserController@store | redirect | guest |
| Login | `POST /login` | AuthenticatedSessionController@store | redirect | guest |
| Logout | `POST /logout` | AuthenticatedSessionController@destroy | redirect | auth |
| Forgot password | `POST /forgot-password` | PasswordResetLinkController@store | redirect | guest |
| Reset password | `POST /reset-password` | NewPasswordController@store | redirect | guest |
| Verify email | `GET /verify-email/{id}/{hash}` | VerifyEmailController | redirect | auth, signed |
| Confirm password | `POST /confirm-password` | ConfirmablePasswordController@store | redirect | auth |

### Onboarding

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Wizard (3 passos) | `GET /bem-vindo` | OnboardingController@index | view | Intenção, humor, preferência |
| Guardar respostas | `POST /bem-vindo` | OnboardingController@store | redirect | Routing contextual (crise→zona calma, etc.) |
| Tour completado | `POST /api/user/tour-completed` | OnboardingController@markTourCompleted | json | Único endpoint em `/api/` |

---

## 2. Dashboard

**Perfil:** B2C

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Página principal | `GET /dashboard` | DashboardController@index | view | Agregação: mood, flames, streaks, missions, insights AI |

**Dados agregados no dashboard:**
- Último mood level do diário
- Flames e streak atuais
- Missões do dia (cache 10min)
- Achievements recentes
- Posts recomendados
- Insight personalizado (AI, cache 24h)
- Notificações não lidas
- Resumo semanal

---

## 3. Fórum — Mural da Esperança

**Perfil:** B2C (posts, reações, comentários) + Admin/Mod (pin, lock, shadowban)

| Funcionalidade | Rota | Método | Resposta | Rate limit | Notas |
|---------------|------|--------|----------|-----------|-------|
| Listar posts | `GET /mural` | ForumController@index | view/json | — | Dual-mode: view ou JSON paginado |
| Ver post | `GET /mural/{post}` | ForumController@show | view | — | + comentários threaded + posts relacionados |
| Criar post | `POST /mural/criar` | ForumController@store | json | content-creation | AI: crisis detection 3 camadas |
| Editar post | `PATCH /mural/{post}` | ForumController@update | json | — | Apenas autor |
| Apagar post | `DELETE /mural/{post}` | ForumController@destroy | json | — | Apenas autor ou mod |
| Reagir a post | `POST /mural/{post}/reagir` | ForumController@react | json | — | Toggle (support/hug/candle/strength) |
| Comentar | `POST /mural/{post}/comentar` | ForumController@comment | redirect | content-creation | Suporta threading via parent_id |
| Reagir a comentário | `POST /comentarios/{comment}/reagir` | ForumController@reactToComment | json | — | |
| Marcar útil | `POST /comentarios/{comment}/util` | ForumController@markHelpful | redirect | — | |
| Report | `POST /mural/{post}/report` | ForumController@report | json | reports | Polimórfico |
| Guardar post | `POST /mural/{post}/save` | ForumController@toggleSave | json | — | Toggle |
| Subscrever | `POST /mural/{post}/subscrever` | ForumController@toggleSubscription | json | — | Toggle |
| Check-in emocional | `POST /mural/{post}/checkin` | ForumController@postCheckin | json | — | Após leitura de conteúdo sensível |
| Sumarizar (AI) | `POST /mural/{post}/summarize` | ForumController@summarize | json | — | GPT-4o-mini summary |
| Pin post | `PATCH /mural/{post}/pin` | ForumController@togglePin | redirect | — | Moderadores |
| Lock post | `PATCH /mural/{post}/lock` | ForumController@toggleLock | redirect | — | Moderadores |
| Shadowban user | `POST /users/{user}/shadowban` | ForumController@shadowbanUser | json | reports | Moderadores |

**Tags de post disponíveis:** vent, hope, anxiety, win, question, art, gratitude, music, reflection

**Campos especiais do Post:**
- `audio_path` — Publicações de áudio (até 60s)
- `is_sensitive` — Blur automático + revelação intencional
- `risk_level` — high/medium/low (classificação AI)
- `sentiment` — positive/neutral/distress
- `ai_summary` — Resumo gerado por AI

---

## 4. Chat — A Fogueira

**Perfil:** B2C (mensagens, reações) + Admin/Mod (mute, pin, crisis mode)

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Listar salas | `GET /fogueira` | RoomController@index | view | Salas públicas |
| Sala silêncio | `GET /salas/silencio` | RoomController@silentRoom | view | Observação sem participação |
| Entrar na sala | `GET /sala/{room:slug}` | ChatController@show | view | Mensagens + presença |
| Enviar mensagem | `POST /chat/{room}/message` | ChatController@send | json + RT | Crisis detection ativo |
| Editar mensagem | `PATCH /chat/{room}/message/{message}` | ChatController@updateMessage | json + RT | Apenas autor |
| Apagar mensagem | `DELETE /chat/messages/{message}` | ChatController@destroyMessage | json + RT | Autor ou mod |
| Reagir a mensagem | `POST /chat/{room}/message/{message}/react` | ChatController@react | json + RT | hug/candle/ear |
| Marcar como lido | `POST /chat/{room}/read` | ChatController@markAsRead | json + RT | Opt-in |
| Report mensagem | `POST /chat/messages/{message}/report` | ChatController@reportMessage | json | reports |
| Mute user | `POST /chat/{room}/mute/{targetUser}` | ChatController@muteUser | json | Moderadores |
| Pin mensagem | `POST /chat/{room}/pin` | ChatController@pinMessage | redirect | Moderadores |
| Presença alert | `POST /chat/{room}/follow/{targetUser}` | ChatController@togglePresenceAlert | json | Toggle |
| Crisis mode | `POST /chat/{room}/crisis` | ChatController@toggleCrisisMode | json + RT | Slow mode 15s |
| Toggle view mode | `POST /chat/preferences/mode` | ChatController@toggleViewMode | json | compact/comfortable |

**Características real-time:**
- MessageSent, MessageReacted, MessageDeleted, MessageUpdated, MessageRead (broadcast events)
- RoomStatusUpdated (crisis mode)
- Slow mode: 3s normal, 15s crisis
- Presença online via canais Presence
- Mensagens anónimas (opcionais)
- Conteúdo sensível com warning

---

## 5. Diário — O Teu Diário

**Perfil:** B2C (privado por utilizador)

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Ver diário + histórico | `GET /diario` | DailyLogController@index | view | Hoje + últimos 7 dias |
| Guardar entrada | `POST /diario` | DailyLogController@store | redirect | AI: CBT analysis + gamification trigger |

**Campos do DailyLog:**
- `mood_level` (1-5)
- `tags` (array de emoções)
- `note` (texto livre)
- `cbt_insight` (gerado por AI)
- `log_date` (1 por dia)

**Lógica associada:**
- Auto-purge baseado em `diary_retention_days` do user
- CBT insight via GPT-4o-mini
- Trigger de gamificação (+10 flames)
- Atualização de streak

---

## 6. Perfil — Santuário

**Perfil:** B2C (cada utilizador gere o seu próprio perfil)

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Ver perfil | `GET /perfil` | ProfileController@show | view | Pseudónimo, flames, milestones, achievements |
| Editar conta | `GET /profile/edit` | ProfileController@edit | view | Formulários: info, password, accessibility, safety |
| Atualizar perfil | `PATCH /profile` | ProfileController@update | redirect | |
| Apagar conta | `DELETE /profile` | ProfileController@destroy | redirect | Trigger GDPR deletion |
| Atualizar energia | `POST /perfil/energia` | ProfileController@updateEnergy | json | Nível de energia atual |
| Safety plan | `POST /perfil/seguranca` | ProfileController@updateSafetyPlan | redirect | Plano de segurança pessoal |
| Log respiração | `POST /perfil/respirar` | ProfileController@logBreathing | json | +5 flames |
| Atualizar tags | `POST /perfil/tags` | ProfileController@updateTags | redirect | Tags emocionais do perfil |
| Adicionar milestone | `POST /perfil/jornada` | ProfileController@storeMilestone | redirect | Timeline pessoal |
| Remover milestone | `DELETE /perfil/jornada/{milestone}` | ProfileController@destroyMilestone | redirect | |
| Exportar passaporte | `GET /perfil/passaporte` | ProfileController@exportPassport | view | Resumo do percurso |
| Preferências notif. | `PATCH /perfil/notificacoes` | ProfileController@updateNotificationPrefs | json | Quiet hours, weekly summary |

---

## 7. Zona Calma

**Perfil:** B2C

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Hub principal | `GET /zona-calma` | CalmZoneController@index | view | Menu de exercícios |
| Grounding 5-4-3-2-1 | `GET /zona-calma/grounding` | CalmZoneController@grounding | view | Técnica sensorial |
| Plano de crise | `GET /zona-calma/crise` | CalmZoneController@crisis | view | Contactos de emergência + plano |
| Sons relaxantes | `GET /zona-calma/sons` | CalmZoneController@sounds | view | Web Audio API |
| Combustão (venting) | `GET /zona-calma/combustao` | CalmZoneController@burn | view | Espaço catártico |
| Respiração guiada | `GET /zona-calma/respiracao` | CalmZoneController@breathe | view | Animação de respiração |
| Sintonia (heartbeat) | `GET /zona-calma/sintonia` | CalmZoneController@heartbeat | view | Body scan, vibração |
| Reflexão | `GET /zona-calma/reflexao` | CalmZoneController@reflection | view | Prompt de reflexão |
| Enviar reflexão (AI) | `POST /zona-calma/reflexao/enviar` | CalmZoneController@sendReflection | json | Conversa com "eu futuro" via AI |
| Cofre pessoal | `GET /zona-calma/cofre` | CalmZoneController@vault | view | Técnicas de coping guardadas |
| Guardar no cofre | `POST /zona-calma/cofre` | CalmZoneController@storeVaultItem | json | |
| Sugerir música | `POST /zona-calma/playlist/sugerir` | CalmZoneController@suggestSong | json | Spotify/iTunes API |
| Votar música | `POST /zona-calma/playlist/{song}/votar` | CalmZoneController@voteSong | json | suggestions |
| Apagar música | `DELETE /zona-calma/playlist/{song}` | CalmZoneController@deleteSong | json | |

---

## 8. Buddy System — Ouvinte

**Perfil:** B2C (qualquer utilizador pode ser ouvinte ou pedir ouvinte)

| Funcionalidade | Rota | Método | Resposta | Rate limit | Notas |
|---------------|------|--------|----------|-----------|-------|
| Dashboard | `GET /ouvinte/dashboard` | BuddyController@dashboard | view | — | Visão geral + pedidos |
| Pedir buddy | `POST /ouvinte/pedir` | BuddyController@requestBuddy | redirect | buddy-actions | Cria sessão pendente |
| Candidatura | `POST /ouvinte/candidatura` | BuddyController@apply | redirect | buddy-actions | Aplicar como ouvinte |
| Aceitar sessão | `POST /ouvinte/{session}/aceitar` | BuddyController@acceptSession | redirect | — | Cria sala privada |
| Escalar | `POST /ouvinte/{session}/escalar` | BuddyController@escalate | redirect | — | Alerta moderadores |
| Avaliar | `POST /ouvinte/{session}/avaliar` | BuddyController@evaluate | redirect | — | Rating da sessão |

---

## 9. Biblioteca

**Perfil:** B2C

| Funcionalidade | Rota | Método | Resposta | Rate limit |
|---------------|------|--------|----------|-----------|
| Listar recursos | `GET /biblioteca` | LibraryController@index | view | — |
| Sugerir recurso | `POST /biblioteca/sugerir` | LibraryController@store | redirect | suggestions |
| Votar recurso | `POST /biblioteca/{resource}/votar` | LibraryController@toggleVote | json | suggestions |

---

## 10. Auto-avaliação

**Perfil:** B2C (privado por utilizador)

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Histórico | `GET /auto-avaliacao` | SelfAssessmentController@index | view | Lista de avaliações passadas |
| Formulário | `GET /auto-avaliacao/{type}` | SelfAssessmentController@create | view | type: phq9 ou gad7 |
| Submeter | `POST /auto-avaliacao/{type}` | SelfAssessmentController@store | redirect | Cálculo de score + severity |
| Ver resultado | `GET /auto-avaliacao/resultado/{assessment}` | SelfAssessmentController@show | view | Score + interpretação |

---

## 11. The Wall (Galeria artística)

**Perfil:** B2C

| Funcionalidade | Rota | Método | Resposta | Rate limit |
|---------------|------|--------|----------|-----------|
| Galeria | `GET /the-wall` | WallController@index | view | — |
| Upload imagem | `POST /the-wall` | WallController@store | redirect | content-creation |

---

## 12. Privacidade e GDPR

**Perfil:** B2C (cada utilizador gere os seus dados)

| Funcionalidade | Rota | Método | Resposta | Rate limit | Notas |
|---------------|------|--------|----------|-----------|-------|
| Painel privacidade | `GET /privacidade` | PrivacyController@index | view | — | Controlos GDPR |
| Exportar dados | `POST /privacidade/exportar` | PrivacyController@exportData | json (file) | privacy-actions | JSON completo |
| Hibernar conta | `POST /privacidade/hibernar` | PrivacyController@hibernate | redirect | privacy-actions | Soft-pause |

---

## 13. Pesquisa global

**Perfil:** B2C

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Pesquisar | `GET /pesquisar` | SearchController@index | view | Agrega posts, recursos, salas |

---

## 14. Comunidade

**Perfil:** B2C (pacto e impacto são funcionalidades comunitárias abertas a todos)

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Pacto comunitário | `GET /comunidade/pacto` | ForumController@pact | view | Prompt diário |
| Responder ao pacto | `POST /comunidade/pacto/responder` | ForumController@storePact | redirect/json | |
| Relatório de impacto | `GET /comunidade/impacto` | CommunityReportController@index | view | Métricas anónimas |

---

## 15. Terapia — triagem B2C

**Perfil:** B2C (utilizador que procura acesso a terapia profissional)
**Nota:** Este módulo é diferente do Portal Terapeuta (secção 16). Aqui o utilizador B2C
faz uma triagem com AI para encontrar o terapeuta certo. O controller é `TherapyController`,
distinto do `TherapistController`.

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Triagem inteligente | `GET /terapia` | TherapyController@index | view | |
| Match chat (AI) | `POST /terapia/triagem` | TherapyController@matchChat | json | GPT-4o-mini |

---

## 16. Portal Terapeuta (PRO)

**Perfil:** PRO — terapeutas registados com role `therapist`
**Android:** Web-first. Não entra na app Android na fase inicial.

| Funcionalidade | Rota | Método | Resposta | Middleware |
|---------------|------|--------|----------|-----------|
| Dashboard | `GET /terapeuta` | TherapistController@dashboard | view | TherapistMiddleware |
| Atribuir missão | `POST /terapeuta/missao` | TherapistController@assignMission | json | TherapistMiddleware |
| Somatic sync | `POST /terapeuta/somatico` | TherapistController@triggerSomaticSync | json + RT | TherapistMiddleware |

---

## 17. Portal Corporate (B2B)

**Perfil:** Corporate — utilizadores com `company_id` preenchido
**Android:** Web-only. Dashboard analítico não adequado a mobile.

| Funcionalidade | Rota | Método | Resposta | Middleware |
|---------------|------|--------|----------|-----------|
| Dashboard empresa | `GET /empresa` | CorporateController@dashboard | view | CorporateMiddleware |

---

## 18. Gamificação

**Perfil:** B2C (a maior parte da gamificação é acionada por outros controllers; este é o endpoint explícito)

| Funcionalidade | Rota | Método | Resposta | Rate limit |
|---------------|------|--------|----------|-----------|
| Desafio gentil | `POST /users/{user}/oferecer-apoio` | GamificationController@sendGentleChallenge | json | gamification |

**Sistema de flames (server-side):**
- daily_log: +10 flames
- reaction: +2 flames
- comment/reply: +5 flames
- breathe: +5 flames
- first_post: +20 flames

**Níveis:** spark (<50) → flame (50-199) → bonfire (200-499) → beacon (500+)

**Streaks:** Reset gentil (volta a 1, não a 0). Sem mecânicas de culpa.

**Missões diárias:** 3 aleatórias por dia (diary, comment, reaction, post)

---

## 19. Notificações e utilidades

**Perfil:** B2C

| Funcionalidade | Rota | Método | Resposta |
|---------------|------|--------|----------|
| Marcar como lidas | `POST /notifications/mark-read` | Closure | json |
| Push subscribe | `POST /push/subscribe` | PushSubscriptionController@store | json |
| Push unsubscribe | `POST /push/unsubscribe` | PushSubscriptionController@destroy | json |

---

## 20. Integrações externas

| Serviço | Uso | Endpoints afetados |
|---------|-----|-------------------|
| OpenAI GPT-4o-mini | Deteção de crise, sumarização, CBT, reflexão, triagem, insights | ForumController@store, @summarize; DailyLogController@store; CalmZoneController@sendReflection; TherapyController@matchChat; DashboardController@index |
| Spotify oEmbed | Embed de músicas na playlist | CalmZoneController@suggestSong |
| iTunes Search API | Metadados de músicas | CalmZoneController@suggestSong |
| Web Push (VAPID) | Push notifications browser | PushSubscriptionController |
| Brevo SMTP | Emails transacionais | Todas as Notifications |

---

## 21. Rate limiting aplicado

| Grupo | Limite | Rotas |
|-------|--------|-------|
| content-creation | 5/min | Criar post, comentar, upload wall |
| suggestions | 2/min | Sugerir recurso, sugerir/votar música |
| buddy-actions | 1/min | Pedir buddy, candidatura |
| reports | 5/hora | Reports de posts e mensagens |
| gamification | 1/user/dia | Desafio gentil |
| privacy-actions | 1/hora | Export dados, hibernar |

---

## 22. Resumo quantitativo

| Categoria | Total |
|-----------|-------|
| Rotas GET (views) | ~35 |
| Rotas POST/PATCH/DELETE (JSON) | ~30 |
| Rotas POST (redirect) | ~15 |
| Rotas com broadcasting | 6 |
| Rotas com integração AI | 6 |
| Rotas com rate limiting | ~15 |
| Rotas com middleware especial | ~5 (Therapist, Corporate) |
| Rotas públicas (sem auth) | ~5 |
| Total de rotas estimadas | ~85 |

---

## 23. Rotas em falta ou incompletas no inventário

Durante a análise foram identificadas rotas presentes no código mas ausentes ou incompletas
neste inventário. Listadas aqui para garantir cobertura total antes de construir a API.

| Rota | Controller | Estado no inventário | Notas |
|------|-----------|---------------------|-------|
| `GET /` | HomeController@index | ❌ Faltava | Adicionada em secção 0 |
| `GET /offline` | View | ❌ Faltava | Adicionada em secção 0 |
| `GET /fogueira` | RoomController@index | ❌ Faltava (estava separado) | Adicionada em secção 0 como pública |
| `GET /comunidade/impacto` | CommunityReportController@index | ⚠️ Estava em secção 14 incompleta | Pública, sem auth |
| `GET /terapia`, `POST /terapia/triagem` | TherapyController | ⚠️ Confundido com TherapistController | Clarificado em secção 15 |
| Listagem de notificações | NotificationController | ❓ Não confirmada | Pode não existir como rota GET separada |
| Listagem de achievements | AchievementController | ❓ Não confirmada | Controller existe mas rota GET não documentada |
| `POST /api/user/tour-completed` | OnboardingController@markTourCompleted | ✅ Presente em secção 1 | Único endpoint `/api/` no sistema atual — importante assinalar |

**Nota sobre AchievementController e notificações:** A existência de rotas GET para listar
achievements e notificações individualmente não foi confirmada com certeza. Possível que
estas funcionalidades sejam apenas renderizadas via dashboard. A confirmar antes de construir
endpoints API.

---

## 24. Ambiguidades e suposições explícitas

### Suposições

| # | Suposição | Impacto |
|---|-----------|---------|
| S1 | Os campos `onboarding_intent`, `onboarding_mood`, `onboarding_preference`, `onboarding_completed_at` existem como colunas na tabela `users` via migration mas não estão nos `fillable` do modelo | O endpoint API de onboarding precisa de confirmar estes campos antes de implementação |
| S2 | `TherapyController` (triagem B2C) e `TherapistController` (portal PRO) são controllers distintos | Relevante para definir quais endpoints criar na API: o de triagem é B2C; o portal é web-first |
| S3 | O Buddy System é exclusivamente B2C — qualquer utilizador pode ser ouvinte ou pedir ouvinte, sem restrição de plano | Não há evidência de restricção de plano PRO no BuddyController |
| S4 | Os moderadores têm acesso às rotas de moderação do fórum e chat (pin, lock, shadowban, mute) mesmo via app | O middleware `CheckBanned` e as policies de role já estão implementados |
| S5 | A galeria The Wall e a playlist da Zona Calma são B2C puras, sem restrição de plano | Não há middleware de plano nestes controllers |

### Ambiguidades

| # | Ambiguidade | Impacto para Android |
|---|-------------|---------------------|
| A1 | `GET /fogueira` (lista de salas de chat) é pública sem middleware de auth | Se intencional: o endpoint API deve ser público. Se for bug de middleware, deve ser corrigido antes da API. |
| A2 | `GET /comunidade/impacto` é pública — qualquer visitante pode ver métricas | Endpoint API deve manter estado público. Considerar rate limiting |
| A3 | Não está claro se existe uma rota `GET /notifications` separada ou se as notificações são sempre carregadas como parte do dashboard | Impacta o design do endpoint de notificações na API |
| A4 | O `AchievementController` existe mas a rota de listagem não foi confirmada no `routes/web.php` | Pode ser que achievements sejam só consultados via dashboard |
| A5 | A rota `/api/user/tour-completed` existe já em `/api/` (não em `/web/`) — porquê apenas esta rota no `/api/`? | Pode ser um artefacto histórico. A nova API deve ser criada em `/api/v1/` de forma consistente |

---

## 25. Impacto para a migração — síntese

| Categoria | Quantidade | Notas |
|-----------|-----------|-------|
| Rotas candidatas a endpoint API Android (B2C) | ~65 | Auth, dashboard, fórum, chat, diário, perfil, zona calma, buddy, biblioteca, wall, GDPR, gamificação, auto-avaliação, pesquisa, comunidade |
| Rotas web-first (não entram na API) | ~5 | Portal terapeuta, portal corporate, admin Filament |
| Rotas que precisam de reconceção para API | ~10 | Auth (session→token), onboarding (redirect→JSON), uploads, WebSocket auth, export GDPR |
| Rotas com broadcasting real-time | 6 | Precisam de implementação nativa de WebSocket client na app |
| Rotas com integração AI | 6 | Podem ter latência; a app precisa de loading states e timeouts |
| Rotas com rate limiting web | ~15 | O rate limiting deve ser portado para middleware de API com os mesmos limites |
| Rotas públicas sem auth | 4 | Landing, offline, fogueira (a confirmar), impacto comunitário |

### Observações críticas para a API

1. **Autenticação:** Todas as rotas protegidas por `auth` passam a usar `auth:sanctum`.
2. **Respostas:** Controllers atuais devolvem `view`, `redirect`, e `json` de forma mista.
   A API deve devolver exclusivamente `json` com estrutura consistente.
3. **Rate limiting:** Os grupos existentes (`content-creation`, `suggestions`, `buddy-actions`,
   `reports`, `gamification`, `privacy-actions`) devem ser preservados na API com os mesmos
   limites.
4. **Broadcasting:** Os 6 eventos de real-time (MessageSent, MessageReacted, etc.) requerem
   que o cliente Android implemente WebSocket nativo (OkHttp) com autenticação por token
   Sanctum em vez de sessão Laravel.
5. **AI endpoints:** Os 6 endpoints com OpenAI têm timeout de 3-5s server-side. A app deve
   mostrar loading states adequados e tratar timeouts graciosamente.

---

*Próximo: [03-mapeamento-funcional.md](03-mapeamento-funcional.md) — Como cada funcionalidade deve existir na app Android.*
