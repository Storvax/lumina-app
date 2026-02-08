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
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('mood_level'); // 1 (Péssimo) a 5 (Incrível)
            $table->json('emotions')->nullable(); // Guardar array de tags ex: ["Ansiedade", "Cansaço"]
            $table->text('note')->nullable(); // O texto do diário
            $table->date('log_date'); // A data do registo
            $table->timestamps();
            
            // Garante que o user só faz um registo principal por dia (opcional)
            $table->unique(['user_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
