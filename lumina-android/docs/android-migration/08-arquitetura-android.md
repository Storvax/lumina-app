# 08 — Proposta de Arquitetura Android

## Contexto

O backend Laravel da Lumina tem 7 services encapsulados com lógica de negócio pura
(ref. [01-estado-atual.md](01-estado-atual.md)): CBTAnalysisService, GamificationService,
RecommendationService, ExperienceMatchingService, AnalyticsService, EncryptionService,
FeatureFlagService. Esta lógica é server-side e reutilizável — a app Android consome-a via API.

O mapeamento funcional (ref. [03-mapeamento-funcional.md](03-mapeamento-funcional.md)) identifica
dois items de Reconceção críticos: autenticação (session → token) e chat (Laravel Echo → WebSocket
nativo). Estes exigem redesign arquitetural, não apenas adaptação visual.

**Porquê Clean Architecture + MVI para a Lumina especificamente:**
O utilizador pode estar em crise emocional. A UI não pode mostrar estado confuso, stale, ou
inconsistente. MVI garante fluxo unidirecional e single source of truth — o estado da tela é
sempre previsível. Se o utilizador abre a Zona Calma em momento de crise, o ecrã aparece
imediatamente com dados cacheados, sem flicker, sem loading spinner, sem estado intermédio.

---

## Visão geral

A arquitetura segue **Clean Architecture** com 3 camadas (data, domain, presentation) e padrão **MVI** (Model-View-Intent) na camada de presentation.

O estado flui num único sentido: **UI → Intent → ViewModel → State → UI**.

```
┌─────────────────────────────────────────────┐
│                 PRESENTATION                 │
│  ┌─────────┐  ┌──────────┐  ┌────────────┐ │
│  │ Screen  │→ │ ViewModel│→ │   State    │ │
│  │(Compose)│← │  (MVI)   │← │  (sealed)  │ │
│  └─────────┘  └──────────┘  └────────────┘ │
├─────────────────────────────────────────────┤
│                   DOMAIN                     │
│  ┌──────────┐  ┌────────────┐  ┌─────────┐ │
│  │ UseCases │  │  Entities  │  │  Repos  │ │
│  │          │  │            │  │(interf.)│ │
│  └──────────┘  └────────────┘  └─────────┘ │
├─────────────────────────────────────────────┤
│                    DATA                      │
│  ┌──────────┐  ┌────────────┐  ┌─────────┐ │
│  │   API    │  │   Room DB  │  │  Repos  │ │
│  │(Retrofit)│  │  (local)   │  │ (impl.) │ │
│  └──────────┘  └────────────┘  └─────────┘ │
└─────────────────────────────────────────────┘
```

---

## Camadas em detalhe

### 1. Presentation (UI)

**Responsabilidade:** Renderizar estado, capturar intenções do utilizador.

- **Screens** — Composables que representam telas completas
- **Components** — Composables reutilizáveis (botões, cards, dialogs)
- **ViewModels** — Processam intents, orquestram use cases, emitem estados
- **State** — Data classes/sealed classes que representam o estado completo da tela
- **Intent/Event** — Sealed classes que representam ações do utilizador

```kotlin
// Exemplo: DiaryScreen
data class DiaryState(
    val todayEntry: DailyLogEntry? = null,
    val recentEntries: List<DailyLogEntry> = emptyList(),
    val isLoading: Boolean = false,
    val error: UiError? = null,
    val draftNote: String = "",
    val selectedMood: Int? = null,
    val selectedTags: Set<String> = emptySet(),
    val cbtInsight: String? = null,
    val isSaving: Boolean = false
)

sealed interface DiaryIntent {
    data class SelectMood(val level: Int) : DiaryIntent
    data class ToggleTag(val tag: String) : DiaryIntent
    data class UpdateNote(val text: String) : DiaryIntent
    data object SaveEntry : DiaryIntent
    data object Refresh : DiaryIntent
}

sealed interface DiaryEffect {
    data object EntrySaved : DiaryEffect
    data class ShowError(val error: UiError) : DiaryEffect
}
```

### 2. Domain

**Responsabilidade:** Lógica de negócio pura. Sem dependências Android.

