<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Adicionar flag de conteúdo sensível às mensagens
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(false);
        });

        // 2. Criar tabela de Reações
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'hug', 'candle', 'ear'
            $table->timestamps();
            
            // Um utilizador só pode dar 1 reação do mesmo tipo por mensagem
            $table->unique(['message_id', 'user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
