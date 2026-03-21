# 09 — Estratégia de Modularização e Organização do Projeto Android

## Contexto

O sistema actual (ref. [01-estado-atual.md](01-estado-atual.md)) tem 32 modelos Eloquent,
24+ controllers, ~85 rotas, e 7 services. A app Android vai expor ~65 destas rotas como
endpoints API (ref. [02-inventario-funcional.md](02-inventario-funcional.md)) e mapear ~14 áreas
funcionais (ref. [03-mapeamento-funcional.md](03-mapeamento-funcional.md)).

A modularização é essencial mesmo para 1 developer porque:
- **Build times**: compilação incremental — alterar `feature-diary` não recompila `feature-forum`
- **Isolamento de features**: cada módulo é uma unidade testável e deployável conceptualmente
- **Fases de rollout**: módulos alinham-se diretamente com as fases de desenvolvimento
  (Fase 1A: core, 1B: features essenciais, 2: comunidade, 3: real-time)
- **Prevenção de acoplamento**: sem modularização, é fácil criar dependências ocultas entre
  features que depois são caras de separar

Ref. [25-riscos-decisoes.md](25-riscos-decisoes.md) D-05 para justificação de modularização
com equipa de 1 developer.

---

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

### core-testing (testImplementation)

```
core-testing → core-domain, core-common, kotlinx-coroutines-test
```

Todos os feature modules: `testImplementation(project(":core:core-testing"))`

---

## Limites de dimensão de módulos

| Tipo de módulo | Soft limit | Ação se exceder |
|---------------|-----------|----------------|
| Feature module | ~30 ficheiros | Considerar split por sub-feature |
| Core module | ~50 ficheiros | Review, mas sem upper limit rígido |

**Exemplo prático:** `feature-calm-zone` pode crescer significativamente (hub + 8 exercícios +
cofre + playlist + safety plan). Se exceder ~30 ficheiros, considerar split em:
- `feature-calm-zone` (hub + exercícios core: grounding, respiração, heartbeat)
- `feature-calm-zone-vault` (cofre pessoal, gestão de items)

Mas é prematuro dividir agora — iniciar como módulo único e dividir quando a complexidade
o justificar. Dividir prematuramente adiciona overhead de configuração Gradle sem benefício real.

---

## Módulo `core-testing`

Test utilities partilhados por todos os módulos:

```
core-testing/
├── src/main/kotlin/pt/lumina/core/testing/
│   ├── fake/
│   │   ├── FakeDiaryRepository.kt
│   │   ├── FakeProfileRepository.kt
│   │   ├── FakeGamificationRepository.kt
│   │   ├── FakeAuthRepository.kt
│   │   └── FakeConnectivityMonitor.kt
│   ├── rule/
│   │   └── TestDispatcherRule.kt        ← Substitui Dispatchers.Main com TestDispatcher
│   ├── assertion/
│   │   └── StateFlowAssertions.kt       ← assertState {}, awaitState {}
│   └── compose/
│       └── ComposeTestHelpers.kt        ← findByContentDescription, assertTalkBackNavigable
```

**Dependências:**
- `core-domain` (interfaces de repository)
- `core-common` (extensions partilhadas)
- `kotlinx-coroutines-test`
- `junit-jupiter-api`

**Uso:** `testImplementation(project(":core:core-testing"))` em todos os feature modules.
Cada fake repository implementa a interface do domain com dados in-memory, permitindo
testes de ViewModel sem mock framework.

---

## Dynamic Feature Delivery

**Decisão: NÃO usar Dynamic Feature Delivery nas Fases 1-3.**

| Critério | Estado | Conclusão |
|----------|--------|-----------|
| APK size alvo | < 15MB download | Bem abaixo do threshold de 150MB |
| Complexidade de DFD | SplitInstallManager, deferred installation UX, fallback handling | Elevada |
| Benefício | Nenhum com APK < 15MB | Zero |
| Quando reconsiderar | APK > 30MB (ex: assets de áudio bundled na Fase 4+) | Fase 4+ |

Dynamic Delivery exige: SplitInstallManager, UI de download em progresso, handling de módulos
ainda não instalados, testes específicos. Esta complexidade não se justifica para um APK < 15MB.

Ref. [25-riscos-decisoes.md](25-riscos-decisoes.md) R-04.

---

## Enforcement de dependências

### Prevenção de dependências acidentais entre feature modules

