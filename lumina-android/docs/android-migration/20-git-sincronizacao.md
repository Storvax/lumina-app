# 20 — Git Workflow e Sincronização entre Máquinas

## Contexto

Este documento define o workflow Git completo para o desenvolvimento da Lumina em múltiplos PCs,
incluindo branch strategy, convenções de commit, sincronização, e processos de merge e review.

Refs:
- [18-setup-ambiente.md](18-setup-ambiente.md) — configuração do ambiente de desenvolvimento
- [21-segredos-env.md](21-segredos-env.md) — ficheiros que devem estar no .gitignore
- [22-bootstrap-novas-maquinas.md](22-bootstrap-novas-maquinas.md) — clone e setup inicial
- [23-roadmap-fases.md](23-roadmap-fases.md) — branches mapeiam para fases do roadmap
- CLAUDE.md — convenções de commit (secção 5)

---

## Observações do estado atual

1. **`.gitignore` não inclui entradas Android** — o projeto Android ainda não existe, mas quando for criado
   o .gitignore deve ser atualizado imediatamente (secção 5 deste doc)
2. **Sem `.gitattributes`** — não há normalização de line endings para ficheiros específicos. Risco de
   phantom diffs CRLF/LF entre Windows e macOS/Linux
3. **Sem PR template** em `.github/` — PRs não têm estrutura padronizada
4. **Sem branch protection rules** no GitHub — `main` e `develop` podem receber push direto

---

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

## 9. Merge strategy

### Feature → develop: Squash merge

```bash
# No GitHub PR: "Squash and merge"
# Resultado: 1 commit limpo no develop por feature
```

**Porquê:** Feature branches acumulam WIP commits, fixups, e experimentação. Squash merge mantém
o histórico do `develop` limpo e legível. Cada commit no develop corresponde a uma feature completa.

### Develop → main: Merge commit

```bash
# No GitHub PR: "Create a merge commit"
# Resultado: merge commit que preserva a boundary de release
```

**Porquê:** O merge commit no `main` funciona como marcador de release. É fácil identificar
quando cada conjunto de features foi promovido para produção. `git log --first-parent main`
mostra apenas os pontos de release.

### Resumo

| Merge | Estratégia | Razão |
|-------|-----------|-------|
| `feature/*` → `develop` | Squash merge | História limpa, 1 commit por feature |
| `fix/*` → `develop` | Squash merge | Idem |
| `develop` → `main` | Merge commit | Preserva boundary de release |
| Hotfix → `main` + `develop` | Cherry-pick | Fix urgente em ambos os branches |

---

## 10. PR Template

Criar `.github/PULL_REQUEST_TEMPLATE.md`:

```markdown
## Resumo

<!-- Descreve o que este PR faz e porquê -->

## Tipo de mudança

- [ ] Nova funcionalidade (feature)
- [ ] Bugfix
- [ ] Refactoring (sem mudança de comportamento)
- [ ] Documentação
- [ ] Configuração / CI/CD

## Scope

- [ ] Backend (Laravel)
- [ ] Android (lumina-android/)
- [ ] Documentação (docs/)
- [ ] Infraestrutura

## Checklist

- [ ] Código segue as convenções do CLAUDE.md
- [ ] Strings da UI em PT-PT (empático, sem jargão técnico)
- [ ] Touch targets ≥ 44dp (≥ 56dp em contexto de crise)
- [ ] Funciona offline (se aplicável à feature)
- [ ] Error handling com mensagens gentis
- [ ] TalkBack navegável (se UI nova)
- [ ] Testes adicionados/atualizados
- [ ] Sem segredos hardcoded

## Screenshots / Gravações

<!-- Se houver mudanças visuais, anexar -->

## Notas para review

<!-- Algo que o reviewer deve saber? Decisões de trade-off? -->
```

---

## 11. .gitattributes

Criar `.gitattributes` na raiz do repo para normalizar line endings e tratar binários:

