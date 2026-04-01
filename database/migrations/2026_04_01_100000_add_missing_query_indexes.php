<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índice composto para range queries de diário por utilizador (perfil, espiral, relatórios).
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->index(['user_id', 'log_date'], 'daily_logs_user_date_idx');
        });

        // Índice composto para slow mode: última mensagem de um utilizador numa sala específica.
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['user_id', 'room_id'], 'messages_user_room_idx');
        });

        // Índice único para reações: previne votos duplicados e acelera o toggle de reação.
        // Guarda verificação de existência para ser idempotente em ambientes de teste (SQLite) e produção (PostgreSQL).
        if (!Schema::hasIndex('post_reactions', 'post_reactions_user_post_type_unique')) {
            Schema::table('post_reactions', function (Blueprint $table) {
                $table->unique(['user_id', 'post_id', 'type'], 'post_reactions_user_post_type_unique');
            });
        }

        // Índice para conversion rate queries do AnalyticsService.
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->index(['event', 'created_at'], 'analytics_events_event_created_idx');
        });

        // Índice para lookup de re-engagement no DetectDisengagement command.
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'type', 'created_at'], 'notifications_notifiable_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropIndex('daily_logs_user_date_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_user_room_idx');
        });

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropUnique('post_reactions_user_post_type_unique');
        });

        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex('analytics_events_event_created_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_notifiable_type_created_idx');
        });
    }

};
