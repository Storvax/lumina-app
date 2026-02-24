<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'color', 'is_active',
        'is_private',     // Salas privadas (ex: buddy sessions)
        'pinned_message', // Essencial para Mensagem Fixada
        'is_crisis_mode'  // Essencial para Modo Crise
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_private' => 'boolean',
        'is_crisis_mode' => 'boolean',
    ];

    public function messages() {
        return $this->hasMany(Message::class);
    }
    
    public function visits() {
        return $this->belongsToMany(User::class, 'room_visits');
    }
}