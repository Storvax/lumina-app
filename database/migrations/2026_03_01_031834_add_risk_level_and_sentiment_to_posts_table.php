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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('risk_level', 10)->default('low')->after('is_locked');     // low|medium|high
            $table->string('sentiment', 10)->default('neutral')->after('risk_level'); // positive|neutral|distress
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('risk_level', 10)->default('low')->after('is_sensitive');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['risk_level', 'sentiment']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('risk_level');
        });
    }
};
