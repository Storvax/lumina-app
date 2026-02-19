<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            // Adiciona as colunas apenas se elas ainda não existirem
            if (!Schema::hasColumn('missions', 'description')) {
                $table->string('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('missions', 'action_type')) {
                $table->string('action_type')->default('daily_log')->after('description');
            }
            if (!Schema::hasColumn('missions', 'target_count')) {
                $table->integer('target_count')->default(1)->after('action_type');
            }
            if (!Schema::hasColumn('missions', 'flames_reward')) {
                $table->integer('flames_reward')->default(10)->after('target_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['description', 'action_type', 'target_count', 'flames_reward']);
        });
    }
};