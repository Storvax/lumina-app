# 15 — Áudio, Media, Uploads e Conteúdos Sensíveis

## Contexto

Este documento cobre a camada de media da app Android — gravação, reprodução, uploads e conteúdos
sensíveis. Numa plataforma de saúde mental, áudio e media são canais íntimos de expressão. A gravação
de um post de voz pode ser o momento em que alguém finalmente articula o que sente.

Refs:
- [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — storage disks (`whispers`, `wall`, `public`) e rate limiters
- [11-backend-gaps.md](11-backend-gaps.md) — Gap 8 (upload validation/storage), Gap 15 (gamification em breathing)
- [13-offline-sync.md](13-offline-sync.md) — sync queue para uploads offline, SQLCipher para cache
- [14-realtime-chat-notificacoes.md](14-realtime-chat-notificacoes.md) — somatic sync (haptic), foreground service para sons
- [16-gamificacao-servidor.md](16-gamificacao-servidor.md) — gamification trigger no primeiro post de áudio (+20 flames)
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — Risk R-04 (tamanho APK com assets bundled)

---

## Observações do estado atual

1. **Forum áudio**: formatos aceites `webm, mp3, wav, ogg`, max 10MB (`ForumController:100-117`),
   stored em disk `whispers` (public storage). **Gap:** Android grava em M4A (AAC) — backend
   precisa de adicionar `m4a` e `aac` à lista de formatos aceites
2. **Wall images**: formatos aceites `jpg, jpeg, png, webp`, max 5MB (`WallController:31-47`),
   stored em disk `wall` (public storage)
3. **Sound library**: CDN-hosted ambient sounds (`config/sound-library.php`) via Google Actions CDN.
   Sons portugueses: ondas de Nazaré, rio do Gerês, floresta de Sintra, Tram 28, guitarra de fado,
   café português, chuva do Porto, tempestade atlântica
4. **Filesystems**: `local` (private), `public` (served at `/storage`), `s3` (opcional, não configurado).
   Media URLs são relativas ao app URL
5. **Wall posts** requerem aprovação de moderador — `scopeApproved()` em WallPost filtra `is_approved = true`
6. **Post.audio_path** armazena caminho relativo dentro do disk `whispers`
7. **PlaylistSong model** para playlists comunitárias com `spotify_url`, `cover_url`, `votes_count`
8. **Sem vídeo** em toda a plataforma — decisão intencional (bandwidth, complexidade de moderação,
   risco de conteúdo inapropriado)
9. **Sem NSFW/AI detection** em uploads — Wall depende de aprovação manual. Forum áudio analisado
   por `CBTAnalysisService` apenas para deteção de crise (não conteúdo visual)

---

## 1. Gravação de áudio (posts de voz)

### Estado atual (web)
- Posts de áudio até 60 segundos
- Upload para disco `whispers/` (public storage)
- Armazenamento em `audio_path` no model Post

### Estratégia Android

**Gravação nativa com MediaRecorder:**

```kotlin
class AudioRecorder(private val context: Context) {
    private var recorder: MediaRecorder? = null
    private var outputFile: File? = null

    fun start(): File {
        outputFile = File(context.cacheDir, "recording_${System.currentTimeMillis()}.m4a")
        recorder = MediaRecorder(context).apply {
            setAudioSource(MediaRecorder.AudioSource.MIC)
            setOutputFormat(MediaRecorder.OutputFormat.MPEG_4)
            setAudioEncoder(MediaRecorder.AudioEncoder.AAC)
            setAudioSamplingRate(44100)
            setAudioEncodingBitRate(128000)
            setMaxDuration(60_000) // 60 segundos
            setOutputFile(outputFile!!.absolutePath)
            prepare()
            start()
        }
        return outputFile!!
    }

    fun stop(): File? {
        recorder?.stop()
        recorder?.release()
        recorder = null
        return outputFile
    }
}
```

**UI de gravação:**
- Botão de pressionar e segurar (press-and-hold) ou toggle
- Waveform em tempo real (amplitude visualization)
- Timer countdown (60s → 0s)
- Haptic feedback ao iniciar e parar
- Preview antes de enviar
- Opção de cancelar/regravar

**Formato:** AAC em contentor M4A — boa qualidade, tamanho compacto, suportado universalmente.

**Permissão:** `RECORD_AUDIO` — pedir no momento de uso com explicação contextual:
> "Para gravares a tua mensagem de voz, precisamos de aceder ao microfone."

---

## 2. Reprodução de áudio

### Posts de áudio no fórum

```kotlin
// ExoPlayer (Media3) para reprodução
class AudioPlayerManager(context: Context) {
    private val player = ExoPlayer.Builder(context).build()

    fun play(audioUrl: String) {
        val mediaItem = MediaItem.fromUri(audioUrl)
        player.setMediaItem(mediaItem)
        player.prepare()
        player.play()
    }

    fun pause() = player.pause()
    fun stop() = player.stop()
    fun seekTo(position: Long) = player.seekTo(position)

    val currentPosition: Long get() = player.currentPosition
    val duration: Long get() = player.duration
    val isPlaying: Boolean get() = player.isPlaying
}
```

**UI do player:**
- Waveform estático ou progress bar
- Play/Pause + seek
- Duração total e posição atual
- Velocidade de reprodução (1x, 1.5x, 2x)

### Sons relaxantes (Zona Calma)

- Múltiplos streams simultâneos (mixer de sons)
- Volume individual por som
- Continua em background (Foreground Service)
- Notification com media controls (play/pause/stop)
- Timer opcional ("parar após 30 minutos")

```kotlin
class SoundMixer {
    private val players = mutableMapOf<String, ExoPlayer>()

    fun addSound(soundId: String, uri: String, volume: Float) {
        val player = ExoPlayer.Builder(context).build().apply {
            setMediaItem(MediaItem.fromUri(uri))
            this.volume = volume
            repeatMode = Player.REPEAT_MODE_ALL
            prepare()
            play()
        }
        players[soundId] = player
    }

    fun setVolume(soundId: String, volume: Float) {
        players[soundId]?.volume = volume
    }

    fun removeSound(soundId: String) {
        players[soundId]?.release()
        players.remove(soundId)
    }

    fun stopAll() {
        players.values.forEach { it.release() }
        players.clear()
    }
}
```

**Assets de sons:**
- Opção A: Bundled na app (aumenta tamanho do APK)
- Opção B: Download on-demand + cache local (recomendado)
- Opção C: Streaming direto (requer rede)

**Recomendação:** Download on-demand com cache persistente. Sons são ficheiros pequenos (1-5MB cada), e o utilizador pode precisar deles offline.

---

## 3. Upload de ficheiros

### Estratégia geral

| Tipo | Tamanho típico | Endpoint | Formato |
|------|---------------|----------|---------|
| Áudio (post) | 500KB-2MB | `POST /api/v1/forum/posts` (multipart) | M4A (AAC) |
| Imagem (Wall) | 200KB-5MB | `POST /api/v1/wall` (multipart) | JPEG/PNG (compressed) |
| Imagem (avatar) | 50KB-500KB | `PATCH /api/v1/profile` (multipart) | JPEG (compressed) |

### Upload com Retrofit (multipart)

```kotlin
@Multipart
@POST("/api/v1/forum/posts")
suspend fun createPost(
    @Part("title") title: RequestBody,
    @Part("content") content: RequestBody,
    @Part("tag") tag: RequestBody,
    @Part audio: MultipartBody.Part? // Ficheiro de áudio
): PostResponse
```

### Compressão antes de upload

**Imagens:**
```kotlin
fun compressImage(uri: Uri, context: Context, maxWidth: Int = 1200): File {
    val bitmap = context.contentResolver.openInputStream(uri)
        ?.use { BitmapFactory.decodeStream(it) }
        ?: throw IOException("Cannot read image")

    val ratio = maxWidth.toFloat() / bitmap.width
    val scaledBitmap = if (ratio < 1f) {
        Bitmap.createScaledBitmap(bitmap, maxWidth, (bitmap.height * ratio).toInt(), true)
    } else bitmap

    val outputFile = File(context.cacheDir, "upload_${System.currentTimeMillis()}.jpg")
    FileOutputStream(outputFile).use {
        scaledBitmap.compress(Bitmap.CompressFormat.JPEG, 85, it)
    }
    return outputFile
}
```

**Áudio:** AAC a 128kbps já é compacto. Sem compressão adicional necessária.

### Retry de upload

```kotlin
suspend fun uploadWithRetry(
    file: File,
    maxRetries: Int = 3,
    upload: suspend (File) -> Response
): Response {
    var attempt = 0
    var lastException: Exception? = null

    while (attempt < maxRetries) {
        try {
            val response = upload(file)
            if (response.isSuccessful) return response
            if (response.code() in 400..499) throw HttpException(response) // Não retry em 4xx
        } catch (e: IOException) {
            lastException = e
        }
        attempt++
        delay(2000L * attempt) // Backoff: 2s, 4s, 6s
    }
    throw lastException ?: IOException("Upload failed after $maxRetries attempts")
}
```

### Progress tracking

```kotlin
class ProgressRequestBody(
    private val file: File,
    private val contentType: MediaType,
    private val onProgress: (Float) -> Unit
) : RequestBody() {
    override fun contentType() = contentType
    override fun contentLength() = file.length()

    override fun writeTo(sink: BufferedSink) {
        val source = file.source()
        var totalBytesRead = 0L
        val bufferSize = 8192L

        while (true) {
            val bytesRead = source.read(sink.buffer, bufferSize)
            if (bytesRead == -1L) break
            totalBytesRead += bytesRead
            sink.flush()
            onProgress(totalBytesRead.toFloat() / contentLength())
        }
    }
}
```

---

## 4. Conteúdo sensível (blur e revelação)

### Estado atual (web)
- Posts marcados `is_sensitive = true` têm blur CSS
- Click para revelar

### Estratégia Android

```kotlin
@Composable
fun SensitiveContent(
    isSensitive: Boolean,
    content: @Composable () -> Unit
) {
    var isRevealed by remember { mutableStateOf(false) }

    if (isSensitive && !isRevealed) {
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .blur(20.dp)
                .pointerInput(Unit) {
                    detectTapGestures(
                        onLongPress = {
                            // Haptic feedback
                            isRevealed = true
                        }
                    )
                },
            contentAlignment = Alignment.Center
        ) {
            content()
            // Overlay
            Surface(
                modifier = Modifier.fillMaxSize(),
                color = MaterialTheme.colorScheme.surface.copy(alpha = 0.7f)
            ) {
                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    verticalArrangement = Arrangement.Center
                ) {
                    Icon(Icons.Outlined.Warning, "Conteúdo sensível")
                    Text("Este conteúdo pode ser difícil.")
                    Text("Pressiona sem largar para revelar.", style = caption)
                }
            }
        }
    } else {
        content()
    }
}
```

**Decisão:** Long-press (pressionar sem largar) em vez de tap simples para revelar — é mais intencional e previne revelação acidental.

---

## 5. Imagens (The Wall + avatars)

### Photo Picker (Android 13+)

```kotlin
val pickMedia = rememberLauncherForActivityResult(
    contract = ActivityResultContracts.PickVisualMedia()
) { uri ->
    uri?.let { processAndUpload(it) }
}

// Trigger
pickMedia.launch(PickVisualMediaRequest(ActivityResultContracts.PickVisualMedia.ImageOnly))
```

**Fallback (Android <13):** `ActivityResultContracts.GetContent()` com `"image/*"`.

### Cache de imagens (Coil)

```kotlin
AsyncImage(
    model = ImageRequest.Builder(LocalContext.current)
        .data(imageUrl)
        .crossfade(true)
        .placeholder(R.drawable.placeholder_calm)
        .error(R.drawable.error_image)
        .memoryCachePolicy(CachePolicy.ENABLED)
        .diskCachePolicy(CachePolicy.ENABLED)
        .build(),
    contentDescription = "Imagem do mural",
    modifier = Modifier.fillMaxWidth()
)
```

---

## 6. Playlist (Spotify/iTunes)

### Estado atual (web)
- Sugestão de músicas com Spotify oEmbed
- Busca de metadados via iTunes API

### Estratégia Android

- **Deep links para Spotify:** Abrir a música na app Spotify (se instalada) ou no browser
- **Não reproduzir áudio na app** — evita problemas de licenciamento
- **Preview de 30s via iTunes API** — se autorizado, reproduzir preview

```kotlin
fun openInSpotify(spotifyUri: String, context: Context) {
    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(spotifyUri))
    if (intent.resolveActivity(context.packageManager) != null) {
        context.startActivity(intent) // Abre Spotify app
    } else {
        // Fallback: abrir no browser
        val webIntent = Intent(Intent.ACTION_VIEW, Uri.parse(spotifyWebUrl))
        context.startActivity(webIntent)
    }
}
```

---

## 7. Vibração háptica (Sintonia / Heartbeat)

### Exercícios somáticos

A funcionalidade de "Sintonia" usa vibração para simular batimento cardíaco:

```kotlin
class HapticEngine(private val context: Context) {
    private val vibrator = context.getSystemService<Vibrator>()

    // Padrão de batimento cardíaco: lub-dub ... lub-dub
    fun heartbeat(bpm: Int = 60) {
        val interval = (60_000L / bpm) // ms entre batimentos
        val pattern = longArrayOf(
            0,      // Delay inicial
            50,     // Vibrar (lub)
            100,    // Pausa
            50,     // Vibrar (dub)
            interval - 200  // Pausa até próximo batimento
        )
        val amplitudes = intArrayOf(0, 200, 0, 150, 0) // Intensidade

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            vibrator?.vibrate(VibrationEffect.createWaveform(pattern, amplitudes, 0))
        }
    }

    // Padrão de respiração: vibrar no inspirar, pausa, vibrar no expirar
    fun breathingGuide(inhaleMs: Long = 4000, holdMs: Long = 4000, exhaleMs: Long = 4000) {
        val pattern = longArrayOf(
            0, inhaleMs,  // Vibrar durante inspiração
            holdMs,       // Pausa (hold)
            exhaleMs,     // Vibrar durante expiração
            2000          // Pausa entre ciclos
        )
        vibrator?.vibrate(VibrationEffect.createWaveform(pattern, 0))
    }

    fun stop() {
        vibrator?.cancel()
    }
}
```

---

## 8. Permission handling

### Princípio

Permissões devem ser pedidas **no momento de uso**, nunca no primeiro launch. A explicação deve
ser empática e contextual — o utilizador deve entender porquê e sentir-se seguro.

### Permissões necessárias

| Permissão | Quando pedir | Explicação (PT-PT) |
|-----------|-------------|-------------------|
| `RECORD_AUDIO` | Primeiro toque no botão de gravação | "Para gravares a tua mensagem de voz, precisamos de aceder ao microfone." |
| `READ_MEDIA_IMAGES` (Android 13+) | Primeiro toque em "Adicionar imagem" | "Para partilhares uma imagem no Mural, precisamos de aceder às tuas fotos." |
| `READ_EXTERNAL_STORAGE` (Android <13) | Idem | Idem |
| `POST_NOTIFICATIONS` (Android 13+) | Após onboarding completo | "Para te avisarmos de interações importantes, precisamos de enviar notificações." |

### Graceful degradation

| Permissão negada | Comportamento | UI |
|-----------------|-------------|-----|
| `RECORD_AUDIO` | Ocultar botão de gravação | "Ativa o microfone nas Definições para gravar mensagens de voz." (link para settings) |
| `READ_MEDIA_IMAGES` | Ocultar picker de imagem | Modo texto-only para Wall e avatar |
| `POST_NOTIFICATIONS` | Apenas notificações in-app | Badge no ícone de notificações. Sugestão suave periódica para ativar |

### "Não perguntar novamente"

```kotlin
// Detetar se user marcou "Não perguntar novamente"
if (!shouldShowRequestPermissionRationale(permission)) {
    // Mostrar dialog com deep link para Settings
    showSettingsDialog(
        title = "Permissão necessária",
        message = "Para usar esta funcionalidade, ativa a permissão nas definições do dispositivo.",
        onSettings = {
            val intent = Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS)
            intent.data = Uri.parse("package:${context.packageName}")
            context.startActivity(intent)
        }
    )
}
```

### Regra

Nunca pedir **todas as permissões de uma vez**. Cada permissão é pedida no momento
em que o utilizador precisa da funcionalidade. Isto aumenta a taxa de aceitação e
respeita a autonomia do utilizador.

---

## 9. Upload em offline/poor connectivity

### Fluxo

```
Utilizador cria post com áudio
    │
    ├─ Online → upload direto via Retrofit (multipart)
    │    ├─ Sucesso → post publicado
    │    └─ Falha (timeout/5xx) → retry (secção 3)
    │
    └─ Offline → queue na sync_queue (ref. doc 13 secção 13)
         ├─ Prioridade P3 (interação social)
         ├─ UI: "O teu post será enviado quando voltares a ter rede"
         ├─ Ficheiro mantido em cache dir
         └─ WorkManager UploadWorker com NetworkType.CONNECTED
```

### Estados de upload

| Estado | Indicador visual | Ação disponível |
|--------|-----------------|----------------|
| `queued` | Ícone relógio + "Na fila" | Cancelar |
| `uploading` | Progress bar (%) | Cancelar |
| `completed` | Check verde | — |
| `failed` | Ícone warning + "Falhou. Tentar novamente?" | Retry / Cancelar |

### Progress tracking

O `ProgressRequestBody` (secção 3) emite progresso que o ViewModel observa:

```kotlin
sealed class UploadState {
    object Idle : UploadState()
    data class Queued(val fileName: String) : UploadState()
    data class Uploading(val progress: Float) : UploadState() // 0.0 - 1.0
    data class Completed(val postId: Long) : UploadState()
    data class Failed(val error: String, val canRetry: Boolean) : UploadState()
}
```

### Resume de uploads parciais

- Se servidor suportar `Range` headers: tentar resume com `Content-Range`
- Se não: reiniciar upload completo (aceitável para ficheiros < 10MB)
- Verificar suporte: `HEAD` request ao endpoint, verificar `Accept-Ranges: bytes`

### Cancelamento

- Upload queued: remover da sync_queue + apagar ficheiro temporário
- Upload em progresso: cancelar coroutine do Retrofit call + cleanup
- Ficheiro original mantido até confirmação do servidor (nunca apagar antes da confirmação)

---

## 10. Audio recording durante crise

### Princípio

O utilizador pode estar a gravar um post de voz enquanto chora ou em pânico. A experiência de
gravação deve ser a mais simples e resiliente possível.

### UX adaptada

| Aspeto | Normal | Crise (mood ≤ 2 ou safety plan ativo) |
|--------|--------|--------------------------------------|
| Botão de gravação | Tamanho normal (56dp) | Tamanho aumentado (72dp) — mais fácil de acertar |
| Waveform | Cores vibrantes | Cores suaves (emerald-200) — calmo |
| Timer | Countdown numérico | Countdown com texto: "Tens tempo. Sem pressa." |
| Controles | Play, Stop, Delete, Send | Play, Stop, Send (ocultar Delete — prevenir descarte acidental) |

### Soft timeout a 60s

- **55s:** vibração suave + texto "Quase a terminar..." (tom gentil)
- **60s:** fade-out suave (diminuição gradual de gain) + auto-stop
  - **Não** um corte abrupto — o AAC em container M4A suporta fade-out via MediaRecorder
- Se o utilizador quiser continuar: botão "Gravar mais" inicia nova gravação que será concatenada

### Recuperação após crash

- AAC em container M4A é **playable mesmo se truncado** — o container permite seeking parcial
- Se app killed durante gravação: ficheiro parcial `.m4a` permanece em `cacheDir`
- No próximo launch: `DraftRecoveryManager` (ref. doc 13 secção 12) deteta áudio órfão
- UI: "Tens uma gravação por terminar. Queres usá-la num novo post?"

### Erros do MediaRecorder

```kotlin
// MediaRecorder pode falhar em alguns OEMs (Samsung, Xiaomi known issues)
try {
    recorder.prepare()
    recorder.start()
} catch (e: Exception) {
    // Fallback: AudioRecord API (mais baixo nível, mais fiável)
    fallbackToAudioRecord()
}
```

---

## 11. Storage budget

### Orçamento por categoria

| Categoria | Budget máximo | Estratégia de evição | Justificação |
|-----------|-------------|---------------------|-------------|
| Room DB (SQLCipher) | 50MB | Retention policies (secção 9) | Diary 30 dias, messages 7 dias |
| Áudio cache | 100MB | LRU (least recently accessed) | Posts ouvidos recentemente |
| Imagens (Coil) | 250MB | Coil gerido automaticamente | Feed + Wall + avatars |
| Sons Calm Zone | 50MB | Persistente (on-demand download) | Sons descarregados pelo utilizador |
| Uploads temporários | 20MB | Apagar após upload confirmado | Ficheiros a aguardar upload |
| **Total** | **~470MB** | — | — |

### LRU eviction

```kotlin
class MediaCacheManager(
    private val cacheDir: File,
    private val maxSizeBytes: Long = 100 * 1024 * 1024 // 100MB
) {
    fun evictIfNeeded() {
        val files = cacheDir.listFiles()?.sortedBy { it.lastModified() } ?: return
        var totalSize = files.sumOf { it.length() }

        for (file in files) {
            if (totalSize <= maxSizeBytes) break
            totalSize -= file.length()
            file.delete()
        }
    }
}
```

### Low storage

- Detetar via `ACTION_DEVICE_STORAGE_LOW` broadcast
- Quando ativo: eviction agressiva — reduzir áudio cache para 50MB, Coil para 100MB
- Mostrar ao utilizador: "O teu dispositivo tem pouco espaço. Algumas imagens e áudios
  cacheados foram removidos."

### Setting do utilizador

- Em Definições → Armazenamento: breakdown visual do espaço usado
- Botão "Limpar cache": apaga áudio + imagens. Mantém Room DB e sons da Calm Zone
- Descrição: "Isto não apaga os teus dados — apenas as cópias temporárias."

### Devices modestos

- Em dispositivos com < 32GB storage total: reduzir budgets automaticamente
  - Coil: 100MB (em vez de 250MB)
  - Áudio: 50MB (em vez de 100MB)
  - Total target: ~270MB

---

## 12. Acessibilidade para áudio

### Posts de voz

| Aspeto | Implementação |
|--------|-------------|
| Screen reader | "Mensagem de voz, {duração} segundos. Toca para reproduzir." |
| Play/Pause | contentDescription: "Reproduzir" / "Pausar" |
| Seek bar | contentDescription: "Posição: {segundos} de {total}" |
| Touch targets | Mínimo 44x44dp para play/pause/seek (CLAUDE.md) |
| Waveform | `contentDescription = null` — puramente decorativa, não anunciada |

### Transcrição (futuro)

- **Fase 1:** sem transcrição automática — custo e complexidade elevados
- **Fase futura:** considerar Whisper API ou Google Speech-to-Text para gerar legendas
- Se implementado: legendas opcionais sob o player ("Mostrar transcrição")

### Imagens do Wall

- **alt_text opcional** — ao submeter imagem, campo "Descrever imagem (opcional)" para acessibilidade
- Se não fornecido: `contentDescription = "Imagem partilhada no mural"`
- Moderadores podem adicionar alt_text durante revisão

### Sound mixer (Calm Zone)

| Elemento | Acessibilidade |
|----------|---------------|
| Cada som | Label: nome do som + volume percentual. Ex: "Ondas de Nazaré, 70%" |
| Slider de volume | `contentDescription = "Volume de {som}. {X} por cento"` |
| Botão play/stop | "Reproduzir {som}" / "Parar {som}" |
| Timer | "Parar após {minutos} minutos" |

### Exercícios hápticos (somáticos)

- Screen reader anuncia: "Exercício háptico ativo: {tipo}" (breathing/heartbeat/grounding)
- Para utilizadores que não sentem vibração: alternativa visual (pulsação na tela)
- `a11y_reduced_motion = true`: vibração sem animação visual

---

## 13. Content moderation pipeline para uploads

### Pipeline por tipo de conteúdo

| Tipo | Análise | Quando | Resultado |
|------|---------|--------|----------|
| **Wall images** | Moderador manual | Antes de publicar | `pending_review` → `approved` / `rejected` |
| **Forum áudio** | `CBTAnalysisService` (server-side) | Após upload, antes de publicar | Crisis detected → `ModeratorCrisisAlert` |
| **Forum texto** | `CBTAnalysisService` (server-side) | No `POST /posts` | `is_sensitive` flag set, crisis alert se necessário |
| **Chat messages** | `CBTAnalysisService` (real-time) | Em `ChatController::send()` | Crisis mode alert imediato |

### Zero análise client-side

Toda a moderação acontece no servidor. O Android:
- **Não** analisa conteúdo localmente
- **Não** bloqueia envio baseado em keywords
- **Não** tem listas de palavras proibidas
- Apenas envia e respeita o resultado do servidor

### Status de upload no response

```json
{
  "data": {
    "id": 123,
    "status": "pending_review",
    "message": "A tua imagem está em revisão. Será visível em breve."
  }
}
```

| Status | Significado | UI Android |
|--------|-----------|-----------|
| `published` | Conteúdo visível para todos | Indicador verde: "Publicado" |
| `pending_review` | A aguardar moderação | Indicador amber: "Em revisão" |
| `flagged` | Sinalizado por moderador | Indicador suave: "O teu conteúdo foi sinalizado para revisão" |

### Linguagem de moderação

- **Nunca** usar "rejeitado", "bloqueado", "proibido" — linguagem demasiado agressiva
- Usar: "em revisão", "sinalizado", "a aguardar aprovação"
- Tom: informativo e empático, nunca punitivo
- Se conteúdo removido: "O teu conteúdo foi revisto pela equipa de moderação.
  Se tiveres dúvidas, contacta-nos."

---

## 14. Secure deletion de cached media

### Quando apagar

| Trigger | O que apagar | Método |
|---------|-------------|--------|
| **Logout** | Tudo: áudio, imagens, Room DB, sons, uploads temp | Secure delete + file delete |
| **GDPR account deletion** | Idem + confirmar eliminação server-side via API | Secure delete + API call |
| **Cache cleanup** (manual) | Áudio + imagens. **Manter** Room DB e sons | File delete normal |
| **Periodic cleanup** | Ficheiros expirados (TTL) | File delete normal |

### Secure deletion

Para proteger contra recuperação em dispositivos rooted:

```kotlin
// Extension function para eliminação segura
fun File.deleteSecurely() {
    if (this.exists()) {
        // Overwrite com zeros antes de apagar
        val length = this.length()
        RandomAccessFile(this, "rw").use { raf ->
            raf.seek(0)
            val buffer = ByteArray(8192)
            var written = 0L
            while (written < length) {
                val toWrite = minOf(8192L, length - written).toInt()
                raf.write(buffer, 0, toWrite)
                written += toWrite
            }
        }
        this.delete()
    }
}
```

### Localização dos ficheiros

- **Cache dir** (`context.cacheDir`): uploads temporários, áudio gravado, imagens comprimidas
- **Não** em external storage — ficheiros NÃO visíveis em gallery apps
- Room DB em `context.getDatabasePath()` — encriptado via SQLCipher
- Sons da Calm Zone em `context.filesDir` / `sounds/` — persistentes mas não sensíveis

### GDPR flow no Android

1. Utilizador toca "Eliminar conta" em Definições → Privacidade
2. Confirmação com texto claro: "Todos os teus dados serão eliminados permanentemente."
3. API: `DELETE /api/v1/profile` → backend queue `ProcessGdprDeletion` job
4. Resposta: 202 Accepted (processamento assíncrono)
5. Android: apagar todos os dados locais imediatamente (não esperar pelo backend)
6. Forçar logout + navegar para ecrã de boas-vindas
7. Backend envia email de certificado de eliminação quando job concluir

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-15-01 | M4A não nos formatos aceites do backend (atualmente `webm, mp3, wav, ogg`) | Alta | Alto | Adicionar `m4a` e `aac` à validation rule no ForumController. Documentar como Gap 8 update em [11-backend-gaps.md](11-backend-gaps.md) |
| RISK-15-02 | Uploads de áudio grandes em rede lenta causam timeout | Média | Médio | Aumentar write timeout para 120s em uploads. Implementar chunked upload se ficheiros > 5MB forem frequentes |
| RISK-15-03 | MediaRecorder crash em OEM específicos (Samsung, Xiaomi known issues) | Média | Médio | Wrap em try/catch. Fallback: AudioRecord API (mais baixo nível, mais fiável mas requer encoding manual) |
| RISK-15-04 | Coil 250MB disk cache enche storage em low-end devices | Baixa | Médio | Reduzir para 100MB em dispositivos com < 32GB. Detetar via `StatFs` |
| RISK-15-05 | Sons CDN Google Actions ficam indisponíveis (URLs externas) | Baixa | Alto | Cache persistente local após primeiro download. Considerar hosting próprio dos sons mais populares como fallback |

---

*Próximo: [16-gamificacao-servidor.md](16-gamificacao-servidor.md) — Gamificação e validações server-side.*
