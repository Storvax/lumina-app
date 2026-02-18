<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adicionar Chamas e Nível à tabela Users
        Schema::table('users', function (Blueprint $table) {
            // Verifica se a coluna já existe para evitar erro
            if (!Schema::hasColumn('users', 'flames')) {
                $table->unsignedInteger('flames')->default(0)->after('avatar');
                $table->unsignedInteger('current_streak')->default(0)->after('flames');
                $table->timestamp('last_activity_at')->nullable()->after('current_streak');
            }
        });

        // 2. Tabela de Conquistas
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('icon')->default('ri-medal-line');
            $table->string('color')->default('amber');
            $table->integer('flames_reward')->default(10);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });

        // 3. Tabela Pivot
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at');
            $table->unique(['user_id', 'achievement_id']);
        });

        // 4. Missões
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->integer('goal_count')->default(1);
            $table->integer('flames_reward')->default(5);
            $table->date('available_from');
            $table->date('available_until');
            $table->timestamps();
        });
        
        Schema::create('mission_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->integer('current_count')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_progress');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['flames', 'current_streak', 'last_activity_at']);
        });
    }
};