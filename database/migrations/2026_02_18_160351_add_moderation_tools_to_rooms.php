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
        // 1. Estado de Crise na Sala
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('is_crisis_mode')->default(false);
        });

        // 2. Tabela de Logs de Moderação (Se ainda não existir)
        if (!Schema::hasTable('moderation_logs')) {
            Schema::create('moderation_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // O Moderador
                $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('action'); // ex: 'mute', 'delete', 'crisis_on'
                $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete(); // Quem sofreu a ação
                $table->text('details')->nullable(); // Motivo ou conteúdo
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('is_crisis_mode');
        });
    }
};
