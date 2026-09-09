<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('value_type')->nullable();
            $table->decimal('amount', 10, 2)->nullable();

            $table->string('scope_target')->default('all');
            $table->foreignId('category_id')->nullable()->constrained('shop_categories')->nullOnDelete();

            $table->decimal('min_cart_amount', 10, 2)->nullable();
            $table->unsignedInteger('min_qty')->nullable();
            $table->json('day_of_week')->nullable();
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();

            $table->json('mechanics')->nullable();

            $table->integer('priority')->default(0);
            $table->boolean('stackable')->default(false);

            $table->timestamps();
        });

        Schema::create('discount_rule_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_rule_id')->constrained('discount_rules')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['discount_rule_id', 'product_id'], 'drp_rule_product_unique');
        });

        Schema::create('discount_rule_excluded_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_rule_id')->constrained('discount_rules')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['discount_rule_id', 'product_id'], 'drep_rule_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_rule_excluded_products');
        Schema::dropIfExists('discount_rule_products');
        Schema::dropIfExists('discount_rules');
    }
};
