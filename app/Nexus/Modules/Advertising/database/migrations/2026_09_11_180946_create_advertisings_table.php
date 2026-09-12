<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No columns of its own — see the sibling Analytics migration's
        // comment for why (settings live in SettingsBuilder's own store).
        Schema::create('advertisings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisings');
    }
};
