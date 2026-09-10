<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration, matching the established pattern: ShopProduct (Phase
 * 2) deliberately left `bonuses` off pending this module.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->decimal('bonuses', 10, 2)->nullable()->after('price_discount');
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('bonuses');
        });
    }
};