- **Entities** — Modelos de domínio (não DTOs, não DB entities)
- **Use Cases** — Operações de negócio atómicas
- **Repository interfaces** — Contratos que a data layer implementa

```kotlin
// Exemplo
class SaveDiaryEntryUseCase(
    private val diaryRepository: DiaryRepository,
    private val gamificationRepository: GamificationRepository
) {
    suspend operator fun invoke(entry: DailyLogEntry): Result<DailyLogEntry> {
        val saved = diaryRepository.save(entry)
        if (saved.isSuccess) {
            gamificationRepository.trackAction("daily_log")
        }
        return saved
    }
}
```

**Princípio:** Os use cases encapsulam a orquestração de repositórios. Um ViewModel nunca chama repositórios diretamente — sempre via use case.

### 3. Data

**Responsabilidade:** Acesso a dados (API, DB local, preferências).

- **API** — Retrofit interfaces + DTOs (request/response)
- **Local** — Room DAOs + entities
- **Repositories (impl.)** — Implementam interfaces do domain, decidem fonte de dados (API vs cache)
- **Mappers** — Convertem entre DTOs ↔ Domain entities ↔ DB entities

```kotlin
// Exemplo
class DiaryRepositoryImpl(
    private val api: DiaryApi,
    private val dao: DailyLogDao,
    private val mapper: DailyLogMapper
) : DiaryRepository {

    override fun getRecentEntries(): Flow<List<DailyLogEntry>> {
        return dao.getRecentEntries()
            .map { entities -> entities.map(mapper::toDomain) }
    }

    override suspend fun save(entry: DailyLogEntry): Result<DailyLogEntry> {
        // Guardar localmente primeiro (optimistic)
        dao.insert(mapper.toEntity(entry))
        // Tentar sync com servidor
        return try {
            val response = api.createEntry(mapper.toDto(entry))
            val saved = mapper.toDomain(response)
            dao.insert(mapper.toEntity(saved)) // Atualizar com dados do servidor
            Result.success(saved)
        } catch (e: Exception) {
            // Marcar para sync posterior
            dao.markPendingSync(entry.id)
            Result.success(entry) // Retorna sucesso (optimistic)
        }
    }
}
```

---

## Fluxo de dados MVI

```
User taps "Guardar" no Diário
        │
        ▼
DiaryIntent.SaveEntry
        │
        ▼
DiaryViewModel.onIntent()
        │
        ▼
SaveDiaryEntryUseCase.invoke()
        │
        ├─→ DiaryRepository.save()
        │       ├─→ Room DB (local first)
        │       └─→ Retrofit API (async sync)
        │
        └─→ GamificationRepository.trackAction()
                └─→ Atualiza flames localmente
        │
        ▼
DiaryState(isSaving = false, todayEntry = saved)
        │
        ▼
UI recomposes com novo estado
```

---

## Gestão de erros

### Error handling strategy

```kotlin
sealed interface UiError {
    data object Network : UiError          // Sem rede
    data object Server : UiError           // 500, timeout
    data object Unauthorized : UiError     // 401 → re-login
    data object NotFound : UiError         // 404
    data class Validation(               // 422
        val errors: Map<String, List<String>>
    ) : UiError
    data object Unknown : UiError          // Fallback
}
```

**Tom acolhedor nos erros:**
- Network: "Parece que não tens ligação à internet. As tuas alterações foram guardadas localmente."
- Server: "Algo correu mal do nosso lado. Tenta novamente daqui a pouco."
- Unauthorized: "A tua sessão expirou. Faz login para continuares."
- Unknown: "Ocorreu um erro inesperado. Se persistir, contacta-nos."

Todas as mensagens de erro devem ser strings localizadas em PT-PT.

### Crisis-specific error handling

**Regra absoluta:** se a API está em baixo durante momento de crise, a app NUNCA mostra ecrã
de erro genérico. Mostra recursos cacheados.

```
API request falha durante acesso à Zona Calma
        │
        ▼
Repository deteta erro de rede/servidor
        │
        ▼
Retorna dados de Room DB (cache)
        │
        ├─→ Safety plan cacheado → mostra
        ├─→ Exercícios → 100% local, sem API
        ├─→ Contactos de emergência → cacheados
        └─→ Banner discreto: "Estás em modo offline.
             Tudo o que precisas está aqui."
```

