<?php

declare(strict_types=1);

namespace App\Enums;

enum CommunityTemperature: string
{
    case Green  = 'green';
    case Yellow = 'yellow';
    case Red    = 'red';

    /**
     * Determina a temperatura com base na percentagem de publicações de alto risco
     * nas últimas 24 horas. Limiares definidos com a equipa clínica.
     */
    public static function fromCrisisPercentage(float $percentage): self
    {
        return match(true) {
            $percentage >= 0.20 => self::Red,
            $percentage >= 0.10 => self::Yellow,
            default             => self::Green,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Green  => 'Calma',
            self::Yellow => 'Alerta',
            self::Red    => 'Crítica',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Green  => 'teal',
            self::Yellow => 'amber',
            self::Red    => 'rose',
        };
    }
}
