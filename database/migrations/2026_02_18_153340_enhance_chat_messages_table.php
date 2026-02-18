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
        // 1. Suporte a Respostas e Edição na tabela Mensagens
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->timestamp('edited_at')->nullable(); // Para mostrar "Editado" explicitamente
        });

        // 2. Preferência de Recibos de Leitura no User
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('read_receipts_enabled')->default(true);
        });

        // 3. Tabela de Recibos de Leitura (Quem leu o quê e quando)
        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');
            
            // Garante que um user só marca como lida uma vez
            $table->unique(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reads');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('read_receipts_enabled');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['reply_to_id']);
            $table->dropColumn(['reply_to_id', 'edited_at']);
        });
    }
};