**Nunca** mostrar: "Erro de servidor", "Tenta novamente", ou qualquer mensagem que implique
que o utilizador precisa de fazer algo para aceder a recursos de crise.

### Retry policy por tipo de operação

| Tipo | Política | Detalhes |
|------|---------|----------|
| Leitura (GET) | Retry automático, 3× com backoff | 1s → 2s → 4s. Fallback para cache se todas falharem |
| Escrita (POST/PUT) | Enqueue para sync posterior | Optimistic UI + SyncQueue. Nunca retry síncrono |
| Delete (DELETE) | Sem retry automático | Requer confirmação explícita do utilizador para re-tentar |
| Auth (refresh token) | Retry 1×, depois re-login | Evitar loops de refresh infinitos |

---

## Padrão de Repository com offline-first

```
┌──────────────┐     ┌──────────────┐
│   API Call   │────→│  Room DB     │
│  (Retrofit)  │     │  (cache)     │
└──────┬───────┘     └──────┬───────┘
       │                     │
       │    ┌────────────┐   │
       └───→│ Repository │←──┘
            │   (impl)   │
            └─────┬──────┘
                  │
            ┌─────▼──────┐
            │   Domain   │
            │  Flow<T>   │
            └────────────┘
```

**Estratégia por tipo de dado:**

| Tipo | Estratégia | Exemplo |
|------|-----------|---------|
| Frequente + offline | Cache-first, sync in background | Diário, perfil, missões |
| Feed/lista | Network-first, fallback to cache | Posts do fórum, salas |
| Real-time | Network-only, sem cache | Mensagens do chat |
| Estático | Cache-first, refresh periódico | Achievements, rooms list |
| Write | Optimistic + queue sync | Criar post, reagir, guardar no cofre |

---

## Injeção de dependências (Hilt)

```
@Module
@InstallIn(SingletonComponent::class)   ← App-wide
├── NetworkModule          → OkHttpClient, Retrofit, API interfaces
├── DatabaseModule         → Room DB, DAOs
├── RepositoryModule       → Repository implementations
└── SecurityModule         → EncryptedSharedPreferences, TokenManager

@Module
@InstallIn(ViewModelComponent::class)   ← Per-ViewModel
└── UseCaseModule          → Use case instances
```

---

## Arquitetura de segurança

### Token storage flow

```
Login (email + password)
        │
        ▼
POST /api/v1/auth/login
        │
        ▼
Laravel Sanctum → Personal Access Token
        │
        ▼
EncryptedSharedPreferences (Android Keystore-backed)
        │
        ▼
AuthInterceptor (OkHttp) injeta "Bearer {token}" em cada request
```

**EncryptedSharedPreferences** usa AES-256-GCM para valores e AES-256-SIV para chaves,
com master key gerida pelo Android Keystore. O token nunca existe em plaintext no filesystem.

### Refresh de token

```
Request com token → 401 Unauthorized
        │
        ▼
AuthInterceptor deteta 401
        │
        ├─→ Tenta refresh: POST /api/v1/auth/refresh
        │       │
        │       ├─→ Sucesso: novo token → EncryptedSharedPreferences → retry request original
        │       └─→ Falha: limpa tokens → redireciona para LoginScreen
        │
        └─→ Mutex: apenas 1 refresh em simultâneo (evitar race conditions com requests paralelos)
```

### Certificate pinning

**Fase 2.** Implementar via OkHttp `CertificatePinner` contra o domínio da API Lumina.
Protege contra MITM em redes comprometidas — relevante para utilizadores em situações
de abuso doméstico ou vigilância.

```kotlin
// Exemplo conceptual
val certificatePinner = CertificatePinner.Builder()
    .add("api.lumina.pt", "sha256/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=")
    .build()
```

**Risco:** certificate rotation requer app update. Mitigação: incluir pin atual + próximo pin.
Ref. [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) secção 4.

### Encriptação de dados at-rest

