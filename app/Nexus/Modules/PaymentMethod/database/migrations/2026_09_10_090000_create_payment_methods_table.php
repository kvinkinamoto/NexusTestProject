<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('type');
            $table->string('provider');
            $table->json('title');
            $table->json('description')->nullable();
            $table->boolean('publish')->default(true);
            $table->unsignedInteger('count_installment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
