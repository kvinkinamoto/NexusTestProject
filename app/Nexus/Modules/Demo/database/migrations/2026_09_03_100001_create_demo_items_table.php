<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demo_id');
            $table->string('title');
            $table->integer('qty')->default(1);
            $table->timestamps();

            $table->index('demo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_items');
    }
};
