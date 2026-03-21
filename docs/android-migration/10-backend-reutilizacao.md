# 10 — Estratégia de Reutilização do Backend Laravel Existente

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

*Próximo: [11-backend-gaps.md](11-backend-gaps.md) — Gaps e alterações necessárias no backend.*
