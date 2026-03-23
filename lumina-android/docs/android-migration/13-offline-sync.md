# 13 — Offline, Autosave, Sincronização e Resiliência

## Contexto

Este documento é a fundação para toda a resiliência da app Android. Numa plataforma de saúde mental,
perda de dados ou falha visível de rede pode causar ansiedade adicional ao utilizador — inaceitável.

Refs:
- [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — padrões de cache (ETag, Cache-Control) e rate limiters reutilizáveis
- [11-backend-gaps.md](11-backend-gaps.md) — Gap 5 (response format), Gap 13 (idempotência) afetam sync
- [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) — token expiration (30 dias) e refresh em contexto offline
- [14-realtime-chat-notificacoes.md](14-realtime-chat-notificacoes.md) — reconexão WebSocket depende deste offline layer
- [15-audio-media-uploads.md](15-audio-media-uploads.md) — uploads queued em offline usam a sync queue aqui definida
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — riscos R-03 (conflitos sync) e R-07 (dados locais)

---

## Observações do estado atual

1. **DailyLogController** usa `updateOrCreate()` com `log_date` como chave — idempotência natural para sync.
   Se a app enviar a mesma entrada duas vezes, o backend sobrepõe sem duplicar
2. **ForumController** já usa `cursorPaginate(20)` para AJAX — mesmo padrão cursor reutilizável para
   API offline pagination (ref. [10-backend-reutilizacao.md](10-backend-reutilizacao.md) secção Paginação)
3. **Message.content** usa cast `encrypted` (`app/Models/Message.php`) — o conteúdo é encriptado at rest
   no servidor. Room DB cache no Android precisa da sua própria camada de encriptação (secção 10)
4. **VaultItem.content** usa cast `encrypted` (`app/Models/VaultItem.php`) — mesma consideração.
   Dados do cofre pessoal são duplamente sensíveis (privados + emocionais)
5. **Sem mecanismo offline/sync na web app** — a web depende de sessão server-side com `session()->flash()`.
   O Android constrói offline-first do zero, sem padrões a portar do backend

---

## Princípio

A Lumina é usada por pessoas em momentos vulneráveis. Perder dados ou ver uma tela de erro por falta de rede é inaceitável. A app deve ser resiliente: funcionar offline quando possível, guardar drafts automaticamente, e sincronizar quando a rede voltar — tudo de forma transparente.

---

## 1. Classificação offline por funcionalidade

| Funcionalidade | Nível offline | Estratégia |
|---------------|--------------|-----------|
| **Zona Calma — exercícios** | Full offline | Tudo local, sem backend |
| **Zona Calma — cofre** | Read offline, write queued | Cache local + sync |
| **Plano de crise** | Full offline | Pre-fetched e cacheado |
| **Diário — leitura** | Read offline | Cache dos últimos 30 dias |
| **Diário — escrita** | Write queued | Draft local, sync quando há rede |
| **Dashboard** | Stale data | Última versão cacheada |
| **Perfil** | Read offline | Cache local |
| **Missões** | Read offline | Cache diário |
| **Fórum — feed** | Read offline (limitado) | Cache dos últimos posts vistos |
| **Fórum — escrita** | Write queued | Draft com sync |
| **Chat** | Sem offline | Real-time only, mostra histórico cacheado |
| **Pesquisa** | Sem offline | Requer backend |
| **Auto-avaliação** | Sem offline | Requer scoring server-side |

---

## 2. Camadas de cache

```
┌──────────────────────┐
│     UI (Compose)     │ ← Observa StateFlow
├──────────────────────┤
│    ViewModel State   │ ← In-memory, lifecycle-aware
├──────────────────────┤
│     Room Database    │ ← Persistent, source of truth local
├──────────────────────┤
│  Network (Retrofit)  │ ← API calls, atualiza Room
└──────────────────────┘
```

### Room como source of truth local

A UI nunca lê diretamente da API. O fluxo é sempre:
1. API response → guardar em Room DB
2. Room DB emite Flow → ViewModel coleta → UI recompõe

```kotlin
// Repository pattern (offline-first)
class DiaryRepositoryImpl(
    private val api: DiaryApi,
    private val dao: DailyLogDao
) : DiaryRepository {

    override fun getRecentEntries(): Flow<List<DailyLogEntry>> {
        return dao.getRecentEntries() // Sempre emite da Room
    }

    override suspend fun refresh() {
        try {
            val remote = api.getEntries()
            dao.replaceAll(remote.map { it.toEntity() })
        } catch (e: IOException) {
            // Silently fail — data already available from cache
        }
    }
}
```

### Estratégias de cache por tipo

| Tipo de dados | TTL cache | Refresh trigger |
|-------------|-----------|----------------|
| Perfil do utilizador | 24h | Login, pull-to-refresh |
| Entradas do diário | 30 dias | Abrir diário, pull-to-refresh |
| Missões do dia | 24h (reset à meia-noite) | Abrir dashboard |
| Dashboard data | 5 min | Pull-to-refresh, resume app |
| Posts do fórum | 15 min | Pull-to-refresh, scroll feed |
| Cofre pessoal | Permanente (até sync) | Abrir cofre |
| Plano de crise | Permanente (até update) | Editar plano |
| Salas de chat | 5 min | Abrir lista de salas |

---

## 3. Autosave

### Diário emocional

```
User escreve nota
    │
    ├─ A cada 5s de pausa → guardar draft em Room DB
    │
    ├─ User toca "Guardar" → marcar como final + sync API
    │
    └─ User sai sem guardar → draft persiste
        │
        └─ Próxima abertura → restaurar draft
            "Tens um rascunho por terminar. Queres continuar?"
```

**Schema Room para drafts:**
```kotlin
@Entity(tableName = "diary_drafts")
data class DiaryDraftEntity(
    @PrimaryKey val date: String,    // "2026-03-21"
    val moodLevel: Int?,
    val tags: String?,               // JSON array
    val note: String?,
    val updatedAt: Long              // Timestamp
)
```

### Posts do fórum (Fase 2)

Mesmo padrão: draft local com auto-save a cada 5s. Suporta texto e referência a áudio gravado.

### Cofre pessoal

Escrita imediata em Room DB. Sync com backend em background.

---

## 4. Sync queue (operações pendentes)

Para operações de escrita que falham por falta de rede:

```kotlin
@Entity(tableName = "sync_queue")
data class SyncQueueEntity(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val action: String,          // "create_diary_entry", "create_vault_item", etc.
    val payload: String,         // JSON serializado
    val createdAt: Long,
    val retryCount: Int = 0,
    val maxRetries: Int = 5,
    val status: String = "pending" // pending, processing, failed
)
```

**WorkManager para sync:**
```kotlin
class SyncWorker(context: Context, params: WorkerParameters) : CoroutineWorker(context, params) {

    override suspend fun doWork(): Result {
        val pendingOps = syncQueueDao.getPending()
        for (op in pendingOps) {
            try {
                executeSyncOperation(op)
                syncQueueDao.delete(op)
            } catch (e: IOException) {
                syncQueueDao.incrementRetry(op.id)
                if (op.retryCount >= op.maxRetries) {
                    syncQueueDao.markFailed(op.id)
                }
            }
        }
        return if (syncQueueDao.hasPending()) Result.retry() else Result.success()
    }
}
```

**Trigger de sync:**
- Quando a rede volta (NetworkCallback)
- Quando a app abre (resume)
- Periodicamente (WorkManager periodic, 15 min mínimo)

---

## 5. Conflict resolution

### Cenário: diário editado offline e online

**Regra:** Last-write-wins com timestamp do servidor.

```
Offline write (T1: 14:00) → queued
Online write via web (T2: 14:05) → saved on server
App sync (T3: 14:10) → server deteta T1 < T2 → rejeita offline write
App mostra: "A tua entrada foi atualizada noutro dispositivo. Queres manter esta versão?"
```

**Para a fase 1, simplificar:**
- Server timestamp é autoridade
- Conflitos raros (1 user = 1 dispositivo normalmente)
- Se conflito: server wins, notificar user

---

## 6. Optimistic UI

Para ações rápidas (reações, toggles), mostrar resultado imediato sem esperar pelo server:

```kotlin
// ViewModel
fun onReact(postId: Long, reactionType: String) {
    // 1. Atualizar UI imediatamente
    _state.update { it.copy(
        reactions = it.reactions.toggleReaction(postId, reactionType)
    )}

    // 2. Enviar para server em background
    viewModelScope.launch {
        val result = reactToPostUseCase(postId, reactionType)
        if (result.isFailure) {
            // 3. Reverter se falhou
            _state.update { it.copy(
                reactions = it.reactions.toggleReaction(postId, reactionType) // Undo
            )}
            _effects.emit(ShowError("Não foi possível reagir. Tenta novamente."))
        }
    }
}
```

**Aplicar optimistic UI em:**
- Reações a posts e mensagens
- Toggle save/bookmark
- Toggle subscribe
- Marcar notificações como lidas
- Guardar no cofre

**Não aplicar em:**
- Criar post (depende de AI crisis detection)
- Criar entrada de diário (depende de CBT analysis)
- Operações de moderação

---

## 7. Indicadores de estado de conectividade

### ConnectivityMonitor

```kotlin
class ConnectivityMonitor(context: Context) {
    private val connectivityManager = context.getSystemService<ConnectivityManager>()

    val isOnline: StateFlow<Boolean> = callbackFlow {
        val callback = object : ConnectivityManager.NetworkCallback() {
            override fun onAvailable(network: Network) { trySend(true) }
            override fun onLost(network: Network) { trySend(false) }
        }
        connectivityManager.registerDefaultNetworkCallback(callback)
        awaitClose { connectivityManager.unregisterNetworkCallback(callback) }
    }.stateIn(CoroutineScope(Dispatchers.Default), SharingStarted.Eagerly, true)
}
```

### UI feedback

- **Banner discreto** quando offline: "Estás offline. As tuas alterações serão guardadas localmente."
- **Indicador de sync** quando há operações pendentes: ícone de sync na toolbar
- **Auto-dismiss** quando rede volta: "De volta online. A sincronizar..."
- **Tom acolhedor** — nunca alarmista

---

## 8. Resiliência de rede

### Retry com backoff exponencial

```kotlin
class RetryInterceptor : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        var attempt = 0
        val maxRetries = 3
        var lastException: IOException? = null

        while (attempt < maxRetries) {
            try {
                return chain.proceed(chain.request())
            } catch (e: IOException) {
                lastException = e
                attempt++
                if (attempt < maxRetries) {
                    Thread.sleep((1000L * 2.0.pow(attempt)).toLong()) // 2s, 4s
                }
            }
        }
        throw lastException!!
    }
}
```

### Timeouts

| Operação | Connect | Read | Write |
|----------|---------|------|-------|
| API calls standard | 15s | 30s | 30s |
| Upload de ficheiros | 15s | 60s | 60s |
| AI-powered endpoints | 15s | 45s | 30s |
| WebSocket handshake | 10s | — | — |

---

## 9. Limpeza de dados locais

### Logout
- Apagar todos os dados de Room DB
- Limpar EncryptedSharedPreferences (token)
- Limpar DataStore (preferences)
- Limpar image cache (Coil)
- Cancelar WorkManager jobs

### Periodic cleanup
- Entradas de diário mais antigas que `diary_retention_days` → apagar da Room
- Sync queue com status `failed` mais de 7 dias → apagar
- Image cache: Coil gere automaticamente (max 250MB disk)

---

## 10. Encriptação at rest para Room DB

### Problema

Dados sensíveis cacheados localmente — diary entries, vault items, mensagens de chat, payloads na
sync queue — ficam em plaintext no SQLite padrão. Num dispositivo rooted ou comprometido, estes dados
são acessíveis diretamente.

### Solução: SQLCipher

Integrar **SQLCipher** via `net.zetetic:android-database-sqlcipher` para encriptar toda a base Room.

```kotlin
// Room builder com SQLCipher
val passphrase = getOrCreatePassphrase() // EncryptedSharedPreferences
val factory = SupportFactory(SQLiteDatabase.getBytes(passphrase.toCharArray()))

Room.databaseBuilder(context, LuminaDatabase::class.java, "lumina.db")
    .openHelperFactory(factory)
    .build()
```

### Key management

- **Derivação:** chave derivada do hash do token Sanctum, armazenada em `EncryptedSharedPreferences`
  (backed by Android Keystore)
- **Rotação:** quando o token é refreshed, a chave não muda (derivada do token original, não do refresh)
- **Backup:** chave também guardada no Android Keystore como fallback se ESP corromper
- **Logout:** chave destruída → base de dados ilegível → delete do ficheiro

### Classificação de tabelas

| Tabela | Encriptada? | Justificação |
|--------|------------|-------------|
| `diary_drafts` | ✅ Sim (via SQLCipher) | Dados emocionais pessoais |
| `vault_items_cache` | ✅ Sim (via SQLCipher) | Conteúdo privado encriptado no servidor |
| `messages_cache` | ✅ Sim (via SQLCipher) | Mensagens encriptadas no servidor |
| `sync_queue` | ✅ Sim (via SQLCipher) | Payloads contêm dados pessoais serializados |
| `forum_posts_cache` | ✅ Sim (via SQLCipher) | Abrangido pela encriptação global da DB |
| `missions_cache` | ✅ Sim (via SQLCipher) | Abrangido pela encriptação global da DB |

**Nota:** SQLCipher encripta a base de dados inteira (não tabelas individuais). A classificação acima
justifica porque a encriptação é necessária — basta uma tabela sensível para justificar.

### Performance

SQLCipher adiciona ~5-15% overhead em operações de leitura/escrita. Para os volumes de dados da Lumina
(centenas de registos, não milhões), este overhead é imperceptível. Não otimizar prematuramente.

---

## 11. Autosave UX durante crise

### Princípio

Um utilizador em crise pode estar a chorar, tremer, ou em pânico. A app **nunca** pode perder o que
essa pessoa estava a escrever. O autosave deve funcionar de forma invisível e infalível.

### Comportamento adaptativo

| Contexto | Intervalo autosave | Justificação |
|----------|-------------------|-------------|
| Normal | 5s de pausa na escrita | Equilíbrio entre I/O e proteção |
| Mood baixo (mood_level ≤ 2 na última entry) | 2s de pausa | Utilizador potencialmente vulnerável |
| Crise ativa (safety plan aberto/editado) | 2s de pausa + save em `ON_STOP` | Máxima proteção |

### Detecção de estado

```kotlin
// AutosaveManager adapta intervalo baseado no contexto emocional
class AutosaveManager(
    private val userStateRepository: UserStateRepository,
    private val draftDao: DraftDao
) {
    val saveInterval: Flow<Long> = userStateRepository.currentMoodLevel.map { mood ->
        when {
            mood != null && mood <= 2 -> 2_000L  // 2s — utilizador vulnerável
            else -> 5_000L                        // 5s — normal
        }
    }
}
```

### Restauração silenciosa

- **Sem diálogos de confirmação complexos** — o utilizador em crise não deve tomar decisões
- Draft de diary/forum: restaurar silenciosamente com toast suave "Rascunho restaurado"
- Draft de safety plan: **auto-restore sem perguntar** — demasiado crítico para arriscar descarte
- Indicador visual: dot pulsante junto ao campo de texto (cor suave, `emerald-400`)
  com texto "a guardar..." — calmo, não alarmante

### Lifecycle hooks

```kotlin
// ProcessLifecycleOwner — save imediato quando app vai para background
lifecycle.addObserver(object : DefaultLifecycleObserver {
    override fun onStop(owner: LifecycleOwner) {
        // Save imediato de TODOS os drafts ativos
        coroutineScope.launch { draftDao.saveAllPending() }
    }
})

// ComponentCallbacks2 — emergency save em low memory
override fun onTrimMemory(level: Int) {
    if (level >= ComponentCallbacks2.TRIM_MEMORY_COMPLETE) {
        runBlocking { draftDao.saveAllPending() } // Blocking — último recurso
    }
}
```

---

## 12. Recuperação de drafts após crash/kill

### Cenários de perda

| Cenário | Room DB persiste? | Draft recuperável? | Ação |
|---------|------------------|-------------------|------|
| App killed (user swipe) | ✅ Sim | ✅ Sim | Restaurar no próximo launch |
| ANR → system kill | ✅ Sim | ✅ Sim | Restaurar no próximo launch |
| Process death (low memory) | ✅ Sim | ✅ Sim | Restaurar no próximo launch |
| OOM kill | ✅ Sim | ✅ Sim | Restaurar no próximo launch |
| Device reboot | ✅ Sim | ✅ Sim | Restaurar no próximo launch |
| Uninstall | ❌ Não | ❌ Não | Dados perdidos (esperado) |

**Room DB é SQLite em disco** — persiste em todos os cenários exceto uninstall. É por isso que
o autosave em Room é a estratégia correta (não SharedPreferences, não ficheiros temporários).

### DraftRecoveryManager

```kotlin
class DraftRecoveryManager(
    private val draftDao: DraftDao,
    private val audioDraftDir: File // cache dir para áudios órfãos
) {
    suspend fun checkOrphanDrafts(): List<OrphanDraft> {
        val textDrafts = draftDao.getDraftsOlderThan(System.currentTimeMillis())
        val audioDrafts = audioDraftDir.listFiles()
            ?.filter { it.extension in listOf("m4a", "aac") }
            ?.map { OrphanDraft.Audio(it) }
            ?: emptyList()

        return textDrafts.map { OrphanDraft.Text(it) } + audioDrafts
    }
}
```

### Recovery UI

- **Safety plan:** auto-restore sem perguntar — demasiado importante para arriscar
- **Diary / forum:** bottom sheet gentil:
  - "Encontrámos um rascunho do teu diário. Queres continuar?"
  - Botão primário: "Continuar" (cor emerald, proeminente)
  - Botão secundário: "Descartar" (texto suave, sem cor forte — prevenir descarte acidental)
- **Áudio órfão:** "Tens uma gravação por terminar. Queres usá-la num novo post?"

### Limites

- Drafts com mais de **7 dias** são descartados automaticamente (periodic cleanup, secção 9)
- Máximo **1 draft por tipo** (diary: 1 por date, forum: 1 draft ativo, vault: sem limite)

---

## 13. Sync priority queue

### Problema

Nem todas as operações de sync têm a mesma urgência. Uma atualização ao safety plan deve ser
sincronizada antes de uma reação a um post.

### Níveis de prioridade

| Prioridade | Tipo | Exemplos | Constraint WorkManager |
|-----------|------|----------|----------------------|
| **P0 — Crítico** | Safety & crisis | Safety plan update, crisis report | `NetworkType.CONNECTED` (imediato) |
| **P1 — Alto** | Dados emocionais urgentes | Diary entry com mood ≤ 2, diary com tags crise | `NetworkType.CONNECTED` |
| **P2 — Normal** | Dados pessoais | Diary entries regulares, vault items, milestones | `NetworkType.CONNECTED` |
| **P3 — Baixo** | Interações sociais | Forum posts, reações, reports, bookmark toggles | `NetworkType.CONNECTED` (opcionalmente `UNMETERED`) |
| **P4 — Background** | Preferências | Profile updates, notification prefs, accessibility settings | `NetworkType.UNMETERED` preferível |

### Implementação

```kotlin
@Entity(tableName = "sync_queue")
data class SyncQueueEntity(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val action: String,
    val payload: String,
    val priority: Int,           // 0-4
    val createdAt: Long,
    val retryCount: Int = 0,
    val maxRetries: Int = 5,
    val status: String = "pending"
)

@Dao
interface SyncQueueDao {
    @Query("SELECT * FROM sync_queue WHERE status = 'pending' ORDER BY priority ASC, createdAt ASC")
    suspend fun getPendingByPriority(): List<SyncQueueEntity>
}
```

### Deteção de prioridade

- **P0:** action contém `safety_plan` ou `crisis`
- **P1:** action é `create_diary_entry` e payload contém `"mood_level": 1` ou `"mood_level": 2`,
  ou tags contêm keywords de crise (`suicidio`, `morte`, `cortar`, etc. — ref. CBTAnalysisService)
- **P2:** action é `create_diary_entry` (normal), `create_vault_item`, `store_milestone`
- **P3:** action é `create_post`, `react_post`, `report_post`, `toggle_bookmark`
- **P4:** action contém `update_profile`, `update_preferences`, `update_accessibility`

### Regra absoluta

**P0 (safety plan) NUNCA é atrasado**, independentemente de battery saver, data saver, ou qualquer
constraint. Se o utilizador editou o plano de crise, essa alteração sincroniza assim que houver rede.

---

## 14. Battery and data awareness

### Princípio

A app deve respeitar os recursos do dispositivo sem comprometer a segurança. Funcionalidades
críticas (P0) são intocáveis; funcionalidades de background ajustam-se.

### Battery awareness

| Estado da bateria | Comportamento |
|------------------|-------------|
| > 20% | Sync normal (periodic 15min) |
| 15-20% | Reduzir periodic sync para 30min |
| < 15% | Reduzir periodic sync para 60min, desativar prefetch de feed |
| Battery saver ativo | Igual a < 15%, respeitar `PowerManager.isPowerSaveMode` |

```kotlin
// BatteryAwareScheduler
val syncInterval = combine(
    batteryMonitor.batteryLevel,
    batteryMonitor.isPowerSaveMode
) { level, powerSave ->
    when {
        powerSave || level < 15 -> 60.minutes
        level < 20 -> 30.minutes
        else -> 15.minutes
    }
}
```

### Data saver awareness

| Data saver | Comportamento |
|-----------|-------------|
| Desativado | Comportamento normal |
| Ativo (`ConnectivityManager.isActiveNetworkMetered` + `RESTRICT_BACKGROUND_DATA`) | Desativar prefetch de feed, reduzir cache de imagens, sync apenas P0-P2 |

### Setting do utilizador

- Toggle "Economizar dados" em Definições → Armazenamento e Rede
- Armazenado em DataStore, respeitado pelo SyncScheduler
- Descrição: "Reduz o uso de dados móveis. Funcionalidades essenciais não são afetadas."

### Regra absoluta

P0 (safety plan) e P1 (diary crise) **nunca** são afetados por battery saver ou data saver.
A segurança emocional do utilizador prevalece sobre a otimização de recursos.

---

## 15. Indicadores de stale data

### Problema

Quando os dados exibidos são cacheados, o utilizador deve saber se estão atualizados — mas sem
ansiedade. Os indicadores devem ser subtis e informativos, nunca alarmantes.

### Estados de freshness

| Estado | Condição | UI | Cor |
|--------|---------|-----|-----|
| **Fresh** | Última sync < 5 min | Sem indicador | — |
| **Aging** | 5-30 min desde última sync | "Atualizado há X min" (texto pequeno) | `gray-400` |
| **Stale** | > 30 min desde última sync | "Última atualização há X min" + ícone refresh | `amber-500` |
| **Offline** | Sem conectividade | Banner no topo: "Estás offline" | `rose-100` bg + `rose-500` texto |

### Componente Compose

```kotlin
@Composable
fun StalenessIndicator(
    lastSyncTimestamp: Long,
    isOnline: Boolean,
    modifier: Modifier = Modifier
) {
    val minutesAgo = (System.currentTimeMillis() - lastSyncTimestamp) / 60_000

    when {
        !isOnline -> OfflineBanner()
        minutesAgo > 30 -> StaleIndicator(minutesAgo)
        minutesAgo > 5 -> AgingIndicator(minutesAgo)
        // Fresh — sem indicador
    }
}
```

### Ecrãs com indicador de staleness

| Ecrã | Mostra indicador? | Justificação |
|------|-------------------|-------------|
| Dashboard | ✅ Sim | Dados agregados podem estar desatualizados |
| Lista do diário | ✅ Sim | Entradas podem ter sido editadas na web |
| Feed do fórum | ✅ Sim | Posts novos podem existir |
| Perfil | ✅ Sim | Dados podem ter sido editados na web |
| Calm Zone (exercícios) | ❌ Não | Exercícios são locais, sempre "fresh" |
| Calm Zone (sons) | ❌ Não | Assets locais ou CDN |
| Safety plan | ✅ Sim (com prioridade) | Crítico estar atualizado |

### Pull-to-refresh

- Disponível em todos os ecrãs com indicador de staleness
- Força sync imediato independentemente do schedule
- Animação de refresh suave (Material 3 pull-to-refresh)
- Se offline: pull-to-refresh mostra "Não foi possível atualizar. Sem ligação à rede."

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-13-01 | Perda de chave SQLCipher se EncryptedSharedPreferences corrompido | Média | Alto | Backup da chave no Android Keystore como fallback. Se ambos falharem, forçar re-login (nova chave, re-download de dados) |
| RISK-13-02 | Sync queue cresce sem limite durante offline prolongado (dias) | Baixa | Médio | Cap em 500 entradas. Ao atingir, mostrar "Tens muitas alterações por sincronizar. Conecta-te para não perderes dados." |
| RISK-13-03 | Conflict resolution confunde utilizador em crise | Média | Alto | Default: server-wins silenciosamente. Apenas para diary (dado mais pessoal) mostrar opção de escolha. Safety plan: merge manual nunca — server wins sempre |
| RISK-13-04 | Battery drain de sync P0 agressivo em dispositivos modestos | Baixa | Baixo | P0 events são raros (edição de safety plan). Monitorizar com Firebase Performance Monitoring |
| RISK-13-05 | SQLCipher aumenta tamanho do APK (~3MB) | Baixa | Baixo | Aceitável. A encriptação de dados emocionais é não-negociável |

---

*Próximo: [14-realtime-chat-notificacoes.md](14-realtime-chat-notificacoes.md) — Real-time, chat, notificações e presença.*
