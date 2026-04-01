<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['therapist_id', 'patient_id', 'content', 'session_date'];

    // Conteúdo da nota clínica encriptado at-rest — dados altamente sensíveis de saúde mental.
    protected $casts = [
        'content'      => 'encrypted',
        'session_date' => 'date',
    ];

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
