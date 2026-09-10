<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration, matching the pattern used for `carts.promo_code_id`
 * and `promo_code_usages.order_id`: `orders.bank_order_id` (the payment
 * gateway's own order/transaction id) only makes sense once a payment
 * driver exists to write it. `signature` was dropped entirely — none of the
 * 3 drivers ever read or wrote it, each computes its own signature locally.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('bank_order_id')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('bank_order_id');
        });
    }
};
