<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns for the "extra_fields" tab — one per field type ported/added to
 * the Livewire pipeline in this pass (see livewire/field_types/{time,
 * location, multiple_string, radio, slug, color, icon, file, markdown,
 * range, currency, rating, url}.blade.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demos', function (Blueprint $table) {
            $table->time('event_time')->nullable();
            $table->string('office_location')->nullable();
            $table->json('tags')->nullable();
            $table->string('priority_radio')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('theme_color')->nullable();
            $table->string('nav_icon')->nullable();
            $table->string('attachment')->nullable();
            $table->text('notes_markdown')->nullable();
            $table->unsignedInteger('volume')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedTinyInteger('stars')->nullable();
            $table->string('website')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('demos', function (Blueprint $table) {
            $table->dropColumn([
                'event_time', 'office_location', 'tags', 'priority_radio', 'slug',
                'theme_color', 'nav_icon', 'attachment', 'notes_markdown', 'volume',
                'price', 'stars', 'website',
            ]);
        });
    }
};
