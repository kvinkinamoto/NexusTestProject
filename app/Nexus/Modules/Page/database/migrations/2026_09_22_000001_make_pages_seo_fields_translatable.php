<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Page::meta_title/meta_description become Spatie-translatable (see
 * Page::$translatable) so SEO copy can vary per language too, same json-
 * column shape as MenuItem::title's own migration — no ->change() (doctrine/
 * dbal isn't installed), add the new column, backfill every existing row's
 * plain string under the current app locale, drop the old column, rename the
 * new one into its place. Both fields are nullable, so an empty existing
 * value is left as a null json column rather than an empty-string locale row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('meta_title_i18n')->nullable()->after('meta_title');
            $table->json('meta_description_i18n')->nullable()->after('meta_description');
        });

        $locale = config('app.locale');

        DB::table('pages')->select('id', 'meta_title', 'meta_description')->orderBy('id')->get()->each(function ($row) use ($locale) {
            DB::table('pages')->where('id', $row->id)->update([
                'meta_title_i18n' => $row->meta_title !== null && $row->meta_title !== '' ? json_encode([$locale => $row->meta_title]) : null,
                'meta_description_i18n' => $row->meta_description !== null && $row->meta_description !== '' ? json_encode([$locale => $row->meta_description]) : null,
            ]);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('meta_title_i18n', 'meta_title');
            $table->renameColumn('meta_description_i18n', 'meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('meta_title_plain')->nullable()->after('meta_title');
            $table->text('meta_description_plain')->nullable()->after('meta_description');
        });

        $locale = config('app.locale');

        DB::table('pages')->select('id', 'meta_title', 'meta_description')->orderBy('id')->get()->each(function ($row) use ($locale) {
            $titleDecoded = json_decode((string) $row->meta_title, true) ?? [];
            $descriptionDecoded = json_decode((string) $row->meta_description, true) ?? [];

            DB::table('pages')->where('id', $row->id)->update([
                'meta_title_plain' => $titleDecoded[$locale] ?? (reset($titleDecoded) ?: null),
                'meta_description_plain' => $descriptionDecoded[$locale] ?? (reset($descriptionDecoded) ?: null),
            ]);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('meta_title_plain', 'meta_title');
            $table->renameColumn('meta_description_plain', 'meta_description');
        });
    }
};
