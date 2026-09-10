<?php

use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_history', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(OrderStatus::class, 'status_id')
                ->nullable()
                ->constrained('order_statuses')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_history');
    }
};
