# 22 — Bootstrap e Documentação para Novas Máquinas

## 1. Checklist rápida de setup (nova máquina)

### Passo 1: Base (10 min)
```bash
# Git
git --version  # Se não instalado, instalar (ver ficheiro 19)

# SSH key para GitHub
ssh-keygen -t ed25519 -C "teu@email.com"
cat ~/.ssh/id_ed25519.pub
# → Copiar e adicionar a github.com/settings/ssh/new

# Clonar repositório
git clone git@github.com:Storvax/lumina-app.git
cd lumina-app
git checkout develop
```

### Passo 2: JDK + Android Studio (20 min)
```bash
# JDK 17
# macOS: brew install --cask temurin17
# Linux: sudo apt install temurin-17-jdk
# Windows: winget install EclipseAdoptium.Temurin.17.JDK

java -version  # Verificar: 17.x

# Android Studio
# Descarregar de developer.android.com/studio
# Instalar e abrir
# → Setup Wizard → Standard → Aceitar licenças → Download SDK
```

### Passo 3: Variáveis de ambiente (5 min)
```bash
# Adicionar ao ~/.zshrc ou ~/.bashrc
export JAVA_HOME=$(/usr/libexec/java_home -v 17)  # macOS
# export JAVA_HOME=/usr/lib/jvm/temurin-17-jdk      # Linux
export ANDROID_HOME=$HOME/Library/Android/sdk       # macOS
# export ANDROID_HOME=$HOME/Android/Sdk              # Linux
export PATH=$PATH:$ANDROID_HOME/platform-tools

source ~/.zshrc  # Aplicar
```

### Passo 4: Segredos (5 min)
```bash
# Copiar do gestor de passwords:
# 1. .env (backend) → raiz do projeto
# 2. google-services.json → lumina-android/app/
# 3. lumina-release.jks → lumina-android/keystores/
# 4. keystore.properties → lumina-android/
```

### Passo 5: Abrir projeto Android (5 min)
```bash
# Abrir Android Studio
# File → Open → selecionar pasta lumina-android/
# Esperar Gradle sync (primeira vez: 5-10 min)
# Criar emulador (se ainda não existir):
#   Tools → Device Manager → Create Virtual Device → Pixel 7 → API 34
```

### Passo 6: Verificar tudo funciona (5 min)
```bash
# No Android Studio:
# 1. Build → Make Project (deve compilar sem erros)
# 2. Run → Run 'app' no emulador (deve abrir a app)

# No terminal (verificação backend se necessário):
cd /path/to/lumina-app
cp .env.example .env  # Se .env não foi copiado
php artisan serve      # Backend em localhost:8000
```

### Passo 7: Backend local (opcional, 10 min)
```bash
# Se precisar de correr backend localmente:
composer install
npm install
php artisan key:generate  # Apenas se .env é novo
php artisan migrate --seed
php artisan serve          # localhost:8000
npm run dev                # localhost:5173 (assets)
```

**Tempo total estimado: ~45-60 minutos** (incluindo downloads).

---

## 2. Script de verificação de ambiente

Criar `scripts/check-env.sh` (a incluir no repositório):

```bash
#!/bin/bash
echo "=== Lumina Environment Check ==="

# Git
echo -n "Git: "
git --version 2>/dev/null || echo "NOT INSTALLED"

# Java
echo -n "JDK: "
java -version 2>&1 | head -1 || echo "NOT INSTALLED"

# Android SDK
echo -n "Android SDK: "
if [ -d "$ANDROID_HOME" ]; then
    echo "$ANDROID_HOME"
else
    echo "NOT FOUND (set ANDROID_HOME)"
fi

# ADB
echo -n "ADB: "
adb --version 2>/dev/null | head -1 || echo "NOT INSTALLED"

# PHP (optional)
echo -n "PHP: "
php -v 2>/dev/null | head -1 || echo "NOT INSTALLED (optional)"

# Composer (optional)
echo -n "Composer: "
composer --version 2>/dev/null | head -1 || echo "NOT INSTALLED (optional)"

# Node (optional)
echo -n "Node: "
node --version 2>/dev/null || echo "NOT INSTALLED (optional)"

# Secrets
echo ""
echo "=== Secrets Check ==="
[ -f ".env" ] && echo "✅ .env" || echo "❌ .env MISSING"
[ -f "lumina-android/app/google-services.json" ] && echo "✅ google-services.json" || echo "⚠️  google-services.json MISSING (needed for Firebase)"
[ -f "lumina-android/keystores/lumina-release.jks" ] && echo "✅ keystore" || echo "⚠️  keystore MISSING (needed for release builds)"
[ -f "lumina-android/keystore.properties" ] && echo "✅ keystore.properties" || echo "⚠️  keystore.properties MISSING (needed for release builds)"

echo ""
echo "=== Done ==="
```

---

## 3. Troubleshooting comum

### "Gradle sync failed"

**Causa:** SDK ou JDK não encontrado.
```bash
# Verificar JAVA_HOME
echo $JAVA_HOME
java -version

# No Android Studio: File → Project Structure → SDK Location
# Apontar para o JDK 17 correto
```

### "SDK not found"

**Causa:** ANDROID_HOME não configurado.
```bash
# Verificar
echo $ANDROID_HOME
ls $ANDROID_HOME/platforms/

# Se vazio: Abrir Android Studio → SDK Manager → instalar SDK necessários
```

### Emulador lento

**Causa:** Aceleração de hardware não ativa.
```bash
# Linux: Verificar KVM
ls /dev/kvm
# Se não existir: sudo apt install qemu-kvm

# Windows: Verificar HAXM ou Hyper-V no BIOS
# macOS Apple Silicon: Nenhuma ação (nativo)
```

### "google-services.json not found"

**Causa:** Ficheiro Firebase não copiado.
```bash
# Copiar do gestor de passwords para:
lumina-android/app/google-services.json
```

### Backend não acessível do emulador

**Causa:** URL errada.
```bash
# Emulador usa 10.0.2.2 para localhost do host
# Verificar: adb shell ping 10.0.2.2

# Para device físico na mesma rede:
# Usar IP local: http://192.168.1.x:8000
# Ou: adb reverse tcp:8000 tcp:8000
```

### "Connection refused" no WebSocket

**Causa:** Reverb não está a correr.
```bash
php artisan reverb:start
# Verificar porta: netstat -tlnp | grep 6001

# No emulador: ws://10.0.2.2:6001
```

---

## 4. Documentação de setup — onde manter

| Documento | Localização | Propósito |
|-----------|------------|-----------|
| Este ficheiro | `docs/android-migration/22-bootstrap-novas-maquinas.md` | Setup completo |
| `scripts/check-env.sh` | Raiz do repo | Verificação rápida |
| `lumina-android/README.md` | Dentro do projeto Android | Quick start Android |
| `.env.example` | Raiz do repo | Template de variáveis backend |
| `keystore.properties.example` | `lumina-android/` | Template de signing |

---

## 5. Manter documentação atualizada

**Regra:** Sempre que mudar o setup (nova dependência, nova variável de ambiente, novo secret), atualizar:
1. Este ficheiro (bootstrap)
2. O `scripts/check-env.sh`
3. O `.env.example` ou `keystore.properties.example`

**Processo:**
```bash
# Ao adicionar uma dependência/config:
git add docs/android-migration/22-bootstrap-novas-maquinas.md
git commit -m "docs: update bootstrap for new dependency X"
```

---

*Próximo: [23-roadmap-fases.md](23-roadmap-fases.md) — Roadmap de execução por fases.*
