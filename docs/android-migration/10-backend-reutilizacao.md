# 10 — Estratégia de Reutilização do Backend Laravel Existente

## Contexto

Este documento analisa o backend Laravel existente e identifica o que pode ser reutilizado
diretamente pela camada API que servirá a app Android nativa.

O estado atual do backend (ref. [01-estado-atual.md](01-estado-atual.md)) inclui 32 modelos
Eloquent, 7 services, 7 broadcasting events, 6 notifications, 1 job, 5 console commands,
4 middlewares customizados, e 6 policies de autorização. O inventário funcional
(ref. [02-inventario-funcional.md](02-inventario-funcional.md)) identifica ~65 rotas B2C
candidatas a API. O mapeamento funcional (ref. [03-mapeamento-funcional.md](03-mapeamento-funcional.md))
define a correspondência entre cada funcionalidade web e a sua forma nativa.

Este documento é complementar a [11-backend-gaps.md](11-backend-gaps.md), que lista o que
**não** pode ser reutilizado diretamente. Ref. [25-riscos-decisoes.md](25-riscos-decisoes.md)
para riscos relacionados com decisões de reutilização.

**Metodologia:** Derivado de leitura direta de `app/Models/`, `app/Services/`, `app/Events/`,
`app/Notifications/`, `app/Policies/`, `app/Http/Middleware/`, e `app/Http/Controllers/`.

---

## Observações do estado atual

Antes de listar componentes reutilizáveis, importa documentar particularidades descobertas
na análise do código que afetam a estratégia de reutilização:

1. **GamificationService** (`app/Services/GamificationService.php`): Usa `session()->flash()`
   em 3 locais para feedback de UI — keys `gamification.flames` (int), `gamification.badge`
   (array com name/icon/image), `gamification.mission` (string título). Estas chamadas
   funcionam para Blade views mas **falham silenciosamente em contexto API stateless**.
   O serviço é reutilizável, mas os callers API devem capturar side-effects via return value
   em vez de session. Ref. [16-gamificacao-servidor.md](16-gamificacao-servidor.md) para
   solução detalhada.

2. **User model** (`app/Models/User.php`): 42+ campos. `$hidden` contém `password`,
   `remember_token`, `encrypted_private_key`. Dois accessors computados são reutilizáveis
   via API Resources: `getPseudonymAttribute()` (sha256 determinístico, irreversível) e
   `getBonfireLevelAttribute()` (spark/flame/bonfire/beacon derivado de flames).

3. **6 Policies** registadas em `AppServiceProvider` — todas usam o padrão standard
   `$this->authorize()`, compatível com API controllers sem alterações.

4. **CheckBanned middleware**: Faz `Auth::logout()` + `session()->invalidate()` +
   `redirect()->route('login')`. Precisa de versão API que retorne 403 JSON em vez de
   redirect e operações de sessão.

5. **EnsureOnboardingCompleted middleware**: Faz `redirect()->route('onboarding.index')`.
   Precisa de versão API que retorne 403 JSON com code `onboarding_required`.

6. **8 rate limiters** em `AppServiceProvider::configureRateLimiting()`: `password-reset`,
   `content-creation`, `buddy-actions`, `gamification`, `reports`, `suggestions`,
   `privacy-actions`, `ai-actions`. Todos keyed por `$request->user()?->id` — reutilizáveis
   para rotas API sem alterações.

7. **Cursor pagination** já usada em `ForumController::index()` para AJAX web — o mesmo
   padrão pode ser adotado nos endpoints API para feeds.

---

## Princípio

O backend Laravel existente é maduro e funcional. Não vamos reescrever o backend — vamos **adicionar uma camada API** que reutiliza toda a lógica de negócio existente (Services, Models, Events, Notifications, Jobs).

A web continua a funcionar exatamente como está. A app Android consome a nova API.

