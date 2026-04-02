<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Programas de bem-estar configuráveis por empresa.
     * Ex: "30 Dias de Mindfulness", "Semana Anti-Stress", etc.
     */
    public function up(): void
    {
        Schema::create('wellness_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            // Estado: draft, active, completed, archived
            $table->string('status', 20)->default('draft');
            // Metas configuráveis pelo administrador de RH
            $table->unsignedSmallInteger('target_diary_days')->default(0);
            $table->unsignedSmallInteger('target_meditations')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellness_programs');
    }
};
