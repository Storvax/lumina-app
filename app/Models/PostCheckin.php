<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registo emocional do leitor após consumir um post sensível.
 *
 * Cada utilizador pode fazer um único check-in por post (unique constraint
 * em [post_id, user_id]). O campo `emotion` usa um dos valores:
 * empathy, sadness, strength, neutral.
 */
class PostCheckin extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'emotion',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