**Regra:** nenhum feature module pode depender de outro feature module (exceção documentada:
`feature-buddy → feature-chat`).

**Mecanismos de enforcement:**

1. **Gradle `implementation` (não `api`)**: todas as dependências de feature modules usam
   `implementation`, limitando a transitividade.

2. **CI check**: script no pipeline que analisa `build.gradle.kts` de cada feature module
   e rejeita PRs que adicionem `project(":feature:")` como dependência (exceto a exceção
   documentada).

3. **Module-graph plugin**: [com.jraska.module.graph.assertion](https://github.com/jraska/modules-graph-assert)
   para visualizar e validar o grafo de dependências. Configuração:

   ```kotlin
   // root build.gradle.kts
   moduleGraphAssert {
       maxHeight = 4 // app → feature → core → core-common
       restricted {
           "feature-.*" dependsOn "feature-.*" // Proibir feature→feature
       }
       configurations = setOf("implementation")
   }
   ```

4. **Excepção documentada:** `feature-buddy` depende de `feature-chat` porque reutiliza
   o componente de chat 1:1. Esta dependência está explicitamente documentada e validada.

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
- **Convention plugins** em `build-logic/` para partilhar configuração
- **Version Catalog** (`gradle/libs.versions.toml`) para todas as dependências

### Convention plugins — detalhe

| Plugin | Aplica-se a | Configuração |
|--------|------------|-------------|
| `lumina.android.library` | Todos os core modules | minSdk 26, targetSdk 35, Kotlin 2.0+, Compose compiler, detekt + ktlint |
| `lumina.android.feature` | Todos os feature modules | Extends `library` + Hilt plugin + Navigation Compose + Compose dependencies + `testImplementation(core-testing)` |
| `lumina.android.application` | `app` module | Extends `feature` + signing configs (debug/release) + R8 rules + Baseline Profiles |
| `lumina.jvm.library` | `core-domain`, módulos Kotlin puros | Kotlin JVM, sem Android dependencies, JUnit 5, detekt + ktlint |

**Vantagem:** adicionar um novo feature module requer apenas:
1. Criar o directório `feature/feature-new/`
2. `build.gradle.kts` com 5 linhas (apply plugin + dependencies)
3. Adicionar ao `settings.gradle.kts`

Sem convention plugins, cada módulo teria ~40 linhas de configuração Gradle duplicada.

---

## Fases de criação de módulos

| Fase | Módulos a criar | Total módulos | Build time estimado (clean) | Build time incremental |
|------|----------------|--------------|---------------------------|----------------------|
| 1A (fundação) | app, core-network, core-database, core-domain, core-ui, core-auth, core-common, core-testing | 8 | ~40s | ~15s |
| 1B (core features) | feature-auth, feature-onboarding, feature-dashboard, feature-diary, feature-calm-zone, feature-profile, feature-gamification | +7 (15 total) | ~60s | ~25s |
| 2 (comunidade) | feature-forum, feature-assessment, feature-library, feature-wall, feature-search | +5 (20 total) | ~75s | ~20s |
| 3 (real-time) | feature-chat, feature-buddy | +2 (22 total) | ~80s | ~15s |

**Nota:** build times são estimativas baseadas em projectos comparáveis. Build time incremental
assume alteração num único feature module. Com Gradle build cache e configuration cache habilitados
(ref. [07-stack-android.md](07-stack-android.md)), o build incremental real deve ser inferior.

---

## Riscos

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| Over-modularization prematura (demasiados módulos cedo) | Média | Médio | Criar core + 2-3 features em 1A, resto incremental conforme necessidade real |
| Room DB entities em core-database mas DAOs espalhados | Alta | Baixo | Convenção: entities SEMPRE em `core-database`. DAOs podem estar em feature modules se a query é específica da feature |
| Convention plugins difíceis de manter | Baixa | Médio | Seguir exemplos do [NowInAndroid](https://github.com/android/nowinandroid) (referência oficial Google) |
| Package naming `pt.lumina` vs domínio real | Baixa | Baixo | Confirmar domínio antes do primeiro release. Renomear packages é refactor pesado — decidir cedo |
| Gradle sync lento com 20+ módulos | Média | Baixo | Configuration cache + Gradle daemon. Aceitável para < 25 módulos |
| feature-buddy → feature-chat cria acoplamento | Baixa | Médio | Isolar interface de chat partilhada em `core-domain` se a dependência se tornar problemática |

---

*Próximo: [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — Reutilização do backend Laravel existente.*
