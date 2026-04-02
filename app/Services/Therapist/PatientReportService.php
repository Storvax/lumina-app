<?php

declare(strict_types=1);

namespace App\Services\Therapist;

use App\Models\DailyLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PatientReportService
{
    /**
     * Agrega os dados de progresso de um paciente num único relatório.
     * Cache de 1h — invalida quando o paciente guarda um novo registo no diário.
     */
    public function generate(User $patient, int $days = 30): array
    {
        return Cache::remember("patient_report:{$patient->id}:{$days}", 3600, function () use ($patient, $days) {
            $logs = $this->fetchLogs($patient, $days);

            return [
                'mood_summary'   => $this->moodSummary($logs),
                'mood_series'    => $this->moodSeries($logs, $days),
                'frequency'      => $this->frequency($logs, $days),
                'streak'         => $patient->current_streak ?? 0,
                'crisis_alerts'  => $this->crisisAlerts($logs),
                'tag_frequency'  => $this->tagFrequency($logs),
                'engagement'     => $this->engagementScore($logs, $days),
            ];
        });
    }

    public function invalidateCache(User $patient): void
    {
        foreach ([7, 30, 90] as $days) {
            Cache::forget("patient_report:{$patient->id}:{$days}");
        }
    }

    private function fetchLogs(User $patient, int $days): Collection
    {
        return DailyLog::where('user_id', $patient->id)
            ->where('log_date', '>=', Carbon::today()->subDays($days - 1)->toDateString())
            ->orderBy('log_date')
            ->get();
    }

    /**
     * Estatísticas agregadas de humor para o período.
     */
    private function moodSummary(Collection $logs): array
    {
        if ($logs->isEmpty()) {
            return ['avg' => null, 'min' => null, 'max' => null, 'trend' => 'insufficient_data'];
        }

        $moods = $logs->pluck('mood_level');
        $avg   = round($moods->average(), 2);

        // Tendência: compara a segunda metade do período com a primeira
        $half   = (int) floor($moods->count() / 2);
        $trend  = 'stable';
        if ($half >= 2) {
            $firstHalf  = $moods->slice(0, $half)->average();
            $secondHalf = $moods->slice($half)->average();
            $delta = $secondHalf - $firstHalf;
            if ($delta >= 0.5) $trend = 'improving';
            elseif ($delta <= -0.5) $trend = 'declining';
        }

        return [
            'avg'   => $avg,
            'min'   => $moods->min(),
            'max'   => $moods->max(),
            'trend' => $trend,
        ];
    }

    /**
     * Série diária de humor para gráfico de linha (inclui null em dias sem registo).
     */
    private function moodSeries(Collection $logs, int $days): array
    {
        $indexed = $logs->keyBy(fn ($log) => Carbon::parse($log->log_date)->toDateString());

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date    = Carbon::today()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('d/m');
            $values[] = isset($indexed[$date]) ? (int) $indexed[$date]->mood_level : null;
        }

        return compact('labels', 'values');
    }

    /**
     * Taxa de preenchimento do diário no período.
     */
    private function frequency(Collection $logs, int $days): array
    {
        $filled = $logs->count();

        return [
            'filled_days'  => $filled,
            'total_days'   => $days,
            'rate_percent' => $days > 0 ? round(($filled / $days) * 100) : 0,
        ];
    }

    /**
     * Dias com humor ≤ 2 — sinais de crise para atenção clínica.
     */
    private function crisisAlerts(Collection $logs): array
    {
        $lowDays = $logs->filter(fn ($log) => $log->mood_level <= 2);

        return [
            'count' => $lowDays->count(),
            'dates' => $lowDays->pluck('log_date')
                ->map(fn ($d) => Carbon::parse($d)->format('d/m'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Tags mais frequentes nos registos do período.
     * Útil para identificar padrões emocionais recorrentes.
     */
    private function tagFrequency(Collection $logs): array
    {
        $allTags = $logs->flatMap(fn ($log) => $log->tags ?? []);

        return $allTags
            ->countBy()
            ->sortDesc()
            ->take(8)
            ->toArray();
    }

    /**
     * Score de envolvimento (0–100) baseado na frequência de registos.
     */
    private function engagementScore(Collection $logs, int $days): int
    {
        if ($days === 0) return 0;

        return (int) min(100, round(($logs->count() / $days) * 100));
    }
}
