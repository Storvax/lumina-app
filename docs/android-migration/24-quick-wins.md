# 24 — Quick Wins

## Contexto

Quick wins são tarefas de alto impacto com esforço relativamente baixo que geram momentum,
validam decisões técnicas, e criam resultados visíveis rapidamente.

Refs:
- [23-roadmap-fases.md](23-roadmap-fases.md) — roadmap completo (QWs mapeiam para Fases 0, 1A, 1B)
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — riscos que os QWs validam cedo
- [11-backend-gaps.md](11-backend-gaps.md) — gaps do backend que QW-01/02/03 resolvem
- [07-stack-android.md](07-stack-android.md) — versões de libraries usadas nos QWs Android
- [08-arquitetura-android.md](08-arquitetura-android.md) — arquitetura que QW-04 scaffold

---

## Quick wins do backend (Fase 0)

### QW-01: Instalar Sanctum e criar login endpoint
**Esforço:** 1-2 horas
**Impacto:** Desbloqueia toda a autenticação mobile

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Criar `POST /api/v1/auth/login` que retorna token. Resultado: app Android pode autenticar.

### QW-02: Criar routes/api.php com 3 endpoints base
**Esforço:** 2-3 horas
**Impacto:** Prova que a API funciona end-to-end

Começar com:
- `POST /api/v1/auth/login`
- `GET /api/v1/profile`
- `GET /api/v1/dashboard`

Testar com Postman/Bruno. Resultado: comunicação client→server validada.

### QW-03: Criar error handler JSON para API
**Esforço:** 1 hora
**Impacto:** Todos os erros da API ficam padronizados imediatamente

Adicionar ao `bootstrap/app.php` o handler que converte exceptions em JSON quando `request->is('api/*')`.

---

## Quick wins do Android (Fase 1A)

### QW-04: Scaffold do projeto Gradle multi-module
**Esforço:** 2-3 horas
**Impacto:** Estrutura base pronta para todo o desenvolvimento futuro

Criar:
- `lumina-android/` com app module
- `core-ui/` com LuminaTheme (cores + tipografia)
- `core-network/` com Retrofit client básico
- Version Catalog

Resultado: projeto compila e mostra tela em branco com tema Lumina.

### QW-05: Tela de login funcional
**Esforço:** 3-4 horas
**Impacto:** Primeira funcionalidade end-to-end (Android → API → resposta)

- Login screen com Compose
- POST para API de login
- Guardar token em EncryptedSharedPreferences
- Navegar para dashboard skeleton

Resultado: prova de vida da app — o utilizador consegue fazer login.

### QW-06: Design system Lumina (componentes base)
**Esforço:** 3-4 horas
**Impacto:** Todos os ecrãs subsequentes usam componentes consistentes

Criar:
- LuminaTheme (cores light/dark)
- LuminaButton (primary, secondary)
- LuminaTextField (com estados de erro acolhedores)
- LuminaCard
- LuminaTopBar

Resultado: base visual consistente para todo o desenvolvimento.

---

## Quick wins do Android (Fase 1B)

### QW-07: Diário com mood selector
**Esforço:** 4-5 horas
**Impacto:** Funcionalidade mais pessoal e frequente — demonstra valor nativo

- Slider de mood (1-5) com haptic
- Chips de tags emocionais
- TextField para nota
- POST para API
- Exibir CBT insight da resposta

Resultado: primeira funcionalidade completa com valor terapêutico real.

### QW-08: Zona Calma — respiração guiada offline
**Esforço:** 3-4 horas
**Impacto:** Funciona 100% offline, demonstra vantagem nativa (vibração)

- Animação circular de respiração (Compose Canvas)
- Timer: 4s inspirar → 4s segurar → 4s expirar
- Vibração rítmica sincronizada
- Zero dependência de rede

Resultado: exercício terapêutico que funciona sem internet e com haptic feedback.

### QW-09: Safe House (double-tap exit)
**Esforço:** 1-2 horas
**Impacto:** Funcionalidade de segurança crítica, demonstra sensibilidade ao produto

- Detectar double-tap no botão de saída
- `finishAndRemoveTask()` + cancelar notificações
- Opcionalmente abrir browser com Google

Resultado: funcionalidade de segurança emocional que demonstra que a app foi pensada para contextos vulneráveis.

### QW-10: Pull-to-refresh no dashboard
**Esforço:** 30 minutos
**Impacto:** Sensação nativa imediata

- `SwipeRefresh` wrapper no dashboard
- Trigger API refresh

Resultado: interação nativa que os utilizadores esperam.

---

## Ordem recomendada de execução

```
Backend:        QW-01 → QW-02 → QW-03
Android setup:  QW-04 → QW-06 → QW-05
Android core:   QW-07 → QW-08 → QW-09 → QW-10
```

**Resultado após completar todos os quick wins:**
- App Android que autentica contra o backend
- Design system Lumina aplicado
- Diário funcional com mood tracking
- Respiração guiada offline com vibração
- Safe House para saída de emergência
- Error handling consistente

---

## Testing strategy por quick win

