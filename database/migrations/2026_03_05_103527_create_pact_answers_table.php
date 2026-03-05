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
        Schema::create('pact_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pact_prompt_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_anonymous')->default(true);
            $table->date('answer_date');
            $table->timestamps();

            $table->unique(['user_id', 'pact_prompt_id', 'answer_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pact_answers');
    }
};
