# 15 — Áudio, Media, Uploads e Conteúdos Sensíveis

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

*Próximo: [16-gamificacao-servidor.md](16-gamificacao-servidor.md) — Gamificação e validações server-side.*
