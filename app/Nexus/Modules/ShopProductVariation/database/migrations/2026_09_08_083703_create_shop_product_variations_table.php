<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('variation_group_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_group_id')->constrained('variation_groups')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('shop_attributes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['variation_group_id', 'attribute_id'], 'vga_group_attribute_unique');
        });

        Schema::table('shop_products', function (Blueprint $table) {
            $table->foreignId('variation_group_id')->nullable()->after('type_id')->constrained('variation_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variation_group_id');
        });

        Schema::dropIfExists('variation_group_attributes');
        Schema::dropIfExists('variation_groups');
    }
};
