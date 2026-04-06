<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de métricas de saúde importadas de wearables ou ficheiros CSV/JSON.
     * A constraint unique impede duplicados ao reimportar o mesmo período.
     */
    public function up(): void
    {
        Schema::create('health_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('metric_date');
            $table->string('metric_type', 50); // heart_rate, sleep_hours, steps, hrv
            $table->decimal('value', 10, 2);
            $table->string('source', 50)->default('csv'); // csv, json, apple_health, google_fit
            $table->timestamps();

            // Upsert seguro: apenas um valor por utilizador/data/tipo
            $table->unique(['user_id', 'metric_date', 'metric_type']);
            $table->index(['user_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_metrics');
    }
};
