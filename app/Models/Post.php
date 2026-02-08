<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'tag',
        'is_sensitive',
        'support_count'
    ];

    // Relação: Um post pertence a um utilizador
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments() { return $this->hasMany(Comment::class); }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    // Helpers para saber se o utilizador atual já reagiu
    public function reactedByType($type)
    {
        // Verifica se na coleção de reações existe alguma deste user com este tipo
        // (Nota: Em produção real usarias queries mais otimizadas, mas para aqui serve)
        return $this->reactions->where('user_id', auth()->id())->where('type', $type)->count() > 0;
    }
}