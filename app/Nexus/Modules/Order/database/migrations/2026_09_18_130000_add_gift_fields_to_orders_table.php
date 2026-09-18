<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distinct from the pre-existing is_another_recipient/recipient_* fields
 * (those are delivery-contact info) — a gift order grants recipient_user_id
 * ownership of the order once redeemed via GiftCodeService::redeem(), it
 * does not change where the physical goods (if any) get delivered.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_gift')->default(false)->after('recipient_phone');
            $table->string('gift_recipient_email')->nullable()->after('is_gift');
            $table->text('gift_message')->nullable()->after('gift_recipient_email');
            $table->foreignId('recipient_user_id')->nullable()->after('gift_message')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_user_id');
            $table->dropColumn(['is_gift', 'gift_recipient_email', 'gift_message']);
        });
    }
};
