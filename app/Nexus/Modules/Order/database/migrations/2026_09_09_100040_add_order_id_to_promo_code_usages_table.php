<?php

use App\Nexus\Modules\Order\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration, matching the pattern used for `carts.promo_code_id`:
 * `promo_code_usages` existed since Phase 4 (Pricing rules) without an
 * `order_id`, since Order didn't exist yet. Recorded at checkout from now on
 * — see OrderService::recordPromotionUsage().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_code_usages', function (Blueprint $table) {
            $table->foreignIdFor(Order::class, 'order_id')
                ->nullable()
                ->after('user_id')
                ->constrained('orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('promo_code_usages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
