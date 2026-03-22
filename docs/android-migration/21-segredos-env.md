# 21 — Segredos, Variáveis de Ambiente e Ficheiros Sensíveis

## Contexto

Este documento é a referência para gestão de todos os segredos e ficheiros sensíveis do projeto Lumina,
incluindo backend (.env), Android (keystore, Firebase), e CI/CD (GitHub Secrets).

Refs:
- [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) — auth, biometria, GDPR, SQLCipher
- [18-setup-ambiente.md](18-setup-ambiente.md) — configuração por máquina
- [20-git-sincronizacao.md](20-git-sincronizacao.md) — .gitignore e ficheiros excluídos
- [22-bootstrap-novas-maquinas.md](22-bootstrap-novas-maquinas.md) — passo de cópia de segredos no bootstrap

---

## 1. Inventário de segredos

### Backend (Laravel)

| Segredo | Ficheiro | Uso |
|---------|----------|-----|
| `APP_KEY` | `.env` | Encriptação Laravel |
| `DB_PASSWORD` | `.env` | Base de dados |
| `OPENAI_API_KEY` | `.env` | GPT-4o-mini |
| `REVERB_APP_KEY/SECRET` | `.env` | WebSocket auth |
| `MAIL_PASSWORD` | `.env` | SMTP (Brevo) |
| `VAPID_PUBLIC/PRIVATE_KEY` | `.env` | Web Push |
| `AWS_ACCESS_KEY_ID/SECRET` | `.env` | S3 (se usado) |

### Android

| Segredo | Ficheiro | Uso |
|---------|----------|-----|
| Keystore password | `keystore.properties` | Signing da app |
| Keystore file | `release.jks` | Signing da app |
| Firebase config | `google-services.json` | FCM, Crashlytics |
| API base URL | `local.properties` ou BuildConfig | Endpoint do backend |
| Reverb app key | BuildConfig | WebSocket client |

---

## 2. Estratégia de gestão por tipo

### Segredos do backend (.env)

**Estratégia:** `.env.example` no repo + `.env` real fora do repo.

```bash
# .env.example (versionado) — Template sem valores reais
APP_KEY=
DB_PASSWORD=
OPENAI_API_KEY=
REVERB_APP_KEY=
REVERB_APP_SECRET=
```

**Sincronização entre máquinas:**
- Opção A: Copiar manualmente via SSH/SCP entre máquinas
- Opção B: Usar gestor de passwords (1Password, Bitwarden) — secção partilhada "Lumina Dev"
- Opção C: Ficheiro encriptado no repo (git-crypt ou SOPS)

**Recomendação:** Opção B (gestor de passwords). Simples, seguro, acessível de qualquer máquina.

### Keystore da app Android

**O keystore é o ficheiro mais crítico.** Se perdes o keystore, não podes atualizar a app na Play Store.

**Estratégia:**
1. Gerar keystore uma vez (comando completo abaixo)
2. **Nunca** colocar no Git
3. Guardar em 3 locais:
   - Gestor de passwords (1Password/Bitwarden) como ficheiro anexo
   - Backup encriptado numa cloud segura (Google Drive encriptado, etc.)
   - Cópia local em cada máquina de desenvolvimento

**Gerar o keystore:**
```bash
keytool -genkey -v \
  -keystore lumina-release.jks \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000 \
  -alias lumina-release \
  -storepass <PASSWORD_SEGURA> \
  -keypass <PASSWORD_SEGURA> \
  -dname "CN=Lumina, OU=Mobile, O=Lumina, L=Lisboa, ST=Lisboa, C=PT"
```

**Nota:** Usar a mesma password para store e key (simplifica configuração). Guardar a password
no gestor de passwords imediatamente após gerar.

**keystore.properties (não versionado):**
```properties
storePassword=senha-segura
keyPassword=senha-segura
keyAlias=lumina-release
storeFile=../keystores/lumina-release.jks
```

**keystore.properties.example (versionado):**
```properties
storePassword=
keyPassword=
keyAlias=lumina-release
storeFile=../keystores/lumina-release.jks
```

### google-services.json (Firebase)

**Estratégia:**
- Não versionar (contém API keys e project IDs)
- Guardar no gestor de passwords
- Opção: versionar apenas para debug flavor (risco baixo)

**No build.gradle.kts:**
```kotlin
// Validar que existe antes de buildar
tasks.register("checkGoogleServicesJson") {
    doLast {
        val file = file("app/google-services.json")
        if (!file.exists()) {
            throw GradleException(
                "google-services.json não encontrado. " +
                "Copia-o a partir do gestor de passwords."
            )
        }
    }
}
```

