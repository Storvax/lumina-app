# 14 — Real-time, Chat, Notificações e Presença

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

*Próximo: [15-audio-media-uploads.md](15-audio-media-uploads.md) — Áudio, media, uploads e conteúdos sensíveis.*
