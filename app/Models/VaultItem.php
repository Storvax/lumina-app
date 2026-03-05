<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaultItem extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
