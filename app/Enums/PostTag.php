<?php

declare(strict_types=1);

namespace App\Enums;

enum PostTag: string
{
    case Hope    = 'hope';
    case Vent    = 'vent';
    case Anxiety = 'anxiety';

    public function label(): string
    {
        return match($this) {
            self::Hope    => 'Esperança',
            self::Vent    => 'Desabafo',
            self::Anxiety => 'Ansiedade',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Hope    => 'teal',
            self::Vent    => 'amber',
            self::Anxiety => 'rose',
        };
    }
}
