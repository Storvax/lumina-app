# 11 — Gaps e Alterações Necessárias no Backend para Suportar Mobile

## Contexto

O backend Laravel atual foi construído exclusivamente para servir páginas web. Para suportar
uma app Android nativa, é necessário resolver vários gaps estruturais. Este documento lista
cada gap, a solução proposta, e a prioridade.

Este documento é complementar a [10-backend-reutilizacao.md](10-backend-reutilizacao.md),
que identifica o que **pode** ser reutilizado — este identifica o que **não** pode ser
reutilizado diretamente e precisa de trabalho novo ou adaptação.

Referências cruzadas:
- [01-estado-atual.md](01-estado-atual.md) — baseline da arquitetura atual
- [04-fase-inicial.md](04-fase-inicial.md) — escopo da Fase 1 que consume estes gaps
- [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) — gaps de auth em profundidade
- [16-gamificacao-servidor.md](16-gamificacao-servidor.md) — gaps de gamificação em profundidade
- [23-roadmap-fases.md](23-roadmap-fases.md) — roadmap que prioriza a resolução dos gaps
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — riscos derivados destes gaps

---

## Observações do estado atual

Descobertas concretas da análise do código que fundamentam os gaps identificados:

1. **`routes/api.php` não existe.** Verificado — todo o routing está em `routes/web.php`.
   Nenhum endpoint retorna JSON puro por design (apenas respostas ad-hoc para AJAX).

2. **Apenas 2 FormRequests** existem: `LoginRequest` e `ProfileUpdateRequest` em
   `app/Http/Requests/`. Toda a outra validação é inline nos controllers com `$request->validate()`.

3. **`fcm_token` não existe** em nenhuma tabela ou migration. O campo proposto como singular
   na tabela `users` não suporta múltiplos dispositivos.

4. **8 rate limiters** em `AppServiceProvider` (ref. [10-backend-reutilizacao.md](10-backend-reutilizacao.md)
   secção 11) mas nenhum com prefixo `api`. Os limiters existentes são reutilizáveis mas falta
   um rate limiter global para a API.

5. **`DashboardController::index()`** agrega 15+ data points numa única view Blade:
   `dailyMissions`, `progressData`, `greeting`, `pendingMilestone`, `emotionalTags`,
   `encouragement`, `todayLog`, `emotionalDate`, `recommendations`, `communityStats`,
   `globalImpact`, `aiInsight`. Não existe equivalente API.

6. **`ForumController::index()`** já usa cursor pagination para AJAX web — este padrão
   é reutilizável para a API de fórum.

7. **`ProfileController`** tem 12+ operações (tags, milestones, safety plan, energy, breathing,
   notification prefs, accessibility, passport) — cada uma precisa de endpoint API dedicado.

---

## Gap 1: Ausência de camada API (CRÍTICO)

**Estado atual:** Não existe `routes/api.php`. Todas as rotas estão em `routes/web.php` e retornam views HTML ou redirects.

**Solução:**
1. Criar `routes/api.php` com prefixo `/api/v1/`
2. Registar no `bootstrap/app.php` com middleware `api`
3. Grupo de rotas com autenticação Sanctum

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // Public
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::apiResource('/diary', DiaryController::class)->only(['index', 'store']);
        // ...
    });
});
```

**Prioridade:** Fase 0 (pré-requisito para qualquer funcionalidade Android)

---

## Gap 2: Ausência de autenticação por tokens (CRÍTICO)

**Estado atual:** Autenticação session-based via Laravel Breeze. Sem Sanctum, sem tokens API.

**Solução:**
1. Instalar Laravel Sanctum: `composer require laravel/sanctum`
2. Publicar migration: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
3. Executar migration (cria tabela `personal_access_tokens`)
4. Adicionar trait `HasApiTokens` ao model `User`
5. Configurar guard `sanctum` no `config/auth.php`

**Endpoints de auth:**
- `POST /api/v1/auth/register` → Registo + token
- `POST /api/v1/auth/login` → Login + token
- `POST /api/v1/auth/logout` → Revoke token
- `POST /api/v1/auth/refresh` → Refresh token (se necessário)
- `POST /api/v1/auth/forgot-password` → Email de reset

**Token response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "...",
    "pseudonym": "...",
    "flames": 42,
    "flame_level": "flame",
    "onboarding_completed": true
  }
}
```

