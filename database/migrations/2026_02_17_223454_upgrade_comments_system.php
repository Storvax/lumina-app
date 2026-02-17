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
        // 1. Atualizar tabela de comentários
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            }
            if (!Schema::hasColumn('comments', 'is_helpful')) {
                $table->boolean('is_helpful')->default(false);
            }
        });

        // 2. Criar tabela de reações aos comentários
        if (!Schema::hasTable('comment_reactions')) {
            Schema::create('comment_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('type', ['hug', 'heart', 'muscle']); 
                $table->timestamps();
                $table->unique(['comment_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'is_helpful']);
        });
    }
};
