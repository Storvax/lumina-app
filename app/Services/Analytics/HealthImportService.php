<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\HealthMetric;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Processa ficheiros CSV e JSON de wearables (Apple Health, Google Fit, formato genérico)
 * e persiste as métricas normalizadas na tabela health_metrics.
 */
class HealthImportService
{
    /** Tipos de métricas aceites. */
    private const VALID_TYPES = ['heart_rate', 'sleep_hours', 'steps', 'hrv'];

    /**
     * Mapeamento de aliases comuns (Apple Health, Google Fit) para os tipos internos.
     */
    private const TYPE_ALIASES = [
        'heartrate'               => 'heart_rate',
        'heart rate'              => 'heart_rate',
        'hkquantitytypeidentifierheartrate' => 'heart_rate',
        'sleep'                   => 'sleep_hours',
        'sleep hours'             => 'sleep_hours',
        'hkcategoryvaluesleepasleep' => 'sleep_hours',
        'step count'              => 'steps',
        'stepcount'               => 'steps',
        'steps count'             => 'steps',
        'hkquantitytypeidentifierstepcount' => 'steps',
        'heart rate variability'  => 'hrv',
        'hrv'                     => 'hrv',
        'hkquantitytypeidentifierheartratevariadabilitysdnn' => 'hrv',
    ];

    /**
     * Lê um ficheiro CSV e devolve as linhas normalizadas.
     * Suporta o formato genérico (date, type, value) e aliases de wearables.
     *
     * @return Collection<int, array{date: string, type: string, value: float}>
     * @throws \InvalidArgumentException Se o CSV não tiver cabeçalhos reconhecíveis.
     */
    public function parseCSV(string $content): Collection
    {
        $lines = array_filter(explode("\n", trim($content)));
        if (count($lines) < 2) {
            throw new \InvalidArgumentException('O ficheiro CSV está vazio ou não tem dados.');
        }

        $headers = array_map(
            fn (string $h) => strtolower(trim($h, " \t\r\n\x00\x0B\"")),
            str_getcsv(array_shift($lines))
        );

        $dateCol  = $this->findColumn($headers, ['date', 'startdate', 'start date', 'data']);
        $typeCol  = $this->findColumn($headers, ['type', 'metric', 'tipo', 'metrica']);
        $valueCol = $this->findColumn($headers, ['value', 'valor', 'average heart rate (bpm)', 'step count']);

        if ($dateCol === null || $typeCol === null || $valueCol === null) {
            throw new \InvalidArgumentException(
                'Formato não reconhecido. O CSV deve ter as colunas: date, type, value.'
            );
        }

        $rows = collect();

        foreach ($lines as $line) {
            $cols = str_getcsv(trim($line));
            if (count($cols) <= max($dateCol, $typeCol, $valueCol)) {
                continue;
            }

            $rawDate  = trim($cols[$dateCol], " \t\r\n\"");
            $rawType  = strtolower(trim($cols[$typeCol], " \t\r\n\""));
            $rawValue = trim($cols[$valueCol], " \t\r\n\"");

            try {
                $date = Carbon::parse($rawDate)->toDateString();
            } catch (\Exception) {
                continue; // Linha com data inválida — ignorar
            }

            $type  = $this->resolveType($rawType);
            $value = (float) str_replace(',', '.', $rawValue);

            if ($type === null || $value <= 0) {
                continue;
            }

            $rows->push(['date' => $date, 'type' => $type, 'value' => $value]);
        }

        return $rows;
    }

    /**
     * Lê um JSON array e devolve as linhas normalizadas.
     * Formato esperado: [{"date":"2024-01-01","type":"sleep_hours","value":7.5}]
     *
     * @return Collection<int, array{date: string, type: string, value: float}>
     * @throws \InvalidArgumentException Se o JSON for inválido.
     */
    public function parseJSON(string $content): Collection
    {
        $data = json_decode($content, true);

        if (! is_array($data)) {
            throw new \InvalidArgumentException('O ficheiro JSON é inválido ou não é um array.');
        }

        $rows = collect();

        foreach ($data as $item) {
            if (! isset($item['date'], $item['type'], $item['value'])) {
                continue;
            }

            try {
                $date = Carbon::parse($item['date'])->toDateString();
            } catch (\Exception) {
                continue;
            }

            $type  = $this->resolveType(strtolower((string) $item['type']));
            $value = (float) $item['value'];

            if ($type === null || $value <= 0) {
                continue;
            }

            $rows->push(['date' => $date, 'type' => $type, 'value' => $value]);
        }

        return $rows;
    }

    /**
     * Persiste as linhas na tabela health_metrics usando upsert para evitar duplicados.
     * Devolve o número de registos inseridos/atualizados.
     */
    public function store(User $user, Collection $rows, string $source): int
    {
        if ($rows->isEmpty()) {
            return 0;
        }

        $records = $rows->map(fn (array $row) => [
            'user_id'     => $user->id,
            'metric_date' => $row['date'],
            'metric_type' => $row['type'],
            'value'       => $row['value'],
            'source'      => $source,
            'created_at'  => now(),
            'updated_at'  => now(),
        ])->all();

        // upsert: se a combinação user/data/tipo já existir, atualiza o valor
        HealthMetric::upsert(
            $records,
            ['user_id', 'metric_date', 'metric_type'],
            ['value', 'source', 'updated_at']
        );

        return count($records);
    }

    // ─── Auxiliares ──────────────────────────────────────────────────────────

    private function findColumn(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $candidate) {
            $idx = array_search($candidate, $headers, true);
            if ($idx !== false) {
                return (int) $idx;
            }
        }
        return null;
    }

    private function resolveType(string $raw): ?string
    {
        if (in_array($raw, self::VALID_TYPES, true)) {
            return $raw;
        }

        return self::TYPE_ALIASES[$raw] ?? null;
    }
}
