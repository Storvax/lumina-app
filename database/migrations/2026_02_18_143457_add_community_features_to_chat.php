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
        // 1. Mensagem Fixada na Sala
        Schema::table('rooms', function (Blueprint $table) {
            $table->text('pinned_message')->nullable(); // Texto direto para facilitar
        });

        // 2. Registo de Visitas (Para saber se é a primeira vez)
        Schema::create('room_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'room_id']); // Só regista uma vez por sala
        });

        // 3. Subscrição de Presença (Quero saber se X entra na sala Y)
        Schema::create('chat_presence_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Quem subscreve
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete(); // Quem é vigiado
            $table->foreignId('room_id')->constrained()->cascadeOnDelete(); // Em que sala
            $table->timestamps();
            $table->unique(['user_id', 'target_user_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_presence_subscriptions');
        Schema::dropIfExists('room_visits');
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('pinned_message');
        });
    }
};
