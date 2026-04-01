<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DailyLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CorporateAnalyticsService
{
    /**
     * Limiar de privacidade: número mínimo de utilizadores ativos para
     * calcular métricas — impede identificação individual.
     */
    private const PRIVACY_THRESHOLD = 5;

    /**
     * Gera o relatório corporativo completo para o dashboard B2B.
     * Cache de 4h por empresa — dados de HR não mudam com frequência.
     */
    public function generateReport(int $companyId, int $days = 30): array
    {
        return Cache::remember("corporate_report:{$companyId}:{$days}", 14400, function () use ($companyId, $days) {
            $companyUserIds = User::where('company_id', $companyId)->pluck('id');
            $totalEmployees = $companyUserIds->count();
            $since = Carbon::today()->subDays($days - 1)->toDateString();

            $activeUserIds = DailyLog::whereIn('user_id', $companyUserIds)
                ->where('log_date', '>=', $since)
                ->distinct('user_id')
                ->pluck('user_id');

            $activeCount = $activeUserIds->count();

            if ($activeCount < self::PRIVACY_THRESHOLD) {
                return [
                    'insufficient_data' => true,
                    'active_count'      => $activeCount,
                    'total_employees'   => $totalEmployees,
                ];
            }

            $logs = DailyLog::whereIn('user_id', $activeUserIds)
                ->where('log_date', '>=', $since)
                ->get(['user_id', 'mood_level', 'tags', 'log_date']);

            return [
                'insufficient_data'   => false,
                'total_employees'     => $totalEmployees,
                'active_count'        => $activeCount,
                'adoption_rate'       => $this->adoptionRate($activeCount, $totalEmployees),
                'mood_distribution'   => $this->moodDistribution($logs),
                'mood_trend_weekly'   => $this->moodTrendWeekly($activeUserIds, $days),
                'burnout'             => $this->burnoutMetrics($logs, $activeUserIds, $since, $activeCount),
                'top_tags'            => $this->topTags($logs),
                'return_rate'         => $this->returnRate($companyUserIds, $since),
                'benchmark'           => $this->platformBenchmark($since),
            ];
        });
    }

    public function invalidateCache(int $companyId): void
    {
        foreach ([7, 30, 90] as $days) {
            Cache::forget("corporate_report:{$companyId}:{$days}");
        }
    }

    /**
     * Taxa de adoção: % de colaboradores que usam o diário no período.
     */
    private function adoptionRate(int $active, int $total): int
    {
        return $total > 0 ? (int) round(($active / $total) * 100) : 0;
    }

    /**
     * Distribuição de humor agregada por nível (1–5).
     */
    private function moodDistribution(Collection $logs): array
    {
        return $logs->groupBy('mood_level')
            ->map(fn ($g) => $g->count())
            ->sortKeys()
            ->toArray();
    }

    /**
     * Média semanal de humor dos últimos N semanas (máximo 12).
     * Agrega por semana para proteger privacidade individual.
     *
     * @return array{labels: string[], values: float[]}
     */
    private function moodTrendWeekly(Collection $userIds, int $days): array
    {
        $weeks = (int) ceil($days / 7);
        $labels = [];
        $values = [];

        for ($w = $weeks - 1; $w >= 0; $w--) {
            $start = Carbon::today()->subWeeks($w + 1)->startOfWeek()->toDateString();
            $end   = Carbon::today()->subWeeks($w)->endOfWeek()->toDateString();

            $avg = DailyLog::whereIn('user_id', $userIds)
                ->whereBetween('log_date', [$start, $end])
                ->avg('mood_level');

            $labels[] = 'Sem ' . Carbon::parse($start)->format('d/m');
            $values[]  = $avg ? round((float) $avg, 2) : null;
        }

        return compact('labels', 'values');
    }

    /**
     * Métricas de risco de burnout: utilizadores com média ≤ 2.0 no período.
     */
    private function burnoutMetrics(Collection $logs, Collection $activeUserIds, string $since, int $activeCount): array
    {
        $perUser = $logs->groupBy('user_id')
            ->map(fn ($g) => $g->avg('mood_level'));

        $burnoutCount = $perUser->filter(fn ($avg) => $avg <= 2.0)->count();

        return [
            'count'      => $burnoutCount,
            'percentage' => $activeCount > 0 ? (int) round(($burnoutCount / $activeCount) * 100) : 0,
        ];
    }

    /**
     * As 8 tags mais frequentes nos registos da empresa no período.
     */
    private function topTags(Collection $logs): array
    {
        $counts = [];
        foreach ($logs as $log) {
            foreach ($log->tags ?? [] as $tag) {
                $counts[$tag] = ($counts[$tag] ?? 0) + 1;
            }
        }
        arsort($counts);
        return array_slice($counts, 0, 8, true);
    }

    /**
     * Taxa de retorno: % de colaboradores que usaram o diário em 2+ semanas do período.
     * Indica engajamento sustentado, não apenas adoção pontual.
     */
    private function returnRate(Collection $companyUserIds, string $since): int
    {
        $multiWeekUsers = DailyLog::whereIn('user_id', $companyUserIds)
            ->where('log_date', '>=', $since)
            ->selectRaw('user_id, COUNT(DISTINCT strftime(\'%Y-%W\', log_date)) as week_count')
            ->groupBy('user_id')
            ->havingRaw('week_count >= 2')
            ->count();

        $total = $companyUserIds->count();

        return $total > 0 ? (int) round(($multiWeekUsers / $total) * 100) : 0;
    }

    /**
     * Benchmark anónimo de plataforma: média de humor de TODAS as empresas
     * no mesmo período, sem expor dados individuais.
     * Permite à empresa comparar o seu clima com o mercado.
     */
    private function platformBenchmark(string $since): ?float
    {
        return Cache::remember("platform_benchmark:{$since}", 86400, function () use ($since) {
            $avg = DailyLog::where('log_date', '>=', $since)->avg('mood_level');
            return $avg ? round((float) $avg, 2) : null;
        });
    }
}
