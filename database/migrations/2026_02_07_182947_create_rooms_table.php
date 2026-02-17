<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Identificador único (ex: ansiedade-social)
            $table->string('description')->nullable();
            $table->string('color')->default('#6366f1'); // Cor default (Indigo)
            $table->string('icon')->default('heroicon-o-chat-bubble-left-right'); // Ícone default
            $table->boolean('is_private')->default(false); // <--- ADICIONADO PARA EVITAR ERRO
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};