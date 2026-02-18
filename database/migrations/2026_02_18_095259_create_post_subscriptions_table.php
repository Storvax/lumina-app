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
        Schema::create('post_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // Garante que um user não subscreve o mesmo post 2 vezes
            $table->unique(['user_id', 'post_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_subscriptions');
    }
};
