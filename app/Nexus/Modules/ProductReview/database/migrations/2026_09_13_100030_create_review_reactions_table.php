<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_reactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('session_id')->nullable();

            $table->foreignId('review_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('review_comment_id')
                ->nullable()
                ->constrained('review_comments')
                ->cascadeOnDelete();

            $table->tinyInteger('value')->comment('1 = like, -1 = dislike');

            $table->timestamps();

            $table->unique(['user_id', 'session_id', 'review_id', 'review_comment_id'], 'unique_reaction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_reactions');
    }
};