**Prioridade:** Fase 0

---

## Gap 3: Ausência de API Resources (ALTO)

**Estado atual:** Controllers retornam models diretamente ou arrays ad-hoc. Não existe `app/Http/Resources/`.

**Solução:** Criar API Resource classes para serialização padronizada.

**Resources necessários (Fase 1):**

| Resource | Model | Campos expostos |
|----------|-------|----------------|
| UserResource | User | id, name, pseudonym, flames, flame_level, current_streak, role, onboarding_completed_at, avatar, a11y preferences |
| DailyLogResource | DailyLog | id, mood_level, tags, note, cbt_insight, log_date, created_at |
| MissionResource | Mission | id, type, description, progress, completed_at |
| AchievementResource | Achievement | id, code, name, description, icon, unlocked_at |
| VaultItemResource | VaultItem | id, title, content, type, created_at |
| NotificationResource | Notification | id, type, data, read_at, created_at |
| DashboardResource | — | Composição de múltiplos dados (custom) |

**Resources necessários (Fase 2+):**

| Resource | Model |
|----------|-------|
| PostResource | Post |
| CommentResource | Comment |
| ReactionResource | PostReaction / MessageReaction |
| RoomResource | Room |
| MessageResource | Message |
| ResourceItemResource | Resource |
| AssessmentResource | SelfAssessment |
| BuddySessionResource | BuddySession |

**Prioridade:** Fase 0

---

## Gap 4: Ausência de Form Requests para API (ALTO)

**Estado atual:** Apenas 2 Form Requests existem (LoginRequest, ProfileUpdateRequest). A maioria da validação é inline nos controllers.

**Solução:** Criar Form Requests dedicados para endpoints API com mensagens de erro em PT-PT.

**Fase 1:**
- `RegisterRequest` — name, email, password, password_confirmation
- `LoginRequest` — email, password
- `OnboardingRequest` — intention, mood, preference
- `StoreDiaryRequest` — mood_level (1-5), tags (array), note (nullable)
- `UpdateProfileRequest` — name, email, bio, etc.
- `StoreVaultItemRequest` — title, content, type

**Prioridade:** Fase 0

---

## Gap 5: Formato de resposta inconsistente (ALTO)

**Estado atual:** Respostas JSON ad-hoc. Alguns endpoints retornam `{'success': true}`, outros retornam o model diretamente, outros retornam arrays customizados.

**Solução:** Padrão de resposta API consistente.

**Sucesso:**
```json
{
  "data": { ... },
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "next_cursor": "abc123"
  }
}
```

**Erro:**
```json
{
  "error": {
    "code": "validation_failed",
    "message": "Os dados enviados não são válidos.",
    "details": {
      "mood_level": ["O nível de humor é obrigatório."]
    }
  }
}
```

**HTTP status codes:**
- 200 OK — Sucesso
- 201 Created — Recurso criado
- 204 No Content — Ação sem retorno (delete, toggle)
- 400 Bad Request — Input malformado
- 401 Unauthorized — Token inválido/expirado
- 403 Forbidden — Sem permissão
- 404 Not Found — Recurso não encontrado
- 422 Unprocessable Entity — Validação falhou
- 429 Too Many Requests — Rate limit excedido
- 500 Internal Server Error — Erro do servidor

**Prioridade:** Fase 0

---

## Gap 6: Paginação inadequada para mobile (MÉDIO)

**Estado atual:** Paginação offset-based (`?page=2&per_page=15`).

**Problema para mobile:** Offset-based pagination é problemática em feeds dinâmicos — se um novo post é criado enquanto o utilizador faz scroll, os items deslocam-se e podem ser duplicados ou perdidos.

**Solução:** Implementar cursor-based pagination.

```json
{
  "data": [...],
  "meta": {
    "per_page": 20,
    "next_cursor": "eyJpZCI6MTAwfQ==",
    "prev_cursor": null,
    "has_more": true
  }
}
```

**Laravel suporta cursor pagination nativamente:**
```php
$posts = Post::orderBy('id', 'desc')->cursorPaginate(20);
```

**Prioridade:** Fase 2 (quando o fórum for migrado)

