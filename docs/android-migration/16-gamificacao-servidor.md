# 16 — Gamificação e Validações Server-Side

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

*Próximo: [17-estrategia-b2c-pro-corporate.md](17-estrategia-b2c-pro-corporate.md) — Estratégia B2C, PRO e Corporate no contexto Android.*
