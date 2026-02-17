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
            // Adiciona as colunas se elas não existirem
            if (!Schema::hasColumn('posts', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false);
            }
            if (!Schema::hasColumn('posts', 'is_locked')) {
                $table->boolean('is_locked')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'is_locked']);
        });
    }
};