```
┌─────────────┐     ┌─────────────┐
│   Browser   │     │ Android App │
│  (Blade UI) │     │  (Kotlin)   │
└──────┬──────┘     └──────┬──────┘
       │                    │
       ▼                    ▼
┌─────────────┐     ┌─────────────┐
│ routes/web  │     │ routes/api  │   ← NOVO
│   .php      │     │   .php      │
└──────┬──────┘     └──────┬──────┘
       │                    │
       └────────┬───────────┘
                ▼
       ┌────────────────┐
       │    Services    │   ← REUTILIZADO
       │    Models      │
       │    Events      │
       │    Jobs        │
       │  Notifications │
       └────────────────┘
```

---

## O que se reutiliza diretamente

### 1. Models (32) — 100% reutilizáveis

Todos os modelos Eloquent servem tanto a web como a API. Não precisam de alterações.

| Modelo | Usado pela API? | Notas |
|--------|----------------|-------|
| User | ✅ | Core de autenticação e perfil |
| Post, Comment, PostReaction, CommentReaction | ✅ | Fórum (fase 2) |
| Message, MessageReaction, MessageRead | ✅ | Chat (fase 3) |
| Room | ✅ | Salas de chat |
| DailyLog | ✅ | Diário |
| SelfAssessment | ✅ | Auto-avaliação (fase 2) |
| Mission, Achievement | ✅ | Gamificação |
| BuddySession, BuddyApplication | ✅ | Buddy (fase 3) |
| VaultItem | ✅ | Cofre pessoal |
| Milestone | ✅ | Timeline do perfil |
| Resource | ✅ | Biblioteca (fase 2) |
| PlaylistSong | ✅ | Playlist (fase 2) |
| WallPost | ✅ | The Wall (fase 2) |
| PactPrompt, PactAnswer | ✅ | Pacto (fase 2) |
| Report | ✅ | Reports |
| Todos os outros | ✅ | |

### 2. Services (7) — 100% reutilizáveis

| Serviço | Usado pela API? | Notas |
|---------|----------------|-------|
| CBTAnalysisService | ✅ | Deteção de crise, análise CBT — chamado pelos API controllers |
| GamificationService | ✅ | Tracking de ações — chamado após operações na API |
| RecommendationService | ✅ | Posts recomendados — endpoint dedicado |
| ExperienceMatchingService | ✅ | Matching de pares |
| AnalyticsService | ✅ | Event logging — usado em API middleware |
| EncryptionService | ✅ | Encriptação de dados sensíveis |
| FeatureFlagService | ✅ | Feature toggles — consulta via API |

### 3. Events (7) — 100% reutilizáveis

Os broadcasting events funcionam independentemente de quem os triggou (web ou API). A app Android conecta-se ao mesmo servidor Reverb.

| Evento | Notas |
|--------|-------|
| MessageSent | Broadcast funciona para web e mobile simultaneamente |
| MessageReacted | Idem |
| MessageDeleted | Idem |
| MessageUpdated | Idem |
| MessageRead | Idem |
| RoomStatusUpdated | Idem |
| SomaticSyncTriggered | Idem |

### 4. Notifications (6) — 100% reutilizáveis

As notificações são enviadas independentemente do canal de origem. Precisam apenas de um novo canal (FCM) para push nativo.

| Notificação | Alteração necessária |
|------------|---------------------|
| ForumInteraction | Adicionar canal FCM |
| GentleChallengeReceived | Adicionar canal FCM |
| WeeklyEmotionalSummary | Adicionar canal FCM |
| ModeratorCrisisAlert | Sem alteração (admin-only, web) |
| GentleReEngagement | Adicionar canal FCM |
| QueuedVerifyEmail | Sem alteração (email-only) |

### 5. Jobs (1) — 100% reutilizável

| Job | Notas |
|-----|-------|
| ProcessGdprDeletion | Triggered via API exatamente como via web |

### 6. Console Commands (5) — 100% reutilizáveis

