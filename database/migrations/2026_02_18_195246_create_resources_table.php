<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de Recursos
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable(); // Autor do livro ou host do podcast
            $table->string('type'); // 'book', 'podcast', 'video', 'article'
            $table->text('description')->nullable();
            $table->string('url')->nullable(); // Link externo
            $table->string('thumbnail')->nullable(); // Capa
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Quem sugeriu (geralmente Admin)
            $table->boolean('is_approved')->default(true); // Curadoria
            $table->timestamps();
        });

        // Tabela de Votos (Pivot)
        Schema::create('resource_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // Impede votos duplicados
            $table->unique(['user_id', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_votes');
        Schema::dropIfExists('resources');
    }
};