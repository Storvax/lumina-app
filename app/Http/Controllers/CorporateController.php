<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CorporateController extends Controller
{
    /**
     * Dashboard corporativo com métricas anónimas agregadas.
     *
     * Respeita o limiar de privacidade B2B: se a empresa tiver < 5
     * utilizadores com registos no período, não calcula nem exibe dados,
     * para impedir a identificação individual.
     */
    public function dashboard()
    {
        $company = Auth::user()->company;
        $companyUserIds = User::where('company_id', $company->id)->pluck('id');
        $totalEmployees = $companyUserIds->count();

        $thirtyDaysAgo = now()->subDays(30)->toDateString();

        // Utilizadores que registaram pelo menos 1 log nos últimos 30 dias
        $activeUserIds = DailyLog::whereIn('user_id', $companyUserIds)
            ->where('log_date', '>=', $thirtyDaysAgo)
            ->distinct('user_id')
            ->pluck('user_id');

        $activeCount = $activeUserIds->count();

        // Limiar de privacidade: < 5 registos ativos impede análise agregada
        if ($activeCount < 5) {
            return view('corporate.dashboard', [
                'company' => $company,
                'insufficient_data' => true,
                'active_count' => $activeCount,
                'total_employees' => $totalEmployees,
            ]);
        }

        // Taxa de adoção: percentagem de colaboradores que usam o diário
        $adoptionRate = round(($activeCount / max($totalEmployees, 1)) * 100);

        // Distribuição de humor agregada (anónima)
        $logs = DailyLog::whereIn('user_id', $activeUserIds)
            ->where('log_date', '>=', $thirtyDaysAgo)
            ->get(['mood_level', 'tags']);

        $moodDistribution = $logs->groupBy('mood_level')
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        // Alerta de burnout: % de utilizadores com média de humor <= 2.0
        $userAvgMoods = $logs->groupBy(fn ($log) => 'u') // Agrupar sem expor user_id
            ->map(fn ($group) => $group->avg('mood_level'));

        // Calcular por utilizador real para burnout
        $perUserMoods = DailyLog::whereIn('user_id', $activeUserIds)
            ->where('log_date', '>=', $thirtyDaysAgo)
            ->selectRaw('user_id, AVG(mood_level) as avg_mood')
            ->groupBy('user_id')
            ->pluck('avg_mood', 'user_id');

        $burnoutCount = $perUserMoods->filter(fn ($avg) => $avg <= 2.0)->count();
        $burnoutPercentage = round(($burnoutCount / max($activeCount, 1)) * 100);

        // Tags mais frequentes (indicador de temas predominantes)
        $tagCounts = [];
        foreach ($logs as $log) {
            foreach ($log->tags ?? [] as $tag) {
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }
        }
        arsort($tagCounts);
        $topTags = array_slice($tagCounts, 0, 5, true);

        return view('corporate.dashboard', [
            'company' => $company,
            'insufficient_data' => false,
            'total_employees' => $totalEmployees,
            'active_count' => $activeCount,
            'adoption_rate' => $adoptionRate,
            'mood_distribution' => $moodDistribution,
            'burnout_percentage' => $burnoutPercentage,
            'burnout_count' => $burnoutCount,
            'top_tags' => $topTags,
        ]);
    }
}