Estes comandos correm no servidor, independentes do cliente.

| Comando | Notas |
|---------|-------|
| SendWeeklySummaries | Envia para todos os utilizadores (web + mobile) |
| PurgeExpiredData | Lógica server-side |
| DetectDisengagement | Lógica server-side |
| CalculateCommunityTemperature | Lógica server-side |
| CreateAdminUser | CLI-only |

### 7. Middleware — Parcialmente reutilizável

| Middleware | API? | Notas |
|-----------|------|-------|
| CheckBanned | ✅ | Aplicar à API (verificar ban em cada request) |
| EnsureOnboardingCompleted | ✅ | Versão API retorna 403 JSON em vez de redirect |
| TherapistMiddleware | ❌ | Web-only nesta fase |
| CorporateMiddleware | ❌ | Web-only nesta fase |

### 8. Database migrations — 100% reutilizáveis

A mesma base de dados serve web e API. Nenhuma migration adicional é necessária para a fase 1, exceto:
- Tabela `personal_access_tokens` (Sanctum)
- Campo `fcm_token` na tabela `users` (para push notifications)

### 9. Seeders e Factories — Reutilizáveis para testing

Os seeders existentes podem ser usados para popular dados de teste durante desenvolvimento da API.

### 10. Policies (6) — 100% reutilizáveis

Todas as policies de autorização usam o padrão standard `$this->authorize()` e funcionam
identicamente com session auth (web) e token auth (Sanctum API). A `AuthorizationException`
é capturada pelo API error handler e retornada como 403 JSON.

| Policy | Modelo | Regras principais | Notas para API |
|--------|--------|-------------------|---------------|
| PostPolicy | Post | create: not banned + not shadowbanned; update: autor; delete: autor ou moderador; pin/lock: moderador; view: posts sensíveis só visíveis ao autor ou moderador | `ShadowbanScope` (global scope) funciona com Sanctum guard — user shadowbanned vê os próprios posts |
| CommentPolicy | Comment | create: not banned + not shadowbanned; update/delete: autor ou moderador; markHelpful: moderador ou autor do post pai | Idêntico à web |
| MessagePolicy | Message | create: not banned; update: autor (soft-deleted não editável); delete: autor ou moderador; pin: moderador | Fase 3 (chat) |
| DailyLogPolicy | DailyLog | **Estritamente privado** — view/update/delete: apenas owner. Sem admin override | Diário é dado mais sensível — nunca expor a outros users |
| VaultItemPolicy | VaultItem | **Estritamente privado** — view/update/delete: apenas owner | "Caixinha de Luz" — absoluta privacidade |
| SelfAssessmentPolicy | SelfAssessment | view: owner **ou** terapeuta do paciente (via relação therapist-patient); create: not banned; delete: owner | Terapeutas podem ver resultados PHQ-9/GAD-7 dos seus pacientes |

### 11. Rate Limiters (8) — 100% reutilizáveis

Os rate limiters existentes em `AppServiceProvider::configureRateLimiting()` são todos keyed
por `$request->user()?->id ?: $request->ip()` e aplicam-se diretamente a rotas API.

| Rate Limiter | Limite | Aplicação API |
|-------------|--------|--------------|
| `password-reset` | 1/min | POST /auth/forgot-password |
| `content-creation` | 5/min | POST diary, posts, comments |
| `buddy-actions` | 3/min | Buddy system (fase 3) |
| `gamification` | 5/min | Gentle challenge |
| `reports` | 3/min | Report de conteúdo |
| `suggestions` | 2/min | Suggestions/recommendations |
| `privacy-actions` | 3/hora | Export data, delete account, hibernate |
| `ai-actions` | 10/min | CBT analysis, AI summarization |

---

## O que precisa de ser criado (novo)

