<?php

use App\Models\User;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Promotion\Models\Promotion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Promotion::class, 'promotion_id')
                ->constrained('promotions')
                ->cascadeOnDelete();

            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignIdFor(Order::class, 'order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->decimal('applied_amount', 12, 2);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_usages');
    }
};
