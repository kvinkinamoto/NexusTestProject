<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an admin assemble a "pack" ShopProduct (e.g. "E-commerce Core Pack")
 * out of real, individually-downloadable module products dynamically, via
 * the admin relation manager — replacing the old approach where a pack was
 * just one row with the bundled module names typed into its description
 * text, with no actual link to those modules' identities or release zips.
 * Same shape as ShopProduct's own existing `products_related` self-pivot
 * (see relatedProducts()) — pack_id/item_id instead of product_id/related_id
 * only to make the relation's direction unambiguous (pack -> its items).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_product_pack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('shop_products')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['pack_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_pack_items');
    }
};