| Componente | Descrição | Impacto |
|-----------|-----------|---------|
| `routes/api.php` | Ficheiro de rotas API dedicado | Novo ficheiro |
| API Controllers | Controllers que retornam JSON (podem chamar Services existentes) | Novos ficheiros |
| API Resources | Transformers para serializar models em JSON padronizado | Novos ficheiros |
| Form Requests | Validação de input para endpoints API | Novos ficheiros |
| Sanctum config | Autenticação por tokens | Novo package + config |
| API error handler | Exception handler para respostas JSON padronizadas | Novo handler |
| FCM integration | Envio de push notifications via Firebase | Novo notification channel |

Detalhes completos em [11-backend-gaps.md](11-backend-gaps.md).

---

## Lógica dos Controllers: reutilização vs duplicação

### Abordagem recomendada: Controllers API separados

**Não** reutilizar os Controllers web existentes. Em vez disso:
- Criar novos controllers em `app/Http/Controllers/Api/V1/`
- Estes controllers chamam os mesmos Services e Models
- Retornam JSON via API Resources em vez de views

**Razão:** Os controllers web atuais misturam lógica de negócio com retorno de views, redirects, e flash messages. Tentar torná-los dual-purpose (web + API) resultaria em código confuso e frágil.

```
app/Http/Controllers/
├── Api/
│   └── V1/
│       ├── AuthController.php          ← NOVO
│       ├── OnboardingController.php    ← NOVO
│       ├── DashboardController.php     ← NOVO
│       ├── DiaryController.php         ← NOVO
│       ├── ProfileController.php       ← NOVO
│       ├── CalmZoneController.php      ← NOVO
│       ├── ForumController.php         ← NOVO (Fase 2)
│       ├── ChatController.php          ← NOVO (Fase 3)
│       └── ...
├── ForumController.php                 ← EXISTENTE (web, não mexer)
├── ChatController.php                  ← EXISTENTE (web, não mexer)
└── ...
```

### Exemplo de controller API reutilizando serviço existente

```php
// app/Http/Controllers/Api/V1/DiaryController.php
class DiaryController extends Controller
{
    public function store(StoreDiaryRequest $request)
    {
        $entry = DailyLog::create([
            'user_id' => auth()->id(),
            'mood_level' => $request->mood_level,
            'tags' => $request->tags,
            'note' => $request->note,
            'log_date' => now()->toDateString(),
        ]);

        // Reutiliza serviço existente
        $cbtInsight = app(CBTAnalysisService::class)->analyze($request->note);
        $entry->update(['cbt_insight' => $cbtInsight['suggestion'] ?? null]);

        // Reutiliza gamificação existente
        app(GamificationService::class)->trackAction(auth()->user(), 'daily_log');

        return new DailyLogResource($entry);
    }
}
```

---

## Resumo de impacto no backend

| Aspeto | Ação | Esforço |
|--------|------|---------|
| Models | Nenhuma alteração | Zero |
| Services | Nenhuma alteração | Zero |
| Events | Nenhuma alteração | Zero |
| Jobs | Nenhuma alteração | Zero |
| Commands | Nenhuma alteração | Zero |
| Migrations | +2 (sanctum tokens, fcm_token) | Baixo |
| Routes | +1 ficheiro (api.php) | Médio |
| Controllers | +8-10 novos (API namespace) | Médio-Alto |
| Resources | +10-15 novos (API serializers) | Médio |
| Form Requests | +10-15 novos (validação) | Médio |
| Config | +1 (sanctum) | Baixo |
| Middleware | +1-2 adaptações | Baixo |

**Conclusão:** ~80% do backend é reutilizado diretamente. O trabalho novo concentra-se na camada de apresentação da API (controllers + resources + validation).

---

## Padrões de design para a API

### Envelope de resposta

Todas as respostas da API seguem um formato consistente com envelope:

**Leitura (GET):**
```json
{
  "data": { ... },
  "meta": {
    "per_page": 20,
    "next_cursor": "eyJpZCI6MTAwfQ==",
    "has_more": true
  }
}
```

