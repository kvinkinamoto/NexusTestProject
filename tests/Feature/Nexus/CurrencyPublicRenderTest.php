<?php

use App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition;
use App\Nexus\Modules\Currency\Models\Currency;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Currency'])->run();
});

it('renders the amount with the symbol after it for an after-position currency', function () {
    $uah = Currency::create(['code' => 'UAH', 'symbol' => '₴', 'symbol_position' => CurrencySymbolPosition::AFTER->value, 'rate' => 1, 'is_base' => true]);

    $html = trim(view('currency::public.currency-price', ['amount' => 1234.5, 'currency' => $uah])->render());

    expect($html)->toBe('1 234.50&nbsp;₴');
});

it('renders the symbol before the amount for a before-position currency', function () {
    $usd = Currency::create(['code' => 'USD', 'symbol' => '$', 'symbol_position' => CurrencySymbolPosition::BEFORE->value, 'rate' => 1]);

    $html = trim(view('currency::public.currency-price', ['amount' => 42, 'currency' => $usd])->render());

    expect($html)->toBe('$42.00');
});

it('falls back to the session-selected currency when none is passed explicitly', function () {
    Currency::create(['code' => 'UAH', 'symbol' => '₴', 'symbol_position' => CurrencySymbolPosition::AFTER->value, 'rate' => 1, 'is_base' => true]);
    $usd = Currency::create(['code' => 'USD', 'symbol' => '$', 'symbol_position' => CurrencySymbolPosition::BEFORE->value, 'rate' => 1]);
    session(['currency' => 'USD']);

    $html = trim(view('currency::public.currency-price', ['amount' => 10])->render());

    expect($html)->toBe('$10.00');
});

it('stores the switched currency code in the session and redirects back', function () {
    Currency::create(['code' => 'USD', 'symbol' => '$', 'symbol_position' => CurrencySymbolPosition::BEFORE->value, 'rate' => 1]);

    $response = $this->from('/some-page')->get('/currency/USD');

    $response->assertRedirect('/some-page');
    expect(session('currency'))->toBe('USD');
});
