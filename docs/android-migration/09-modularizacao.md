# 09 — Estratégia de Modularização e Organização do Projeto Android

## Princípio

O projeto usa **modularização por feature** com módulos core partilhados. Cada feature module é auto-contido (tem as suas próprias telas, ViewModels, use cases, e repositories). Os módulos core fornecem infraestrutura partilhada (networking, database, design system, auth).

**Benefícios:**
- Build times mais rápidos (compilação paralela e incremental)
- Separação clara de responsabilidades
- Facilita trabalho em paralelo
- Previne dependências circulares
- Cada feature pode ser desenvolvida e testada independentemente

---

## Estrutura de módulos

```
lumina-android/
├── app/                          ← Application module (entry point)
│   ├── src/main/
│   │   ├── LuminaApplication.kt
│   │   ├── MainActivity.kt
│   │   └── navigation/
│   │       └── LuminaNavHost.kt
│   └── build.gradle.kts
│
├── core/
│   ├── core-network/             ← Retrofit, OkHttp, interceptors, API base
│   ├── core-database/            ← Room DB, migrations, base DAOs
│   ├── core-domain/              ← Base entities, Result wrapper, base use case
│   ├── core-ui/                  ← Design system, theme, componentes partilhados
│   ├── core-auth/                ← Token management, session, biometric
│   └── core-common/              ← Extensions, utilities, constants
│
├── feature/
│   ├── feature-auth/             ← Login, registo, forgot password
│   ├── feature-onboarding/       ← Wizard 3 passos
│   ├── feature-dashboard/        ← Home screen
│   ├── feature-diary/            ← Diário emocional
│   ├── feature-calm-zone/        ← Zona Calma (hub + exercícios)
│   ├── feature-profile/          ← Perfil, settings, privacy
│   ├── feature-gamification/     ← Flames, streaks, missions, achievements
│   ├── feature-forum/            ← Mural da Esperança (Fase 2)
│   ├── feature-chat/             ← Fogueira (Fase 3)
│   ├── feature-buddy/            ← Ouvinte (Fase 3)
│   ├── feature-assessment/       ← PHQ-9, GAD-7 (Fase 2)
│   ├── feature-library/          ← Biblioteca (Fase 2)
│   ├── feature-wall/             ← The Wall (Fase 2)
│   └── feature-search/           ← Pesquisa global (Fase 2)
│
├── gradle/
│   └── libs.versions.toml        ← Version catalog
│
├── build.gradle.kts              ← Root build config
├── settings.gradle.kts           ← Module includes
└── gradle.properties             ← Gradle config
```

---

## Detalhes dos módulos core

### core-network

```
core-network/
├── src/main/kotlin/pt/lumina/core/network/
│   ├── di/
│   │   └── NetworkModule.kt          ← Hilt module (OkHttp, Retrofit)
│   ├── interceptor/
│   │   ├── AuthInterceptor.kt        ← Adiciona Bearer token
│   │   ├── RetryInterceptor.kt       ← Retry com backoff
│   │   └── ErrorInterceptor.kt       ← Converte erros HTTP → domain errors
│   ├── model/
│   │   ├── ApiResponse.kt            ← Wrapper genérico de resposta
│   │   ├── ApiError.kt               ← Modelo de erro padronizado
│   │   └── PaginatedResponse.kt      ← Paginação cursor-based
│   └── websocket/
│       ├── LuminaWebSocket.kt        ← Client WebSocket (OkHttp)
│       ├── WebSocketEvent.kt         ← Sealed class de eventos
│       └── WebSocketManager.kt       ← Gestão de conexão + reconexão
```

### core-database

```
core-database/
├── src/main/kotlin/pt/lumina/core/database/
│   ├── LuminaDatabase.kt             ← Room database (all entities)
│   ├── di/
│   │   └── DatabaseModule.kt         ← Hilt module
│   ├── converter/
│   │   └── Converters.kt             ← Type converters (date, lists, json)
│   └── entity/
│       ├── DailyLogEntity.kt
│       ├── UserEntity.kt
│       ├── MissionEntity.kt
│       ├── VaultItemEntity.kt
│       ├── PostEntity.kt             ← (Fase 2)
│       ├── MessageEntity.kt          ← (Fase 3)
│       └── SyncQueueEntity.kt        ← Fila de operações pendentes
```

