<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Same fix as shop_products' companion migration: drops the separate
 * name_i18n column in favor of making `name` itself the Spatie-translatable
 * json column, matching this codebase's own convention (Delivery::$title,
 * Role::$display_name). See that migration's docblock for the full reasoning.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->json('name_new')->nullable()->after('name_i18n');
        });

        $locale = config('app.locale');

        DB::table('shop_categories')
            ->select('id', 'name', 'name_i18n')
            ->orderBy('id')
            ->get()
            ->each(function ($row) use ($locale) {
                $translations = $row->name_i18n ? (json_decode($row->name_i18n, true) ?: []) : [];

                if (! isset($translations[$locale]) && $row->name !== null && $row->name !== '') {
                    $translations[$locale] = $row->name;
                }

                if ($translations === []) {
                    $translations = [$locale => (string) $row->name];
                }

                DB::table('shop_categories')->where('id', $row->id)->update([
                    'name_new' => json_encode($translations),
                ]);
            });

        Schema::table('shop_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'name_i18n']);
        });

        Schema::table('shop_categories', function (Blueprint $table) {
            $table->renameColumn('name_new', 'name');
        });
    }

    public function down(): void
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->string('name_plain')->nullable()->after('name');
        });

        $locale = config('app.locale');

        DB::table('shop_categories')->select('id', 'name')->orderBy('id')->get()->each(function ($row) use ($locale) {
            $decoded = json_decode((string) $row->name, true) ?? [];
            DB::table('shop_categories')->where('id', $row->id)->update([
                'name_plain' => $decoded[$locale] ?? (reset($decoded) ?: ''),
            ]);
        });

        Schema::table('shop_categories', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('shop_categories', function (Blueprint $table) {
            $table->renameColumn('name_plain', 'name');
        });
    }
};
