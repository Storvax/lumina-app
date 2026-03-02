<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'public_key')) {
                $table->text('public_key')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'encrypted_private_key')) {
                $table->text('encrypted_private_key')->nullable()->after('public_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['public_key', 'encrypted_private_key']);
        });
    }
};
