<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->string('key');
            $table->string('label');
            $table->string('type');
            $table->boolean('required')->default(false);
            $table->text('options')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('form_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
