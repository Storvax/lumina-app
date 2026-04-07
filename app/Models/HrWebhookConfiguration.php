<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrWebhookConfiguration extends Model
{
    protected $fillable = [
        'company_id',
        'provider',
        'webhook_url',
        'secret_token',
        'is_active',
        'event_types',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'event_types' => 'array',
    ];

    // Nunca expor o secret_token na serialização JSON
    protected $hidden = ['secret_token'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HrWebhookLog::class, 'company_id', 'company_id');
    }
}
