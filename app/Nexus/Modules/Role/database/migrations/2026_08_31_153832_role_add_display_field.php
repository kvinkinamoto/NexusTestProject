<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('display_name')->nullable();
        });
    }

    public function down(): void
    {
    }
};
