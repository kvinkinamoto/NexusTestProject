<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MenuItem::title becomes Spatie-translatable (see MenuItem::$translatable)
 * so an item's label can vary per language once the site has more than
 * one — same json-column shape as the existing display_name precedent at
 * packages/nodex/nexus/src/Modules/Permission/database/migrations. No
 * ->change() (doctrine/dbal isn't installed in this project) — add the new
 * column, backfill every existing row's plain string under the current
 * app locale, drop the old column, rename the new one into its place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->json('title_i18n')->nullable()->after('title');
        });

        $locale = config('app.locale');

        DB::table('menu_items')->select('id', 'title')->orderBy('id')->get()->each(function ($row) use ($locale) {
            DB::table('menu_items')->where('id', $row->id)->update([
                'title_i18n' => json_encode([$locale => $row->title]),
            ]);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('title');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->renameColumn('title_i18n', 'title');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('title_plain')->nullable()->after('title');
        });

        $locale = config('app.locale');

        DB::table('menu_items')->select('id', 'title')->orderBy('id')->get()->each(function ($row) use ($locale) {
            $decoded = json_decode((string) $row->title, true) ?? [];
            $plain = $decoded[$locale] ?? reset($decoded) ?: '';
            DB::table('menu_items')->where('id', $row->id)->update(['title_plain' => $plain]);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('title');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->renameColumn('title_plain', 'title');
        });
    }
};
