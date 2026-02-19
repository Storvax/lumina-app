<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('wants_weekly_summary')->default(false)->after('safety_plan');
            $table->time('quiet_hours_start')->nullable()->after('wants_weekly_summary');
            $table->time('quiet_hours_end')->nullable()->after('quiet_hours_start');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wants_weekly_summary', 'quiet_hours_start', 'quiet_hours_end']);
        });
    }
};