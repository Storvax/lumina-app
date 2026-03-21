# 07 — Stack Android Moderna Recomendada

## Contexto

A Lumina é uma plataforma de saúde mental. Cada decisão técnica na app Android deve ser avaliada
não apenas pela robustez, mas pela adequação a um produto onde o utilizador pode estar em crise
emocional, com mãos a tremer, em modo de pânico, ou com atenção reduzida.

Este documento define a stack para uma app **verdadeiramente nativa** — não um wrapper de web,
não um hybrid, não uma solução cross-platform. Cada escolha é justificada pelo acesso total
às APIs do Android (vibração, áudio, câmara, notificações, encriptação, biometria) que um
produto de saúde mental exige.

**De onde vimos** (ref. [01-estado-atual.md](01-estado-atual.md)): Blade + Alpine.js, autenticação
session-based, sem offline, sem haptic feedback, sem gravação de áudio nativa, sem push nativo,
sem encriptação de dados at-rest.

**Para onde vamos**: Kotlin nativo com acesso total ao ecossistema Android.

---

## Observações do estado atual

O sistema web não tem equivalente para:
- **Offline-first** — todas as ações requerem rede; sem Service Worker implementado
- **Haptic feedback** — exercícios de respiração e grounding não têm componente tátil
- **Gravação de áudio nativa** — posts de áudio passam por Web API limitada
- **Push notifications nativas** — VAPID parcial para web, FCM inexistente
- **Encriptação de dados at-rest** — dados sensíveis (diário, cofre, safety plan) não estão
  encriptados no browser
- **Biometria** — sem autenticação biométrica possível em web

Estes gaps são o motivo fundamental para a abordagem nativa completa.

---

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

**Análise de alternativas:**

| Framework | Vantagens | Riscos | Decisão |
|-----------|-----------|--------|---------|
| **Hilt** | Compile-time safety, integração oficial com ViewModel/WorkManager/Navigation, graph validation em build | Boilerplate de módulos Hilt, curva Dagger | **Escolhido** |
| **Koin** | Setup simples, Kotlin-native, DSL intuitivo | Erros só em runtime, sem graph validation, falhas silenciosas | Descartado |
| **Manual DI** | Zero dependências, controlo total | Não escala para 15+ módulos, manutenção pesada | Descartado |

**Justificação da decisão:** Com modularização de 15+ feature modules (ref. [09-modularizacao.md](09-modularizacao.md)),
a validação compile-time do Hilt é mais importante que a simplicidade de setup do Koin. Um erro de
DI em runtime numa app de saúde mental (ex: crash ao abrir Zona Calma durante crise) é inaceitável.

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

### Áudio e media

| Biblioteca | Uso | Justificação |
|-----------|-----|-------------|
| **ExoPlayer (Media3)** | Reprodução de áudio | Oficial Google. Suporta streaming, background playback, notification controls. |
| **MediaRecorder** | Gravação de áudio | API nativa Android. Gravação de posts de áudio. |

**Justificação técnica detalhada:**

- **Media3 (ExoPlayer successor)**: API unificada para playback, MediaSession para controlo de
  media na notification area, Foreground Service para sons relaxantes em background,
  suporte a múltiplos streams simultâneos (mixer de sons da Zona Calma com volume independente).
  Porquê sobre alternativas: suporte oficial Google, notification controls integrados, sem
  dependências de terceiros.
- **MediaRecorder**: gravação de posts de áudio (≤60s) e reflexão por voz na Zona Calma.
  Porquê sobre AudioRecord: API mais simples, suficiente para gravação de voz, sem necessidade
  de processing raw PCM.
- **Compressão**: AAC (M4A), bitrate 128kbps — suficiente para voz, tamanho reduzido para upload.
  Ref. [15-audio-media-uploads.md](15-audio-media-uploads.md) para detalhes.

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
| **Firebase Crashlytics** | Crash reporting + ANR | Standard. Correlação com Analytics. ANR detection automático. |
| **Firebase Analytics** | Event tracking | Alinhado com AnalyticsService do backend. |
| **Firebase Performance** | Performance monitoring | Tracing de network calls, cold start, screen render time. |

**Observabilidade expandida:**

- **Performance monitoring**: cold start time, screen render time, network call latency.
  Métrica crítica: tempo-até-ecrã-de-crise (Zona Calma) deve ser < 500ms.
- **ANR detection**: Firebase Crashlytics detecta ANRs automaticamente. Monitorizar threads
  bloqueadas em operações de Room DB ou crypto.
