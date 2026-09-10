<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            // Nullable despite being required Fields: Nexus's admin form saves
            // relation fields in a second pass (associate()->save() after the
            // initial create — see StoreRelationActionMethod), so the base
            // insert briefly has neither FK set. Same pattern as
            // ProductQuestion.product_id/ProductReview.product_id.
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('shop_products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
