<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restores', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique();
            $table->string('disk');
            $table->string('path');
            $table->string('type')->default('unknown');
            $table->string('size')->nullable();
            $table->timestamp('last_modified')->nullable();
            $table->string('status')->default('available');
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restores');
    }
};
