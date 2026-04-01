<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Segredo TOTP — armazenado encriptado via cast no modelo.
            $table->text('two_factor_secret')->nullable()->after('password');
            // Flag de confirmação: o utilizador completou a ativação com um código válido.
            $table->boolean('two_factor_confirmed')->default(false)->after('two_factor_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_confirmed']);
        });
    }
};
