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
        // 1. Shadowban na tabela Users
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('shadowbanned_until')->nullable(); // Se tiver data futura, está invisível
        });

        // 2. Tabela de Logs de Moderação
        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderator_id')->constrained('users'); // Quem fez a ação
            $table->foreignId('target_user_id')->nullable()->constrained('users'); // Quem sofreu a ação
            $table->nullableMorphs('target'); // O que foi afetado (Post ou Comment)
            $table->string('action'); // 'delete', 'shadowban', 'pin', 'lock'
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('shadowbanned_until');
        });
        Schema::dropIfExists('moderation_logs');
    }
};