### core-domain

```
core-domain/
├── src/main/kotlin/pt/lumina/core/domain/
│   ├── model/
│   │   ├── User.kt                   ← Domain entity
│   │   ├── FlameLevel.kt             ← Enum: spark, flame, bonfire, beacon
│   │   └── Result.kt                 ← Result wrapper com loading/success/error
│   ├── repository/
│   │   └── BaseRepository.kt         ← Interface base
│   └── usecase/
│       └── BaseUseCase.kt            ← Convenção base
```

### core-ui

```
core-ui/
├── src/main/kotlin/pt/lumina/core/ui/
│   ├── theme/
│   │   ├── LuminaTheme.kt            ← Material 3 theme
│   │   ├── Color.kt                  ← Paleta Lumina (light + dark)
│   │   ├── Typography.kt             ← Plus Jakarta Sans + OpenDyslexic
│   │   └── Shape.kt                  ← Rounded corners suaves
│   ├── component/
│   │   ├── LuminaButton.kt           ← Botões primary, secondary, danger
│   │   ├── LuminaCard.kt             ← Card com sombra suave
│   │   ├── LuminaTextField.kt        ← Input com estados de erro acolhedores
│   │   ├── LuminaTopBar.kt           ← App bar com back, título
│   │   ├── LuminaBottomBar.kt        ← Bottom navigation
│   │   ├── LuminaDialog.kt           ← Dialogs com tom empático
│   │   ├── LuminaLoadingState.kt     ← Loading com animação calma
│   │   ├── LuminaErrorState.kt       ← Erro com mensagem acolhedora
│   │   ├── LuminaEmptyState.kt       ← Estado vazio
│   │   ├── MoodSlider.kt             ← Slider 1-5 com haptic
│   │   ├── FlameCounter.kt           ← Animação de flames
│   │   ├── EmotionChips.kt           ← Tags emocionais selecionáveis
│   │   └── SensitiveContentBlur.kt   ← Blur com revelação intencional
│   ├── animation/
│   │   ├── BreathingAnimation.kt     ← Animação de respiração
│   │   └── FlameAnimation.kt         ← Animação de chama
│   └── util/
│       ├── AccessibilityUtils.kt     ← TalkBack helpers
│       └── HapticUtils.kt            ← Vibration patterns
```

### core-auth

```
core-auth/
├── src/main/kotlin/pt/lumina/core/auth/
│   ├── TokenManager.kt               ← Guardar/ler/refresh token
│   ├── SessionManager.kt             ← Estado de sessão (logged in/out)
│   ├── BiometricHelper.kt            ← Wrapper para BiometricPrompt
│   └── di/
│       └── AuthModule.kt             ← Hilt module
```

### core-common

```
core-common/
├── src/main/kotlin/pt/lumina/core/common/
│   ├── extension/
│   │   ├── DateExtensions.kt         ← Formatação PT-PT
│   │   ├── StringExtensions.kt
│   │   └── FlowExtensions.kt
│   ├── util/
│   │   ├── ConnectivityMonitor.kt    ← Observar estado de rede
│   │   └── DateTimeUtil.kt           ← Formatação e parsing
│   └── constant/
│       └── LuminaConstants.kt        ← Constantes globais
```

---

## Detalhes de feature modules (exemplo: feature-diary)

