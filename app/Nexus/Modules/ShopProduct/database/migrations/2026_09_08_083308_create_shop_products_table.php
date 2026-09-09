<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('shop_categories')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('shop_product_types')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('price_discount', 10, 2)->nullable();
            $table->integer('stock')->nullable();
            $table->boolean('publish')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });

        Schema::create('product_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('attribute_option_id')->constrained('attribute_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_option_id']);
        });

        Schema::create('products_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('related_id')->constrained('shop_products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_related');
        Schema::dropIfExists('product_attribute_options');
        Schema::dropIfExists('shop_products');
    }
};
