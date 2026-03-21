# 02 — Inventário Funcional Completo

## Convenções deste documento

- **Resposta**: Tipo de resposta atual do controller (`view` = HTML, `json` = JSON, `redirect` = redirect, `broadcast` = WebSocket event, `file` = download)
- **Middleware**: Middleware aplicado à rota
- **Rate limit**: Grupo de throttling aplicado
- **AI**: Usa integração com OpenAI
- **RT**: Emite evento real-time via broadcasting

---

## 1. Autenticação e onboarding

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

| Funcionalidade | Rota | Método | Resposta | Rate limit |
|---------------|------|--------|----------|-----------|
| Listar recursos | `GET /biblioteca` | LibraryController@index | view | — |
| Sugerir recurso | `POST /biblioteca/sugerir` | LibraryController@store | redirect | suggestions |
| Votar recurso | `POST /biblioteca/{resource}/votar` | LibraryController@toggleVote | json | suggestions |

---

## 10. Auto-avaliação

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Histórico | `GET /auto-avaliacao` | SelfAssessmentController@index | view | Lista de avaliações passadas |
| Formulário | `GET /auto-avaliacao/{type}` | SelfAssessmentController@create | view | type: phq9 ou gad7 |
| Submeter | `POST /auto-avaliacao/{type}` | SelfAssessmentController@store | redirect | Cálculo de score + severity |
| Ver resultado | `GET /auto-avaliacao/resultado/{assessment}` | SelfAssessmentController@show | view | Score + interpretação |

---

## 11. The Wall (Galeria artística)

| Funcionalidade | Rota | Método | Resposta | Rate limit |
|---------------|------|--------|----------|-----------|
| Galeria | `GET /the-wall` | WallController@index | view | — |
| Upload imagem | `POST /the-wall` | WallController@store | redirect | content-creation |

---

## 12. Privacidade e GDPR

| Funcionalidade | Rota | Método | Resposta | Rate limit | Notas |
|---------------|------|--------|----------|-----------|-------|
| Painel privacidade | `GET /privacidade` | PrivacyController@index | view | — | Controlos GDPR |
| Exportar dados | `POST /privacidade/exportar` | PrivacyController@exportData | json (file) | privacy-actions | JSON completo |
| Hibernar conta | `POST /privacidade/hibernar` | PrivacyController@hibernate | redirect | privacy-actions | Soft-pause |

---

## 13. Pesquisa global

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Pesquisar | `GET /pesquisar` | SearchController@index | view | Agrega posts, recursos, salas |

---

## 14. Comunidade

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Pacto comunitário | `GET /comunidade/pacto` | ForumController@pact | view | Prompt diário |
| Responder ao pacto | `POST /comunidade/pacto/responder` | ForumController@storePact | redirect/json | |
| Relatório de impacto | `GET /comunidade/impacto` | CommunityReportController@index | view | Métricas anónimas |

---

## 15. Terapia (PRO)

| Funcionalidade | Rota | Método | Resposta | Notas |
|---------------|------|--------|----------|-------|
| Triagem inteligente | `GET /terapia` | TherapyController@index | view | |
| Match chat (AI) | `POST /terapia/triagem` | TherapyController@matchChat | json | GPT-4o-mini |

---

## 16. Portal Terapeuta (PRO)

| Funcionalidade | Rota | Método | Resposta | Middleware |
|---------------|------|--------|----------|-----------|
| Dashboard | `GET /terapeuta` | TherapistController@dashboard | view | TherapistMiddleware |
| Atribuir missão | `POST /terapeuta/missao` | TherapistController@assignMission | json | TherapistMiddleware |
| Somatic sync | `POST /terapeuta/somatico` | TherapistController@triggerSomaticSync | json + RT | TherapistMiddleware |

---

## 17. Portal Corporate (B2B)

| Funcionalidade | Rota | Método | Resposta | Middleware |
|---------------|------|--------|----------|-----------|
| Dashboard empresa | `GET /empresa` | CorporateController@dashboard | view | CorporateMiddleware |

---

## 18. Gamificação

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

*Próximo: [03-mapeamento-funcional.md](03-mapeamento-funcional.md) — Como cada funcionalidade deve existir na app Android.*
