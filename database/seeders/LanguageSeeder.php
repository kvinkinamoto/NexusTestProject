<?php

namespace Database\Seeders;

use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Database\Seeder;

/**
 * Without at least one Language row, FormBuilder::getLanguages() returns an
 * empty collection and every translatable field in the admin (ShopProduct
 * name/description, ShopCategory name, ...) renders zero locale rows — the
 * translation inputs simply don't appear. uk is the storefront's existing
 * single locale (APP_LOCALE), en is the one already-seeded catalog copy has
 * real translations for.
 */
class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::query()->updateOrCreate(
            ['code' => 'uk'],
            ['language' => 'Українська', 'is_default' => true, 'publish' => true, 'is_rtl' => false],
        );

        Language::query()->updateOrCreate(
            ['code' => 'en'],
            ['language' => 'English', 'is_default' => false, 'publish' => true, 'is_rtl' => false],
        );
    }
}
