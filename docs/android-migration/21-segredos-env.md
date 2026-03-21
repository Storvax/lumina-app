# 21 — Segredos, Variáveis de Ambiente e Ficheiros Sensíveis

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
1. Gerar keystore uma vez: `keytool -genkey -v -keystore lumina-release.jks ...`
2. **Nunca** colocar no Git
3. Guardar em 3 locais:
   - Gestor de passwords (1Password/Bitwarden) como ficheiro anexo
   - Backup encriptado numa cloud segura (Google Drive encriptado, etc.)
   - Cópia local em cada máquina de desenvolvimento

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

*Próximo: [22-bootstrap-novas-maquinas.md](22-bootstrap-novas-maquinas.md) — Bootstrap e documentação para novas máquinas.*
