<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Garante que a tabela missions tem os campos necessários
        if (!Schema::hasTable('missions')) {
            Schema::create('missions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('description')->nullable();
                $table->string('action_type'); // ex: 'daily_log', 'reply', 'reaction', 'breathe'
                $table->integer('target_count')->default(1); // Quantas vezes tem de fazer
                $table->integer('flames_reward')->default(10);
                $table->timestamps();
            });
        }

        // Tabela para guardar o progresso do utilizador nas missões
        Schema::create('mission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->integer('progress')->default(0);
            $table->date('assigned_date'); // Para sabermos que é a missão "de hoje"
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_user');
        Schema::dropIfExists('missions');
    }
};