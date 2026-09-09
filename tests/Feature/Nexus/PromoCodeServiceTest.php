<?php

use App\Models\User;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use App\Nexus\Modules\PromoCode\Services\PromoCodeService;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'PromoCode'])->run();

    $this->service = app(PromoCodeService::class);
    $this->rule = DiscountRule::create(['name' => 'Cart 10%', 'type' => DiscountRuleType::CART_THRESHOLD->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10]);
});

it('finds a code case-insensitively', function () {
    PromoCode::create(['code' => 'summer10', 'discount_rule_id' => $this->rule->id]);

    expect($this->service->findByCode('Summer10')?->code)->toBe('SUMMER10');
    expect($this->service->findByCode(' summer10 ')?->code)->toBe('SUMMER10');
});

it('returns null for a code that does not exist', function () {
    expect($this->service->findByCode('NOPE'))->toBeNull();
});

it('rejects an unpublished code', function () {
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'publish' => false]);

    $this->service->validate($code, null);
})->throws(DomainException::class);

it('rejects a code that has not started yet', function () {
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'starts_at' => now()->addDay()]);

    $this->service->validate($code, null);
})->throws(DomainException::class);

it('rejects an expired code', function () {
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'expires_at' => now()->subDay()]);

    $this->service->validate($code, null);
})->throws(DomainException::class);

it('accepts a code within its active window', function () {
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'starts_at' => now()->subDay(), 'expires_at' => now()->addDay(), 'publish' => true]);

    $this->service->validate($code, null);
})->throwsNoExceptions();

it('rejects a code once the global usage limit is reached', function () {
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'usage_limit' => 1, 'publish' => true]);
    $this->service->recordUsage($code, null, 10.0);

    $this->service->validate($code, null);
})->throws(DomainException::class);

it('rejects a code once a specific user has reached their per-user usage limit', function () {
    $user = User::factory()->create();
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'usage_limit_per_user' => 1, 'publish' => true]);
    $this->service->recordUsage($code, $user, 10.0);

    $this->service->validate($code, $user);
})->throws(DomainException::class);

it('does not enforce the per-user limit for a guest (no user)', function () {
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'usage_limit_per_user' => 1, 'publish' => true]);

    $this->service->validate($code, null);
})->throwsNoExceptions();

it('lets another user use a code that one user has already exhausted', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'usage_limit_per_user' => 1, 'publish' => true]);
    $this->service->recordUsage($code, $a, 10.0);

    $this->service->validate($code, $b);
})->throwsNoExceptions();

it('isUsable() reports false instead of throwing', function () {
    $code = PromoCode::create(['code' => 'OFF', 'discount_rule_id' => $this->rule->id, 'publish' => false]);

    expect($this->service->isUsable($code, null))->toBeFalse();
});
