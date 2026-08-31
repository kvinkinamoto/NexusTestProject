<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal-workspace storage for the admin dashboard (Phase 5.6) — deliberately
 * separate from widget_assignments (front placement), which is keyed by
 * (position, target_template) and represents the site's global layout, not a
 * per-user one. Mirrors nexus_user_table_preferences's per-user shape.
 *
 * `role` and `is_default` exist for role-level / system-level fallback rows
 * on top of a user's personal layout; only the personal-row and is_default
 * paths are consumed by DashboardLayoutResolver so far — role-based
 * resolution is deferred until there's a confirmed source for "current
 * user's role name" in this codebase.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('nexus_dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('layout');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nexus_dashboard_layouts');
    }
};