Dados sensíveis em Room DB encriptados campo a campo:

| Dados | Encriptação | Justificação |
|-------|------------|-------------|
| Diary notes (texto livre) | AES-256-GCM via AndroidX Security | Conteúdo emocional privado |
| Vault items (cofre pessoal) | AES-256-GCM via AndroidX Security | Fotos, textos, áudios de conforto |
| Safety plan | AES-256-GCM via AndroidX Security | Informação clínica sensível |
| Chat messages (cache) | AES-256-GCM via AndroidX Security | Mensagens privadas |
| Profile data | Sem encriptação extra | Dados não-sensíveis, já protegidos por sandboxing |
| Gamification data | Sem encriptação extra | Dados não-sensíveis |

**Alternativa descartada:** SQLCipher (encripta DB inteira). Descartado porque: overhead de performance
em queries frequentes, complexidade de integração com Room, e nem todos os dados justificam encriptação.
Campo-level é mais granular e performante.

### FLAG_SECURE

Ativado em telas sensíveis — impede screenshots e gravação de ecrã:

| Tela | FLAG_SECURE | Razão |
|------|------------|-------|
| Diário (leitura/escrita) | Sim | Conteúdo emocional privado |
| Safety Plan | Sim | Informação clínica |
| Chat privado (buddy) | Sim | Mensagens privadas |
| Vault (cofre pessoal) | Sim | Conteúdo de conforto pessoal |
| Login (campo password) | Sim | Credenciais |
| Outras telas | Não | Não contêm dados sensíveis |

Implementação: `window.setFlags(FLAG_SECURE, FLAG_SECURE)` per-Activity, ou via Modifier wrapper
em Compose que aplica o flag quando o composable entra em composição.

### Safe House (Casa Segura)

Mecanismo de segurança para utilizadores em situações de abuso/violência doméstica.
No web: double Escape → redirect para Google. No Android: adaptação ao paradigma nativo.

```
Double-tap no botão Safe House (ou gesture configurável)
        │
        ▼
1. finish() em todas as Activities (clear back stack)
2. FLAG_EXCLUDE_FROM_RECENTS temporário (30s)
3. Cancelar todas as notificações pendentes
4. Limpar notification history
        │
        ▼
App desaparece completamente — sem vestígios visíveis
```

Ao reabrir, a app apresenta o ecrã de login normal (não retoma tela sensível).
Ref. [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) secção 6.

---

## Gestão de WebSocket e lifecycle

### Quando conectar/desconectar

| Estado | WebSocket | Razão |
|--------|-----------|-------|
| Foreground + tela de chat | Conectado | Real-time necessário |
| Foreground + outra tela | Desconectado | Sem necessidade de real-time |
| Background | Desconectado | Conservar bateria e dados |
| App killed | Desconectado | FCM cobre notificações |

### Flow de dados

```
OkHttp WebSocket Client
        │
        ▼
WebSocketManager (core-network)
        │ emite SharedFlow<WebSocketEvent>
        ▼
ChatRepository (feature-chat)
        │ transforma em domain entities
        ▼
ChatViewModel
        │ atualiza StateFlow<ChatState>
        ▼
ChatScreen (Compose) recomposes
```

`WebSocketManager` é singleton (Hilt `@Singleton`) que gere a conexão e emite eventos tipados:

```kotlin
sealed interface WebSocketEvent {
    data class MessageReceived(val roomId: String, val message: ChatMessageDto) : WebSocketEvent
    data class UserJoined(val roomId: String, val userId: String) : WebSocketEvent
    data class UserLeft(val roomId: String, val userId: String) : WebSocketEvent
    data class TypingIndicator(val roomId: String, val userId: String) : WebSocketEvent
    data object Connected : WebSocketEvent
    data object Disconnected : WebSocketEvent
    data class Error(val throwable: Throwable) : WebSocketEvent
}
```

### Reconexão

Exponential backoff: 1s → 2s → 4s → 8s → max 30s. Reset ao conectar com sucesso.
Após 5 falhas consecutivas, mostrar banner na UI: "Ligação perdida. A tentar reconectar..."
com opção manual de retry.

### Complementaridade com FCM

