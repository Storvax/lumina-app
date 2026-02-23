<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 
        'room_id', 
        'content', 
        'is_sensitive', 
        'is_anonymous',
        'reply_to_id', // Essencial para respostas
        'edited_at'    // Essencial para edição
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'is_anonymous' => 'boolean',
        'content' => 'encrypted',
        'edited_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function room() {
        return $this->belongsTo(Room::class);
    }

    public function reactions() {
        return $this->hasMany(MessageReaction::class);
    }

    public function replyTo() {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function reads() {
        return $this->hasMany(MessageRead::class);
    }
}