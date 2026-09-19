<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same bug shape as add_last_name_and_phone_to_users_table: User::$fillable
 * and its #[Field] attributes already declare birthday/gender (admin form +
 * account-profile.blade.php both read $user->birthday/$user->gender), but
 * the base users migration never created either column — a logged-in visit
 * to /account/profile threw MissingAttributeException. Additive, idempotent
 * with the existing pattern.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthday')->nullable()->after('phone');
            $table->string('gender')->nullable()->after('birthday');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birthday', 'gender']);
        });
    }
};
