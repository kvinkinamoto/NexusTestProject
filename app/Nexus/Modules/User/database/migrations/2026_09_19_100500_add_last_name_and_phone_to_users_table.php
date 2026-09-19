<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User::$fillable already lists last_name/phone (see the #[Field] for
 * phone in User.php) but the base users migration never created these
 * columns — every logged-in visit to checkout.blade.php/account-profile
 * .blade.php (both read $user->last_name/$user->phone) threw
 * MissingAttributeException. Additive, same pattern as PaymentMethod's
 * add_bank_order_id_to_orders_table migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('last_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'phone']);
        });
    }
};
