<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->string('version');
            $table->string('github_tag');
            $table->text('changelog')->nullable();
            $table->string('min_core_version')->nullable();
            $table->string('max_core_version')->nullable();
            $table->timestamp('published_at')->nullable();

            // Filled exclusively by GithubReleaseSyncService, never edited in the admin form.
            $table->string('zip_disk_path')->nullable();
            $table->unsignedBigInteger('zip_size')->nullable();
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'github_tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_releases');
    }
};