- **WebSocket ativo** (foreground + chat): mensagens em real-time, suprimir notificação FCM duplicada.
- **WebSocket inativo** (background ou outra tela): FCM entrega push notification.
- **Deduplicação**: cada mensagem tem UUID. Se recebida via WebSocket, ignorar FCM com mesmo UUID.

### Autenticação WebSocket

WebSocket auth com Sanctum token no handshake (header `Authorization: Bearer {token}`),
não com session cookie. Se token expira durante conexão longa, o servidor fecha o WebSocket
com código 4001 → cliente reconecta com token refreshed.

Ref. [14-realtime-chat-notificacoes.md](14-realtime-chat-notificacoes.md) para protocolo completo.

---

## Arquitetura de crisis mode

### Princípio fundamental

**Funcionalidades de crise NUNCA dependem de rede.**

O utilizador em crise emocional pode estar: sem rede, com mãos a tremer, em pânico, com atenção
fragmentada. A app deve responder imediatamente com recursos locais.

### Zona Calma — fluxo offline-first

```
Utilizador abre Zona Calma
        │
        ▼
CalmZoneViewModel verifica Room DB
        │
        ├─→ Safety plan (cacheado) → mostra imediatamente
        ├─→ Exercícios (grounding, respiração, heartbeat) → 100% local
        ├─→ Contactos de emergência (cacheados) → mostra imediatamente
        └─→ Vault items (cacheados, encriptados) → desencripta e mostra
        │
        ▼
Se há rede: sync silencioso em background (sem bloquear UI)
Se não há rede: tudo funciona com dados locais
```

### Crisis detection response

Quando a API devolve `risk_level: "high"` ou `"critical"` num post ou mensagem
(via CBTAnalysisService server-side, ref. [01-estado-atual.md](01-estado-atual.md)):

```
API response com risk_level = "high" ou "critical"
        │
        ▼
Repository propaga CrisisAlert ao ViewModel
        │
        ▼
UI mostra bottom sheet imediato:
├─→ Contactos de emergência (112, SNS 24, SOS Voz Amiga)
├─→ Link direto para Zona Calma
├─→ Safety plan do utilizador (se existir)
└─→ Mensagem acolhedora: "Estamos aqui contigo."
```

### SOS FAB (Floating Action Button)

Botão flutuante acessível em **toda a navegação principal** (não apenas na Zona Calma):

- Sempre visível, nunca obscurecido por outros elementos
- Tap → abre Zona Calma diretamente na tela de crise
- Touch target mínimo: 56×56dp (acima do mínimo de 44×44)
- Cor contrastante mas não agressiva (tom suave, não vermelho)
- Animação subtil de pulso para indicar disponibilidade permanente

### Offline guarantee — pré-cache

Após primeiro login bem-sucedido, a app pré-carrega e armazena em Room DB:

| Recurso | Quando cachear | Refresh |
|---------|---------------|---------|
| Safety plan | Após login + após edição | Silencioso quando há rede |
| Contactos de emergência | Após login | Semanal |
| Exercícios core (grounding, respiração, heartbeat) | Primeiro lançamento | Nunca (100% local) |
| Vault items | Após adição pelo utilizador | Após edição |
| Perfil do utilizador | Após login | A cada abertura da app |

---

## Sync e resolução de conflitos

### SyncQueue

Entity em Room DB (`SyncQueueEntity` em `core-database`) que armazena operações pendentes:

```kotlin
@Entity(tableName = "sync_queue")
data class SyncQueueEntity(
    @PrimaryKey val id: String = UUID.randomUUID().toString(),
    val entityType: String,       // "diary_entry", "post", "reaction", "vault_item"
    val entityId: String,
    val operation: String,        // "create", "update", "delete"
    val payload: String,          // JSON serializado
    val createdAt: Long,
    val retryCount: Int = 0,
    val maxRetries: Int = 5,
    val status: String = "pending" // "pending", "in_progress", "failed", "completed"
)
```

### SyncWorker

WorkManager periodic worker que processa a fila quando há rede:

