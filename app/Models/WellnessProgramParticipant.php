<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WellnessProgramParticipant extends Model
{
    protected $fillable = [
        'wellness_program_id', 'user_id',
        'diary_days_completed', 'meditations_completed',
        'enrolled_at', 'completed_at',
    ];

    protected $casts = [
        'enrolled_at'           => 'datetime',
        'completed_at'          => 'datetime',
        'diary_days_completed'  => 'integer',
        'meditations_completed' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(WellnessProgram::class, 'wellness_program_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
