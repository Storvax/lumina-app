<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Métrica de saúde importada via CSV/JSON de wearables (Apple Health, Google Fit, etc.).
 */
class HealthMetric extends Model
{
    protected $fillable = [
        'user_id',
        'metric_date',
        'metric_type',
        'value',
        'source',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'value'       => 'float',
    ];

    /** Etiquetas legíveis para cada tipo de métrica. */
    public const TYPES = [
        'heart_rate'  => 'Frequência Cardíaca (bpm)',
        'sleep_hours' => 'Sono (horas)',
        'steps'       => 'Passos',
        'hrv'         => 'Variabilidade Cardíaca (ms)',
    ];

    /** Ícones remixicon para cada tipo. */
    public const ICONS = [
        'heart_rate'  => 'ri-heart-pulse-line',
        'sleep_hours' => 'ri-moon-line',
        'steps'       => 'ri-walk-line',
        'hrv'         => 'ri-bar-chart-2-line',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
