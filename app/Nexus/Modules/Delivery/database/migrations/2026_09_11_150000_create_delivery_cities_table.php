<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->string('ref');
            $table->string('name');
            $table->string('region')->nullable();
            $table->timestamps();

            $table->unique(['delivery_id', 'ref']);
            $table->index(['delivery_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_cities');
    }
};
