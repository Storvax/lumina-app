<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Obter dados agregados do dashboard.
     * GET /api/v1/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        // Agregação de dados para o dashboard
        $dailyLogs = $user->dailyLogs()
            ->whereDate('log_date', today())
            ->first();

        $todayMood = $dailyLogs?->mood_level ?? null;

        $missions = $user->missions()
            ->wherePivot('completed_at', null)
            ->limit(5)
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'flames' => $user->flames,
                'current_streak' => $user->current_streak,
                'flame_level' => $user->flame_level,
            ],
            'today' => [
                'mood' => $todayMood,
                'diary_entry_count' => $user->dailyLogs()->whereDate('log_date', today())->count(),
            ],
            'missions_count' => $missions->count(),
            'achievements_unlocked' => $user->achievements()->count(),
            'greeting' => $this->getGreeting(),
        ], 200);
    }

    /**
     * Gerar mensagem de boas-vindas baseada na hora do dia.
     */
    private function getGreeting(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour < 12 => 'Bom dia! 🌅',
            $hour < 18 => 'Boa tarde! ☀️',
            default => 'Boa noite! 🌙',
        };
    }
}
