<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona suporte a Soft Deletes para eliminação segura (RGPD).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes(); // Cria a coluna 'deleted_at'
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};