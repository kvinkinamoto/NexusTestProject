<?php

use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(ShopProduct::class, 'product_id')
                ->nullable()
                ->constrained('shop_products')
                ->nullOnDelete();

            $table->unsignedInteger('quantity');
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();

            $table->json('product_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};
