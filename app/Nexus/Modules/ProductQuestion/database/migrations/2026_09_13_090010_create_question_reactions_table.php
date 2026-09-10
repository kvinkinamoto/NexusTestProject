<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_reactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('session_id')->nullable();

            $table->foreignId('question_id')
                ->nullable()
                ->constrained('product_questions')
                ->cascadeOnDelete();

            $table->tinyInteger('value')->comment('1 = like, -1 = dislike');

            $table->timestamps();

            $table->unique(['user_id', 'session_id', 'question_id'], 'unique_question_reaction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_reactions');
    }
};
