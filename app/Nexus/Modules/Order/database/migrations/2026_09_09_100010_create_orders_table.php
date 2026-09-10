<?php

use App\Models\User;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(OrderStatus::class, 'status_id')
                ->nullable()
                ->constrained('order_statuses')
                ->nullOnDelete();

            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignIdFor(PromoCode::class, 'promo_code_id')
                ->nullable()
                ->constrained('promo_codes')
                ->nullOnDelete();

            $table->string('user_name')->nullable();
            $table->string('user_last_name')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('user_email')->nullable();
            $table->text('user_comment')->nullable();
            $table->boolean('needs_phone_call')->default(true);

            $table->boolean('is_another_recipient')->default(false);
            $table->string('recipient_name')->nullable();
            $table->string('recipient_last_name')->nullable();
            $table->string('recipient_phone')->nullable();

            $table->string('delivery_method')->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_address')->nullable();

            $table->string('payment_method')->nullable();
            $table->unsignedTinyInteger('payment_status')->default(PaymentStatus::PENDING->value);

            $table->decimal('products_price', 12, 2)->nullable();
            $table->decimal('delivery_price', 12, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->string('language_code', 10)->nullable();

            $table->text('admin_comment')->nullable();
            $table->string('ip')->nullable();
            $table->string('forwarded_ip')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'orders_user_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
