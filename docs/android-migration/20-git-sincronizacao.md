# 20 — Git Workflow e Sincronização entre Máquinas

## 1. Estratégia de repositório: Monorepo

### Decisão: Monorepo com separação por diretórios

```
lumina-app/                    ← Repositório existente
├── app/                       ← Backend Laravel (existente)
├── resources/                 ← Frontend web (existente)
├── routes/                    ← Rotas web + API (existente + novo)
├── config/                    ← Config Laravel (existente)
├── database/                  ← Migrations (existente)
├── docs/                      ← Documentação (este plano)
│   └── android-migration/
├── lumina-android/            ← NOVO: Projeto Android
│   ├── app/
│   ├── core/
│   ├── feature/
│   ├── gradle/
│   ├── build.gradle.kts
│   └── settings.gradle.kts
├── .gitignore                 ← Atualizar com exclusões Android
├── composer.json
├── package.json
└── ...
```

### Justificação do monorepo

| Aspeto | Monorepo | Multi-repo |
|--------|---------|-----------|
| Coordenação backend↔mobile | ✅ Fácil (mesmo commit/PR) | ❌ Cross-repo PRs |
| Versionamento API | ✅ Backend + mobile no mesmo commit | ❌ Coordenação manual |
| CI/CD | ✅ Paths filter por diretório | Pipelines separados |
| Git history | ✅ Contexto completo | Fragmentado |
| Clone size | ⚠️ Maior | Cada repo menor |
| Permissões | ⚠️ Tudo junto | Granular |

**Para a escala da Lumina, monorepo é a melhor opção.** A equipa é pequena e a coordenação backend↔mobile é frequente.

---

## 2. Branch strategy

### Branches principais

| Branch | Propósito | Protegida |
|--------|-----------|-----------|
| `main` | Produção (web deploy) | ✅ |
| `develop` | Integração contínua | ✅ |

### Branches de feature

```
feature/android-scaffold          ← Setup inicial do projeto Android
feature/android-auth              ← Autenticação (login, registo, tokens)
feature/android-onboarding        ← Wizard de onboarding
feature/android-dashboard         ← Dashboard
feature/android-diary             ← Diário emocional
feature/android-calm-zone         ← Zona Calma
feature/android-profile           ← Perfil
feature/api-v1-auth               ← API endpoints de auth
feature/api-v1-diary              ← API endpoints do diário
feature/api-v1-dashboard          ← API endpoint agregado
```

### Convenção de nomes

```
feature/android-{feature}     ← Funcionalidade Android
feature/api-v1-{feature}      ← Endpoints API no backend
fix/android-{description}     ← Bugfix Android
fix/api-{description}         ← Bugfix API
docs/{description}            ← Documentação
```

### Workflow

```
1. Criar branch a partir de develop:
   git checkout develop
   git pull origin develop
   git checkout -b feature/android-diary

2. Desenvolver (commits pequenos e frequentes)

3. Push:
   git push -u origin feature/android-diary

4. Pull Request para develop:
   - Título claro
   - Descrição do que foi feito
   - Checklist de testing

5. Merge para develop (squash ou merge commit)

6. Periodicamente: develop → main (release)
```

---

## 3. Convenções de commits

### Formato

```
type(scope): description

[optional body]
```

### Tipos

| Tipo | Uso |
|------|-----|
| `feat` | Nova funcionalidade |
| `fix` | Bugfix |
| `docs` | Documentação |
| `refactor` | Refactoring sem mudança de comportamento |
| `test` | Testes |
| `chore` | Build, deps, config |
| `style` | Formatação, linting |

### Scopes

| Scope | Diretório |
|-------|----------|
| `android` | lumina-android/ |
| `api` | routes/api.php, app/Http/Controllers/Api/ |
| `backend` | Alterações no backend Laravel |
| `docs` | docs/ |

### Exemplos

```
feat(android): add diary screen with mood selector and auto-save
feat(api): add POST /api/v1/diary endpoint with CBT analysis
fix(android): fix crash on rotation during breathing exercise
docs: update migration plan with offline sync strategy
chore(android): update Compose BOM to 2025.02
test(android): add unit tests for DiaryViewModel
```

---

## 4. Sincronização entre máquinas

### Workflow diário

```
Início do trabalho (qualquer máquina):
  git fetch origin
  git checkout develop
  git pull origin develop
  git checkout <branch-atual>
  git rebase develop    # ou merge, conforme preferência

Fim do trabalho:
  git add .
  git commit -m "feat(android): work in progress on diary"
  git push origin <branch>
```

### Regra de ouro

**Nunca deixar trabalho uncommitted ao mudar de máquina.**

Se o trabalho não está pronto para commit:
```bash
git stash push -m "WIP: diary auto-save"
git push  # Push do branch atual (sem o stash)
```

Mas **preferencialmente**, fazer commits WIP:
```bash
git add .
git commit -m "wip(android): diary auto-save (in progress)"
git push
```

WIP commits serão squashed no merge do PR.

### Resolver conflitos entre máquinas

Se ambas as máquinas tocaram no mesmo ficheiro:
```bash
git pull origin <branch>
# Se conflito:
# 1. Abrir ficheiro com conflito
# 2. Resolver manualmente
# 3. git add <ficheiro>
# 4. git commit
```

---

## 5. .gitignore — Atualizações necessárias

Adicionar ao `.gitignore` existente:

```gitignore
# === Android ===
lumina-android/.gradle/
lumina-android/build/
lumina-android/*/build/
lumina-android/local.properties
lumina-android/.idea/
lumina-android/*.iml
lumina-android/captures/
lumina-android/.externalNativeBuild/
lumina-android/.cxx/

# Signing
lumina-android/*.jks
lumina-android/*.keystore
lumina-android/keystore.properties

# Generated
lumina-android/app/release/

# === IDE ===
.idea/
*.iml

# === OS ===
.DS_Store
Thumbs.db
```

---

## 6. Ficheiros que NÃO devem entrar no repositório

| Ficheiro | Razão | Alternativa |
|----------|-------|-------------|
| `local.properties` | SDK paths específicos da máquina | Gerado pelo Android Studio |
| `*.jks` / `*.keystore` | Signing keys | Vault seguro (ver ficheiro 21) |
| `keystore.properties` | Passwords do keystore | Vault seguro |
| `google-services.json` | Firebase config | 1Password/Vault + template |
| `.env` (backend) | Secrets | `.env.example` como template |
| `lumina-android/.idea/` | IDE-specific | Cada máquina gera o seu |

---

## 7. Tags e releases

### Versionamento da app Android

```
v1.0.0-alpha.1    ← Primeira build testável
v1.0.0-beta.1     ← Beta para testers
v1.0.0-rc.1       ← Release candidate
v1.0.0            ← Release para Play Store
```

### Git tags

```bash
git tag -a v1.0.0-alpha.1 -m "First testable build: auth + diary + calm zone"
git push origin v1.0.0-alpha.1
```

---

## 8. Automação Git

### Pre-commit hooks (opcional)

```bash
# .git/hooks/pre-commit (ou via Husky)
#!/bin/sh

# Lint Kotlin (se alterações em lumina-android/)
if git diff --cached --name-only | grep -q "lumina-android/"; then
    cd lumina-android && ./gradlew detekt --daemon
fi

# Lint PHP (se alterações em app/)
if git diff --cached --name-only | grep -q "^app/"; then
    ./vendor/bin/pint --test
fi
```

---

*Próximo: [21-segredos-env.md](21-segredos-env.md) — Gestão de segredos, variáveis de ambiente e ficheiros sensíveis.*