---

## 3. BuildConfig para configuração por ambiente

### Flavors de build

```kotlin
// app/build.gradle.kts
android {
    buildTypes {
        debug {
            buildConfigField("String", "API_BASE_URL", "\"http://10.0.2.2:8000/api/v1/\"")
            buildConfigField("String", "REVERB_HOST", "\"10.0.2.2\"")
            buildConfigField("Int", "REVERB_PORT", "6001")
            buildConfigField("String", "REVERB_APP_KEY", "\"dev-key\"")
        }
        release {
            buildConfigField("String", "API_BASE_URL", "\"https://api.lumina.pt/api/v1/\"")
            buildConfigField("String", "REVERB_HOST", "\"ws.lumina.pt\"")
            buildConfigField("Int", "REVERB_PORT", "443")
            buildConfigField("String", "REVERB_APP_KEY", "\"prod-key\"")
        }
    }
}
```

**Acesso no código:**
```kotlin
val baseUrl = BuildConfig.API_BASE_URL
```

### local.properties para overrides

```properties
# local.properties (não versionado, específico da máquina)
sdk.dir=/Users/nome/Library/Android/sdk
api.base.url=http://192.168.1.100:8000/api/v1/  # Override para device físico
```

---

## 4. Checklist de segredos por máquina

Ao configurar uma nova máquina, verificar que existe:

```
[ ] .env (backend) — Copiar do gestor de passwords
[ ] lumina-release.jks — Copiar do gestor de passwords
[ ] keystore.properties — Criar com passwords do gestor
[ ] google-services.json — Copiar do gestor de passwords
[ ] SSH key — Gerar nova e adicionar ao GitHub
[ ] local.properties — Gerado pelo Android Studio
```

---

## 5. Rotação de segredos

| Segredo | Frequência | Processo |
|---------|-----------|---------|
| APP_KEY (Laravel) | Nunca (a não ser que comprometido) | `php artisan key:generate` |
| DB_PASSWORD | Anual ou se comprometido | Atualizar no hosting + .env |
| OPENAI_API_KEY | Se comprometido | Regenerar no dashboard OpenAI |
| Keystore password | Nunca mudar (imutável para updates Play Store) | — |
| Firebase config | Raramente | Regenerar no Firebase Console |
| SSH keys | Anual ou se comprometido | Regenerar e atualizar GitHub |

---

## 6. CI/CD secrets

Para GitHub Actions, os segredos são armazenados como GitHub Secrets:

```
Settings → Secrets and variables → Actions

KEYSTORE_BASE64        ← Keystore encriptado em base64
KEYSTORE_PASSWORD      ← Password do keystore
KEY_ALIAS              ← Alias da chave
KEY_PASSWORD           ← Password da chave
GOOGLE_SERVICES_JSON   ← Conteúdo do google-services.json
```

**No workflow:**
```yaml
- name: Decode keystore
  run: echo "${{ secrets.KEYSTORE_BASE64 }}" | base64 -d > lumina-android/app/release.jks

- name: Create google-services.json
  run: echo '${{ secrets.GOOGLE_SERVICES_JSON }}' > lumina-android/app/google-services.json
```

---

## 7. Dev / Staging / Prod separation

### Ambientes

| Ambiente | Backend URL | Keystore | Firebase project | Secret source |
|----------|-----------|---------|-----------------|--------------|
| **Dev** | `http://10.0.2.2:8000/api/v1/` (emulador) ou `http://192.168.x.x:8000/api/v1/` (device) | Debug keystore (gerado pelo Android Studio) | `lumina-dev` (opcional, pode usar prod inicialmente) | `.env` local |
| **Staging** | `https://staging.lumina.pt/api/v1/` | Release keystore (lumina-release.jks) | `lumina-staging` (opcional) | GitHub Secrets / Gestor passwords |
| **Prod** | `https://api.lumina.pt/api/v1/` | Release keystore (lumina-release.jks) | `lumina-prod` | GitHub Secrets / Gestor passwords |

### BuildConfig por ambiente

```kotlin
// app/build.gradle.kts
android {
    buildTypes {
        debug {
            applicationIdSuffix = ".debug"
            buildConfigField("String", "API_BASE_URL", "\"http://10.0.2.2:8000/api/v1/\"")
            buildConfigField("String", "REVERB_HOST", "\"10.0.2.2\"")
            buildConfigField("Int", "REVERB_PORT", "6001")
            buildConfigField("Boolean", "ENABLE_LOGGING", "true")
        }
        release {
            buildConfigField("String", "API_BASE_URL", "\"https://api.lumina.pt/api/v1/\"")
            buildConfigField("String", "REVERB_HOST", "\"ws.lumina.pt\"")
            buildConfigField("Int", "REVERB_PORT", "443")
            buildConfigField("Boolean", "ENABLE_LOGGING", "false")
        }
    }
}
```

