<?php

namespace Database\Seeders;

use App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition;
use App\Nexus\Modules\Currency\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * UAH is the base currency (every ShopProduct/order price is stored in it —
 * see NexusStoreCatalogSeeder's own note on LiqPay settling in UAH). USD's
 * rate converts FROM the base currency, so it's 1 / (UAH per USD), using the
 * same ~44.5 rate NexusStoreCatalogSeeder's prices were derived from. English
 * storefront pages resolve USD automatically via
 * CurrencyService::getSelectedCurrency()'s locale fallback — no admin action
 * needed for the default install.
 */
class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::query()->updateOrCreate(
            ['code' => 'UAH'],
            [
                'name' => ['uk' => 'Гривня', 'en' => 'Hryvnia'],
                'symbol' => 'грн',
                'symbol_position' => CurrencySymbolPosition::AFTER,
                'rate' => 1,
                'is_base' => true,
                'publish' => true,
            ],
        );

        Currency::query()->updateOrCreate(
            ['code' => 'USD'],
            [
                'name' => ['uk' => 'Долар США', 'en' => 'US Dollar'],
                'symbol' => '$',
                'symbol_position' => CurrencySymbolPosition::BEFORE,
                'rate' => round(1 / 44.5, 4),
                'is_base' => false,
                'publish' => true,
            ],
        );
    }
}
