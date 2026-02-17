<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 
        'room_id', 
        'content', 
        'is_anonymous', 
        'is_sensitive' 
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }
}