# 08 — Proposta de Arquitetura Android

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

---

*Próximo: [09-modularizacao.md](09-modularizacao.md) — Estratégia de modularização do projeto.*
