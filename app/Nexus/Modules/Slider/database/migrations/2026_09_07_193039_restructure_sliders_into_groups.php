<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Turns the flat slide list into groups: `sliders` becomes the container
 * (name/key/is_active — a site can now have several, independently
 * published), and every existing row's slide content (title/subtitle/image/
 * link_url/link_text/position) moves into a new `slides` child table keyed
 * by slider_id, matching Form/FormField's repeater pattern.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('key')->nullable()->unique()->after('name');
        });

        DB::table('sliders')->orderBy('id')->get()->each(function ($row) {
            DB::table('sliders')->where('id', $row->id)->update([
                'name' => $row->title ?: "Slider {$row->id}",
                'key' => Str::slug(($row->title ?: 'slider').'-'.$row->id),
            ]);
        });

        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slider_id');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image');
            $table->string('link_url')->nullable();
            $table->string('link_text')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        DB::table('sliders')->orderBy('id')->get()->each(function ($row) {
            DB::table('slides')->insert([
                'slider_id' => $row->id,
                'title' => $row->title,
                'subtitle' => $row->subtitle,
                'image' => $row->image,
                'link_url' => $row->link_url,
                'link_text' => $row->link_text,
                'order' => $row->position ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle', 'link_url', 'link_text', 'position', 'image']);
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_text')->nullable();
            $table->integer('position')->default(0);
            $table->string('image')->nullable();
        });

        DB::table('slides')->orderBy('slider_id')->orderBy('order')->get()->groupBy('slider_id')->each(function ($slides, $sliderId) {
            $first = $slides->first();
            DB::table('sliders')->where('id', $sliderId)->update([
                'title' => $first->title,
                'subtitle' => $first->subtitle,
                'image' => $first->image,
                'link_url' => $first->link_url,
                'link_text' => $first->link_text,
                'position' => $first->order,
            ]);
        });

        Schema::dropIfExists('slides');

        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['name', 'key']);
        });
    }
};
