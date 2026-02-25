<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfAssessment extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'answers',
        'total_score',
        'severity',
    ];

    protected $casts = [
        'answers' => 'array',
        'total_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Questionário PHQ-9 — 9 perguntas validadas para rastreio de depressão.
     * Tradução oficial para PT-PT (domínio público).
     */
    public static function phq9Questions(): array
    {
        return [
            'Pouco interesse ou prazer em fazer coisas',
            'Sentir-se em baixo, deprimido(a) ou sem esperança',
            'Dificuldade em adormecer, em manter o sono, ou dormir demais',
            'Sentir-se cansado(a) ou com pouca energia',
            'Falta de apetite ou comer em excesso',
            'Sentir-se mal consigo próprio(a), sentir que é um fracasso ou que desiludiu a família',
            'Dificuldade em concentrar-se, por exemplo, a ler ou a ver televisão',
            'Mover-se ou falar tão devagar que as outras pessoas notam. Ou o contrário: estar tão agitado(a) que anda de um lado para o outro mais do que o habitual',
            'Pensamentos de que seria melhor estar morto(a) ou de se magoar de alguma forma',
        ];
    }

    /**
     * Questionário GAD-7 — 7 perguntas validadas para rastreio de ansiedade.
     * Tradução oficial para PT-PT (domínio público).
     */
    public static function gad7Questions(): array
    {
        return [
            'Sentir-se nervoso(a), ansioso(a) ou muito tenso(a)',
            'Não ser capaz de parar ou controlar as preocupações',
            'Preocupar-se demasiado com coisas diferentes',
            'Dificuldade em relaxar',
            'Estar tão agitado(a) que se torna difícil ficar parado(a)',
            'Ficar facilmente irritado(a) ou aborrecido(a)',
            'Sentir medo, como se algo terrível pudesse acontecer',
        ];
    }

    /** Opções de resposta (iguais para ambos os questionários) */
    public static function answerOptions(): array
    {
        return [
            0 => 'Nunca',
            1 => 'Vários dias',
            2 => 'Mais de metade dos dias',
            3 => 'Quase todos os dias',
        ];
    }

    /** Calcula a severidade com base no score total e no tipo de questionário */
    public static function calculateSeverity(string $type, int $score): string
    {
        if ($type === 'phq9') {
            return match (true) {
                $score <= 4  => 'minimal',
                $score <= 9  => 'mild',
                $score <= 14 => 'moderate',
                $score <= 19 => 'moderately_severe',
                default      => 'severe',
            };
        }

        // GAD-7
        return match (true) {
            $score <= 4  => 'minimal',
            $score <= 9  => 'mild',
            $score <= 14 => 'moderate',
            default      => 'severe',
        };
    }

    /** Tradução da severidade para PT-PT */
    public static function severityLabel(string $severity): string
    {
        return match ($severity) {
            'minimal'           => 'Mínima',
            'mild'              => 'Ligeira',
            'moderate'          => 'Moderada',
            'moderately_severe' => 'Moderadamente grave',
            'severe'            => 'Grave',
            default             => $severity,
        };
    }

    /** Cor associada à severidade para a UI */
    public static function severityColor(string $severity): string
    {
        return match ($severity) {
            'minimal'           => 'teal',
            'mild'              => 'amber',
            'moderate'          => 'orange',
            'moderately_severe' => 'rose',
            'severe'            => 'red',
            default             => 'slate',
        };
    }
}
