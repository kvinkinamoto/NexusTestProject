<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_attributes', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('type')->default('text');
            $table->string('key')->nullable();
            $table->boolean('publish')->default(true);
            $table->boolean('merges_catalog_cards')->default(false);
            $table->timestamps();
        });

        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->nullable()->constrained('shop_attributes')->nullOnDelete();
            $table->string('value');
            $table->string('color')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('shop_attributes');
    }
};
