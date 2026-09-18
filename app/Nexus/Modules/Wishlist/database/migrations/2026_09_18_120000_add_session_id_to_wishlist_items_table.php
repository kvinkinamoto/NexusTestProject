<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WishlistItem's own $fillable and WishlistRepository already branch on a
 * guest `session_id` — this column was the only piece missing for that code
 * path to actually work. Ported from a sibling project's booksite build,
 * which added exactly this column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->string('session_id')->nullable()->after('user_id');

            $table->unique(['session_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropUnique(['session_id', 'product_id']);
            $table->dropColumn('session_id');
        });
    }
};
