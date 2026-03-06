<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aligns pact tables with the simplified frontend contract:
     * prompts use `question`/`active_date`, answers use `answer`.
     */
    public function up(): void
    {
        // --- pact_prompts: body→question, add active_date, drop category/is_active ---
        Schema::table('pact_prompts', function (Blueprint $table) {
            if (!Schema::hasColumn('pact_prompts', 'question')) {
                $table->text('question')->after('id')->nullable();
            }
            if (!Schema::hasColumn('pact_prompts', 'active_date')) {
                $table->date('active_date')->nullable()->after('question');
            }
        });

        if (Schema::hasColumn('pact_prompts', 'body')) {
            DB::table('pact_prompts')->whereNull('question')->update([
                'question' => DB::raw('body'),
            ]);
        }

        Schema::table('pact_prompts', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('pact_prompts', 'body')) $columns[] = 'body';
            if (Schema::hasColumn('pact_prompts', 'category')) $columns[] = 'category';
            if (Schema::hasColumn('pact_prompts', 'is_active')) $columns[] = 'is_active';
            if (!empty($columns)) $table->dropColumn($columns);
        });

        // --- pact_answers: body→answer, drop is_anonymous/answer_date ---
        Schema::table('pact_answers', function (Blueprint $table) {
            if (!Schema::hasColumn('pact_answers', 'answer')) {
                $table->text('answer')->after('pact_prompt_id')->nullable();
            }
        });

        if (Schema::hasColumn('pact_answers', 'body')) {
            DB::table('pact_answers')->whereNull('answer')->update([
                'answer' => DB::raw('body'),
            ]);
        }

        // SQLite requires dropping the unique index before removing columns it references
        try {
            Schema::table('pact_answers', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'pact_prompt_id', 'answer_date']);
            });
        } catch (\Exception $e) {
            // Index may already be dropped from a partial prior run
        }

        Schema::table('pact_answers', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('pact_answers', 'body')) $columns[] = 'body';
            if (Schema::hasColumn('pact_answers', 'is_anonymous')) $columns[] = 'is_anonymous';
            if (Schema::hasColumn('pact_answers', 'answer_date')) $columns[] = 'answer_date';
            if (!empty($columns)) $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::table('pact_prompts', function (Blueprint $table) {
            $table->text('body')->nullable();
            $table->string('category')->default('geral');
            $table->boolean('is_active')->default(true);
        });

        DB::table('pact_prompts')->update(['body' => DB::raw('question')]);

        Schema::table('pact_prompts', function (Blueprint $table) {
            $table->dropColumn(['question', 'active_date']);
        });

        Schema::table('pact_answers', function (Blueprint $table) {
            $table->text('body')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->date('answer_date')->nullable();
        });

        DB::table('pact_answers')->update(['body' => DB::raw('answer')]);

        Schema::table('pact_answers', function (Blueprint $table) {
            $table->dropColumn('answer');
            $table->unique(['user_id', 'pact_prompt_id', 'answer_date']);
        });
    }
};
