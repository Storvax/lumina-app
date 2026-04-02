<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meditation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'category', 'duration_seconds', 'audio_url', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'duration_seconds' => 'integer',
        'sort_order'       => 'integer',
    ];

    /** Devolve apenas as meditações visíveis ao utilizador. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Duração formatada para display (ex: "8 min"). */
    public function getDurationFormattedAttribute(): string
    {
        $minutes = (int) floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return $seconds > 0 ? "{$minutes} min {$seconds} s" : "{$minutes} min";
    }
}
