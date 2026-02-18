<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_private',
        'pinned_message',
        'is_crisis_mode',
    ];
    protected $casts = [
        'is_private' => 'boolean',
        'is_crisis_mode' => 'boolean',
    ];
    // Relação com mensagens (caso precises)
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function visits()
    {
        return $this->belongsToMany(User::class, 'room_visits');
    }
}