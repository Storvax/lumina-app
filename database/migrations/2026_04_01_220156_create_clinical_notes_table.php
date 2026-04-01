<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de notas clínicas encriptadas at-rest.
     * Apenas o terapeuta atribuído ao paciente pode ler e escrever.
     * A coluna `content` usa o cast `encrypted` do Laravel (AES-256-CBC via APP_KEY).
     */
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            // Terapeuta que redigiu a nota.
            $table->foreignId('therapist_id')->constrained('therapists')->cascadeOnDelete();
            // Paciente a quem a nota se refere.
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->text('content'); // Encriptado ao nível do cast Eloquent
            $table->date('session_date')->nullable(); // Permite filtrar por sessão
            $table->timestamps();
            $table->softDeletes(); // Mantém histórico auditável mesmo após remoção pelo terapeuta

            $table->index(['therapist_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
