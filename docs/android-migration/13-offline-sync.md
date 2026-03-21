# 13 — Offline, Autosave, Sincronização e Resiliência

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

*Próximo: [14-realtime-chat-notificacoes.md](14-realtime-chat-notificacoes.md) — Real-time, chat, notificações e presença.*