---

## Gap 7: Versionamento de API (MÉDIO)

**Estado atual:** Sem versionamento.

**Solução:**
- Prefixo `/api/v1/` em todas as rotas
- Namespace `App\Http\Controllers\Api\V1`
- Quando houver breaking changes: criar `V2` sem remover `V1`
- Header `Accept: application/json` obrigatório
- Header `X-API-Version: 1` opcional (informativo)

**Estratégia de deprecação:**
1. Novo endpoint em V2
2. V1 marcado como deprecated (header `Deprecation: true`)
3. V1 removido após 6 meses + notificação

**Prioridade:** Fase 0 (definir a estrutura, implementar v1)

---

## Gap 8: Upload de ficheiros para mobile (MÉDIO)

**Estado atual:** Uploads de áudio e imagens via form multipart standard (web).

**Problemas para mobile:**
- Uploads grandes podem falhar com rede instável
- Sem resumable uploads
- Sem pre-signed URLs para upload direto a S3

**Solução (Fase 1 — simples):**
- Endpoints multipart standard para uploads pequenos (<10MB)
- Timeout generoso (60s)
- Client-side: retry com backoff exponencial

**Solução (Fase 2+ — robusta):**
- Tus protocol para resumable uploads (ou chunked upload)
- Pre-signed URLs para S3 direto (reduz carga no servidor)
- Compressão client-side (imagens: redimensionar antes de upload)
- Áudio: comprimir para formato eficiente (AAC/Opus)

**Prioridade:** Fase 1 (simples), Fase 2 (robusto)

---

## Gap 9: Push notifications via FCM (ALTO)

**Estado atual:** Web Push com VAPID (parcialmente implementado). Sem Firebase Cloud Messaging.

**Solução:**
1. Instalar pacote FCM para Laravel (ex: `laravel-notification-channels/fcm`)
2. Adicionar campo `fcm_token` à tabela `users` (migration)
3. Endpoint para registar token: `POST /api/v1/push/register`
4. Endpoint para remover token: `POST /api/v1/push/unregister`
5. Adicionar canal FCM às notificações existentes

```php
// Em cada Notification class
public function via($notifiable): array
{
    $channels = ['database'];
    if ($notifiable->fcm_token) {
        $channels[] = 'fcm';
    }
    if ($notifiable->wants_weekly_summary) {
        $channels[] = 'mail';
    }
    return $channels;
}
```

**Migration:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('fcm_token')->nullable()->after('email_verified_at');
});
```

**Prioridade:** Fase 2 (após funcionalidades core da fase 1)

---

## Gap 10: WebSocket auth para tokens (MÉDIO)

**Estado atual:** Broadcasting auth usa session cookies (web middleware).

**Problema:** A app Android usa tokens Sanctum, não cookies de sessão. A autenticação de canais WebSocket precisa de suportar tokens.

**Solução:**
1. Configurar Sanctum para autenticar broadcasting requests
2. No `BroadcastServiceProvider` ou `channels.php`, usar `auth:sanctum` guard
3. A app Android envia o Bearer token no handshake WebSocket

```php
// channels.php — já funciona com Sanctum se o guard estiver configurado
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    // Mesma lógica, funciona com session ou token
    $room = Room::find($roomId);
    return $room && (!$room->is_private || /* check access */);
});
```

**Prioridade:** Fase 3 (quando o chat for migrado)

---

## Gap 11: Rate limiting para API (MÉDIO)

**Estado atual:** Rate limiting definido no `AppServiceProvider` para rotas web.

**Solução:** Adicionar rate limiters específicos para API.

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('api-write', function (Request $request) {
    return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
});
```

**Headers de rate limit nas respostas:**
- `X-RateLimit-Limit: 60`
- `X-RateLimit-Remaining: 42`
- `Retry-After: 30` (quando excedido)

**Prioridade:** Fase 0

---

## Gap 12: Error handling para API (ALTO)

**Estado atual:** Exception handler retorna páginas HTML de erro.

**Solução:** Criar exception handler que deteta pedidos API e retorna JSON padronizado.

