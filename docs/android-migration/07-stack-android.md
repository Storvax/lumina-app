# 07 — Stack Android Moderna Recomendada

## Decisão principal

**Kotlin + Jetpack Compose + Clean Architecture + MVI**

Esta é a stack oficial e mais madura para desenvolvimento Android nativo em 2026. Cada escolha é justificada abaixo.

---

## Linguagem: Kotlin

| Aspeto | Detalhe |
|--------|---------|
| **Escolha** | Kotlin (JVM) |
| **Alternativas descartadas** | Java (legado, verbose), Kotlin Multiplatform (prematuro para este caso) |
| **Justificação** | Linguagem oficial do Android desde 2019. Null-safety, coroutines, extension functions, data classes. 100% da documentação oficial do Android é Kotlin-first. |
| **Versão recomendada** | Kotlin 2.0+ |
| **Impacto** | Código mais conciso, seguro e mantível. Coroutines simplificam operações assíncronas (networking, DB, file I/O). |

---

## UI Framework: Jetpack Compose

| Aspeto | Detalhe |
|--------|---------|
| **Escolha** | Jetpack Compose com Material 3 |
| **Alternativas descartadas** | Views XML (legado), Flutter (cross-platform, diferente ecossistema), React Native (JavaScript, não nativo) |
| **Justificação** | Framework declarativo oficial do Android. Estado da arte, com suporte completo a Material 3, animações, acessibilidade, temas, e tooling. Código UI mais conciso e testável que Views XML. |
| **Versão recomendada** | Compose BOM 2025+ |
| **Impacto** | UI declarativa alinhada com tendências da indústria. Animações fluidas para exercícios da Zona Calma. Theming nativo para light/dark mode. |

### Porquê não Flutter ou React Native?
- A Lumina é um produto Android-first (sem necessidade imediata de iOS)
- Compose dá acesso total às APIs Android nativas (vibração, áudio, câmara, notificações)
- Performance nativa garantida sem bridge
- Ecossistema Google-first (Firebase, Hilt, Room, etc.)
- Se iOS for necessário no futuro, Kotlin Multiplatform + Compose Multiplatform é uma opção de migração natural

---

## Arquitetura: Clean Architecture + MVI

| Aspeto | Detalhe |
|--------|---------|
| **Escolha** | Clean Architecture com padrão MVI (Model-View-Intent) |
| **Alternativas descartadas** | MVVM puro (menos previsível), MVC (demasiado acoplado) |
| **Justificação** | Clean Architecture separa domain, data, e presentation. MVI garante fluxo unidirecional de dados, estado previsível, e facilidade de debug. Ideal para app com estados emocionais complexos. |
| **Impacto** | Testabilidade alta, separação clara de responsabilidades, facilita contribuições futuras. |

Detalhes completos em [08-arquitetura-android.md](08-arquitetura-android.md).

---

## Bibliotecas recomendadas

### Networking

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Retrofit 2** | HTTP client | Standard da indústria. Type-safe, extensível, suporte a coroutines. |
| **OkHttp 4** | HTTP engine + WebSocket | Base do Retrofit. Interceptors para auth, logging, retry. WebSocket client nativo. |
| **Kotlinx Serialization** | JSON parsing | Oficial Kotlin, compile-time safe, mais rápido que Gson/Moshi. |

### Base de dados local

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Room** | Base de dados SQLite | Oficial, type-safe, integra com Flow/coroutines. Migrations suportadas. |
| **DataStore** | Preferências simples | Substituto moderno de SharedPreferences. Para settings, tokens, flags. |

### Dependency Injection

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Hilt** | DI framework | Oficial (built on Dagger). Compile-time validation, integração com ViewModel, WorkManager, Navigation. |

### Imagens

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Coil 3** | Image loading | Kotlin-first, Compose-native, coroutine-based. Disk + memory cache. |

### Navegação

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Navigation Compose** | Navegação entre telas | Oficial. Type-safe arguments, deep links, nested graphs. |

### Real-time

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **OkHttp WebSocket** | WebSocket client | Já incluído no OkHttp. Fiável, reconexão manual simples. |

### Áudio

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **ExoPlayer (Media3)** | Reprodução de áudio | Oficial Google. Suporta streaming, background playback, notification controls. |
| **MediaRecorder** | Gravação de áudio | API nativa Android. Gravação de posts de áudio. |

### Push Notifications

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Firebase Cloud Messaging** | Push notifications | Standard Android. Fiável, gratuito, suporta topics e targeting. |

### Segurança

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **EncryptedSharedPreferences** | Token storage | AndroidX Security. Encripta chaves e valores. |
| **BiometricPrompt** | Auth biométrica | AndroidX Biometric. Fingerprint + Face. |

### Background Work

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **WorkManager** | Sync, uploads em background | Oficial. Garante execução mesmo com app fechada. Constraint-aware (rede, bateria). |

### Testing

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **JUnit 5** | Unit tests | Standard. |
| **Mockk** | Mocking | Kotlin-native, coroutine-aware. |
| **Turbine** | Flow testing | Testa Kotlin Flows facilmente. |
| **Compose UI Test** | UI tests | Oficial. Semântica-based, acessibilidade-aware. |
| **Espresso** | Integration tests | Se necessário para Views legadas. |

### Observabilidade

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Timber** | Logging | Leve, extensível, strip em release. |
| **Firebase Crashlytics** | Crash reporting | Standard. Correlação com Analytics. |
| **Firebase Analytics** | Event tracking | Alinhado com AnalyticsService do backend. |

### Utilidades

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **Accompanist** | Permissões, system UI | Compose utilities oficiais Google. |
| **Lottie Compose** | Animações complexas | Para loading states terapêuticos, animações de flame. |

---

## Versões mínimas

| Componente | Versão |
|-----------|--------|
| Android min SDK | 26 (Android 8.0) |
| Android target SDK | 35 (Android 15) |
| Kotlin | 2.0+ |
| Compose BOM | 2025.01+ |
| Gradle | 8.5+ |
| AGP (Android Gradle Plugin) | 8.5+ |
| JDK | 17 |

**Porquê API 26 (Android 8.0)?**
- Cobre 97%+ dos dispositivos Android ativos em 2026
- Notification Channels (obrigatórias para notificações estruturadas)
- Autofill framework
- Fonts in XML
- Picture-in-Picture (potencial futuro para exercícios)

---

## Gestão de dependências

- **Gradle Version Catalog** (`libs.versions.toml`) para centralizar versões
- **Compose BOM** para alinhar todas as versões Compose
- **Dependabot** ou **Renovate** para atualizações automáticas de dependências

---

## Resumo da stack

```
Linguagem:        Kotlin 2.0+
UI:               Jetpack Compose + Material 3
Arquitetura:      Clean Architecture + MVI
DI:               Hilt
Networking:       Retrofit + OkHttp + Kotlinx Serialization
Real-time:        OkHttp WebSocket
DB local:         Room + DataStore
Imagens:          Coil 3
Áudio:            ExoPlayer (Media3) + MediaRecorder
Navegação:        Navigation Compose
Background:       WorkManager
Push:             Firebase Cloud Messaging
Segurança:        EncryptedSharedPreferences + BiometricPrompt
Observabilidade:  Timber + Firebase Crashlytics + Firebase Analytics
Animações:        Compose Animations + Lottie
Testing:          JUnit 5 + Mockk + Turbine + Compose UI Test
Build:            Gradle 8.5+ + Version Catalog
Min SDK:          26 (Android 8.0)
```

---

*Próximo: [08-arquitetura-android.md](08-arquitetura-android.md) — Proposta detalhada de arquitetura.*
