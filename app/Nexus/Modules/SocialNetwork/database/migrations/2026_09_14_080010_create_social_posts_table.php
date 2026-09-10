<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('social_account_id')
                ->nullable()
                ->constrained('social_networks')
                ->cascadeOnDelete();

            $table->string('platform');
            $table->string('external_id');
            $table->text('text')->nullable();
            $table->json('media')->nullable();
            $table->string('post_url')->nullable();
            $table->json('metrics')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'external_id']);
            $table->index(['platform', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
