<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded (unlike this codebase's other migrations): this module's
        // directory is registered via loadMigrationsFrom() unconditionally
        // for every discovered module (see NexusServiceProvider::
        // loadMigration()'s "load every module's migrations, not just
        // enabled ones" comment) — observed this path getting queued twice
        // in one migrate run in this project's test environment, which
        // otherwise fails with a bare "table already exists" on a plain
        // Schema::create().
        if (Schema::hasTable('nexus_plugins')) {
            return;
        }

        Schema::create('nexus_plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nexus_plugins');
    }
};