| QW | Testes | Como validar |
|----|--------|-------------|
| **QW-01** | PHPUnit: `actingAs($user)->postJson('/api/v1/auth/login', [...])` | Token retornado, 200 OK. Credenciais erradas → 401 JSON |
| **QW-02** | PHPUnit: `getJson('/api/v1/profile')` com Sanctum token header | Profile retorna JSON correto. Sem token → 401 |
| **QW-03** | PHPUnit: forçar exceptions e verificar formato JSON | 404 → `{"message": "..."}`. 422 → `{"message": "...", "errors": {...}}`. 500 → `{"message": "Erro interno"}` |
| **QW-04** | `./gradlew assembleDebug` + `./gradlew testDebugUnitTest` | Build sem erros. App abre no emulador (tela em branco com tema Lumina) |
| **QW-05** | Compose UI test: login flow end-to-end | Campo email → campo password → botão login → navegação para dashboard skeleton. Erro → mensagem PT-PT empática |
| **QW-06** | Compose Preview + Screenshot tests | Cada componente renderiza corretamente em light e dark. Touch targets ≥ 44dp |
| **QW-07** | Unit test: DiaryViewModel. UI test: mood selector flow | Slider retorna valor 1-5. Tags selecionáveis. POST envia dados corretos. CBT insight exibido |
| **QW-08** | Manual: cronometrar ciclo 4-4-4. Verificar vibração | Animação fluida 60fps. Vibração sincronizada com fases. Funciona em airplane mode |
| **QW-09** | Manual: double-tap → app fecha. `adb shell dumpsys activity` confirma task removed | App desaparece do recents. Nenhuma notificação visível |
| **QW-10** | Manual: pull-to-refresh atualiza dados | Indicador de loading aparece. Dados refreshed do backend |

---

## Validation criteria ("Done when")

| QW | Done when |
|----|-----------|
| **QW-01** | `POST /api/v1/auth/login` retorna `{"token": "...", "user": {...}}` com status 200. Credenciais inválidas retornam 401 JSON padronizado |
| **QW-02** | 3 endpoints respondem JSON correto com Sanctum token. Sem token retorna 401. Postman/Bruno collection funciona |
| **QW-03** | Todos os erros HTTP (401, 403, 404, 422, 500) retornam JSON com `message` field. Nenhum erro retorna HTML |
| **QW-04** | `./gradlew assembleDebug` compila sem erros. App abre no emulador. Multi-module structure visible no Android Studio |
| **QW-05** | Utilizador consegue: abrir app → ver login → inserir credenciais → fazer login → ver dashboard skeleton. Token persiste após app restart |
| **QW-06** | 5 componentes base (Button, TextField, Card, TopBar, theme) criados. Compose Preview funciona. Dark mode variant existe |
| **QW-07** | Utilizador cria entrada de diário: mood (1-5) + tags + nota. POST enviado para API. CBT insight da resposta exibido. Entrada aparece no histórico |
| **QW-08** | Exercício de respiração funciona 100% offline. Ciclo 4-4-4 com animação + vibração. TalkBack anuncia fases ("Inspirar", "Segurar", "Expirar") |
| **QW-09** | Double-tap no botão Safe House fecha a app completamente. Task removida do recents. Notificações canceladas |
| **QW-10** | Pull-to-refresh no dashboard mostra indicador de loading e recarrega dados do backend |

---

## Dependency matrix

```
QW-01 (Sanctum + login endpoint)
  ├── QW-02 (3 endpoints base) ← precisa de auth funcional
  │     └── QW-05 (tela de login) ← precisa de endpoints para consumir
  │           ├── QW-07 (diário) ← precisa de login + API
  │           └── QW-10 (pull-to-refresh) ← precisa de dados do dashboard
  └── QW-03 (error handler) ← pode ser feito em paralelo com QW-02

QW-04 (scaffold Gradle)
  ├── QW-06 (design system) ← precisa do projeto para existir
  │     └── QW-05 (tela de login) ← precisa de componentes UI
  │           └── QW-07 (diário) ← precisa de login + componentes
  ├── QW-08 (respiração offline) ← precisa do projeto, independente de API
  └── QW-09 (Safe House) ← precisa do projeto, independente de API
```

### Paralelismo possível

| Backend (Fase 0) | Android (Fase 1A/1B) | Notas |
|----------|---------|-------|
| QW-01 + QW-03 | QW-04 | Podem correr em paralelo — backend e Android scaffold são independentes |
| QW-02 | QW-06 + QW-08 + QW-09 | Design system e exercícios offline não precisam de API |
| — | QW-05 | Precisa de QW-01 + QW-02 completos |
| — | QW-07 + QW-10 | Precisa de QW-05 completo |

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-24-01 | Instalar Sanctum conflita com auth middleware existente (web sessions) | Média | Alto | Sanctum suporta ambos (SPA cookies + API tokens). Não alterar auth web. Usar guard `sanctum` apenas em `routes/api.php`. Testar web login após instalar |
| RISK-24-02 | Login UX não suficientemente emotionally safe (mensagens de erro frias) | Alta | Médio | Seguir guidelines do CLAUDE.md: strings PT-PT empáticas. Erro de login: "As credenciais não correspondem. Sem problema, tenta novamente." Em vez de: "Credenciais inválidas" |
| RISK-24-03 | Animação de respiração com jank em devices low-end (Redmi Note 12 etc.) | Média | Médio | Usar `Canvas` Compose com `animateFloatAsState`. Evitar recompositions desnecessárias. Testar no emulador com CPU throttle 2x |

---

*Próximo: [25-riscos-decisoes.md](25-riscos-decisoes.md) — Riscos, dependências e decisões em aberto.*