```gitattributes
# Normalização de line endings
* text=auto eol=lf

# Forçar LF em ficheiros de código
*.kt text eol=lf
*.kts text eol=lf
*.java text eol=lf
*.xml text eol=lf
*.php text eol=lf
*.blade.php text eol=lf
*.js text eol=lf
*.json text eol=lf
*.md text eol=lf
*.yml text eol=lf
*.yaml text eol=lf
*.toml text eol=lf
*.properties text eol=lf
*.sh text eol=lf

# Windows batch files mantêm CRLF
*.bat text eol=crlf
*.cmd text eol=crlf

# Binários — não fazer merge, não converter
*.png binary
*.jpg binary
*.jpeg binary
*.gif binary
*.ico binary
*.jks binary
*.keystore binary
*.jar binary
*.ttf binary
*.otf binary
*.woff binary
*.woff2 binary
```

**Porquê:** Sem `.gitattributes`, Windows (CRLF) e macOS/Linux (LF) produzem phantom diffs
em ficheiros não-binários. Isto é especialmente problemático ao trabalhar em múltiplos PCs
com sistemas operativos diferentes.

---

## 12. Code review guidelines (solo developer)

Como developer solo, o code review é self-review antes de aprovar o merge. Usar esta checklist:

### Self-review checklist

**Funcionalidade:**
- [ ] A feature funciona como descrito no PR?
- [ ] Todos os edge cases foram considerados (offline, erro de rede, campo vazio)?
- [ ] Não há regressões nas features existentes?

**Código:**
- [ ] Código em inglês (variáveis, funções, classes)?
- [ ] Strings da UI em PT-PT, tom empático?
- [ ] Sem TODO esquecidos ou código comentado?
- [ ] Sem segredos hardcoded (API keys, URLs de produção em debug)?

**Android-specific:**
- [ ] Touch targets ≥ 44dp (≥ 56dp em contexto de crise)?
- [ ] TalkBack: todos os elementos interativos têm contentDescription?
- [ ] Offline: funcionalidade degrada gracefully sem rede?
- [ ] Memory: sem leaks em ViewModels, coroutines cancelam corretamente?
- [ ] Performance: listas usam LazyColumn, imagens usam Coil com cache?

**Segurança e privacidade:**
- [ ] Dados sensíveis (humor, diário) encriptados em Room (SQLCipher)?
- [ ] Tokens guardados em EncryptedSharedPreferences?
- [ ] Sem logging de dados pessoais em produção?

**Processo:**
- [ ] Commit messages seguem Conventional Commits?
- [ ] PR tem descrição clara do que foi feito e porquê?
- [ ] CI passa (build + lint + tests)?

### Quando fazer self-review

1. **Antes de criar o PR** — Ler o diff completo no GitHub
2. **Esperar 10 minutos** — Olhos frescos detetam mais problemas
3. **Ler o diff como se fosse de outra pessoa** — Ser crítico
4. **Se o PR tem >400 linhas** — Considerar partir em PRs mais pequenos

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-20-01 | Force push em branch partilhado perde trabalho da outra máquina | Baixa | Alto | Nunca force push em `develop` ou `main`. Usar `--force-with-lease` se necessário em feature branches. Ativar branch protection rules |
| RISK-20-02 | CRLF/LF phantom diffs entre Windows e macOS/Linux | Média | Baixo | Criar `.gitattributes` com `* text=auto eol=lf`. Configurar `git config core.autocrlf input` em cada máquina |
| RISK-20-03 | Binários grandes (APKs, assets de som) incham o repositório | Baixa | Médio | Nunca commitar APKs gerados. Manter `app/release/` no .gitignore. Assets de som descarregados on-demand (não bundled). Considerar Git LFS se necessário |
| RISK-20-04 | Trabalho uncommitted perdido ao mudar de máquina | Média | Alto | Regra de ouro: sempre commit + push antes de fechar o laptop. WIP commits são aceitáveis e serão squashed no merge. Usar `git stash` apenas como último recurso |

---

*Próximo: [21-segredos-env.md](21-segredos-env.md) — Gestão de segredos, variáveis de ambiente e ficheiros sensíveis.*
