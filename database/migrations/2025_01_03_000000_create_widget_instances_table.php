<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Front-placement widget instances (Phase 5.3/A3) — adapted from the
 * archived Widget module's migration. `widget_key` is the primary reference
 * (the stable #[Widget(name:)] slug via WidgetRegistry); `widget_class`
 * is kept as a denormalized FQCN fallback only, per the plan's note that
 * keying purely on FQCN would orphan placements on a class rename.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_instances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('widget_key')->nullable()->index();
            $table->string('widget_class')->nullable();
            $table->string('widget_view')->nullable();
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_instances');
    }
};
