<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'body', 'parent_id', 'is_helpful'];

    public function user() { return $this->belongsTo(User::class); }
    public function post() { return $this->belongsTo(Post::class); }

    // Relação: Respostas (Filhos)
    public function replies() { return $this->hasMany(Comment::class, 'parent_id'); }

    // Relação: Comentário Original (Pai)
    public function parent() { return $this->belongsTo(Comment::class, 'parent_id'); }

    // Relação: Reações
    public function reactions() { return $this->hasMany(CommentReaction::class); }
}