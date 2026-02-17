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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quem denunciou
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // O post denunciado
            $table->string('reason'); // Motivo (Spam, Ódio, Risco, etc.)
            $table->text('details')->nullable(); // Detalhes opcionais
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending'); // Estado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
