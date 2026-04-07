<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name', 'slug'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    /**
     * Convites pendentes — ainda não aceites e dentro do prazo de validade.
     */
    public function pendingInvitations(): HasMany
    {
        return $this->hasMany(CompanyInvitation::class)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }
}
