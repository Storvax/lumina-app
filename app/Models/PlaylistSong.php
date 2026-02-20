<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaylistSong extends Model
{
    // Adicionado 'cover_url' aqui
    protected $fillable = ['user_id', 'title', 'artist', 'spotify_url', 'cover_url', 'votes_count', 'is_weekly_winner'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}