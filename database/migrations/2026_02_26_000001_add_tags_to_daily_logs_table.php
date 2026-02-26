<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona a coluna `tags` à tabela daily_logs.
 *
 * Contexto: A migração original criou a coluna `emotions` (JSON),
 * mas todo o código (Model, Controller, View) referencia `tags`.
 * Esta migração adiciona a coluna correcta sem remover `emotions`,
 * preservando dados existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('mood_level');
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
