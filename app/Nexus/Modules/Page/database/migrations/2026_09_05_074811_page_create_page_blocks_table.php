<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->string('type');
            $table->json('data')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index('page_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
