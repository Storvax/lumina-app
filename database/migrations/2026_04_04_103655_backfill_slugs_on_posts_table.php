<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Preenche slugs em posts existentes que ficaram com slug NULL após a
     * migração que adicionou a coluna. Usa o ID como sufixo para garantir
     * unicidade sem colisões entre títulos idênticos.
     */
    public function up(): void
    {
        DB::table('posts')->whereNull('slug')->orderBy('id')->each(function (object $post): void {
            DB::table('posts')->where('id', $post->id)->update([
                'slug' => Str::slug($post->title) . '-' . $post->id,
            ]);
        });
    }

    /**
     * Não é possível reverter este preenchimento de forma determinística.
     */
    public function down(): void
    {
        //
    }
};
