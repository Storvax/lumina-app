<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Funcionalidade "Conta em Pausa"
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('hibernated_at')->nullable()->after('password');
        });

        // 2. Playlist Comunitária (Músicas sugeridas)
        Schema::create('playlist_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('artist');
            $table->string('spotify_url')->nullable();
            $table->integer('votes_count')->default(0);
            $table->boolean('is_weekly_winner')->default(false);
            $table->timestamps();
        });

        // 3. Votos da Playlist (para evitar duplo voto)
        Schema::create('playlist_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('playlist_song_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'playlist_song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_votes');
        Schema::dropIfExists('playlist_songs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hibernated_at');
        });
    }
};