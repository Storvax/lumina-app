# 14 — Real-time, Chat, Notificações e Presença

## Contexto

Este documento cobre a área mais interativa da app Android — chat em tempo real, notificações push,
presença e sessões terapêuticas. Numa plataforma de saúde mental, o chat é simultaneamente o espaço
de maior conexão e de maior risco (crises em tempo real).

Refs:
- [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — 7 broadcast events reutilizáveis, notificações com `toArray()`
- [11-backend-gaps.md](11-backend-gaps.md) — Gap 10 (WebSocket auth Sanctum), Gap 24 (device_tokens table)
- [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) — Sanctum token para `/broadcasting/auth`, quiet hours
- [13-offline-sync.md](13-offline-sync.md) — reconexão WebSocket depende do ConnectivityMonitor e Room DB cache
- [15-audio-media-uploads.md](15-audio-media-uploads.md) — posts de áudio no fórum e somatic sync (haptic)
- [16-gamificacao-servidor.md](16-gamificacao-servidor.md) — gamification events no response envelope
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — Risk R-01 (compatibilidade WebSocket Reverb/Android)

---

## Observações do estado atual

1. **7 broadcast events** confirmados em `app/Events/`:
   - `MessageSent`, `MessageReacted`, `MessageDeleted`, `MessageRead`, `MessageUpdated` — todos `ShouldBroadcastNow` (entrega imediata)
   - `RoomStatusUpdated` — `ShouldBroadcastNow`
   - `SomaticSyncTriggered` — `ShouldBroadcast` (queued, não imediato)
2. **4 canais** em `routes/channels.php`:
   - `presence-chat.{roomId}` — autorização por room (público = allow, privado = participant check)
   - `presence-silent-room` — autenticado, payload usa pseudonym (não nome real)
   - `private-session.{sessionId}` — apenas participantes + terapeuta atribuído
   - `private-App.Models.User.{id}` — match direto de user ID
3. **Message.content** usa cast `encrypted` — Laravel desencripta automaticamente no response.
   Android recebe plaintext via HTTPS, re-encripta para Room DB cache (ref. [13-offline-sync.md](13-offline-sync.md) secção 10)
4. **Slow mode**: 3s entre mensagens (normal), 15s em crisis mode (`ChatController:131`)
5. **Mute**: cache-based com 10min TTL (`Cache::put` em `ChatController:256`)
6. **Crisis detection**: `CBTAnalysisService` usado em `ChatController::send()` — pipeline de 3 camadas
   (keywords → intent patterns → GPT-4o-mini com 3s timeout)
7. **ModeratorCrisisAlert** ignora quiet hours (`via()` retorna `['database', 'broadcast']` sem check)
8. **Read receipts** opt-in via `User.read_receipts_enabled` — respeitado em `ChatController::markAsRead()`
9. **Presence subscriptions** em tabela `chat_presence_subscriptions` (user_id → target_user_id → room_id)
10. **Room model flags**: `is_crisis_mode` (boolean), `pinned_message` (text), `is_private` (boolean)
11. **Message model**: fillable `user_id, room_id, content, is_sensitive, is_anonymous, reply_to_id, edited_at`
12. **Reaction types**: `hug`, `candle`, `ear` — unique constraint per (user, message, type), toggle behavior
13. **Reverb config**: max 10.000 bytes por mensagem, ping interval 60s, activity timeout 30s, allowed origins `['*']`

---

## 1. Arquitetura real-time na app Android

### Estado atual (web)
- Laravel Reverb como servidor WebSocket
- Laravel Echo (JavaScript) como client
- 4 canais: user notifications, chat room, silent room, buddy session
- 7 events: MessageSent, MessageReacted, MessageDeleted, MessageUpdated, MessageRead, RoomStatusUpdated, SomaticSyncTriggered

### Estratégia Android

A app Android conecta-se diretamente ao servidor Reverb via WebSocket nativo (OkHttp), sem depender do Laravel Echo JavaScript. O protocolo Reverb é compatível com Pusher, portanto o client usa o protocolo Pusher.

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│ Android App │────→│ Reverb Server│←────│   Browser   │
│ (OkHttp WS) │     │ (WebSocket)  │     │ (Echo.js)   │
└─────────────┘     └──────┬───────┘     └─────────────┘
                           │
                    ┌──────▼───────┐
                    │   Laravel    │
                    │  (Backend)   │
                    └──────────────┘
```

### WebSocket Manager

```kotlin
class LuminaWebSocketManager(
    private val okHttpClient: OkHttpClient,
    private val tokenManager: TokenManager,
    private val connectivityMonitor: ConnectivityMonitor
) {
    private var webSocket: WebSocket? = null
    private val _events = MutableSharedFlow<WebSocketEvent>()
    val events: SharedFlow<WebSocketEvent> = _events

    fun connect() {
        val request = Request.Builder()
            .url("wss://api.lumina.pt/app/${REVERB_APP_KEY}?protocol=7")
            .build()

        webSocket = okHttpClient.newWebSocket(request, object : WebSocketListener() {
            override fun onOpen(webSocket: WebSocket, response: Response) {
                // Subscribe to channels
                subscribeToUserChannel()
            }

            override fun onMessage(webSocket: WebSocket, text: String) {
                val event = parseEvent(text)
                _events.tryEmit(event)
            }

            override fun onClosed(webSocket: WebSocket, code: Int, reason: String) {
                scheduleReconnect()
            }

            override fun onFailure(webSocket: WebSocket, t: Throwable, response: Response?) {
                scheduleReconnect()
            }
        })
    }

    private fun subscribeToUserChannel() {
        // Autenticar canal privado via API
        // POST /broadcasting/auth com Bearer token
    }
}
```

### Autenticação de canais

Para canais privados/presence, o client precisa de autenticação:

1. Client envia `subscribe` com channel name
2. Reverb pede auth ao Laravel: `POST /broadcasting/auth`
3. Laravel valida o token Sanctum e retorna auth string
4. Reverb permite a subscrição

**Requisito backend:** O endpoint `/broadcasting/auth` deve aceitar autenticação por Bearer token (Sanctum), não apenas session cookies.

---

## 2. Chat — A Fogueira (Fase 3)

### Arquitetura do chat Android

```
ChatScreen (Compose)
    │
    ▼
ChatViewModel
    ├── Observa: Room messages (Room DB) via Flow
    ├── Recebe: WebSocket events (real-time)
    ├── Envia: API calls (send, edit, delete, react)
    └── Emite: UI State + Effects
```

### Funcionalidades do chat

| Feature | Implementação Android | Notas |
|---------|---------------------|-------|
| Mensagens em tempo real | WebSocket events → Room DB → UI Flow | |
| Enviar mensagem | POST API + optimistic UI | |
| Reply (threading) | Swipe-to-reply gesture | |
| Editar mensagem | Long-press → edit mode | |
| Apagar mensagem | Long-press → confirm dialog | |
| Reações | Long-press → reaction picker + haptic | |
| Mensagens anónimas | Toggle no input bar | |
| Conteúdo sensível | Blur + tap longo para revelar | |
| Read receipts | WebSocket event + UI indicators | Opt-in |
| Typing indicator | Throttled WebSocket event | |
| Presença online | Presence channel → avatars com dot | |
| Crisis mode (15s) | Timer no input + UI indicator | |
| Slow mode (3s) | Timer no input | |

### Lazy loading de mensagens

```kotlin
// Chat usa paginação inversa (mais recentes primeiro)
@Dao
interface MessageDao {
    @Query("SELECT * FROM messages WHERE room_id = :roomId ORDER BY created_at DESC")
    fun getMessages(roomId: Long): PagingSource<Int, MessageEntity>
}
```

- Carregar últimas 50 mensagens ao entrar na sala
- Scroll up → carregar mais 50 (Paging 3)
- Novas mensagens em tempo real → inserir no topo do Room DB

### Reconexão automática

```kotlin
private fun scheduleReconnect() {
    val delay = (2.0.pow(reconnectAttempt) * 1000).toLong()
        .coerceAtMost(30_000L) // Max 30s

    viewModelScope.launch {
        delay(delay)
        if (connectivityMonitor.isOnline.value) {
            reconnectAttempt++
            connect()
        }
    }
}
```

---

## 3. Notificações Push (FCM)

### Arquitetura

```
┌──────────────┐     ┌──────────┐     ┌─────────────┐
│   Laravel    │────→│   FCM    │────→│ Android App │
│  (Backend)   │     │ (Google) │     │ (Service)   │
└──────────────┘     └──────────┘     └─────────────┘
```

### Tipos de notificação

| Notificação | Canal Android | Prioridade | Deep link |
|------------|--------------|-----------|-----------|
| Forum interaction | `lumina_community` | Default | `/mural/{postId}` |
| New chat message | `lumina_community` | Default | `/sala/{roomSlug}` |
| Gentle challenge | `lumina_wellness` | Low | `/perfil` |
| Weekly summary | `lumina_wellness` | Low | `/dashboard` |
| Crisis alert (mod) | `lumina_crisis` | High | `/mural/{postId}` |
| Re-engagement | `lumina_wellness` | Low | `/dashboard` |
| Mission completed | `lumina_missions` | Default | `/dashboard` |

### Notification Channels (Android 8+)

```kotlin
fun createNotificationChannels(context: Context) {
    val manager = context.getSystemService<NotificationManager>()

    val channels = listOf(
        NotificationChannel("lumina_crisis", "Alertas de crise", IMPORTANCE_HIGH).apply {
            description = "Alertas importantes de segurança"
        },
        NotificationChannel("lumina_community", "Comunidade", IMPORTANCE_DEFAULT).apply {
            description = "Interações no fórum e chat"
        },
        NotificationChannel("lumina_wellness", "Bem-estar", IMPORTANCE_LOW).apply {
            description = "Lembretes gentis e resumos"
        },
        NotificationChannel("lumina_missions", "Missões", IMPORTANCE_DEFAULT).apply {
            description = "Progresso e missões diárias"
        }
    )

    channels.forEach { manager.createNotificationChannel(it) }
}
```

### Registo de token FCM

```kotlin
class LuminaMessagingService : FirebaseMessagingService() {

    override fun onNewToken(token: String) {
        // Enviar para o backend
        CoroutineScope(Dispatchers.IO).launch {
            api.registerFcmToken(FcmTokenRequest(token))
        }
    }

    override fun onMessageReceived(message: RemoteMessage) {
        val data = message.data
        when (data["type"]) {
            "forum_interaction" -> showForumNotification(data)
            "chat_message" -> showChatNotification(data)
            "gentle_challenge" -> showWellnessNotification(data)
            "weekly_summary" -> showSummaryNotification(data)
            // ...
        }
    }
}
```

### Quiet Hours

A app respeita `quiet_hours_start` e `quiet_hours_end` do perfil do utilizador:
- **Server-side:** Não enviar FCM durante quiet hours
- **Client-side:** Backup — configurar DND via NotificationChannel se server falhar

### Grouping

Múltiplas notificações do mesmo tipo são agrupadas:
```kotlin
val notification = NotificationCompat.Builder(context, "lumina_community")
    .setGroup("lumina_forum")
    .setGroupSummary(true)
    .setContentTitle("3 novas interações no Mural")
    .build()
```

---

## 4. Presença e typing indicators

### Presença online

O canal presence do Reverb informa quem está online:

```kotlin
// Ao subscrever um canal presence
fun subscribePresence(roomId: Long) {
    val subscribeMsg = """
    {
        "event": "pusher:subscribe",
        "data": {
            "channel": "presence-chat.$roomId",
            "auth": "$authString",
            "channel_data": "{\"user_id\":$userId,\"user_info\":{\"name\":\"$pseudonym\"}}"
        }
    }
    """.trimIndent()
    webSocket?.send(subscribeMsg)
}
```

**UI:** Dots verdes nos avatars dos utilizadores online na sala.

### Typing indicator

```kotlin
// Enviar typing event (throttled a cada 3s)
fun sendTyping(roomId: Long) {
    val event = """
    {
        "event": "client-typing",
        "channel": "presence-chat.$roomId",
        "data": {"user_id": $userId}
    }
    """.trimIndent()
    webSocket?.send(event)
}
```

**UI:** "Alguém está a escrever..." (nunca revelar quem, para preservar anonimato)

---

## 5. Notificações in-app

Para além do FCM (push), a app tem notificações in-app:

```kotlin
// Polling de notificações (ou via WebSocket no canal user)
@GET("/api/v1/notifications")
suspend fun getNotifications(
    @Query("unread_only") unreadOnly: Boolean = false,
    @Query("cursor") cursor: String? = null
): PaginatedResponse<NotificationDto>

@POST("/api/v1/notifications/mark-read")
suspend fun markAsRead(@Body ids: MarkReadRequest)
```

**UI:** Badge counter na bottom bar + lista de notificações acessível via ícone.

---

## 6. Lifecycle e background

### WebSocket lifecycle

| Estado da app | WebSocket | Notas |
|-------------|-----------|-------|
| Foreground (chat aberto) | Conectado | Full real-time |
| Foreground (outra tela) | Conectado | Recebe notificações user channel |
| Background (recente) | Desconectado | FCM substitui |
| Background (longo) | Desconectado | FCM substitui |
| Killed | Desconectado | FCM substitui |

**Regra:** WebSocket conecta quando a app está em foreground. FCM cobre tudo o resto.

### Foreground Service para sons

Se o utilizador está a ouvir sons da Zona Calma e vai para background:
```kotlin
class SoundService : Service() {
    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        val notification = createSoundNotification() // Media controls
        startForeground(NOTIFICATION_ID, notification)
        return START_STICKY
    }
}
```

---

## 7. WebSocket event payloads

Payloads derivados dos métodos `broadcastWith()` (ou public properties quando `broadcastWith()` não existe)
nos ficheiros `app/Events/*.php`.

| Evento | Canal | Payload JSON | Notas |
|--------|-------|-------------|-------|
| `MessageSent` | `presence-chat.{roomId}` | `{ "id": int, "content": string, "user_id": int, "user_name": string, "created_at": "HH:mm", "is_sensitive": bool }` | `broadcastWith()` explícito |
| `MessageReacted` | `presence-chat.{roomId}` | `{ "message_id": int, "type": string, "user_id": int, "count": int }` | Pass-through de `$reactionData` do controller |
| `MessageDeleted` | `presence-chat.{roomId}` | `{ "roomId": int, "messageId": int }` | Sem `broadcastWith()` — serializa properties públicas |
| `MessageRead` | `presence-chat.{roomId}` | `{ "messageIds": [int], "roomId": int, "userId": int }` | Sem `broadcastWith()` — serializa properties públicas |
| `MessageUpdated` | `presence-chat.{roomId}` | `{ "message": { ...full message object com replyTo relation } }` | Sem `broadcastWith()` — serializa message model inteiro |
| `RoomStatusUpdated` | `presence-chat.{roomId}` | `{ "status": "normal" \| "crisis" }` | `broadcastWith()` explícito |
| `SomaticSyncTriggered` | `private-session.{sessionId}` | `{ "exercise": string, "bpm": int, "triggered_by": int }` | `broadcastWith()` explícito. `exercise` ∈ {breathing, grounding, heartbeat} |

**Backend gap:** `MessageDeleted`, `MessageRead` e `MessageUpdated` não têm `broadcastWith()` explícito,
serializando properties públicas. Isto pode expor dados indesejados (e.g., `MessageUpdated` serializa
o model inteiro incluindo `content` encriptado/desencriptado). Recomendação: adicionar `broadcastWith()`
a estes 3 eventos (ref. [11-backend-gaps.md](11-backend-gaps.md)).

---

## 8. Chat API endpoints

Mapeamento das 12 rotas web (`routes/web.php:99-117`) para API equivalentes:

| Rota Web | Método API | Endpoint API | Notas |
|----------|-----------|-------------|-------|
| `GET /fogueira` | GET | `/api/v1/chat/rooms` | Lista de salas públicas + privadas do user |
| `GET /sala/{room:slug}` | GET | `/api/v1/chat/rooms/{room}` | Detalhe da sala + últimas 50 mensagens |
| `POST /chat/{room}/message` | POST | `/api/v1/chat/rooms/{room}/messages` | Enviar mensagem. Rate limited: 3s (normal), 15s (crisis) |
| `PATCH /chat/{room}/message/{message}` | PATCH | `/api/v1/chat/rooms/{room}/messages/{message}` | Editar (janela 5min, apenas autor) |
| `DELETE /chat/messages/{message}` | DELETE | `/api/v1/chat/messages/{message}` | Apagar (autor ou moderador) |
| `POST /chat/{room}/read` | POST | `/api/v1/chat/rooms/{room}/read` | Marcar mensagens como lidas |
| `POST /chat/{room}/message/{message}/react` | POST | `/api/v1/chat/rooms/{room}/messages/{message}/reactions` | Toggle reação (hug, candle, ear) |
| `POST /chat/messages/{message}/report` | POST | `/api/v1/chat/messages/{message}/report` | Reportar mensagem |
| `POST /chat/{room}/mute/{user}` | POST | `/api/v1/chat/rooms/{room}/mute/{user}` | Moderador: silenciar 10min |
| `POST /chat/{room}/pin` | POST | `/api/v1/chat/rooms/{room}/pin` | Moderador: fixar mensagem |
| `POST /chat/{room}/follow/{user}` | POST | `/api/v1/chat/rooms/{room}/follow/{user}` | Toggle alerta presença |
| `POST /chat/{room}/crisis` | POST | `/api/v1/chat/rooms/{room}/crisis` | Moderador: toggle crisis mode |
| `POST /chat/preferences/mode` | POST | `/api/v1/chat/preferences/mode` | Toggle compact/comfortable view |

**Endpoint adicional para API:**
- `GET /api/v1/chat/rooms/silent` — sala silenciosa (presença anónima, sem mensagens)
- `GET /api/v1/chat/rooms/{room}/messages?cursor=X` — paginação cursor de mensagens antigas

---

## 9. Crisis mode UX

### O que é

Crisis mode é ativado por moderadores quando detetam sinais de crise numa sala de chat.
O `CBTAnalysisService` pode auto-alertar moderadores, mas a ativação é sempre manual.

### Trigger

1. `CBTAnalysisService::detectCrisis()` deteta keywords/intent em mensagem
2. `ModeratorCrisisAlert` enviado a todos os moderadores (bypasses quiet hours)
3. Moderador avalia e decide ativar: `POST /chat/{room}/crisis`
4. `RoomStatusUpdated` broadcast com `{ "status": "crisis" }`

### UX no Android

| Elemento | Estado normal | Estado crisis |
|----------|-------------|--------------|
| Input bar | Envio imediato | Countdown 15s visível antes de poder enviar |
| Background | Neutro (surface color) | Suave rose/pink (`rose-50`) — **não** vermelho alarmante |
| Ações | Todas disponíveis | Sem reações, sem edição, apenas mensagens novas |
| Mensagem fixada | Conteúdo original | Sistema auto-fixa: "Este é um espaço seguro. Respira fundo." |
| Badge na sala | Nenhum | Ícone de escudo (🛡️) — calmo, protetor |

### State machine (Compose)

```kotlin
sealed class ChatRoomState {
    object Normal : ChatRoomState()
    object CrisisTransitioning : ChatRoomState() // Animação suave (300ms fade)
    object Crisis : ChatRoomState()
}

// Transição suave — nunca abrupta
val backgroundColor by animateColorAsState(
    targetValue = when (roomState) {
        is ChatRoomState.Crisis -> MaterialTheme.colorScheme.errorContainer.copy(alpha = 0.3f)
        else -> MaterialTheme.colorScheme.surface
    },
    animationSpec = tween(300)
)
```

### Acessibilidade em crisis mode

- TalkBack anuncia: "Modo de crise ativo. Mensagens têm intervalo de 15 segundos."
- Haptic pulse suave na transição (não alarme — feedback tátil gentil)
- Contraste aumentado: texto mais escuro sobre fundo rose para legibilidade
- Countdown de 15s anunciado: "Podes enviar em X segundos"

---

## 10. Sensitive message handling

### Fluxo

1. `MessageSent` chega com `is_sensitive: true`
2. Android renderiza mensagem com blur overlay (`Modifier.blur(20.dp)`)
3. Utilizador faz long-press (>500ms) para revelar conteúdo
4. Ao sair do viewport (scroll), conteúdo re-blur automaticamente
5. Cada reveal é local — não notifica o servidor

### Implementação

```kotlin
@Composable
fun SensitiveMessageBubble(message: MessageUiModel) {
    var isRevealed by remember { mutableStateOf(false) }

    Box(
        modifier = Modifier
            .pointerInput(Unit) {
                detectTapGestures(
                    onLongPress = { isRevealed = true }
                )
            }
    ) {
        Text(
            text = message.content,
            modifier = if (!isRevealed) Modifier.blur(20.dp) else Modifier
        )

        if (!isRevealed) {
            // Overlay com ícone de olho
            Text(
                "Conteúdo sensível. Pressiona sem largar para revelar.",
                style = MaterialTheme.typography.labelSmall
            )
        }
    }

    // Re-blur quando sai do viewport
    DisposableEffect(Unit) {
        onDispose { isRevealed = false }
    }
}
```

### Screen reader

- Mensagem sensível NÃO é lida automaticamente pelo TalkBack
- Em vez disso: "Conteúdo sensível. Toca duas vezes e mantém para revelar."
- Após reveal: conteúdo lido normalmente

### Notificações

- FCM para mensagem sensível: body = "Nova mensagem sensível" (sem preview do conteúdo)
- In-app: mesmo tratamento — indicador visual sem conteúdo

---

## 11. Anonymous messaging

### Comportamento

- Toggle "Enviar anonimamente" no input bar (ícone máscara, toggle suave)
- Quando ativo: `is_anonymous: true` no payload de envio
- Servidor armazena `user_id` real mas broadcast usa `user_name: pseudonym`
- Pseudonym é SHA-256 determinístico (`getPseudonymAttribute()`) — formato "Lumina-XXXXXX"

### Garantias de privacidade

| Quem | Vê nome real? | Vê pseudonym? |
|------|-------------|--------------|
| Outros utilizadores | ❌ Não | ✅ Sim (no broadcast payload) |
| Moderadores | ✅ Sim (server-side, Filament) | ✅ Sim |
| Terapeutas | ❌ Não (a menos que sejam pacientes atribuídos) | ✅ Sim |

### Restrições em mensagens anónimas

- **Não editáveis** — prevenir leak de identidade via histórico de edições
- **Deletáveis** pelo autor (via `user_id` verificado server-side)
- **Reportáveis** como qualquer mensagem
- Reações a mensagens anónimas são normais (reações não revelam identidade do autor da mensagem)

---

## 12. Message encryption flow

### Pipeline completa

```
Server (Message.content encrypted at rest via Laravel cast)
    │
    ▼ API response (Laravel desencripta automaticamente)
    │
    ▼ HTTPS transport (TLS 1.3)
    │
    ▼ Android recebe plaintext
    │
    ├─→ Display no ChatScreen (Compose)
    │
    └─→ Cache em Room DB (SQLCipher — ref. doc 13 secção 10)
```

### Pontos-chave

- Message content **nunca** armazenado em plaintext no dispositivo — SQLCipher encripta toda a DB
- WebSocket events também chegam em plaintext (Reverb desencripta) — tratamento idêntico
- Cache eviction: mensagens com mais de **7 dias** removidas da Room DB
- `MessageDeleted` event: remover imediatamente da Room DB (não esperar por eviction)
- `MessageUpdated` event: atualizar in-place na Room DB

### Dados que NÃO são cacheados

- Moderation logs (web-only)
- Message reports (enviados e esquecidos no client)
- Presence state (volátil, apenas em memória)

---

## 13. Buddy session real-time

### Contexto

Buddy sessions são sessões de suporte peer-to-peer em salas privadas (`is_private: true`).
Um terapeuta atribuído pode supervisionar e triggerar exercícios somáticos.

### Canal

- `private-session.{sessionId}` — autorização em `routes/channels.php`:
  - Participantes diretos (`user_id` ou `buddy_id`): ✅ Allow
  - Terapeuta atribuído (via `patient_therapist` pivot table): ✅ Allow
  - Outros (incluindo moderadores sem atribuição): ❌ Deny

### SomaticSyncTriggered

Quando terapeuta trigger exercício somático:

1. Terapeuta: `POST /api/v1/therapist/somatic-sync` com `{session_id, exercise, bpm}`
2. Backend broadcast: `SomaticSyncTriggered` no canal `private-session.{sessionId}`
3. Paciente Android recebe: `{ "exercise": "breathing", "bpm": 60, "triggered_by": 42 }`
4. Android renderiza exercício:
   - **breathing**: animação circular + haptic rítmico (ref. [15-audio-media-uploads.md](15-audio-media-uploads.md) secção 7)
   - **grounding**: guia passo a passo (5 sentidos)
   - **heartbeat**: vibração a `bpm` — Vibrator pattern matching

### Escalation

Se sinais de crise durante buddy session:
1. Participante pode tocar "Preciso de ajuda" (sempre visível em sessões)
2. API: `POST /api/v1/buddy-sessions/{session}/escalate`
3. Backend notifica terapeuta atribuído (ModeratorCrisisAlert equivalente)
4. Terapeuta pode ativar crisis mode na sala ou trigger somatic sync calmante

---

## 14. FCM payload contracts

Contratos JSON derivados dos métodos `toArray()` e `toWebPush()` das 5 notification classes
em `app/Notifications/`. Backend gap: nenhuma tem `toFcm()` — precisa implementar
(ref. [10-backend-reutilizacao.md](10-backend-reutilizacao.md) secção Transformação de notificações).

### ForumInteraction

```json
{
  "type": "forum_interaction",
  "post_id": 123,
  "interaction_type": "reaction|comment|milestone",
  "actor_pseudonym": "Lumina-A3F2C1",
  "message": "Alguém reagiu ao teu post com um abraço",
  "icon": "ri-heart-fill",
  "channel": "lumina_community",
  "priority": "default",
  "click_action": "lumina://mural/post/123"
}
```

### ModeratorCrisisAlert

```json
{
  "type": "crisis_alert",
  "room_id": 5,
  "room_slug": "fogueira-principal",
  "message_id": 789,
  "level": "critical|high|alert",
  "message": "Alerta de crise [critical] na sala «Fogueira Principal»",
  "excerpt": "Primeiros 80 caracteres da mensagem...",
  "icon": "ri-alarm-warning-fill",
  "channel": "lumina_crisis",
  "priority": "high",
  "click_action": "lumina://chat/room/fogueira-principal"
}
```

**Nota:** Crisis alerts **ignoram** quiet hours. O campo `priority: "high"` garante entrega
imediata pelo FCM mesmo com battery optimization ativa.

### GentleChallengeReceived

```json
{
  "type": "gentle_challenge",
  "sender_pseudonym": "Lumina-B2D4E6",
  "mission_text": "Escreve 3 coisas boas do teu dia",
  "icon": "ri-gift-line",
  "channel": "lumina_wellness",
  "priority": "low",
  "click_action": "lumina://dashboard"
}
```

### GentleReEngagement

```json
{
  "type": "re_engagement",
  "days_absent": 7,
  "message": "Sentimos a tua falta. Quando quiseres, estamos aqui. Sem pressa.",
  "icon": "ri-heart-pulse-line",
  "channel": "lumina_wellness",
  "priority": "low",
  "click_action": "lumina://dashboard"
}
```

### WeeklyEmotionalSummary

```json
{
  "type": "weekly_summary",
  "logs_count": 5,
  "hugs_received": 12,
  "message": "Um momento para ti, Ana",
  "icon": "ri-sun-line",
  "channel": "lumina_wellness",
  "priority": "low",
  "click_action": "lumina://dashboard"
}
```

---

## 15. Deep linking from notifications

### Mapeamento de destinos

| Tipo FCM | Deep link URI | NavGraph destination | Fallback |
|----------|-------------|---------------------|----------|
| `forum_interaction` | `lumina://mural/post/{postId}` | `PostDetailScreen` | Feed principal |
| `crisis_alert` | `lumina://chat/room/{roomSlug}` | `ChatScreen` | Lista de salas |
| `chat_message` | `lumina://chat/room/{roomSlug}` | `ChatScreen` | Lista de salas |
| `gentle_challenge` | `lumina://dashboard` | `DashboardScreen` | — |
| `re_engagement` | `lumina://dashboard` | `DashboardScreen` | — |
| `weekly_summary` | `lumina://dashboard` | `DashboardScreen` | — |
| `mission_completed` | `lumina://dashboard` | `DashboardScreen` | — |

### Implementação

```kotlin
// Em LuminaMessagingService.onMessageReceived()
val deepLink = data["click_action"] ?: return
val intent = Intent(Intent.ACTION_VIEW, Uri.parse(deepLink)).apply {
    flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
}

val pendingIntent = PendingIntent.getActivity(
    this, requestCode, intent,
    PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
)
```

### App killed / cold start

- Deep link via `<intent-filter>` no `AndroidManifest.xml`
- `NavHost` recebe deep link URI, resolve para destino correto
- Se token expirado: mostrar login primeiro, depois navegar para destino após auth

---

## 16. Notification deduplication

### Problema

Se o utilizador está a ver o chat da sala X e recebe um FCM para uma mensagem nessa mesma sala,
mostrar a notificação é redundante e irritante.

### Solução: ActiveScreenTracker

```kotlin
object ActiveScreenTracker {
    private val _currentScreen = MutableStateFlow<Screen?>(null)
    val currentScreen: StateFlow<Screen?> = _currentScreen

    fun setScreen(screen: Screen?) { _currentScreen.value = screen }
}

sealed class Screen {
    data class ChatRoom(val roomSlug: String) : Screen()
    data class PostDetail(val postId: Long) : Screen()
    object Dashboard : Screen()
    // ...
}
```

### Lógica em onMessageReceived

```kotlin
override fun onMessageReceived(message: RemoteMessage) {
    val data = message.data
    val type = data["type"] ?: return

    // Suprimir se user está no ecrã alvo
    when (type) {
        "chat_message" -> {
            val roomSlug = data["room_slug"]
            val current = ActiveScreenTracker.currentScreen.value
            if (current is Screen.ChatRoom && current.roomSlug == roomSlug) return
        }
        "forum_interaction" -> {
            val postId = data["post_id"]?.toLongOrNull()
            val current = ActiveScreenTracker.currentScreen.value
            if (current is Screen.PostDetail && current.postId == postId) return
        }
    }

    // Mostrar notificação normalmente
    showNotification(type, data)
}
```

### Edge cases

- User muda de ecrã entre envio e receção do FCM: aceitar duplicado ocasional (preferível a perder notificação)
- **Crisis alerts nunca são suprimidos** — mesmo que o moderador esteja na sala, o alerta aparece sempre
- App em background: ActiveScreenTracker é `null` → todas as notificações passam

---

## 17. Acessibilidade no chat

### Screen reader (TalkBack)

| Elemento | Anúncio |
|----------|---------|
| Mensagem normal | "{pseudonym} disse: {content}" |
| Mensagem sensível | "Conteúdo sensível. Toca duas vezes e mantém para revelar." |
| Mensagem anónima | "Utilizador anónimo disse: {content}" |
| Nova mensagem (live region) | "Nova mensagem de {pseudonym}" |
| Typing indicator | "Alguém está a escrever" |
| Crisis mode ativado | "Modo de crise ativo. Mensagens têm intervalo de 15 segundos." |
| Reação | "Reação {type} de {pseudonym}" |
| Read receipt | "Mensagem lida" (apenas se read receipts ativos) |

### Haptic feedback

| Evento | Tipo | Configurável? |
|--------|------|-------------|
| Nova mensagem recebida | Light vibration (10ms) | ✅ Sim (toggle em preferências) |
| Reação recebida | Double tap (10ms + 10ms) | ✅ Sim |
| Crisis mode transição | Gentle pulse (50ms) | ❌ Não (segurança) |
| Envio confirmado | Tick (5ms) | ✅ Sim |

### Reaction picker

- 3 reações: "Abraço" (`hug`), "Vela" (`candle`), "Ouvido" (`ear`)
- Cada botão com `contentDescription` em PT-PT
- Tamanho mínimo: **48x48dp** (acima do mínimo 44x44 — reações são frequentes)
- Disposição horizontal com espaçamento adequado para evitar toques acidentais

### Crisis mode — acessibilidade reforçada

- Alto contraste: texto `onSurface` sobre `rose-50` (ratio ≥ 4.5:1)
- Texto do countdown: tamanho `titleMedium` (não `labelSmall`)
- Animações reduzidas se `a11y_reduced_motion` ativo — sem transição de cor, mudança instantânea
- Input field com borda mais visível para indicar que o envio está bloqueado

### Touch targets

- Todos os botões interactivos no chat: **mínimo 44x44dp** (CLAUDE.md)
- Mensagens em lista: swipe-to-reply requer gesto amplo, não fiddly
- Long-press para menu: feedback visual imediato (highlight de fundo)
- Scroll-to-bottom FAB: **56x56dp** para fácil acesso

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-14-01 | WebSocket auth com Bearer token falha no Reverb (broadcasting/auth não aceita Sanctum) | Alta | Alto | Testar em Fase 0 (spike técnico). Se falhar: adicionar middleware para aceitar Bearer no broadcasting/auth endpoint. Fallback: Pusher cloud (SaaS) |
| RISK-14-02 | Max 10KB por mensagem WebSocket insuficiente para mensagens longas em crise | Baixa | Médio | Aumentar `max_request_size` no Reverb config. Mensagens de chat raramente excedem 10KB. Se exceder: truncar server-side com aviso |
| RISK-14-03 | FCM delivery unreliable em OEM chineses (Xiaomi, Huawei, Oppo — battery optimization agressiva) | Média | Médio | Documentar "whitelist Lumina do battery optimization" no onboarding. In-app polling como fallback (via WebSocket quando em foreground) |
| RISK-14-04 | Crisis detection false positives causam flood de ModeratorCrisisAlert | Média | Alto | Rate limit: max 1 crisis alert por sala por 5 minutos. CBTAnalysisService fallback local é conservador (prefere false negatives a false positives) |
| RISK-14-05 | Presence channel pode leakar status online de utilizadores que publicam anonimamente | Baixa | Alto | Presence data usa apenas pseudonym e user ID (nunca nome real). Silent room usa pseudonym explicitamente. Canal de sessão terapêutica é privado |
| RISK-14-06 | Reconexão WebSocket em rede instável causa duplicação de mensagens | Média | Médio | Deduplicação client-side por `message.id`. Room DB usa `INSERT OR REPLACE` com ID como primary key |

---

*Próximo: [15-audio-media-uploads.md](15-audio-media-uploads.md) — Áudio, media, uploads e conteúdos sensíveis.*
