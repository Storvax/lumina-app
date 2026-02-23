<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de imutabilidade para auditoria de acessos.
     * Crucial para transparência e conformidade legal.
     */
    public function up(): void
    {
        Schema::create('data_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Titular dos dados
            $table->foreignId('accessed_by')->constrained('users')->cascadeOnDelete(); // Moderador/Admin
            $table->string('data_type'); // Ex: 'report', 'sensitive_post'
            $table->string('purpose'); // Justificação do acesso
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_access_logs');
    }
};