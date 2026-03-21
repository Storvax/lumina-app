# 19 — Software e Ferramentas a Instalar em Cada Máquina

## Checklist de instalação

Esta é a lista completa e sequencial de tudo o que deve ser instalado numa nova máquina para desenvolver a app Android da Lumina e, quando necessário, correr o backend localmente.

---

## Bloco 1 — Base do sistema

### 1.1 Git

| OS | Instalação |
|----|-----------|
| Windows | `winget install Git.Git` ou [git-scm.com](https://git-scm.com) |
| macOS | `brew install git` ou Xcode Command Line Tools |
| Linux | `sudo apt install git` |

**Configuração inicial (em todas as máquinas):**
```bash
git config --global user.name "Teu Nome"
git config --global user.email "teu@email.com"
git config --global core.autocrlf input  # Normalizar line endings
git config --global init.defaultBranch main
git config --global pull.rebase false
```

### 1.2 SSH keys (para GitHub)

```bash
ssh-keygen -t ed25519 -C "teu@email.com"
# Adicionar a chave pública ao GitHub: Settings → SSH Keys
```

**Sincronizar entre máquinas:** Cada máquina tem a sua própria SSH key. Adicionar todas ao GitHub.

---

## Bloco 2 — Android Development

### 2.1 JDK 17

| OS | Instalação |
|----|-----------|
| Windows | `winget install EclipseAdoptium.Temurin.17.JDK` |
| macOS | `brew install --cask temurin17` |
| Linux | `sudo apt install temurin-17-jdk` ou SDKMAN |

**Verificar:** `java -version` → deve mostrar 17.x

### 2.2 Android Studio

| OS | Instalação |
|----|-----------|
| Windows | [developer.android.com/studio](https://developer.android.com/studio) |
| macOS | `brew install --cask android-studio` |
| Linux | Download + extract, ou `snap install android-studio --classic` |

**Primeira execução — Setup Wizard:**
1. Standard installation
2. Aceitar licenças SDK
3. Download: Android SDK, SDK Build-Tools, SDK Platform-Tools, Emulator

### 2.3 Android SDK (via SDK Manager no Android Studio)

**SDK Platforms:**
- Android 15 (API 35) — target SDK
- Android 14 (API 34) — para testing
- Android 8.0 (API 26) — min SDK (opcionalmente, para testar compatibilidade)

**SDK Tools:**
- Android SDK Build-Tools (latest)
- Android SDK Platform-Tools
- Android Emulator
- Android SDK Command-line Tools
- Google Play services (para FCM testing)

### 2.4 Variáveis de ambiente

```bash
# Adicionar ao ~/.bashrc, ~/.zshrc, ou equivalente

# JAVA_HOME
export JAVA_HOME=/path/to/jdk-17    # Ajustar por OS

# ANDROID_HOME
export ANDROID_HOME=$HOME/Android/Sdk         # Linux
# export ANDROID_HOME=$HOME/Library/Android/sdk  # macOS
# export ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk  # Windows

export PATH=$PATH:$ANDROID_HOME/platform-tools
export PATH=$PATH:$ANDROID_HOME/tools/bin
```

### 2.5 Emulador Android

Criar AVD no Android Studio:
```
Tools → Device Manager → Create Virtual Device
  → Phone → Pixel 7
  → System Image: API 34 (x86_64 ou ARM)
  → Finish
```

**Aceleração de hardware:**
- Windows: Ativar HAXM (Intel) ou WHPX (Hyper-V)
- Linux: `sudo apt install qemu-kvm` + adicionar user ao grupo kvm
- macOS (Apple Silicon): Nativo, sem configuração extra

---

## Bloco 3 — Backend Development (opcional mas recomendado)

### 3.1 PHP 8.2+

| OS | Instalação |
|----|-----------|
| Windows | Via XAMPP, Laragon, ou `winget install PHP.PHP.8.2` |
| macOS | `brew install php@8.2` |
| Linux | `sudo apt install php8.2 php8.2-{sqlite3,mbstring,xml,curl,zip,bcmath,redis}` |

### 3.2 Composer

```bash
# Todas as plataformas
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

### 3.3 Node.js 20 LTS

| OS | Instalação |
|----|-----------|
| Windows | `winget install OpenJS.NodeJS.LTS` |
| macOS | `brew install node@20` |
| Linux | Via nvm: `nvm install 20` |

### 3.4 Docker (alternativa a PHP/Node locais)

| OS | Instalação |
|----|-----------|
| Windows | Docker Desktop: `winget install Docker.DockerDesktop` |
| macOS | `brew install --cask docker` |
| Linux | `sudo apt install docker.io docker-compose-v2` |

---

## Bloco 4 — Ferramentas de produtividade

### 4.1 Editor para backend/docs

| Ferramenta | Instalação |
|-----------|-----------|
| VS Code | `winget install Microsoft.VisualCode` / `brew install --cask visual-studio-code` |
| Cursor | [cursor.com](https://cursor.com) |

### 4.2 API testing

| Ferramenta | Tipo | Notas |
|-----------|------|-------|
| **Bruno** | Desktop app, Git-friendly | Recomendado — coleções versionáveis |
| Postman | Desktop app | Alternativa popular |
| httpie | CLI | Para testes rápidos: `http POST api/login email=x password=y` |

### 4.3 Database browser

| Ferramenta | Tipo | Notas |
|-----------|------|-------|
| DB Browser for SQLite | Desktop | Para inspecionar Room DB e backend SQLite |
| TablePlus | Desktop | Multi-DB (SQLite, PostgreSQL, Redis) |

### 4.4 Debugging

| Ferramenta | Tipo | Notas |
|-----------|------|-------|
| **scrcpy** | Mirror device | `brew install scrcpy` / `apt install scrcpy` |
| **Charles Proxy** | HTTP proxy | Inspecionar tráfego de rede da app |
| **Layout Inspector** | Android Studio | Debug de layouts Compose |
| **Database Inspector** | Android Studio | Inspecionar Room DB em runtime |

---

## Bloco 5 — Gestão de versões (opcional mas recomendado)

### SDKMAN (JDK e ferramentas)

```bash
curl -s "https://get.sdkman.io" | bash
sdk install java 17.0.11-tem
sdk install gradle 8.5
```

### nvm (Node.js)

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
nvm install 20
nvm use 20
```

**Vantagem:** Manter versões consistentes entre máquinas com `sdk env` e `.nvmrc`.

---

## Resumo — Ordem de instalação recomendada

```
1. Git + SSH key
2. JDK 17 (Temurin)
3. Android Studio
4. Android SDK (via Studio)
5. Emulador (via Studio)
6. Variáveis de ambiente (JAVA_HOME, ANDROID_HOME)
7. [Opcional] PHP 8.2+ + Composer
8. [Opcional] Node.js 20
9. [Opcional] Docker
10. VS Code / Cursor
11. Bruno / Postman
12. scrcpy
```

**Tempo estimado:** ~45 minutos para setup completo Android + ~15 minutos para backend.

---

*Próximo: [20-git-sincronizacao.md](20-git-sincronizacao.md) — Git workflow e sincronização entre máquinas.*
