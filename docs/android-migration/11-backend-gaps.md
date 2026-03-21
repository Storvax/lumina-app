# 11 — Gaps e Alterações Necessárias no Backend para Suportar Mobile

## Contexto

O backend Laravel atual foi construído exclusivamente para servir páginas web. Para suportar uma app Android nativa, é necessário resolver vários gaps estruturais. Este documento lista cada gap, a solução proposta, e a prioridade.

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

## Resumo de gaps por prioridade

### Fase 0 (pré-requisito, antes de qualquer funcionalidade Android)
1. ✅ Criar `routes/api.php` com prefixo v1
2. ✅ Instalar e configurar Sanctum
3. ✅ Criar API controllers base
4. ✅ Criar API Resources
5. ✅ Criar Form Requests para API
6. ✅ Padronizar formato de resposta
7. ✅ Error handling para API
8. ✅ Rate limiting para API
9. ✅ Definir versionamento (v1)

### Fase 1
10. Upload de ficheiros (simples, multipart)

### Fase 2
11. Push notifications (FCM)
12. Cursor-based pagination
13. Idempotência
14. Upload robusto (resumable/chunked)

### Fase 3
15. WebSocket auth para tokens
16. Broadcasting via Sanctum

---

*Próximo: [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) — Autenticação, sessão, segurança e privacidade.*
