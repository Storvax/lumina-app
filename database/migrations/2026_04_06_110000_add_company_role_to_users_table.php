<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Papel do utilizador dentro da empresa — hr_admin pode gerir convites e ver relatórios
            $table->enum('company_role', ['employee', 'hr_admin'])->default('employee')->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('company_role');
        });
    }
};
