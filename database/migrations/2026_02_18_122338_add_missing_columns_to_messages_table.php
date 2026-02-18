<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // 1. Para o Soft Deletes (O erro atual)
            if (!Schema::hasColumn('messages', 'deleted_at')) {
                $table->softDeletes();
            }

            // 2. Para o Anonimato (Se ainda não tiveres)
            if (!Schema::hasColumn('messages', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false);
            }

            // 3. Para o Conteúdo Sensível (Se ainda não tiveres)
            if (!Schema::hasColumn('messages', 'is_sensitive')) {
                $table->boolean('is_sensitive')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['is_anonymous', 'is_sensitive']);
        });
    }
};