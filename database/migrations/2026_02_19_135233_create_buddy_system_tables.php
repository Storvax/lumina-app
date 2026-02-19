<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adicionar status de Buddy aos Utilizadores
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_buddy')->default(false)->after('role');
            $table->boolean('is_buddy_available')->default(false)->after('is_buddy');
        });

        // 2. Candidaturas para Buddy
        Schema::create('buddy_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('motivation'); // Porque quer ser Buddy?
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });

        // 3. Sessões de Buddy (Conversas 1-para-1)
        Schema::create('buddy_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Quem pediu ajuda
            $table->foreignId('buddy_id')->nullable()->constrained('users')->nullOnDelete(); // O Ouvinte
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete(); // Sala de chat gerada
            
            $table->string('status')->default('pending'); // pending, active, completed, escalated
            $table->integer('rating')->nullable(); // Avaliação: 1 (Triste), 2 (Neutro), 3 (Aliviado)
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buddy_sessions');
        Schema::dropIfExists('buddy_applications');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_buddy', 'is_buddy_available']);
        });
    }
};