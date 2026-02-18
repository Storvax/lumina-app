<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'author', 'type', 'description', 
        'url', 'thumbnail', 'user_id', 'is_approved'
    ];

    // Relação com quem criou
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relação de votos (Muitos-para-Muitos)
    public function votes()
    {
        return $this->belongsToMany(User::class, 'resource_votes');
    }

    // Acessor para saber se o user atual já votou
    public function getIsVotedAttribute()
    {
        if (!Auth::check()) return false;
        return $this->votes()->where('user_id', Auth::id())->exists();
    }
    
    // Helper para ícones baseados no tipo
    public function getIconAttribute()
    {
        return match($this->type) {
            'book' => 'ri-book-read-line',
            'podcast' => 'ri-mic-line',
            'video' => 'ri-play-circle-line',
            'article' => 'ri-article-line',
            default => 'ri-links-line',
        };
    }
    
    // Helper para cores
    public function getColorAttribute()
    {
        return match($this->type) {
            'book' => 'indigo',
            'podcast' => 'rose',
            'video' => 'red',
            'article' => 'slate',
            default => 'gray',
        };
    }
}