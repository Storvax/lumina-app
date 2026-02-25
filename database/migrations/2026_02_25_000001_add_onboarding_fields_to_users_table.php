<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->string('onboarding_intent')->nullable();
            $table->string('onboarding_mood')->nullable();
            $table->string('onboarding_preference')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_completed_at',
                'onboarding_intent',
                'onboarding_mood',
                'onboarding_preference',
            ]);
        });
    }
};
