<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapy_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapist_id')->constrained('therapists')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(50);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->enum('session_type', ['video', 'in_person'])->default('video');
            // Notas do paciente antes da sessão (contexto para o terapeuta)
            $table->text('patient_notes')->nullable();
            // Token único da sala Jitsi — gerado ao confirmar sessão de vídeo
            $table->string('video_room_token')->nullable()->unique();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            // Registo de quem cancelou (patient ou therapist)
            $table->enum('cancelled_by', ['patient', 'therapist'])->nullable();
            $table->timestamps();

            $table->index(['therapist_id', 'scheduled_at']);
            $table->index(['patient_id', 'scheduled_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapy_sessions');
    }
};
