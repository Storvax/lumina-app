<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin        = 'admin';
    case Moderator    = 'moderator';
    case Therapist    = 'therapist';
    case HrAdmin      = 'hr_admin';
    case User         = 'user';

    public function isPrivileged(): bool
    {
        return match($this) {
            self::Admin, self::Moderator, self::Therapist, self::HrAdmin => true,
            self::User => false,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Admin      => 'Administrador',
            self::Moderator  => 'Moderador',
            self::Therapist  => 'Terapeuta',
            self::HrAdmin    => 'Gestor RH',
            self::User       => 'Utilizador',
        };
    }
}
