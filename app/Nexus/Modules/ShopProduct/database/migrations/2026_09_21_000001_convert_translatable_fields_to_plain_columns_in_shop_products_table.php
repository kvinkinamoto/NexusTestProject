<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Undoes the separate name_i18n/short_description_i18n/description_i18n
 * columns (added 2026-09-19) in favor of this codebase's own established
 * convention (see Delivery::$title, Role::$display_name): the plain column
 * itself becomes the Spatie-translatable json column, no parallel "_i18n"
 * twin. No ->change() (doctrine/dbal isn't installed) — same add/backfill/
 * drop/rename recipe as Menu's make_menu_items_title_translatable migration.
 * Backfill prefers each row's existing *_i18n data (already correctly
 * bilingual from NexusStoreCatalogSeeder) and only falls back to the plain
 * column's single-locale value where no *_i18n entry exists yet.
 */
return new class extends Migration
{
    private array $fields = ['name', 'short_description', 'description'];

    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                $table->json("{$field}_new")->nullable()->after("{$field}_i18n");
            }
        });

        $locale = config('app.locale');

        DB::table('shop_products')
            ->select('id', 'name', 'name_i18n', 'short_description', 'short_description_i18n', 'description', 'description_i18n')
            ->orderBy('id')
            ->get()
            ->each(function ($row) use ($locale) {
                DB::table('shop_products')->where('id', $row->id)->update([
                    'name_new' => $this->mergeTranslations($row->name, $row->name_i18n, $locale),
                    'short_description_new' => $this->mergeTranslations($row->short_description, $row->short_description_i18n, $locale),
                    'description_new' => $this->mergeTranslations($row->description, $row->description_i18n, $locale),
                ]);
            });

        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'name_i18n',
                'short_description', 'short_description_i18n',
                'description', 'description_i18n',
            ]);
        });

        Schema::table('shop_products', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                $table->renameColumn("{$field}_new", $field);
            }
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                $table->string("{$field}_plain")->nullable()->after($field);
            }
        });

        $locale = config('app.locale');

        DB::table('shop_products')
            ->select('id', ...$this->fields)
            ->orderBy('id')
            ->get()
            ->each(function ($row) use ($locale) {
                $updates = [];
                foreach ($this->fields as $field) {
                    $decoded = json_decode((string) $row->{$field}, true) ?? [];
                    $updates["{$field}_plain"] = $decoded[$locale] ?? (reset($decoded) ?: '');
                }
                DB::table('shop_products')->where('id', $row->id)->update($updates);
            });

        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn($this->fields);
        });

        Schema::table('shop_products', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                $table->renameColumn("{$field}_plain", $field);
            }
        });
    }

    private function mergeTranslations(?string $plain, ?string $i18nJson, string $locale): string
    {
        $translations = $i18nJson ? (json_decode($i18nJson, true) ?: []) : [];

        if (! isset($translations[$locale]) && $plain !== null && $plain !== '') {
            $translations[$locale] = $plain;
        }

        if ($translations === []) {
            $translations = [$locale => (string) $plain];
        }

        return json_encode($translations);
    }
};
