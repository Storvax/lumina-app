<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simplifies vault_items to just user_id + content,
     * matching the frontend's single-field "luz" (light) input.
     */
    public function up(): void
    {
        Schema::table('vault_items', function (Blueprint $table) {
            if (Schema::hasColumn('vault_items', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('vault_items', 'title')) {
                $table->dropColumn('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vault_items', function (Blueprint $table) {
            $table->string('type')->default('text');
            $table->string('title')->nullable();
        });
    }
};
