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
            if (!Schema::hasColumn('posts', 'risk_level')) {
                $table->string('risk_level', 10)->default('low')->after('is_locked');
            }
            
            if (!Schema::hasColumn('posts', 'sentiment')) {
                $table->string('sentiment', 10)->default('neutral')->after('risk_level');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'risk_level')) {
                $table->string('risk_level', 10)->default('low')->after('is_sensitive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'risk_level')) {
                $table->dropColumn('risk_level');
            }
            
            if (Schema::hasColumn('posts', 'sentiment')) {
                $table->dropColumn('sentiment');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'risk_level')) {
                $table->dropColumn('risk_level');
            }
        });
    }
};
