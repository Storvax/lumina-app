<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 
        'room_id', 
        'content', 
        'is_sensitive', 
        'is_anonymous',
        'reply_to_id',
        'edited_at',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'is_anonymous' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }

    // Relação: Mensagem Pai (a que está a ser respondida)
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    // Relação: Quem leu esta mensagem?
    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }
    
    // Helper: Foi lida por alguém? (Simples verificação para UI)
    public function isRead()
    {
        return $this->reads()->exists();
    }
}