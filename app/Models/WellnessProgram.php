<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WellnessProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'title', 'description', 'starts_at', 'ends_at',
        'status', 'target_diary_days', 'target_meditations',
    ];

    protected $casts = [
        'starts_at'           => 'date',
        'ends_at'             => 'date',
        'target_diary_days'   => 'integer',
        'target_meditations'  => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(WellnessProgramParticipant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Duração do programa em dias. */
    public function getDurationDaysAttribute(): int
    {
        return (int) $this->starts_at->diffInDays($this->ends_at) + 1;
    }

    /** Taxa de conclusão anónima: % de participantes que completaram o programa. */
    public function getCompletionRateAttribute(): int
    {
        $total = $this->participants()->count();
        if ($total === 0) return 0;

        $completed = $this->participants()->whereNotNull('completed_at')->count();
        return (int) round(($completed / $total) * 100);
    }
}
