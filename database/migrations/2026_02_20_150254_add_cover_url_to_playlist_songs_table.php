<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playlist_songs', function (Blueprint $table) {
            $table->string('cover_url')->nullable()->after('spotify_url');
        });
    }

    public function down(): void
    {
        Schema::table('playlist_songs', function (Blueprint $table) {
            $table->dropColumn('cover_url');
        });
    }
};