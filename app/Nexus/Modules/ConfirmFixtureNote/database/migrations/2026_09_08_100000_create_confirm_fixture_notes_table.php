<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confirm_fixture_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('confirm_fixture_id')->constrained()->cascadeOnDelete();
            $table->string('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confirm_fixture_notes');
    }
};
