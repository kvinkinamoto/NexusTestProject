<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_networks', function (Blueprint $table) {
            $table->id();
            $table->json('title')->nullable();
            $table->json('caption')->nullable();
            $table->string('url');
            $table->string('logo')->nullable();
            $table->string('key')->nullable();
            $table->string('platform')->nullable();
            $table->boolean('sync_posts')->default(false);
            $table->boolean('publish')->default(true);

            NestedSet::columns($table);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_networks');
    }
};
