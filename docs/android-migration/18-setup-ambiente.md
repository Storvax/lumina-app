# 18 — Setup de Ambiente de Desenvolvimento em Múltiplos PCs

## Contexto

O desenvolvimento vai envolver trabalho em pelo menos 2 máquinas (casa + outra localização). É essencial que o ambiente seja reproduzível, consistente e rápido de configurar numa máquina nova.

---

## 1. Requisitos de hardware

### Mínimo (funcional)

| Componente | Requisito |
|-----------|----------|
| CPU | Intel i5/AMD Ryzen 5 (8ª gen+) ou Apple M1 |
| RAM | 16 GB |
| Disco | SSD com 50 GB livres |
| Ecrã | 1920x1080 |
| OS | Windows 10/11, macOS 12+, ou Ubuntu 22.04+ |

### Recomendado (confortável)

| Componente | Requisito |
|-----------|----------|
| CPU | Intel i7/AMD Ryzen 7 ou Apple M2/M3 |
| RAM | 32 GB |
| Disco | NVMe SSD com 100 GB livres |
| Ecrã | 1920x1080+ (dual monitor ideal) |

**Nota:** O emulador Android consome ~4-8 GB de RAM. Com Android Studio + emulador + backend Laravel local, 16 GB é o mínimo viável.

---

## 2. Sistemas operativos suportados

| OS | Android Dev | Backend Dev | Notas |
|----|------------|------------|-------|
| Windows 10/11 | ✅ | ✅ (WSL2) | HAXM ou WHPX para emulador |
| macOS 12+ | ✅ | ✅ | Melhor experiência (Apple Silicon excelente) |
| Ubuntu 22.04+ | ✅ | ✅ | KVM para emulador |

**Recomendação:** Qualquer um dos três funciona. macOS tem a melhor experiência integrada. Windows com WSL2 é a segunda melhor opção.

---

## 3. Software essencial

### Para desenvolvimento Android

| Software | Versão | Propósito | Obrigatório |
|----------|--------|-----------|-------------|
| **Android Studio** | Ladybug 2024.2+ | IDE principal | ✅ |
| **JDK** | 17 (Temurin/Corretto) | Compilação Kotlin/Gradle | ✅ |
| **Android SDK** | API 35 + build-tools | Compilação e emulação | ✅ |
| **Git** | 2.40+ | Controlo de versão | ✅ |
| **ADB** | Incluído no SDK | Debug em devices | ✅ |

### Para desenvolvimento backend (quando necessário localmente)

| Software | Versão | Propósito | Obrigatório |
|----------|--------|-----------|-------------|
| **PHP** | 8.2+ | Executar Laravel | Recomendado |
| **Composer** | 2.x | Dependências PHP | Recomendado |
| **Node.js** | 20 LTS | Build de assets (Vite) | Recomendado |
| **Docker** | 24+ | Backend containerizado | Alternativa ao PHP local |
| **SQLite** | 3.x | DB de desenvolvimento | Incluído no PHP |
| **Redis** | 7+ | Cache/queues (se necessário) | Opcional |

### Ferramentas de produtividade

| Software | Propósito | Obrigatório |
|----------|-----------|-------------|
| **VS Code** ou **Cursor** | Editor para backend/docs | Recomendado |
| **Postman** ou **Bruno** | Testar API endpoints | Recomendado |
| **scrcpy** | Mirror de device Android no PC | Opcional |
| **Charles Proxy** | Debug de networking | Opcional |

---

## 4. Configuração do Android Studio

### Plugins recomendados

| Plugin | Propósito |
|--------|-----------|
| Kotlin | Incluído por defeito |
| Compose Multiplatform | Compose tooling |
| Material Theme UI | Melhor UI do IDE |
| .env files support | Leitura de ficheiros .env |
| Database Navigator | Inspecionar Room DB |
| JSON Formatter | Formatar payloads API |

### Configurações de IDE

