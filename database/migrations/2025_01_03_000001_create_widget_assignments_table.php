<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a widget_instances row is placed: (position, target_template).
 * NULL target_template = shown on every template. Adapted from the archived
 * Widget module's migration — schema is unchanged, the bug was in the old
 * PHP code writing a 'template_type' key that didn't match this column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_instance_id')->constrained('widget_instances')->cascadeOnDelete();
            $table->string('position');
            $table->string('target_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['position', 'target_template', 'is_active'], 'widget_position_template_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_assignments');
    }
};
