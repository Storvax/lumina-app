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
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable(); // "Sobre mim"
            $table->integer('energy_level')->default(5); // 1 a 5 (Colheres)
            $table->json('safety_plan')->nullable(); // JSON para guardar gatilhos e contactos
            $table->json('journey_tags')->nullable(); // JSON para guardar tags como "Luto", "Ansiedade"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
