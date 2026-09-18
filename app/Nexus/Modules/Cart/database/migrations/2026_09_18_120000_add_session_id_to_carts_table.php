<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CartService::resolveCart()/findCart() already branch on a guest
 * `session_id` (Cart::firstOrCreate(['session_id' => $sessionId])), and
 * GuestCartMerger already merges a session cart into the user's cart on
 * login — this column was the only piece missing for that code path to
 * actually work. Ported from a sibling project's booksite build, which
 * added exactly this column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('session_id')->nullable()->unique()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};
