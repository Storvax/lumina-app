<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Gere o progresso do utilizador de forma terapêutica e não punitiva.
 * Integra a lógica de missões diárias sem pressionar o utilizador.
 */
class GamificationService
{
    private const REWARDS = [
        'daily_log' => 10,
        'reaction' => 2,
        'reply' => 5,
        'breathe' => 5,
        'first_post' => 20,
    ];

    /**
     * Processa uma ação do utilizador, atribui chamas, atualiza a consistência
     * terapêutica e progride as missões ativas.
     */
    public function trackAction(User $user, string $actionType): void
    {
        // 1. Atualizar Progresso das Missões Diárias
        $this->updateMissionProgress($user, $actionType);

        // 2. Recompensas fixas (ação genérica)
        if (array_key_exists($actionType, self::REWARDS)) {
            $this->awardFlames($user, self::REWARDS[$actionType]);
            
            // O Diário dita o ritmo da Fogueira (Streak)
            if ($actionType === 'daily_log') {
                $this->updateGentleStreak($user);
            }
        }

        // 3. Verificar marcos de longo prazo e conquistas
        $this->checkMilestones($user);
    }

    /**
     * Adiciona chamas de forma silenciosa ou via flash session.
     */
    private function awardFlames(User $user, int $amount): void
    {
        $user->increment('flames', $amount);
        session()->flash('gamification.flames', $amount);
    }

    /**
     * Atualiza os dias seguidos de forma terapêutica.
     * Se falhou um dia, recomeça sem culpa e sem alertas a vermelho.
     */
    private function updateGentleStreak(User $user): void
    {
        $lastActivity = $user->last_activity_at ? Carbon::parse($user->last_activity_at) : null;
        $today = Carbon::today();

        if (!$lastActivity) {
            $user->current_streak = 1;
        } elseif ($lastActivity->isYesterday()) {
            $user->current_streak += 1;
        } elseif (!$lastActivity->isToday()) {
            // Recomeço sem culpa: a vida acontece.
            $user->current_streak = 1;
        }

        $user->last_activity_at = now();
        $user->save();
    }

    /**
     * Verifica e desbloqueia conquistas contextuais baseadas em tempo/presença.
     */
    private function checkMilestones(User $user): void
    {
        // Exemplo: Desbloqueia conquista "Guardião da Chama" aos 100 pontos
        if ($user->flames >= 100) {
            $this->unlockAchievement($user, 'guardian_flame'); 
        }

        // Exemplo: 7 dias de autocuidado contínuo
        if ($user->current_streak === 7) {
            $this->unlockAchievement($user, 'seven_days_peace');
        }
    }

    /**
     * Desbloqueia um badge específico e notifica a UI.
     */
    private function unlockAchievement(User $user, string $slug): void
    {
        // Verifica por 'slug' ou 'code' consoante a estrutura da tua BD
        $achievement = Achievement::where('slug', $slug)->orWhere('code', $slug)->first();

        if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
            $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
            
            // Bónus de chamas do badge
            if ($achievement->flames_reward) {
                $user->increment('flames', $achievement->flames_reward);
            }
            
            // Notificação visual para o Front-end
            session()->flash('gamification.badge', [
                'name' => $achievement->name,
                'icon' => $achievement->icon,
                'image' => $achievement->image ?? null,
            ]);
        }
    }

    /**
     * Atribui 3 missões diárias ao utilizador, se ainda não as tiver hoje.
     * Utilizado pelo DashboardController.
     */
    public function assignDailyMissions(User $user): void
    {
        $today = now()->toDateString();
        $hasMissions = $user->missions()->wherePivot('assigned_date', $today)->exists();

        // Se já tem missões hoje, não faz nada
        if ($hasMissions) return;

        // Se não tem, vai buscar 3 aleatórias e atribui
        $missions = \App\Models\Mission::inRandomOrder()->limit(3)->get();
        
        foreach ($missions as $mission) {
            $user->missions()->attach($mission->id, [
                'assigned_date' => $today,
                'progress' => 0
            ]);
        }
    }

    /**
     * Atualiza o progresso das missões ativas baseadas na ação realizada.
     */
    private function updateMissionProgress(User $user, string $actionType): void
    {
        $today = now()->toDateString();

        // Vai buscar as missões de hoje que correspondem a esta ação e que ainda não estão concluídas
        $activeMissions = $user->missions()
            ->wherePivot('assigned_date', $today)
            ->whereNull('mission_user.completed_at')
            ->where('action_type', $actionType)
            ->get();

        foreach ($activeMissions as $mission) {
            $newProgress = $mission->pivot->progress + 1;
            
            // Atualiza o progresso na Base de Dados
            $user->missions()->updateExistingPivot($mission->id, ['progress' => $newProgress]);

            // Se atingiu o objetivo, conclui a missão
            if ($newProgress >= $mission->target_count) {
                $user->missions()->updateExistingPivot($mission->id, ['completed_at' => now()]);
                
                // Dá a recompensa da missão
                $this->awardFlames($user, $mission->flames_reward);
                
                // Dispara o Toast de "Missão Concluída"
                session()->flash('gamification.mission', $mission->title);
            }
        }
    }
}