```php
// app/Exceptions/Handler.php (ou bootstrap/app.php)
$exceptions->render(function (Throwable $e, Request $request) {
    if ($request->is('api/*') || $request->wantsJson()) {
        return match (true) {
            $e instanceof ValidationException => response()->json([
                'error' => [
                    'code' => 'validation_failed',
                    'message' => 'Os dados enviados não são válidos.',
                    'details' => $e->errors(),
                ]
            ], 422),
            $e instanceof AuthenticationException => response()->json([
                'error' => [
                    'code' => 'unauthenticated',
                    'message' => 'Sessão expirada. Faz login novamente.',
                ]
            ], 401),
            $e instanceof ModelNotFoundException => response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Recurso não encontrado.',
                ]
            ], 404),
            default => response()->json([
                'error' => [
                    'code' => 'server_error',
                    'message' => 'Ocorreu um erro. Tenta novamente.',
                ]
            ], 500),
        };
    }
});
```

**Prioridade:** Fase 0

---

## Gap 13: Idempotência em operações críticas (BAIXO)

**Estado atual:** Sem idempotency keys.

**Problema:** Em redes instáveis, a app pode reenviar requests. Sem idempotência, pode criar duplicados (ex: duplicar entrada de diário, duplicar post).

**Solução:**
- Header `Idempotency-Key: <uuid>` em requests POST
- Middleware que verifica se a key já foi processada (cache 24h)
- Se duplicada: retorna a resposta original em vez de reprocessar

**Prioridade:** Fase 2 (quando operações de escrita aumentam)

---

## Gap 14: CORS para app nativa (BAIXO)

**Estado atual:** CORS configurado para web (mesmo domínio, sem necessidade).

**Nota:** Apps Android nativas fazem requests HTTP diretos, não browser requests. CORS não se aplica a requests nativos — o browser é que impõe CORS, não o servidor.

**Ação:** Nenhuma alteração necessária para a app Android. CORS continua relevante apenas para o frontend web.

**Prioridade:** N/A

---

## Gap 15: Session flash para gamificação (CRÍTICO)

**Estado atual:** `GamificationService::trackAction()` usa `session()->flash()` em 3 locais
para feedback de gamificação:
- `session()->flash('gamification.flames', $amount)` — após cada award de flames
- `session()->flash('gamification.badge', ['name' => ..., 'icon' => ..., 'image' => ...])` — após achievement unlock
- `session()->flash('gamification.mission', $mission->title)` — após mission completion

O método retorna `void`. Os controllers web lêem estes valores da sessão na próxima view.

**Problema:** Requests API são stateless. `session()->flash()` escreve numa sessão que nunca
é lida pelo response da API. O feedback de gamificação é **silenciosamente perdido** — o user
completa uma ação mas nunca vê as flames ganhas, badges desbloqueados, ou missões completadas.

**Solução:** Modificar `trackAction()` para retornar um `GamificationResult` DTO que acumula
todos os eventos de gamificação da operação:

```php
class GamificationResult {
    public int $flames_earned = 0;
    public int $total_flames;
    public string $flame_level;
    public int $current_streak;
    public ?array $achievement_unlocked = null;  // {name, icon, image}
    public ?string $mission_completed = null;     // título da missão
}
```

**Backwards compatibility:** Manter as chamadas `session()->flash()` para os controllers web
que as lêem. Os API controllers usam o valor de retorno. Zero breaking changes.

**Prioridade:** Fase 0 (pré-requisito para qualquer ação com gamificação na API)

Ref. [16-gamificacao-servidor.md](16-gamificacao-servidor.md) para detalhes da transformação.

---

## Gap 16: Safety plan endpoint (MÉDIO)

**Estado atual:** Safety plan armazenado como JSON no campo `User.safety_plan`. O
`ProfileController::updateSafetyPlan()` valida 6 campos (`warning_signs`, `coping_strategies`,
`reasons_to_live`, `support_contacts`, `professional_contacts`, `environment_safety` — todos
nullable, max 1000 chars) e retorna redirect.

**Problema:** Sem endpoint API. O safety plan é funcionalidade crítica de segurança emocional
(ref. [04-fase-inicial.md](04-fase-inicial.md)) e precisa de estar disponível desde a Fase 1.