**Mutação (POST/PUT/DELETE):**
```json
{
  "data": { ... },
  "gamification": {
    "flames_earned": 10,
    "total_flames": 142,
    "flame_level": "flame",
    "current_streak": 5,
    "achievement_unlocked": null,
    "mission_completed": "Escreve no teu diário hoje"
  }
}
```

A key `gamification` substitui o `session()->flash()` para contexto API. Presente em toda
resposta de mutação que trigger gamificação. `null` quando sem evento de gamificação.
Ref. [16-gamificacao-servidor.md](16-gamificacao-servidor.md) para detalhes da transformação.

### Paginação

| Tipo | Quando usar | Suporte Laravel |
|------|------------|----------------|
| Cursor-based | Feeds dinâmicos (fórum, notificações, chat) | `cursorPaginate()` — já usado em `ForumController` web |
| Offset-based | Listagens estáticas (biblioteca, achievements, salas) | `paginate()` — padrão Laravel |

**Default:** 20 items por página. Configurável via `?per_page=` (max 50).

### Filtragem e ordenação

```
GET /api/v1/posts?filter[tag]=ansiedade&sort=-created_at&per_page=20
GET /api/v1/library?filter[type]=article&sort=votes
GET /api/v1/notifications?filter[unread]=true
```

- Filtragem: `?filter[campo]=valor` — apenas campos permitidos por endpoint
- Ordenação: `?sort=campo` (ascendente) ou `?sort=-campo` (descendente)
- Sem query builder genérico — cada endpoint define filtros e sorts permitidos

### Sparse fieldsets

```
GET /api/v1/posts?fields[post]=id,title,tag,created_at&fields[user]=id,pseudonym
```

Opcional. Útil para reduzir payload em listagens. Implementação via `whenLoaded()` e
`when($this->resource->relationLoaded(...))` nos API Resources.

### API Resource design

- **Flat por default**: Relações não incluídas a menos que pedidas
- **Include explícito**: `?include=achievements,missions` para relações
- **Campos computados**: `pseudonym` (sha256) e `bonfire_level` (derivado de flames) expostos
  diretamente no `UserResource` — são accessors do model, reutilizáveis sem lógica extra
- **Nunca expor**: `encrypted_private_key`, `password`, `remember_token`, `shadowbanned_until`,
  `onboarding_tours`
- **Exposição condicional**: `safety_plan` e `emotional_tags` apenas no perfil próprio

### Cache de respostas API

| Endpoint | Estratégia | TTL | Notas |
|---------|-----------|-----|-------|
| GET /profile | ETag + `If-None-Match` | — | 304 Not Modified se dados não mudaram |
| GET /dashboard | ETag + `Cache-Control: private` | 5min | AI insight já cacheado 24h server-side |
| GET /missions | `Cache-Control: private, max-age=300` | 5min | Missões já cacheadas 10min server-side |
| GET /posts (feed) | Sem cache | — | Cursor pagination garante dados frescos |
| GET /achievements | `Cache-Control: private, max-age=3600` | 1h | Muda raramente |

**Conditional requests:** O client envia `If-None-Match: "etag-hash"`. Se o recurso não
mudou, o server responde 304 sem body — reduz bandwidth e latência para a app Android.

---

## Transformação de notificações web para FCM

As 6 notification classes existentes têm uma estrutura reutilizável para FCM:

**Estado atual:** Cada notification implementa `toWebPush()` (para web push) e `toArray()`
(para canal database). O método `via()` respeita quiet hours — durante quiet hours, apenas
o canal `database` é usado.

**Transformação necessária:** Adicionar método `toFcm()` a cada notification que precisa de
push mobile. A estrutura de `toArray()` (que já contém `post_id`, `user_id`, `message`,
`icon`, `color`) mapeia diretamente para FCM data payload.

