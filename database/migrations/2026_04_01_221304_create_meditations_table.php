<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de meditações guiadas e conteúdo de mindfulness.
     * Gerido via Filament pelo administrador — sem criação por utilizadores.
     */
    public function up(): void
    {
        Schema::create('meditations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            // Categorias: breathing, body_scan, visualization, sleep, anxiety, gratitude
            $table->string('category', 50)->default('breathing');
            $table->unsignedSmallInteger('duration_seconds'); // Duração do áudio em segundos
            $table->string('audio_url')->nullable(); // URL do ficheiro de áudio (storage ou CDN)
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meditations');
    }
};
