<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->string('package_type')->nullable()->after('type_id');
            $table->string('github_owner')->nullable()->after('package_type');
            $table->string('github_repo')->nullable()->after('github_owner');
            $table->string('min_core_version')->nullable()->after('github_repo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn(['package_type', 'github_owner', 'github_repo', 'min_core_version']);
        });
    }
};
