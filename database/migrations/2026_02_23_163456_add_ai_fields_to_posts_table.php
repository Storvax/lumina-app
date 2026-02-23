<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona colunas para armazenar os outputs da Análise NLP.
     * Facilita a triagem para moderadores e a futura recomendação de recursos.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('risk_level')->default('low')->after('is_sensitive'); // low, medium, high
            $table->string('sentiment')->default('neutral')->after('risk_level'); // positive, neutral, distress
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['risk_level', 'sentiment']);
        });
    }
};