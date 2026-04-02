<?php

declare(strict_types=1);

namespace App\Enums;

enum RiskLevel: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';

    /** Nível mínimo que activa notificação para moderadores. */
    public function requiresModeratorAlert(): bool
    {
        return $this === self::High;
    }

    public function label(): string
    {
        return match($this) {
            self::Low    => 'Baixo',
            self::Medium => 'Médio',
            self::High   => 'Alto',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low    => 'teal',
            self::Medium => 'amber',
            self::High   => 'rose',
        };
    }
}
