<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 3 independent pivot tables, one per Demo::permissions*() relation method,
 * so the 3 ajax/preload demo fields (preload / ajax-search-only /
 * ajax-load-mode) don't share selection state through a common pivot —
 * see app/Nexus/Modules/Demo/Models/Demo.php.
 */
return new class extends Migration
{
    private array $tables = [
        'demo_permissions_preload',
        'demo_permissions_search',
        'demo_permissions_load',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('demo_id');
                $table->unsignedBigInteger('permission_id');
                $table->unique(['demo_id', 'permission_id']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
