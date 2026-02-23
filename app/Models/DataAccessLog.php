<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa um registo de auditoria de acesso a dados pessoais.
 */
class DataAccessLog extends Model
{
    protected $fillable = [
        'user_id',
        'accessed_by',
        'data_type',
        'purpose',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accessed_by');
    }
}