```
SyncWorker (periodic, constraints: NetworkType.CONNECTED)
        │
        ▼
Ler SyncQueue ordenada por createdAt ASC
        │
        ▼
Para cada operação pendente:
├─→ Tentar executar via API
│       ├─→ Sucesso: marcar "completed", remover da fila
│       └─→ Falha:
│               ├─→ retryCount < maxRetries: incrementar retry, backoff
│               └─→ retryCount >= maxRetries: marcar "failed", notificar UI
└─→ Próxima operação
```

### Integração com Repository pattern

```
Write operation (ex: guardar diário)
        │
        ▼
Repository:
1. Guardar em Room DB (estado local imediato)
2. Enqueue em SyncQueue (para sync posterior)
3. Retornar sucesso ao ViewModel (optimistic)
        │
        ▼
SyncWorker (em background):
4. Processar SyncQueue quando há rede
5. Atualizar Room DB com dados do servidor (IDs, timestamps)
6. Remover da SyncQueue
```

### Conflict resolution (Fase 1)

**Estratégia: server-timestamp wins.**

Se o servidor rejeitar uma operação (409 Conflict — ex: utilizador editou no web e na app
simultaneamente), a versão do servidor prevalece e a app atualiza o estado local.

Justificação: simples de implementar, correto para Fase 1 onde a maioria dos utilizadores
usa apenas a app. Estratégia mais sofisticada (merge, user choice) pode ser adicionada
na Fase 4+ se o cenário multi-dispositivo se tornar comum.

Ref. [13-offline-sync.md](13-offline-sync.md) secção 5 para detalhes.

---

## State management deep dive

### StateFlow vs SharedFlow

| Tipo | Uso | Replay | Exemplo |
|------|-----|--------|---------|
| `StateFlow<State>` | UI state (último valor) | 1 (sempre tem valor) | `DiaryState`, `ProfileState` |
| `SharedFlow<Effect>` | One-time events | 0 (sem replay) | Navegação, snackbar, haptic trigger |

**Porquê a distinção:**
- `StateFlow` garante que a UI sempre tem um estado para renderizar — essencial para recomposição.
- `SharedFlow` com replay 0 garante que eventos one-time (ex: "navegar para tela X") não são
  re-emitidos ao recolectar após configuration change.

### Múltiplas fontes de dados

```
Room DB (cache local)     WebSocket (real-time)     API (fetch)
        │                         │                       │
        └─────────┐     ┌────────┘           ┌──────────┘
                  ▼     ▼                    ▼
              Repository (decide fonte, merge)
                          │
                    Flow<DomainEntity>
                          │
                    ViewModel (transforma)
                          │
                    StateFlow<UiState>
                          │
                    Compose UI (renderiza)
```

O Repository decide a fonte de dados; o ViewModel recebe `Flow` unificado e transforma
em `StateFlow<State>` que a UI consome. A UI nunca sabe de onde vêm os dados.

### SavedStateHandle

Preserva estado crítico durante process death (Android pode matar a app em background):

| Dado | SavedStateHandle | Room DB | Razão |
|------|-----------------|---------|-------|
| Tab atual no bottom nav | Sim | Não | Leve, UI-only |
| Draft do diário (texto) | Sim | Sim (auto-save) | Prevenir perda de dados em crise |
| Scroll position | Sim | Não | UX continuity |
| Mood selecionado (draft) | Sim | Não | Leve, UI-only |
| Dados de perfil | Não | Sim | Demasiado grande para SavedStateHandle |
| Mensagens de chat | Não | Sim | Volume elevado |

**Princípio:** SavedStateHandle para estado leve de UI. Room DB para dados persistentes.
Nunca duplicar dados grandes em ambos.

---

## Navegação

```
NavHost
├── AuthGraph (login, register, forgot-password)
│
├── OnboardingGraph (step1, step2, step3)
│
└── MainGraph (bottom nav)
    ├── DashboardTab
    │   └── DashboardScreen
    │
    ├── ForumTab (Fase 2)
    │   ├── ForumFeedScreen
    │   ├── PostDetailScreen
    │   └── CreatePostScreen
    │
    ├── ChatTab (Fase 3)
    │   ├── RoomListScreen
    │   └── ChatRoomScreen
    │
    ├── CalmZoneTab
    │   ├── CalmZoneHubScreen
    │   ├── GroundingScreen
    │   ├── BreathingScreen
    │   ├── HeartbeatScreen
    │   ├── CrisisScreen
    │   └── VaultScreen
    │
    └── ProfileTab
        ├── ProfileScreen
        ├── EditProfileScreen
        ├── SettingsScreen
        └── PrivacyScreen
```

