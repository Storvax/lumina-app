# 24 — Quick Wins

## Contexto

Quick wins são tarefas de alto impacto com esforço relativamente baixo que geram momentum, validam decisões técnicas, e criam resultados visíveis rapidamente.

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

*Próximo: [25-riscos-decisoes.md](25-riscos-decisoes.md) — Riscos, dependências e decisões em aberto.*
