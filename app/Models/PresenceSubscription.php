<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscrição de presença num canal de sala.
 *
 * Permite que um utilizador receba alertas quando um utilizador específico
 * entra numa sala — útil para moderadores e buddies acompanharem utilizadores
 * em risco sem ter de monitorizar manualmente.
 */
class PresenceSubscription extends Model
{
    protected $fillable = ['user_id', 'target_user_id', 'room_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