```
feature-diary/
├── src/main/kotlin/pt/lumina/feature/diary/
│   ├── data/
│   │   ├── api/
│   │   │   └── DiaryApi.kt           ← Retrofit interface
│   │   ├── dto/
│   │   │   ├── DailyLogRequest.kt
│   │   │   └── DailyLogResponse.kt
│   │   ├── local/
│   │   │   └── DailyLogDao.kt        ← Room DAO
│   │   ├── mapper/
│   │   │   └── DailyLogMapper.kt     ← DTO ↔ Domain ↔ Entity
│   │   └── repository/
│   │       └── DiaryRepositoryImpl.kt
│   ├── domain/
│   │   ├── model/
│   │   │   └── DailyLogEntry.kt      ← Domain entity
│   │   ├── repository/
│   │   │   └── DiaryRepository.kt    ← Interface
│   │   └── usecase/
│   │       ├── GetRecentEntriesUseCase.kt
│   │       ├── SaveDiaryEntryUseCase.kt
│   │       └── GetTodayEntryUseCase.kt
│   ├── presentation/
│   │   ├── DiaryScreen.kt            ← Composable
│   │   ├── DiaryViewModel.kt
│   │   ├── DiaryState.kt             ← UI state
│   │   ├── DiaryIntent.kt            ← User intents
│   │   └── component/
│   │       ├── MoodSelector.kt
│   │       ├── DiaryEntryCard.kt
│   │       └── CbtInsightCard.kt
│   └── di/
│       └── DiaryModule.kt            ← Hilt module (binds repository)
├── src/test/                          ← Unit tests
└── build.gradle.kts
```

---

## Grafo de dependências entre módulos

```
app
├── feature-auth         → core-network, core-auth, core-ui, core-domain
├── feature-onboarding   → core-network, core-auth, core-ui, core-domain
├── feature-dashboard    → core-network, core-database, core-ui, core-domain, core-auth
├── feature-diary        → core-network, core-database, core-ui, core-domain
├── feature-calm-zone    → core-database, core-ui, core-domain
├── feature-profile      → core-network, core-database, core-ui, core-domain, core-auth
├── feature-gamification → core-network, core-database, core-ui, core-domain
├── feature-forum        → core-network, core-database, core-ui, core-domain (Fase 2)
├── feature-chat         → core-network, core-database, core-ui, core-domain (Fase 3)
├── feature-buddy        → core-network, core-ui, core-domain, feature-chat (Fase 3)
├── feature-assessment   → core-network, core-database, core-ui, core-domain (Fase 2)
├── feature-library      → core-network, core-ui, core-domain (Fase 2)
├── feature-wall         → core-network, core-ui, core-domain (Fase 2)
└── feature-search       → core-network, core-ui, core-domain (Fase 2)

core-network   → core-auth, core-common
core-database  → core-common
core-ui        → core-common
core-auth      → core-common
core-domain    → core-common (minimal)
core-common    → (nenhuma dependência interna)
```

**Regra:** Feature modules nunca dependem de outros feature modules (exceção: feature-buddy depende de feature-chat para reutilizar o UI de chat).

---

## Package naming

Base package: `pt.lumina`

| Módulo | Package |
|--------|---------|
| app | `pt.lumina` |
| core-network | `pt.lumina.core.network` |
| core-database | `pt.lumina.core.database` |
| core-domain | `pt.lumina.core.domain` |
| core-ui | `pt.lumina.core.ui` |
| core-auth | `pt.lumina.core.auth` |
| core-common | `pt.lumina.core.common` |
| feature-diary | `pt.lumina.feature.diary` |
| feature-calm-zone | `pt.lumina.feature.calmzone` |
| ... | `pt.lumina.feature.*` |

---

## Convenções de build

- **Gradle Kotlin DSL** (`.kts`) em todos os módulos
- **Convention plugins** em `build-logic/` para partilhar configuração:
  - `lumina.android.library` — Configuração base de módulo Android
  - `lumina.android.feature` — Feature module com Compose + Hilt
  - `lumina.android.application` — App module
  - `lumina.jvm.library` — Módulos Kotlin puros (domain)
- **Version Catalog** (`gradle/libs.versions.toml`) para todas as dependências

---

## Fases de criação de módulos

| Fase | Módulos a criar |
|------|----------------|
| 1A (fundação) | app, core-*, feature-auth |
| 1B (core) | feature-onboarding, feature-dashboard, feature-diary, feature-calm-zone, feature-profile, feature-gamification |
| 2 (comunidade) | feature-forum, feature-assessment, feature-library, feature-wall, feature-search |
| 3 (real-time) | feature-chat, feature-buddy |

---

*Próximo: [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — Reutilização do backend Laravel existente.*
