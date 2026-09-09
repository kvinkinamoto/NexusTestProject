<?php

use App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition;
use App\Nexus\Modules\Currency\Models\Currency;
use App\Nexus\Modules\Currency\Services\CurrencyService;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Currency'])->run();

    $this->uah = Currency::create([
        'code' => 'UAH', 'symbol' => '₴', 'symbol_position' => CurrencySymbolPosition::AFTER->value,
        'rate' => 1, 'is_base' => true, 'publish' => true,
    ]);
    $this->usd = Currency::create([
        'code' => 'USD', 'symbol' => '$', 'symbol_position' => CurrencySymbolPosition::BEFORE->value,
        'rate' => 0.024, 'is_base' => false, 'publish' => true,
    ]);
});

it('returns the price unchanged (rounded) when converting to the base currency', function () {
    expect(CurrencyService::convertToTargetCurrency(1000, $this->uah))->toBe(1000.0);
});

it('multiplies the base price by the target currency rate', function () {
    expect(CurrencyService::convertToTargetCurrency(1000, $this->usd))->toBe(24.0);
});

it('falls back to the base currency when nothing is selected in the session', function () {
    expect(CurrencyService::getSelectedCurrency()->code)->toBe('UAH');
    expect(CurrencyService::convertToSessionCurrency(1000))->toBe(1000.0);
});

it('converts using whichever currency code is stored in the session', function () {
    session(['currency' => 'USD']);

    expect(CurrencyService::getSelectedCurrency()->code)->toBe('USD');
    expect(CurrencyService::convertToSessionCurrency(1000))->toBe(24.0);
});

it('ignores an unpublished currency code stored in the session and falls back to base', function () {
    $this->usd->update(['publish' => false]);
    session(['currency' => 'USD']);

    expect(CurrencyService::getSelectedCurrency()->code)->toBe('UAH');
});

it('finds the base currency regardless of which one is currently selected', function () {
    expect(CurrencyService::getBaseCurrency()->code)->toBe('UAH');
});
