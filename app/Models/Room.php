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
        'icon', 
        'color', 
        'is_active',
        'pinned_message', // <--- NOVO
        'is_crisis_mode'  // <--- NOVO
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_crisis_mode' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function visits()
    {
        return $this->belongsToMany(User::class, 'room_visits');
    }
}