| Notification | Payload database (`toArray()`) | FCM action |
|-------------|-------------------------------|-----------|
| ForumInteraction | `{post_id, user_id, message, icon, color}` | Abrir post no fórum |
| GentleChallengeReceived | `{icon, color, message, action_url}` | Abrir dashboard |
| GentleReEngagement | `{icon, color, message}` | Abrir dashboard |
| WeeklyEmotionalSummary | (a definir) | Abrir perfil/passaporte |
| ModeratorCrisisAlert | `{level, room, message}` | **Sem FCM** — admin-only, web |
| QueuedVerifyEmail | — | **Sem FCM** — email-only |

**Quiet hours:** A lógica `isInQuietHours()` no método `via()` é reutilizada sem alterações.
Durante quiet hours, FCM não é enviado — apenas `database`. O user vê a notificação quando
abrir a app.

```php
// Padrão de transformação — adicionar FCM ao lado de WebPush
public function via($notifiable): array
{
    if ($notifiable->isInQuietHours()) {
        return ['database'];
    }

    $channels = ['database', 'broadcast'];

    if ($notifiable->push_subscriptions()->exists()) {
        $channels[] = WebPushChannel::class;
    }

    if ($notifiable->deviceTokens()->exists()) {  // ← NOVO
        $channels[] = FcmChannel::class;           // ← NOVO
    }

    return $channels;
}
```

---

## Comportamento de shadowban e ban no contexto API

### Shadowban

O `ShadowbanScope` (`app/Models/Scopes/ShadowbanScope.php`) é um Global Scope aplicado ao
model `Post`. Filtra posts de users shadowbanned para que sejam invisíveis para outros, mas
visíveis para o próprio user (`orWhere user_id = Auth::id()`).

**Compatibilidade API:** O scope usa `Auth::check()` e `Auth::user()` internamente. Com o
guard `auth:sanctum` configurado nas rotas API, `Auth::user()` resolve corretamente para o
user autenticado via token. O comportamento é **idêntico** ao da web sem alterações.

**Regra para API Resources:** O campo `shadowbanned_until` **nunca** é exposto no
`UserResource`. O user não deve saber que está shadowbanned — é uma decisão de design
existente no web que se mantém na API.

### Ban

O `CheckBanned` middleware verifica `$user->isBanned()` em cada request. Na versão web,
faz `Auth::logout()`, `session()->invalidate()`, e `redirect()->route('login')`.

**Adaptação para API:** Criar versão API do middleware que retorne JSON:

```json
{
  "error": {
    "code": "account_banned",
    "message": "A tua conta foi suspensa por violar as regras da comunidade."
  }
}
```

HTTP status: 403 Forbidden. Sem operações de sessão. O token é invalidado server-side
(revogar todos os tokens do user banido).

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-10-01 | `session()->flash()` em GamificationService falha silenciosamente em contexto API — feedback de gamificação invisível | Alta | Alto | Modificar `trackAction()` para retornar `GamificationResult` DTO. Manter flash para web. Ref. [16-gamificacao-servidor.md](16-gamificacao-servidor.md) |
| RISK-10-02 | `CheckBanned` middleware faz `Auth::logout()` + operações de sessão que podem causar erros em contexto API stateless | Média | Médio | Criar `CheckBannedApi` middleware dedicado que retorne 403 JSON sem operações de sessão |
| RISK-10-03 | `ShadowbanScope` depende de `Auth::check()` — pode ter timing issues se Sanctum guard não é o default | Baixa | Baixo | Garantir que middleware `auth:sanctum` corre antes de qualquer query a models com global scopes |
| RISK-10-04 | Notification classes sem `toFcm()` — push notifications não chegam a mobile | Alta | Médio | Adicionar `toFcm()` antes de lançar funcionalidades que dependem de push (fase 2) |

---

*Próximo: [11-backend-gaps.md](11-backend-gaps.md) — Gaps e alterações necessárias no backend.*
