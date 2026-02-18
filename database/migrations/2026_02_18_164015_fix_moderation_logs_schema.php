<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Apagar a tabela antiga que está incorreta
        Schema::dropIfExists('moderation_logs');

        // 2. Criar a tabela nova com a estrutura completa
        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            // A coluna que faltava:
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
            
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('action'); // ex: 'mute', 'delete', 'crisis_on'
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
    }
};