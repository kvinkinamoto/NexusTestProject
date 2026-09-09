<?php

use App\Nexus\Modules\Slider\Models\Slider;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Slider'])->run();
});

it('renders only the requested, active slider\'s slides, in order', function () {
    $homepage = Slider::create(['name' => 'Homepage', 'key' => 'homepage', 'is_active' => true]);
    $homepage->slides()->create(['title' => 'Second', 'image' => 'uploads/second.jpg', 'order' => 2]);
    $homepage->slides()->create(['title' => 'First', 'image' => 'uploads/first.jpg', 'order' => 1]);

    $other = Slider::create(['name' => 'Checkout Promo', 'key' => 'checkout-promo', 'is_active' => true]);
    $other->slides()->create(['title' => 'Other Slider Slide', 'image' => 'uploads/other.jpg', 'order' => 0]);

    $html = view('slider::public.slider', ['key' => 'homepage'])->render();

    expect($html)->toContain('First')
        ->and($html)->toContain('Second')
        ->and($html)->not->toContain('Other Slider Slide')
        ->and(strpos($html, 'First'))->toBeLessThan(strpos($html, 'Second'));
});

it('renders nothing for an inactive slider', function () {
    $promo = Slider::create(['name' => 'Checkout Promo', 'key' => 'checkout-promo', 'is_active' => false]);
    $promo->slides()->create(['title' => 'Hidden Slide', 'image' => 'uploads/hidden.jpg', 'order' => 0]);

    $html = view('slider::public.slider', ['key' => 'checkout-promo'])->render();

    expect(trim($html))->toBe('');
});

it('renders nothing for an unknown key', function () {
    $html = view('slider::public.slider', ['key' => 'does-not-exist'])->render();

    expect(trim($html))->toBe('');
});
