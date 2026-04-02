<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Participantes inscritos num programa de bem-estar.
     * Regista progresso individual (diários escritos, meditações concluídas).
     * Dados individuais nunca são expostos ao gestor de RH — apenas agregados.
     */
    public function up(): void
    {
        Schema::create('wellness_program_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wellness_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('diary_days_completed')->default(0);
            $table->unsignedSmallInteger('meditations_completed')->default(0);
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['wellness_program_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellness_program_participants');
    }
};
