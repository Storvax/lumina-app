<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tags na tabela de utilizadores
        Schema::table('users', function (Blueprint $table) {
            $table->json('emotional_tags')->nullable()->after('bio');
        });

        // 2. Tabela de Marcos da Jornada (Milestones)
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('date');
            $table->boolean('is_public')->default(false); // Privado por default
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('emotional_tags');
        });
    }
};