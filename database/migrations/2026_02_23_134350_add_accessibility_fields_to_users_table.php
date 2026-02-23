<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aplica as colunas de acessibilidade na tabela de utilizadores.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('a11y_dyslexic_font')->default(false)->after('wants_weekly_summary');
            $table->boolean('a11y_reduced_motion')->default(false)->after('a11y_dyslexic_font');
            $table->string('a11y_text_size', 10)->default('base')->after('a11y_reduced_motion'); // Suporta: sm, base, lg, xl
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'a11y_dyslexic_font',
                'a11y_reduced_motion',
                'a11y_text_size'
            ]);
        });
    }
};