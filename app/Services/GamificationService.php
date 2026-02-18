<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Processa uma ação do utilizador e atribui recompensas.
     */
    public function trackAction(User $user, string $actionType)
    {
        // 1. Atualizar Streak (apenas uma vez por dia)
        $this->checkDailyStreak($user);

        // 2. Processar ação específica
        switch ($actionType) {
            case 'daily_log':
                $this->addFlames($user, 5); // 5 chamas por registo
                $this->checkCountAchievement($user, 'App\Models\DailyLog', 1, 'first-journal');
                $this->checkCountAchievement($user, 'App\Models\DailyLog', 7, 'journal-week');
                break;

            case 'first_post':
                $this->addFlames($user, 10); // 10 chamas por criar tópico
                $this->unlockAchievement($user, 'voice-found');
                break;

            case 'reply':
                $this->addFlames($user, 5); // 5 chamas por ajudar (comentário)
                $this->checkCountAchievement($user, 'App\Models\Comment', 1, 'first-help');
                break;
                
            case 'breathe':
                $this->addFlames($user, 5); // 5 chamas por respirar
                // Lógica para badge de respiração se quisermos adicionar depois
                break;
        }
    }

    /**
     * Adiciona chamas e notifica (flash session para o frontend).
     */
    private function addFlames(User $user, int $amount)
    {
        $user->increment('flames', $amount);
        session()->flash('gamification.flames', $amount);
    }

    /**
     * Verifica e atualiza o streak diário.
     */
    private function checkDailyStreak(User $user)
    {
        $now = now();
        
        if (!$user->last_activity_at) {
            $user->update(['current_streak' => 1, 'last_activity_at' => $now]);
            return;
        }

        // Se a última atividade foi ontem, aumenta streak
        if ($user->last_activity_at->isYesterday()) {
            $user->increment('current_streak');
            
            // Badges de Consistência
            if($user->current_streak == 3) $this->unlockAchievement($user, 'consistency-3');
            if($user->current_streak == 7) $this->unlockAchievement($user, 'consistency-7');
        } 
        // Se foi antes de ontem (quebrou streak), reseta, a menos que seja hoje
        elseif (!$user->last_activity_at->isToday()) {
            $user->update(['current_streak' => 1]);
        }

        $user->update(['last_activity_at' => $now]);
    }

    /**
     * Desbloqueia um badge específico.
     */
    private function unlockAchievement(User $user, string $slug)
    {
        $achievement = Achievement::where('slug', $slug)->first();

        if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
            $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
            
            // Bónus de chamas do badge
            $user->increment('flames', $achievement->flames_reward);
            
            // Notificação visual
            session()->flash('gamification.badge', [
                'name' => $achievement->name,
                'icon' => $achievement->icon,
                'image' => $achievement->image ?? null, // Opcional
            ]);
        }
    }

    /**
     * Helper para verificar contagens (ex: 10º post).
     */
    private function checkCountAchievement(User $user, string $modelClass, int $targetCount, string $badgeSlug)
    {
        // Conta quantos registos o user tem neste modelo
        $count = $modelClass::where('user_id', $user->id)->count();
        
        if ($count >= $targetCount) {
            $this->unlockAchievement($user, $badgeSlug);
        }
    }
}