<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_webhook_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['sap', 'workday', 'generic'])->default('generic');
            // URL do endpoint no sistema HR que receberá eventos exportados pelo Lumina
            $table->string('webhook_url')->nullable();
            // Token HMAC para verificar assinatura dos webhooks recebidos
            $table->string('secret_token', 128);
            $table->boolean('is_active')->default(true);
            // Tipos de evento subscritos (ex: ["employee.created", "employee.terminated"])
            $table->json('event_types');
            $table->timestamps();

            $table->unique(['company_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_webhook_configurations');
    }
};
