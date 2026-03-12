<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices de performance em falta nas tabelas de alta leitura.
 *
 * Identificados na auditoria técnica de Março 2026: full table scans em queries
 * frequentes causariam degradação progressiva com o crescimento da plataforma.
 */
return new class extends Migration
{
    public function up(): void
    {
        // posts — feed principal da plataforma; queries por autor e por tag emocional
        Schema::table('posts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('tag');
        });

        // comments — carregamento de comentários por post e threads aninhadas por parent
        Schema::table('comments', function (Blueprint $table) {
            $table->index('post_id');
            $table->index('parent_id');
        });

        // buddy_sessions — dashboard e fila de espera filtrados por status e por buddy
        Schema::table('buddy_sessions', function (Blueprint $table) {
            $table->index('status');
            $table->index('buddy_id');
        });

        // wall_posts — fila de moderação (is_approved) e galeria por utilizador
        Schema::table('wall_posts', function (Blueprint $table) {
            $table->index('is_approved');
            $table->index('user_id');
        });

        // moderation_logs — dashboard admin: filtrar por utilizador alvo ou tipo de ação
        Schema::table('moderation_logs', function (Blueprint $table) {
            $table->index('target_user_id');
            $table->index('action');
        });

        // daily_logs — gráficos de humor (range queries por utilizador além do UNIQUE composto)
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['tag']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
            $table->dropIndex(['parent_id']);
        });

        Schema::table('buddy_sessions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['buddy_id']);
        });

        Schema::table('wall_posts', function (Blueprint $table) {
            $table->dropIndex(['is_approved']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('moderation_logs', function (Blueprint $table) {
            $table->dropIndex(['target_user_id']);
            $table->dropIndex(['action']);
        });

        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