**`applicationIdSuffix = ".debug"`** permite instalar debug e release side-by-side no mesmo device.

### Alternar entre ambientes

- **Dev:** `./gradlew assembleDebug` — usa debug keystore, localhost
- **Release:** `./gradlew assembleRelease` — usa release keystore, prod URL
- **Staging (futuro):** Adicionar `staging` build type quando necessário

Para apontar debug a um backend remoto (ex: staging), override via `local.properties`:
```properties
api.base.url=https://staging.lumina.pt/api/v1/
```

---

## 8. Compromise response plan

### Cenário 1: .env leaked (backend secrets)

**Impacto:** Acesso a DB, OpenAI, SMTP, WebSocket.

**Resposta:**
1. Revogar imediatamente: `APP_KEY`, `DB_PASSWORD`, `OPENAI_API_KEY`, `MAIL_PASSWORD`, `REVERB_APP_SECRET`
2. Regenerar no respetivo serviço (Railway, OpenAI, Brevo, etc.)
3. Atualizar `.env` em todas as máquinas e ambientes
4. `php artisan key:generate` (invalida sessions existentes)
5. Verificar logs de acesso no Railway para atividade suspeita
6. Se DB comprometida: avaliar necessidade de notificar utilizadores (GDPR Art. 34)

### Cenário 2: Keystore leaked

**Impacto:** Alguém pode assinar APKs como se fosse a Lumina oficial.

**Resposta:**
1. **Se publicado na Play Store com Google Play App Signing (recomendado):**
   - O upload key pode ser revogado e substituído via Google Play Console
   - A app signing key nunca sai dos servidores Google
   - Impacto limitado: atacante não consegue publicar sem acesso à Play Console
2. **Se NÃO usar Google Play App Signing:**
   - A app não pode ser atualizada com um novo keystore
   - Seria necessário republicar com novo package name (perda de installs)
3. Gerar novo keystore e atualizar em todas as máquinas e CI/CD
4. Revogar passwords do keystore comprometido

**Prevenção:** Ativar Google Play App Signing desde o primeiro upload.

### Cenário 3: Firebase config leaked (google-services.json)

**Impacto:** API key e project ID expostos. Risco baixo se restrições de API key estiverem configuradas.

**Resposta:**
1. Ir ao Firebase Console → Project Settings → verificar restrições
2. Adicionar restrição por SHA-1 fingerprint (só aceitar requests do nosso APK)
3. Adicionar restrição por package name (`pt.lumina.app`)
4. Se necessário: regenerar API key no Google Cloud Console
5. Atualizar `google-services.json` em todas as máquinas

**Prevenção:** Configurar restrições de API key no dia 1.

### Cenário 4: SSH key comprometida

**Impacto:** Acesso ao repositório GitHub.

**Resposta:**
1. Revogar a SSH key no GitHub (Settings → SSH Keys → Delete)
2. Gerar nova SSH key na máquina afetada
3. Adicionar a nova key ao GitHub
4. Verificar logs de acesso do repositório no GitHub (Settings → Audit log)
5. Se houve push malicioso: `git reflog` + `git reset` para reverter

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-21-01 | Keystore perdido = impossível atualizar app na Play Store | Baixa | Crítico | Guardar em 3 locais (gestor passwords, cloud encriptada, cópia local). Ativar Google Play App Signing desde o primeiro upload |
| RISK-21-02 | Secret acidentalmente committed ao Git | Média | Alto | `.gitignore` robusto (secção 5 do doc 20). Pre-commit hook que verifica padrões de secrets. GitHub secret scanning ativo |
| RISK-21-03 | .env diverge entre máquinas (chave diferente, URL errada) | Média | Médio | `.env.example` como referência. Checklist de bootstrap (doc 22). `scripts/check-env.sh` valida presença |
| RISK-21-04 | Firebase API key exposta no APK decompilado | Alta | Baixo | API keys do Firebase são designed para ser públicas se restrições estiverem configuradas. Restringir por SHA-1 + package name. Não colocar secrets reais no APK |

---

*Próximo: [22-bootstrap-novas-maquinas.md](22-bootstrap-novas-maquinas.md) — Bootstrap e documentação para novas máquinas.*
