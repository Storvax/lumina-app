<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // emotion: como o leitor se sentiu após ler o post
            $table->string('emotion', 20); // empathy | sadness | strength | neutral
            $table->timestamp('created_at')->useCurrent();

            // Um utilizador só pode fazer check-in uma vez por post
            $table->unique(['post_id', 'user_id']);

            $table->index(['post_id', 'emotion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_checkins');
    }
};
