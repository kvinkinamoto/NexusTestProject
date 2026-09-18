<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertising_id')->constrained('advertisings')->cascadeOnDelete();
            $table->string('visitor_key');
            $table->unsignedInteger('views_count')->default(1);
            $table->timestamps();

            $table->unique(['advertising_id', 'visitor_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_impressions');
    }
};
