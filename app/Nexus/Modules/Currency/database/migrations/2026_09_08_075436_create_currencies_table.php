<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->json('name')->nullable();
            $table->string('symbol', 10)->nullable();
            $table->string('symbol_position')->default('after');
            $table->decimal('rate', 12, 4)->default(1.0000);
            $table->boolean('is_base')->default(false);
            $table->boolean('publish')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
