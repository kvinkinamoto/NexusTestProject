<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No columns of its own — #[Setting] values (see Models/Analytics.php)
        // are stored generically by SettingsBuilder, not on this table. The
        // table only needs to exist for Nexus's module machinery to resolve
        // Analytics::query() without erroring; it stays permanently empty.
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