**Solução:**
- `GET /api/v1/profile/safety-plan` — retorna o plano atual (ou objeto vazio)
- `PUT /api/v1/profile/safety-plan` — atualiza com validação dos 6 campos
- FormRequest dedicado: `UpdateSafetyPlanRequest`
- Safety plan é pré-cacheado em Room DB para acesso offline
  (ref. [13-offline-sync.md](13-offline-sync.md))

**Prioridade:** Fase 1

---

## Gap 17: Accessibility settings endpoint (BAIXO)

**Estado atual:** 3 campos no User model: `a11y_dyslexic_font` (boolean), `a11y_reduced_motion`
(boolean), `a11y_text_size` (enum: sm/base/lg/xl). `ProfileController::updateAccessibility()`
valida inline e retorna redirect.

**Problema:** Sem endpoint API. A app Android precisa de sincronizar preferências de
acessibilidade com o servidor para consistência cross-device.

**Solução:**
- `PUT /api/v1/profile/accessibility` — atualiza os 3 campos
- A app sincroniza na login e envia updates quando o user muda preferências
- A app também respeita system preferences do Android (ref.
  [05-adaptacoes-nativas.md](05-adaptacoes-nativas.md) secção 10)

**Prioridade:** Fase 1

---

## Gap 18: Energy level tracking (BAIXO)

**Estado atual:** `ProfileController::updateEnergy()` já retorna JSON (`{'success': true}`),
valida `level` como integer 1-5, e guarda em `User.energy_level`. Funciona via rota web.

**Problema:** O endpoint já é quase API-ready mas está registado em `routes/web.php` com
session middleware.

**Solução:** Registar endpoint equivalente em `routes/api.php`:
- `PUT /api/v1/profile/energy` com body `{ "level": 3 }`
- A lógica do controller é idêntica — apenas muda o routing

**Prioridade:** Fase 1

---

## Gap 19: Breathing log gamification (BAIXO)

**Estado atual:** `ProfileController::logBreathing()` já retorna JSON (`{'success': true,
'flames': 5}`) e chama `GamificationService::trackAction('breathe')`. Funciona via rota web.

**Problema:** Está em `routes/web.php`. Além disso, o response não inclui o envelope de
gamificação completo (apenas `flames: 5` hardcoded, não inclui streak, mission progress, etc.).

**Solução:**
- Registar em `routes/api.php`: `POST /api/v1/calm-zone/breathe`
- Usar o `GamificationResult` DTO (Gap 15) para retornar envelope completo
- Rate limited: `throttle:gamification` (5/min) + daily cap de 3 rewards

**Depende de:** Gap 15 (session flash)

**Prioridade:** Fase 1

---

## Gap 20: Onboarding flow (ALTO)

**Estado atual:** `OnboardingController` tem 3 campos: `intent` (enum: crisis/talk/write/learn/explore),
`mood` (1-5), `preference` (enum: read/listen/talk/create). O `store()` guarda os valores,
marca `onboarding_completed_at`, e retorna `redirect()` para rota contextual baseada no intent.

O `EnsureOnboardingCompleted` middleware faz `redirect()->route('onboarding.index')` se
`onboarding_completed_at` é null.

**Problema:** Dois sub-problemas:
1. O endpoint retorna redirect HTML, não JSON
2. O middleware retorna redirect, não 403 JSON

**Solução:**
- `POST /api/v1/onboarding` com body `{ "intent": "write", "mood": 3, "preference": "listen" }`
- Response retorna screen identifier (não URL): `{ "data": { "suggested_screen": "diary" } }`
- Middleware API: `EnsureOnboardingCompletedApi` retorna 403 com
  `{ "error": { "code": "onboarding_required" } }`
- FormRequest: `StoreOnboardingRequest` com regras de validação

**Prioridade:** Fase 0 (pré-requisito para navegação inicial da app)

---

## Gap 21: Dashboard aggregation endpoint (ALTO)

**Estado atual:** `DashboardController::index()` faz 15+ queries/computações e passa tudo
para uma view Blade:

| Variável | Fonte | Cache |
|---------|-------|-------|
| `dailyMissions` | GamificationService::assignDailyMissions() | 10min |
| `todayLog` | DailyLog::where(log_date, today) | Sem cache |
| `progressData` | Contagem semanal de logs | Sem cache |
| `greeting` | Saudação baseada na hora | Sem cache |
| `pendingMilestone` | User milestone mais recente | Sem cache |
| `emotionalTags` | User.emotional_tags | Sem cache |
| `encouragement` | Mensagem motivacional aleatória | Sem cache |
| `emotionalDate` | Formato de data emocional | Sem cache |
| `recommendations` | RecommendationService | Sem cache |
| `communityStats` | Contagens agregadas | 15min |
| `globalImpact` | Estatísticas globais | 15min |
| `aiInsight` | OpenAI CBT insight | 24h |

**Problema:** Sem endpoint API. É a primeira tela que o user vê — crítico para a Fase 1.

**Solução:**
- `GET /api/v1/dashboard` retorna `DashboardResource` com todos os dados agregados
- Cache: 5min por user (key: `dashboard:{user_id}`)
- AI insight: já cacheado 24h server-side (reutilizável)
- Community stats: já cacheados 15min (reutilizável)
- ETag para conditional requests (`If-None-Match`)

**Prioridade:** Fase 1

---

## Gap 22: Emotional passport (MÉDIO)

**Estado atual:** `ProfileController::exportPassport()` agrega últimos 30 dias: média de mood,
top 5 tags por frequência, total de logs, e todas as entradas. Retorna view Blade.

**Problema:** Sem endpoint API. O passaporte emocional é funcionalidade de Fase 2 na app
(ref. [04-fase-inicial.md](04-fase-inicial.md)) — resumo na app, PDF server-side via browser.

**Solução:**
- `GET /api/v1/profile/passport` retornando JSON:
  ```json
  {
    "data": {
      "average_mood": 3.7,
      "top_tags": [{"tag": "gratidão", "count": 12}, ...],
      "total_logs": 28,
      "period_days": 30,
      "logs": [...]
    }
  }
  ```
- PDF: gerado server-side, download via share intent no browser
  (ref. [06-web-first-admin.md](06-web-first-admin.md) secção 5)

**Prioridade:** Fase 2

---

## Gap 23: Search API (MÉDIO)

**Estado atual:** `SearchController::index()` pesquisa posts, resources, e rooms. Mínimo 2
caracteres. Respeita `ShadowbanScope`. Moderadores têm filtro `safe`. Retorna view HTML.

**Problema:** Sem endpoint API. A pesquisa é funcionalidade transversal usada em múltiplas
telas da app.

**Solução:**
- `GET /api/v1/search?q=ansiedade&type=posts&emotion=tristeza`
- Parâmetros: `q` (min 2 chars), `type` (posts/resources/rooms, nullable = todos),
  `emotion` (filtro por tag emocional, nullable)
- Cursor pagination para resultados
- `ShadowbanScope` funciona automaticamente (ref.
  [10-backend-reutilizacao.md](10-backend-reutilizacao.md))

**Prioridade:** Fase 2

---

## Gap 24: Multiple FCM tokens per user (MÉDIO)

**Estado atual:** O campo `fcm_token` não existe. A proposta inicial (Gap 9) sugere um campo
único na tabela `users` — mas um user pode ter múltiplos dispositivos Android.

**Problema:** Campo único `fcm_token` não suporta:
- Mesmo user em 2 dispositivos Android
- Cleanup de tokens expirados
- Tracking de último uso por dispositivo

**Solução:** Criar tabela `device_tokens`:
```
device_tokens:
  id, user_id, token, device_name, platform, created_at, last_used_at
```

Endpoints:
- `POST /api/v1/devices` — registar token (com device_name e platform)
- `DELETE /api/v1/devices/{id}` — remover token (logout de dispositivo)
- `GET /api/v1/devices` — listar dispositivos ativos (para gestão de sessões)

**Atualização ao Gap 9:** Substituir `fcm_token` na tabela `users` por tabela `device_tokens`.
As notifications consultam `$notifiable->deviceTokens()` em vez de `$notifiable->fcm_token`.

**Prioridade:** Fase 2 (quando FCM for implementado)

---

## Mapa de dependências entre gaps