**Deep links:** Cada tela tem um deep link pattern para notificações push abrirem a tela correta.

---

## Threading model

| Operação | Dispatcher | Razão |
|----------|-----------|-------|
| API calls (Retrofit) | `Dispatchers.IO` | Suspending, managed by Retrofit |
| Room queries | `Dispatchers.IO` | Suspending ou Flow |
| JSON parsing | `Dispatchers.Default` | CPU-bound |
| UI rendering | `Dispatchers.Main` | Compose |
| File I/O | `Dispatchers.IO` | Blocking |
| Crypto operations | `Dispatchers.Default` | CPU-bound |

Coroutines são geridas por `viewModelScope` (cancela ao destruir ViewModel) e `lifecycleScope` (para operações ao nível da Activity).

---

## Testing strategy (por camada)

| Camada | Tipo de teste | Ferramenta | Coverage alvo |
|--------|--------------|-----------|--------------|
| Domain (use cases) | Unit test | JUnit 5 + Mockk | 90%+ |
| Data (repositories) | Unit test | JUnit 5 + Mockk | 80%+ |
| Data (Room DAOs) | Instrumented | AndroidJUnit4 + Room in-memory | 80%+ |
| Presentation (ViewModel) | Unit test | JUnit 5 + Mockk + Turbine | 80%+ |
| Presentation (UI) | UI test | Compose UI Test | Telas críticas |
| Integration | End-to-end | Espresso/Compose | Fluxos críticos |

### Módulo `core-testing`

Test utilities partilhados por todos os módulos, evitando duplicação de test infrastructure:

- **FakeRepositories** — implementações in-memory de todas as interfaces de repository
  (DiaryRepository, ProfileRepository, GamificationRepository, etc.)
- **TestDispatcherRule** — JUnit Rule que substitui `Dispatchers.Main` com `TestCoroutineDispatcher`
- **StateFlowAssertions** — extension functions para testar emissões de StateFlow
  (`assertState { it.isLoading == true }`, `awaitState { it.error != null }`)
- **ComposeTestHelpers** — helpers para testes de UI Compose
  (`findByContentDescription`, `assertTalkBackNavigable`)

Dependência: `testImplementation` em todos os feature modules.
Ref. [09-modularizacao.md](09-modularizacao.md) para detalhes do módulo.

---

## Riscos arquiteturais

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| MVI boilerplate excessivo (State, Intent, Effect por tela) | Alta | Baixo | Base classes em `core-domain`: `BaseViewModel<State, Intent, Effect>` com `onIntent()` e `reduce()` |
| Room schema migration entre versões da app | Média | Alto | Auto-migrations para alterações simples. Migrations manuais testadas em CI. Ref. [07-stack-android.md](07-stack-android.md) |
| WebSocket reconnection reliability | Média | Alto | FCM como fallback. Exponential backoff. UI feedback de estado de conexão |
| Complexidade de mappers DTO↔Domain↔Entity | Alta | Médio | Kotlin extension functions (`DailyLogDto.toDomain()`, `DailyLogEntity.toDomain()`). Manter convenções consistentes |
| Process death perde estado de tela | Média | Médio | SavedStateHandle para estado leve. Room DB para dados persistentes. Auto-save para drafts |
| Certificate pinning bloqueia utilizadores após certificate rotation | Baixa | Alto | Incluir pin atual + próximo pin. Fallback sem pin (com flag remota via FeatureFlag) |
| Encriptação campo-level impacta performance de queries Room | Baixa | Médio | Encriptar apenas campos de texto livre (notes, messages). Campos de metadata (IDs, timestamps) em plaintext para queries eficientes |

---

*Próximo: [09-modularizacao.md](09-modularizacao.md) — Estratégia de modularização do projeto.*
