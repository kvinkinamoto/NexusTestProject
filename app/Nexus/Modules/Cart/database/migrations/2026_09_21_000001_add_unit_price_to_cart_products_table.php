<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nullable override so a pack's decomposed member products (see
 * CartService::add()) can be sold at the pack's own (possibly discounted)
 * price instead of the sum of each member's own `price` column — null for
 * every ordinary, non-pack cart line, which keeps resolving its price
 * dynamically via ResolveProductPriceAction/PromotionEngine as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
};