```
Gap 1 (API layer) ──────► bloqueia TODOS os outros gaps
    │
    ▼
Gap 2 (Sanctum) ────────► bloqueia Gaps 10, 15-24
    │
    ├──► Gap 5 (Response format) ──► Gap 15 (Gamification envelope)
    │                                     │
    │                                     ▼
    │                               Gap 19 (Breathing gamification)
    │
    ├──► Gap 3 (Resources) ────────► Gaps 21 (Dashboard), 22 (Passport), 23 (Search)
    │
    ├──► Gap 12 (Error handling) ──► Gap 20 (Onboarding 403)
    │
    └──► Gap 9 (FCM) ─────────────► Gap 24 (Multiple device tokens)
```

**Caminho crítico:** Gap 1 → Gap 2 → Gap 5 → Gap 15 → todos os endpoints de mutação.

Sem resolver o caminho crítico, nenhuma funcionalidade Android com gamificação funciona
correctamente.

---

## Resumo de gaps por prioridade

### Fase 0 (pré-requisito, antes de qualquer funcionalidade Android)

| # | Gap | Criticidade | Depende de |
|---|-----|------------|-----------|
| 1 | Criar `routes/api.php` com prefixo v1 | CRÍTICO | — |
| 2 | Instalar e configurar Sanctum | CRÍTICO | Gap 1 |
| 3 | Criar API Resources | ALTO | Gap 1 |
| 4 | Criar Form Requests para API | ALTO | Gap 1 |
| 5 | Padronizar formato de resposta (envelope) | ALTO | Gap 1 |
| 7 | Definir versionamento (v1) | MÉDIO | Gap 1 |
| 11 | Rate limiting global para API | MÉDIO | Gap 1 |
| 12 | Error handling para API (JSON) | ALTO | Gap 1 |
| 15 | Session flash → GamificationResult DTO | CRÍTICO | Gap 5 |
| 20 | Onboarding flow API | ALTO | Gap 12 |

### Fase 1

| # | Gap | Criticidade | Depende de |
|---|-----|------------|-----------|
| 8 | Upload de ficheiros (simples, multipart) | MÉDIO | Gap 1 |
| 16 | Safety plan endpoint | MÉDIO | Gap 2 |
| 17 | Accessibility settings endpoint | BAIXO | Gap 2 |
| 18 | Energy level tracking | BAIXO | Gap 2 |
| 19 | Breathing log gamification | BAIXO | Gap 15 |
| 21 | Dashboard aggregation endpoint | ALTO | Gap 3 |

### Fase 2

| # | Gap | Criticidade | Depende de |
|---|-----|------------|-----------|
| 6 | Cursor-based pagination | MÉDIO | Gap 1 |
| 9 | Push notifications (FCM) | ALTO | Gap 24 |
| 13 | Idempotência | BAIXO | Gap 1 |
| 14 | Upload robusto (resumable/chunked) | MÉDIO | Gap 8 |
| 22 | Emotional passport | MÉDIO | Gap 3 |
| 23 | Search API | MÉDIO | Gap 3 |
| 24 | Multiple FCM tokens (device_tokens) | MÉDIO | Gap 2 |

### Fase 3

| # | Gap | Criticidade | Depende de |
|---|-----|------------|-----------|
| 10 | WebSocket auth para tokens | MÉDIO | Gap 2 |

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-11-01 | Dashboard aggregation endpoint lento (15+ queries por request) | Média | Médio | Cache agressivo (5min/user), queries paralelas, considerar materializar dados |
| RISK-11-02 | Modificar `GamificationService` para retornar DTO pode quebrar controllers web que lêem session flash | Média | Alto | Manter `session()->flash()` E retornar valor. API lê return, web lê session. Zero breaking changes |
| RISK-11-03 | Single `fcm_token` implementado antes de `device_tokens` — requer migração posterior | Baixa | Baixo | Implementar `device_tokens` desde o início (não implementar campo singular) |
| RISK-11-04 | 10 novos gaps (15-24) aumentam significativamente o escopo da Fase 0/1 | Alta | Médio | Gaps 17, 18 são de esforço mínimo (mover rota). Gaps 15 e 20 são críticos mas de escopo limitado. Priorizar pelo caminho crítico |
| RISK-11-05 | FormRequests inline nos controllers podem ter regras de validação não documentadas | Média | Baixo | Auditar cada controller web antes de criar FormRequest API equivalente |

---

*Próximo: [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) — Autenticação, sessão, segurança e privacidade.*
