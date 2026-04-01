<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaultItem extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'content'];

    // Conteúdo do cofre pessoal encriptado em repouso — dados altamente sensíveis
    // que o utilizador confia à plataforma em momentos de vulnerabilidade.
    protected $casts = ['content' => 'encrypted'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