- **User flow tracking**: cuidado especial numa app de saúde mental. Tracking focado em
  eventos estruturais (`screen_view`, `action_type`, `feature_used`), nunca conteúdo.

**Nota de privacidade GDPR:**
Dados de analytics NUNCA incluem conteúdo do diário, mensagens de chat, informação clínica,
ou texto livre do utilizador. Apenas eventos estruturais (screen_view, action_type, latency).
Analytics é opt-in conforme preferências GDPR do utilizador. Ref. [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md).

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
| Android compile SDK | 35 |
| Build tools | 35.0+ |
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

**Notas sobre serialização:**
Kotlinx Serialization escolhida sobre Moshi e Gson: compile-time safe, Kotlin-native,
sem reflection. Para compatibilidade com Laravel snake_case, usar `@SerialName("field_name")`
nas data classes de DTO.

**Room migration strategy:**
- Auto-migrations para alterações simples (adicionar coluna, criar tabela)
- Migrations manuais para alterações destrutivas (renomear, remover, transformar dados)
- Testes de migration em CI para prevenir crashes em produção

---

## Gestão de dependências

- **Gradle Version Catalog** (`libs.versions.toml`) para centralizar versões
- **Compose BOM** para alinhar todas as versões Compose
- **Dependabot** ou **Renovate** para atualizações automáticas de dependências

---

## CI/CD

| Componente | Ferramenta | Detalhes |
|-----------|-----------|---------|
| CI | GitHub Actions | Build + lint + unit tests em cada PR |
| Lint | Android Lint + detekt + ktlint | Acessibilidade, performance, security, formatação |
| Build matrix | API 26 + API 35 | Testar contra min e target SDK |
| Instrumented tests | GitHub Actions emulator | Firebase Test Lab se budget permitir |
| Release signing | GitHub Secrets | Keystore management via [21-segredos-env.md](21-segredos-env.md) |
| Artifacts | APK debug em PRs, AAB release em tags | Distribuição via Play Console |

**Pipeline por PR:**
1. ktlint check → 2. detekt → 3. Android Lint → 4. Unit tests → 5. Build debug APK

**Pipeline de release (tags):**
1. Full pipeline + 2. Instrumented tests → 3. Build AAB release → 4. Upload artifact

---

## Qualidade de código

| Ferramenta | Propósito | Configuração |
|-----------|-----------|-------------|
| **detekt** | Static analysis Kotlin | Custom rules Lumina: enforce `contentDescription` em todos os `Image()` composables |
| **ktlint** | Formatação consistente | Kotlin official style guide |
| **Android Lint** | Avisos Android-specific | Focus: acessibilidade, performance, security |

**Integração local:**
- Pre-commit hook com `ktlint --check` para catch de formatação antes de push.
- detekt integrado no build Gradle: `./gradlew detekt` falha se houver violações.

**Custom rule Lumina (detekt):**
Todos os composables `Image()` e `Icon()` devem ter `contentDescription` preenchido —
essencial para TalkBack em app de saúde mental.

---

## Build optimization

| Optimização | Detalhe | Impacto |
|------------|---------|---------|
| Gradle build cache | Habilitado desde o início | Evita recompilação de módulos unchanged |
| Configuration cache | Habilitado | Acelera configuration phase |
| Compilação paralela | Habilitada pela modularização (ref. [09](09-modularizacao.md)) | Módulos compilados em paralelo |
| R8 full mode | Release builds | Minificação, tree-shaking, otimização |
| Baseline Profiles | Via Macrobenchmark | 30-40% melhoria em cold start |

**Keep rules R8:**
- Modelos Retrofit (Kotlinx Serialization): `@Keep` ou regras ProGuard
- Room entities: preservar via annotations
- Hilt generated code: preservado automaticamente

**Alvos:**
- Build time incremental: < 30s para um feature module
- APK download size: < 15MB
- APK installed size: < 30MB

---

## Riscos da stack

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| Compose BOM breaking changes | Média | Alto | Pin BOM version, update deliberado e testado |
| Reverb WebSocket protocol diverge de Pusher | Baixa | Alto | Testar cedo na Fase 1A (ref. [25-riscos-decisoes.md](25-riscos-decisoes.md) R-01) |
| Firebase dependência (vendor lock-in) | Baixa | Médio | Abstrair por interfaces; possibilidade de trocar provider |
| Kotlinx Serialization vs Laravel snake_case | Alta | Baixo | `@SerialName` annotations em todos os DTOs |
| Hilt boilerplate crescente | Média | Baixo | Convention plugins + templates de módulo |

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