```
File → Settings → Editor → Code Style → Kotlin
  Tab size: 4
  Indent: 4
  Continuation indent: 8

File → Settings → Editor → General → Auto Import
  ✅ Add unambiguous imports on the fly
  ✅ Optimize imports on the fly

File → Settings → Build, Execution → Build Tools → Gradle
  Gradle JDK: JDK 17
  Build and run using: Gradle
```

### Emulador

**Device recomendado:** Pixel 7 (ou equivalente, API 34)

```
SDK Manager → SDK Platforms → Android 14 (API 34)
SDK Manager → SDK Tools → Android Emulator, SDK Build-Tools, SDK Platform-Tools

AVD Manager → Create Virtual Device
  → Pixel 7
  → System Image: API 34 (x86_64 ou ARM para Apple Silicon)
  → RAM: 4096 MB
  → Internal Storage: 4096 MB
```

**Performance:**
- Windows: Ativar HAXM ou Hyper-V (WHPX)
- Linux: Ativar KVM (`sudo apt install qemu-kvm`)
- macOS Apple Silicon: Emulador ARM nativo (excelente performance)

---

## 5. Configuração do projeto Android

### Gradle

```properties
# gradle.properties
org.gradle.jvmargs=-Xmx4g -XX:MaxMetaspaceSize=1g
org.gradle.parallel=true
org.gradle.caching=true
org.gradle.configuration-cache=true
android.useAndroidX=true
kotlin.code.style=official
```

### Version Catalog

```toml
# gradle/libs.versions.toml
[versions]
kotlin = "2.0.21"
compose-bom = "2025.01.01"
hilt = "2.51.1"
retrofit = "2.11.0"
room = "2.6.1"
# ...

[libraries]
compose-bom = { group = "androidx.compose", name = "compose-bom", version.ref = "compose-bom" }
# ...
```

---

## 6. Configuração do backend local (quando necessário)

### Opção A: Docker (recomendada para consistência)

```bash
# Na raiz do projeto lumina-app
docker compose up -d
# Acesso: http://localhost:8000
```

### Opção B: PHP local

```bash
# Instalar dependências
composer install
npm install

# Configurar
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Executar
php artisan serve    # API em localhost:8000
npm run dev          # Assets em localhost:5173
php artisan reverb:start  # WebSocket em localhost:6001
```

### Conectar app Android ao backend local

**Emulador:**
- `10.0.2.2` é o alias do localhost do host no emulador Android
- Configurar `BASE_URL=http://10.0.2.2:8000/api/v1/`

**Device físico (mesma rede):**
- Usar IP local da máquina: `http://192.168.1.x:8000/api/v1/`
- Ou usar `adb reverse tcp:8000 tcp:8000`

---

## 7. Device testing

### Devices físicos recomendados

| Device | Porquê |
|--------|--------|
| Gama média recente (Samsung A54, Pixel 7a) | Representa a maioria dos utilizadores em Portugal |
| Gama baixa (Redmi Note 12, Samsung A15) | Testar performance mínima |
| Tablet Android (opcional) | Verificar layout responsivo |

### ADB debugging

```bash
# Listar devices conectados
adb devices

# Instalar APK
adb install app/build/outputs/apk/debug/app-debug.apk

# Ver logs da app
adb logcat | grep "Lumina"

# Reverse port (aceder backend local via device)
adb reverse tcp:8000 tcp:8000
adb reverse tcp:6001 tcp:6001
```

---

## 8. CI/CD

### GitHub Actions (mínimo para fase 1)

```yaml
# .github/workflows/android-build.yml
name: Android Build
on:
  push:
    branches: [main, develop]
    paths: ['lumina-android/**']
  pull_request:
    paths: ['lumina-android/**']

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-java@v4
        with:
          distribution: 'temurin'
          java-version: '17'
      - uses: gradle/actions/setup-gradle@v3
      - run: cd lumina-android && ./gradlew assembleDebug
      - run: cd lumina-android && ./gradlew testDebugUnitTest
      - run: cd lumina-android && ./gradlew detekt  # Lint
```

---

*Próximo: [19-software-ferramentas.md](19-software-ferramentas.md) — Lista detalhada de software a instalar.*
