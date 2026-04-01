<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DailyLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MoodTrendService
{
    /**
     * Períodos disponíveis para análise de tendências (em dias).
     */
    private const PERIODS = [7, 30, 90];

    /**
     * Janela de dias usada para calcular a média móvel simples.
     */
    private const MOVING_AVG_WINDOW = 3;

    /**
     * Devolve os dados de tendência completos para o dashboard.
     * Cache de 6h por utilizador — atualizado quando o utilizador escreve no diário.
     */
    public function getDashboardData(User $user): array
    {
        return Cache::remember("mood_trend:{$user->id}", 21600, function () use ($user) {
            $data = [];

            foreach (self::PERIODS as $days) {
                $logs = $this->fetchLogs($user, $days);
                $series = $this->buildDailySeries($logs, $days);

                $data["period_{$days}"] = [
                    'labels'      => $series->pluck('date'),
                    'values'      => $series->pluck('value'),
                    'moving_avg'  => $this->movingAverage($series->pluck('value')->toArray()),
                    'average'     => $this->average($series),
                    'trend'       => $this->detectTrend($series),
                    'filled_days' => $logs->count(),
                ];
            }

            $data['alert'] = $this->buildProactiveAlert($user);

            return $data;
        });
    }

    /**
     * Invalida o cache quando o utilizador registar um novo log.
     * Deve ser chamado em DailyLogController após store/update.
     */
    public function invalidateCache(User $user): void
    {
        Cache::forget("mood_trend:{$user->id}");
    }

    /**
     * Obtém os registos diários do utilizador para o período solicitado.
     */
    private function fetchLogs(User $user, int $days): Collection
    {
        return DailyLog::where('user_id', $user->id)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1)->toDateString())
            ->orderBy('log_date')
            ->get(['log_date', 'mood_level']);
    }

    /**
     * Constrói séries completas com null nos dias sem registo.
     * Dias em branco são excluídos da média mas mantidos no gráfico para contexto visual.
     */
    private function buildDailySeries(Collection $logs, int $days): Collection
    {
        $indexed = $logs->keyBy(fn ($log) => Carbon::parse($log->log_date)->toDateString());

        $series = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date    = Carbon::today()->subDays($i)->toDateString();
            $log     = $indexed->get($date);
            $series->push([
                'date'  => Carbon::parse($date)->format('d/m'),
                'value' => $log ? (int) $log->mood_level : null,
            ]);
        }

        return $series;
    }

    /**
     * Calcula média móvel simples para suavizar variações de curto prazo.
     * Posições com null (sem registo) são ignoradas na janela.
     *
     * @param  array<int|null>  $values
     * @return array<float|null>
     */
    private function movingAverage(array $values): array
    {
        $result = [];
        $count  = count($values);
        $window = self::MOVING_AVG_WINDOW;

        for ($i = 0; $i < $count; $i++) {
            $slice  = array_slice($values, max(0, $i - $window + 1), min($window, $i + 1));
            $filled = array_filter($slice, fn ($v) => $v !== null);

            $result[] = count($filled) > 0
                ? round(array_sum($filled) / count($filled), 2)
                : null;
        }

        return $result;
    }

    /**
     * Média dos dias com registo no período.
     */
    private function average(Collection $series): ?float
    {
        $filled = $series->filter(fn ($item) => $item['value'] !== null)->pluck('value');

        return $filled->isEmpty() ? null : round($filled->average(), 2);
    }

    /**
     * Detecta tendência nos últimos 7 dias da série:
     * - "improving" se a segunda metade tem média superior à primeira
     * - "declining" se a segunda metade tem média inferior à primeira
     * - "stable" caso contrário
     *
     * Apenas usado para gerar o aviso proativo.
     */
    private function detectTrend(Collection $series): string
    {
        $filled = $series->filter(fn ($item) => $item['value'] !== null)->pluck('value')->values();

        if ($filled->count() < 4) {
            return 'insufficient_data';
        }

        $half    = (int) floor($filled->count() / 2);
        $first   = $filled->slice(0, $half)->average();
        $second  = $filled->slice($half)->average();
        $delta   = $second - $first;

        if ($delta >= 0.5) return 'improving';
        if ($delta <= -0.5) return 'declining';

        return 'stable';
    }

    /**
     * Gera alerta proativo quando o humor está em queda há 3+ dias consecutivos.
     * Só é ativo se os últimos 3 registos tiverem humor ≤ 2.
     */
    private function buildProactiveAlert(User $user): ?array
    {
        $recentLogs = DailyLog::where('user_id', $user->id)
            ->orderBy('log_date', 'desc')
            ->take(3)
            ->pluck('mood_level');

        if ($recentLogs->count() < 3) {
            return null;
        }

        $allLow = $recentLogs->every(fn ($mood) => $mood <= 2);

        if (!$allLow) {
            return null;
        }

        return [
            'type'    => 'declining_streak',
            'message' => 'Notámos que os teus últimos dias têm sido difíceis. Não estás sozinho/a.',
            'cta'     => 'Falar com um Ouvinte',
            'route'   => 'buddy.request',
        ];
    }
}
