# 16 — Gamificação e Validações Server-Side

## Contexto

Este documento define como o sistema de gamificação da Lumina funciona no contexto da API
Android e quais adaptações são necessárias para que o feedback de gamificação chegue
correctamente à app nativa.

Referências cruzadas:
- [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — GamificationService listado como
  reutilizável (com caveats sobre session flash)
- [11-backend-gaps.md](11-backend-gaps.md) — Gap 15 (session flash → DTO), Gap 19 (breathing)
- [08-arquitetura-android.md](08-arquitetura-android.md) — Room DB cache layer para gamificação
- [13-offline-sync.md](13-offline-sync.md) — dados de gamificação stale quando offline
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — riscos de anti-cheat

---

## Observações do estado atual

Análise directa de `app/Services/GamificationService.php` (196 linhas):

1. **Entry point único:** `trackAction(User $user, string $actionType): void` — orquestra
   todo o fluxo de gamificação. Chamado por controllers após mutações.

2. **Session flash — descoberta crítica:** 3 chamadas a `session()->flash()`:
   - `session()->flash('gamification.flames', $amount)` — após cada `awardFlames()`
   - `session()->flash('gamification.badge', ['name' => ..., 'icon' => ..., 'image' => ...])` — após `unlockAchievement()`
   - `session()->flash('gamification.mission', $mission->title)` — após mission completion
   Estas chamadas permitem que as views Blade mostrem feedback de gamificação na próxima
   página. **Em contexto API stateless, estas chamadas são silenciosamente ignoradas.**

3. **Return type `void`:** Impossível para o API controller saber o que aconteceu durante
   `trackAction()`. Flames ganhas, badges desbloqueados, e missões completadas são invisíveis.

4. **REWARDS constant:** `daily_log => 10, reaction => 2, reply => 5, breathe => 5, first_post => 20`

5. **ACTION_TO_MISSION_TYPE:** `daily_log => diary, reply => comment, reaction => reaction,
   first_post => post, breathe => null`. Respiração não tem tipo de missão equivalente.

6. **Mission assignment:** `assignDailyMissions()` usa `now()->toDateString()` — **server time**.

7. **Streak logic:** `updateGentleStreak()` usa `Carbon::today()` e `isYesterday()` — **server time**.

8. **Achievement conditions:** Apenas 2 automáticos: `guardian_flame` (≥100 flames) e
   `seven_days_peace` (streak === 7). Outros achievements são manuais ou por triggers específicos.

---

## 1. Estado atual

O sistema de gamificação da Lumina é projetado para ser **terapêutico, não competitivo**. Não há leaderboards, não há mecânicas de culpa, e os streaks resetam gentilmente.

### Componentes existentes (GamificationService)

| Componente | Descrição | Validação |
|-----------|-----------|-----------|
| Flames | Moeda emocional (+2 a +20 por ação) | Server-side |
| Levels | spark → flame → bonfire → beacon | Derivado de flames |
| Streaks | Dias consecutivos com atividade | Server-side (last_activity_at) |
| Missions | 3 missões aleatórias por dia | Server-side (cache 10min) |
| Achievements | Badges desbloqueados por condições | Server-side |
| Gentle Challenge | Users 50+ flames podem oferecer apoio | Server-side + rate limit |

### Recompensas por ação

| Ação | Flames | Trigger |
|------|--------|---------|
| daily_log | +10 | Guardar entrada de diário |
| reaction | +2 | Reagir a post/comentário |
| comment/reply | +5 | Comentar num post |
| breathe | +5 | Completar exercício de respiração |
| first_post | +20 | Criar o primeiro post |

---

## 2. Princípio de segurança: server-side only

**Toda a lógica de gamificação é validada e executada no servidor.**

A app Android nunca calcula flames, nunca determina se um achievement foi desbloqueado, e nunca decide o progresso de uma missão. O servidor é a única fonte de verdade.

### Porquê?
- **Anti-cheat:** Impedir que um utilizador manipule flames ou desbloqueie achievements falsamente
- **Consistência:** O mesmo utilizador na web e na app vê os mesmos dados
- **Lógica temporal:** Streaks e missões dependem de datas do servidor, não do dispositivo

### Fluxo de gamificação na app Android

```
User completa ação (ex: guardar diário)
    │
    ▼
POST /api/v1/diary → Server
    │
    ├── DailyLog::create()
    ├── GamificationService::trackAction('daily_log')
    │       ├── flames += 10
    │       ├── updateGentleStreak()
    │       ├── updateMissionProgress('diary')
    │       └── checkAchievements()
    │
    ▼
Response inclui gamification data atualizado:
{
    "data": { ... diary entry ... },
    "gamification": {
        "flames_earned": 10,
        "total_flames": 142,
        "flame_level": "flame",
        "streak": 5,
        "mission_completed": "diary",
        "achievement_unlocked": null
    }
}
    │
    ▼
App atualiza UI com animação de flames
```

---

## 3. Endpoints API para gamificação

| Endpoint | Método | Resposta | Notas |
|----------|--------|----------|-------|
| `GET /api/v1/missions` | GET | Lista de 3 missões do dia com progresso | Cache 10min |
| `GET /api/v1/profile` | GET | Inclui flames, level, streak | Sempre atualizado |
| `GET /api/v1/achievements` | GET | Achievements desbloqueados | |
| Qualquer ação (diary, react, etc.) | POST | Inclui `gamification` no response | Side-effect |

**Não existe endpoint separado para "adicionar flames"** — flames são sempre side-effect de uma ação legítima.

---

## 4. Missões diárias

### Lógica server-side

O `GamificationService::assignDailyMissions()` atribui 3 missões aleatórias por dia:

```php
// Tipos de missões existentes:
$types = ['diary', 'comment', 'reaction', 'post'];
// 3 aleatórias, sem repetição no dia
```

### API response

```json
{
    "data": [
        {
            "id": 42,
            "type": "diary",
            "description": "Escreve no teu diário hoje",
            "progress": 0,
            "target": 1,
            "completed_at": null
        },
        {
            "id": 43,
            "type": "reaction",
            "description": "Reage a uma publicação",
            "progress": 1,
            "target": 1,
            "completed_at": "2026-03-21T14:30:00Z"
        }
    ]
}
```

### UI na app

- Cards de missão no dashboard
- Progress bar visual (0/1, 1/1)
- Animação + haptic ao completar
- Check mark com animação suave
- Sem pressão: "Não te preocupes se não completares todas."

---

## 5. Streaks

### Validação temporal

```php
// GamificationService::updateGentleStreak()
$lastActivity = $user->last_activity_at;
$today = now()->startOfDay();
$yesterday = now()->subDay()->startOfDay();

if ($lastActivity >= $yesterday && $lastActivity < $today) {
    // Dia consecutivo
    $user->current_streak++;
} elseif ($lastActivity < $yesterday) {
    // Dia(s) perdido(s) — reset gentil
    $user->current_streak = 1; // Não 0!
}
```

**Princípio Lumina:** O streak nunca vai a 0. Se o utilizador perde um dia, volta a 1. Sem mensagens de culpa, sem indicadores vermelhos.

### UI na app

- Ícone de chama com número: 🔥 5
- Animação suave ao incrementar
- Se reset: nenhuma notificação negativa, apenas mostra "1" sem drama
- Tooltip: "Estás numa sequência de 5 dias. Continua ao teu ritmo."

---

## 6. Achievements

### Condições de desbloqueio (server-side)

| Achievement | Código | Condição |
|------------|--------|---------|
| Guardian of Flame | `guardian-flame` | 100+ flames |
| First Voice | `voice-found` | Primeiro post criado |
| First Journal | `first-journal` | Primeira entrada de diário |
| Consistency 3 | `consistency-3` | Streak de 3 dias |
| Active Listener | `active-listener` | 10+ reações dadas |

### Achievement unlock flow

```
Server deteta condição cumprida
    │
    ▼
GamificationService::unlockAchievement()
    │
    ├── Verifica se já foi desbloqueado (guard)
    ├── user_achievements pivot insert
    └── Notificação (database + FCM)
    │
    ▼
App recebe achievement no response da ação
    ou via push notification
    │
    ▼
UI mostra celebração:
    - Dialog com badge animado (Lottie)
    - Confetti suave
    - Mensagem empática: "Desbloqueaste: Primeira Voz 🎉"
    - Haptic feedback
```

---

## 7. Flame levels — display na app

| Level | Range | Ícone | Cor |
|-------|-------|-------|-----|
| Spark | 0-49 | ✨ | Amber claro |
| Flame | 50-199 | 🔥 | Laranja |
| Bonfire | 200-499 | 🔥🔥 | Laranja intenso |
| Beacon | 500+ | 🌟 | Dourado |

- O nível é derivado localmente a partir de `flames` (para display rápido)
- Mas a contagem de flames é sempre server-authoritative
- Animação de transição entre níveis (Lottie ou Compose animation)

---

## 8. Gentle Challenge (Oferecer Apoio)

### Regras server-side
- Apenas users com 50+ flames podem enviar
- Rate limit: 1 por dia por destinatário
- O destinatário recebe notificação (sem obrigação de responder)

### Endpoint
- `POST /api/v1/users/{user}/gentle-challenge`
- Rate limited: `throttle:gamification`

### UI
- Botão visível apenas se user tem 50+ flames
- Dialog de confirmação: "Queres enviar um gesto de apoio a esta pessoa?"
- Feedback: "O teu gesto de apoio foi enviado. 💛"

---

## 9. Anti-cheat e edge cases

| Cenário | Proteção |
|---------|----------|
| User tenta enviar `flames` via API | Endpoint não existe. Flames são side-effect |
| User faz múltiplos POST diary no mesmo dia | Server valida: 1 entry por `log_date` |
| User manipula data do dispositivo | Server usa timestamps do servidor |
| User tenta desbloquear achievement via API | Não existe endpoint. Server verifica condições |
| User spamma reações para ganhar flames | Rate limiting: `content-creation` throttle |
| User cria e apaga posts repetidamente | Server pode contar deletes e ajustar |

---

## 10. Dados de gamificação na Room DB

```kotlin
@Entity(tableName = "missions")
data class MissionEntity(
    @PrimaryKey val id: Long,
    val type: String,
    val description: String,
    val progress: Int,
    val target: Int,
    val completedAt: String?,
    val cachedAt: Long // Para saber quando foi atualizado
)

// Gamification data lives in UserEntity
@Entity(tableName = "user_profile")
data class UserProfileEntity(
    @PrimaryKey val id: Long,
    val flames: Int,
    val flameLevel: String,
    val currentStreak: Int,
    // ...
)
```

**Cache strategy:** Missões e flames são atualizados sempre que o utilizador faz uma ação. O dashboard faz refresh ao abrir. Não é necessário polling separado.

---

## 11. Transformação de session flash para API response

### O problema

`session()->flash()` armazena dados na sessão para o **próximo** request. Em contexto web,
o controller faz `session()->flash('gamification.flames', 10)`, o browser faz redirect, e a
próxima view lê o valor.

Em contexto API, não há "próximo request". A sessão é stateless. O flash é escrito e
imediatamente descartado — o response da API não inclui nenhuma informação de gamificação.

**Resultado:** O user guarda uma entrada de diário via API, ganha +10 flames, mas a app
não sabe. Sem animação, sem feedback, sem atualização de UI.

### A solução: GamificationResult DTO

Modificar `trackAction()` para retornar um objeto que acumula todos os eventos:

```php
class GamificationResult {
    public int $flames_earned = 0;
    public int $total_flames;
    public string $flame_level;
    public int $current_streak;
    public ?array $achievement_unlocked = null;  // {name, icon, image, flames_reward}
    public ?string $mission_completed = null;     // título da missão
}
```

`trackAction()` muda de `void` para `GamificationResult`:
- `awardFlames()`: `$result->flames_earned += $amount`
- `unlockAchievement()`: `$result->achievement_unlocked = ['name' => ..., 'icon' => ..., ...]`
- `updateMissionProgress()` (completion): `$result->mission_completed = $mission->title`
- Final: `$result->total_flames = $user->flames`, `$result->flame_level = $user->bonfire_level`,
  `$result->current_streak = $user->current_streak`

### Backwards compatibility

**Manter `session()->flash()` ao lado do return value.** Os controllers web continuam a ler
da sessão. Os API controllers usam o return value. Zero breaking changes para a web.

```php
public function trackAction(User $user, string $actionType): GamificationResult
{
    $result = new GamificationResult();

    // ... lógica existente ...
    $this->awardFlames($user, $amount);
    session()->flash('gamification.flames', $amount);  // ← mantém para web
    $result->flames_earned += $amount;                  // ← novo para API

    // ... no final ...
    $result->total_flames = $user->flames;
    $result->flame_level = $user->bonfire_level;
    $result->current_streak = $user->current_streak;

    return $result;
}
```

### Envelope de resposta

Toda resposta de mutação que trigger gamificação inclui a key `gamification`:

```json
{
    "data": { "...diary entry..." },
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

Quando a ação não trigger gamificação (ex: GET requests, update de perfil):
`"gamification": null`

---

## 12. Gamificação por funcionalidade

Mapeamento de quais funcionalidades trigger gamificação e o que a API response inclui:

| Funcionalidade | Action type | Endpoint API | Flames | Mission? | Fase |
|---------------|-------------|-------------|--------|----------|------|
| Diário | `daily_log` | `POST /api/v1/diary` | +10 | Sim (`diary`) | 1 |
| Reação fórum | `reaction` | `POST /api/v1/posts/{id}/reactions` | +2 | Sim (`reaction`) | 2 |
| Comentário fórum | `reply` | `POST /api/v1/posts/{id}/comments` | +5 | Sim (`comment`) | 2 |
| Primeiro post | `first_post` | `POST /api/v1/posts` | +20 | Sim (`post`) | 2 |
| Respiração | `breathe` | `POST /api/v1/calm-zone/breathe` | +5 | Não | 1 |

**Nota:** Apenas 5 ações trigger gamificação. Todas as outras operações (update perfil,
settings, safety plan, etc.) não geram flames. O envelope `gamification` é `null` nestes casos.

---

## 13. Edge cases

### Timezone: missões e streaks

`assignDailyMissions()` e `updateGentleStreak()` usam `now()` e `Carbon::today()` — ambos
resolvem para o timezone do servidor (UTC ou configuração).

**Cenário:** User em UTC+1, server em UTC. User abre app às 23:30 hora local (00:30 UTC).
- **Missões:** Server vê 00:30 do dia seguinte → atribui missões do dia seguinte. User
  recebe "amanhã" antes do esperado.
- **Streaks:** User logou ontem às 22:00 local (23:00 UTC). Hoje loga às 23:30 local
  (00:30 UTC do dia seguinte). Server vê 2 dias consecutivos (dia N às 23:00, dia N+1
  às 00:30) → streak incrementa correctamente.

**Decisão:**
- **Fase 1:** Aceitar comportamento server-time. Documentar que missões e streaks resetam
  à meia-noite UTC. A maioria dos users portugueses (UTC/UTC+1) tem impacto mínimo.
- **Fase 2:** Aceitar timezone no header `X-Lumina-Timezone: Europe/Lisbon`. Server ajusta
  cálculos. Ref. [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) secção 14.

### Mission 23:59 — missões stale

Se user abre app às 23:59 server time, missões são atribuídas para hoje. Às 00:01, essas
missões são de "ontem" e o user deveria ter novas missões.

**Solução:** API response de missões inclui `assigned_date` no response (já presente no
pivot). A app Android verifica se `assigned_date == today`. Se não, refresh automático
do dashboard.

### GamificationService failure durante ação

Se `trackAction()` lança exception **após** a ação principal (ex: diary entry criada com
sucesso), o request inteiro falharia — user perde a diary entry por causa de um erro na
gamificação.

**Solução:** API controllers envolvem gamificação em `try/catch`:

```php
$entry = DailyLog::create([...]);

try {
    $gamification = app(GamificationService::class)->trackAction($user, 'daily_log');
} catch (\Throwable $e) {
    Log::error('Gamification failed', ['action' => 'daily_log', 'error' => $e->getMessage()]);
    $gamification = null;
}

return (new DailyLogResource($entry))
    ->additional(['gamification' => $gamification]);
```

A ação principal **nunca** falha por causa de gamificação. O envelope retorna
`gamification: null` se houve erro, e a app pode mostrar a diary entry sem animação de flames.

### Offline: dados de gamificação stale

Quando a app está offline, os dados de gamificação em Room DB podem estar desatualizados
(ex: flames de ontem, streak desatualizado).

**Comportamento:**
- Display valores cacheados com indicador visual discreto ("Última atualização: X min")
- Ao reconectar, dashboard refresh busca dados frescos
- Se flames mudaram (ex: sync de uma ação offline que gerou flames), animar o delta
  (ex: 132 → 142, animação de +10 flames)

Ref. [13-offline-sync.md](13-offline-sync.md) para estratégia completa de offline/sync.

### Breathing spam: abuso de flames

O endpoint `POST /api/v1/calm-zone/breathe` gera +5 flames sem input. Com rate limiter
`gamification` (5/min), um user pode gerar 25 flames/min.

**Solução:** Adicionar daily cap server-side: max 3 breathing rewards por dia.
Implementação: contar `AnalyticsEvent` do tipo `breathe_complete` para o dia atual.
Se ≥3, retornar sucesso mas `flames_earned: 0` no envelope.

---

## 14. Notificação de achievements via FCM

Quando `unlockAchievement()` desbloqueia um badge, a notificação pode chegar por 2 vias:

1. **Inline no response da ação:** O `GamificationResult` inclui `achievement_unlocked`
   no envelope — a app mostra celebração imediatamente.

2. **Via FCM push (se ação foi via web ou scheduled):** Para achievements desbloqueados por
   scheduled checks ou ações na web, FCM notifica a app.

**FCM payload structure:**
```json
{
    "notification": {
        "title": "Conquista Desbloqueada! 🎉",
        "body": "Desbloqueaste: Guardião da Chama"
    },
    "data": {
        "type": "achievement_unlocked",
        "achievement_slug": "guardian_flame",
        "achievement_name": "Guardião da Chama",
        "achievement_icon": "ri-fire-fill",
        "flames_reward": 15
    }
}
```

**Quiet hours:** Se user está em quiet hours, achievement é armazenado no canal `database`
apenas. Sem push FCM. O user vê quando abrir a app (via badge indicator ou achievement
list refresh).

**Deduplicação:** Se achievement chega via inline response E via FCM (race condition),
a app ignora duplicados verificando `achievement_slug` contra achievements já mostrados
na sessão.

---

## 15. Validação de endpoints que geram gamificação

### POST /api/v1/diary

| Campo | Regra | Notas |
|-------|-------|-------|
| mood_level | required, integer, between:1,5 | Escala emocional |
| tags | nullable, array, max:3 | Cada tag: string, max:30 |
| note | nullable, string, max:5000 | Texto livre |

**Restrição server-side:** 1 entrada por `log_date` por user. Se já existe entry para hoje,
retorna 409 Conflict. **Gamificação:** +10 flames + progress em missão `diary`.

### POST /api/v1/posts/{id}/reactions

| Campo | Regra | Notas |
|-------|-------|-------|
| type | required, string, in:hug,candle,ear | Emoji terapêuticos |

**Rate limited:** `content-creation` (5/min).
**Restrição:** 1 reação por tipo por user por post (toggle). Se já reagiu, remove reação.
Remove não gera flames negativas.
**Gamificação:** +2 flames + progress em missão `reaction` (apenas ao adicionar, não ao remover).

### POST /api/v1/calm-zone/breathe

| Campo | Regra | Notas |
|-------|-------|-------|
| (nenhum) | — | Endpoint sem body |

**Rate limited:** `gamification` (5/min) + daily cap (3 rewards/dia).
**Gamificação:** +5 flames. Sem missão associada.
Após 3 breathing rewards no dia: `flames_earned: 0` no envelope (ação registada mas sem reward).

---

## 16. Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-16-01 | Session flash falha silenciosamente em API — feedback de gamificação invisível para mobile | Alta | Crítico | Modificar `trackAction()` para retornar `GamificationResult` DTO. Prioridade: Fase 0 |
| RISK-16-02 | Timezone mismatch pode causar perda de streak injusta (user ativo mas streak reseta porque server vê "novo dia" antes) | Média | Médio | Fase 1: aceitar UTC. Fase 2: timezone no header. Comunicar ao user que streaks resetam à meia-noite UTC |
| RISK-16-03 | Exception em GamificationService falha todo o API request — user perde a ação (ex: diary entry) | Média | Alto | Wrap gamificação em try/catch. Ação principal nunca falha por causa de gamificação |
| RISK-16-04 | Breathing endpoint sem daily cap é abusável (5 flames/call × 5/min = 25 flames/min) | Média | Baixo | Daily cap de 3 breathing rewards. Server conta via AnalyticsEvent |
| RISK-16-05 | Achievement deduplicação: FCM e inline response podem entregar o mesmo achievement, causando duplo celebration UI | Baixa | Baixo | App verifica slug contra achievements já mostrados na sessão |

---

*Próximo: [17-estrategia-b2c-pro-corporate.md](17-estrategia-b2c-pro-corporate.md) — Estratégia B2C, PRO e Corporate no contexto Android.*
