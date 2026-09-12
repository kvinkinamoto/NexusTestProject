<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ref');
            $table->string('number')->nullable();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->unique(['delivery_id', 'ref']);
            $table->index(['delivery_city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_warehouses');
    }
};
