<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índice composto para o live polling de visitantes ativos por sala
        // Otimiza: WHERE room_id IN (...) AND updated_at >= ? GROUP BY room_id
        Schema::table('room_visits', function (Blueprint $table) {
            $table->index(['room_id', 'updated_at'], 'room_visits_room_id_updated_at_index');
        });

        // Índice composto para carregar mensagens recentes de uma sala
        // Otimiza: WHERE room_id = ? AND created_at >= ? ORDER BY created_at DESC
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['room_id', 'created_at'], 'messages_room_id_created_at_index');
        });

        // Índice para marcar mensagens como lidas eficientemente
        // Otimiza: WHERE message_id IN (...) AND user_id = ?
        Schema::table('message_reads', function (Blueprint $table) {
            $table->index(['message_id', 'user_id'], 'message_reads_message_id_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('room_visits', function (Blueprint $table) {
            $table->dropIndex('room_visits_room_id_updated_at_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_room_id_created_at_index');
        });

        Schema::table('message_reads', function (Blueprint $table) {
            $table->dropIndex('message_reads_message_id_user_id_index');
        });
    }
};
