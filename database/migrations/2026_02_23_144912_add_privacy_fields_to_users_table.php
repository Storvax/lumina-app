<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aplica colunas dedicadas à gestão de privacidade e retenção de dados.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Null significa que o utilizador quer guardar os dados para sempre (default)
            $table->integer('diary_retention_days')->nullable()->after('wants_weekly_summary');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['diary_retention_days']);
        });
    }
};