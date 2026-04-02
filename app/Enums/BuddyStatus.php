<?php

declare(strict_types=1);

namespace App\Enums;

enum BuddyStatus: string
{
    case Pending   = 'pending';
    case Active    = 'active';
    case Escalated = 'escalated';
    case Completed = 'completed';

    /** Sessões que ainda ocupam um slot de disponibilidade do buddy. */
    public function isOpen(): bool
    {
        return match($this) {
            self::Pending, self::Active, self::Escalated => true,
            self::Completed => false,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pendente',
            self::Active    => 'Ativa',
            self::Escalated => 'Escalada',
            self::Completed => 'Concluída',
        };
    }
}
