<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Índices GIN funcionais para Full-Text Search nativo do PostgreSQL.
     * Permitem pesquisa por relevância com ts_rank sem serviço externo.
     * O dicionário 'portuguese' trata stems e stop words em PT-PT.
     * Silenciosamente ignorado em SQLite (ambiente local de desenvolvimento).
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Posts: título tem peso A (mais relevante), corpo tem peso B
        DB::statement("
            CREATE INDEX IF NOT EXISTS posts_fts_idx
            ON posts
            USING GIN (
                setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('portuguese', coalesce(content, '')), 'B')
            )
        ");

        // Recursos: título > autor > descrição em termos de relevância
        DB::statement("
            CREATE INDEX IF NOT EXISTS resources_fts_idx
            ON resources
            USING GIN (
                setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('portuguese', coalesce(author, '')), 'B') ||
                setweight(to_tsvector('portuguese', coalesce(description, '')), 'C')
            )
        ");

        // Salas: nome tem mais peso que descrição
        DB::statement("
            CREATE INDEX IF NOT EXISTS rooms_fts_idx
            ON rooms
            USING GIN (
                setweight(to_tsvector('portuguese', coalesce(name, '')), 'A') ||
                setweight(to_tsvector('portuguese', coalesce(description, '')), 'B')
            )
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS posts_fts_idx');
        DB::statement('DROP INDEX IF EXISTS resources_fts_idx');
        DB::statement('DROP INDEX IF EXISTS rooms_fts_idx');
    }
};
