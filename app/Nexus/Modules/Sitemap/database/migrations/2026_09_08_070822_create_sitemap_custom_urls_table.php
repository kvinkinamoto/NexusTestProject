<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_custom_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->decimal('priority', 3, 1)->default(0.5);
            $table->string('changefreq', 20)->default('monthly');
            $table->timestamp('lastmod')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_custom_urls');
    }
};
