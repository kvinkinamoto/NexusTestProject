<?php

use App\Nexus\Modules\SocialNetwork\Models\SocialToken;
use App\Nexus\Modules\SocialNetwork\Services\Auth\InstagramAuthService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'SocialNetwork'])->run();

    config(['services.instagram.client_key' => 'key', 'services.instagram.client_secret' => 'secret']);

    $this->auth = app(InstagramAuthService::class);
});

it('throws until authorized', function () {
    $this->auth->getAccessToken();
})->throws(RuntimeException::class, 'Instagram not authorized');

it('exchanges a short-lived code for a long-lived token and stores it', function () {
    Http::fake([
        '*api.instagram.com/oauth/access_token*' => Http::response(['access_token' => 'short-token', 'user_id' => 123], 200),
        '*graph.instagram.com/access_token*' => Http::response(['access_token' => 'long-token'], 200),
    ]);

    $this->auth->storeTokensFromCode('a-code');

    $token = SocialToken::where('provider', 'instagram')->firstOrFail();
    expect($token->access_token)->toBe('long-token');
    expect($token->meta['user_id'])->toBe('123');
    expect($this->auth->getAccessToken())->toBe('long-token');
    expect($this->auth->getUserId())->toBe('123');
});

it('refreshes an expired token', function () {
    SocialToken::create([
        'provider' => 'instagram',
        'access_token' => 'stale-token',
        'expires_at' => now()->subDay(),
        'meta' => ['user_id' => 123],
    ]);

    Http::fake([
        '*refresh_access_token*' => Http::response(['access_token' => 'fresh-token', 'expires_in' => 5184000], 200),
    ]);

    expect($this->auth->getAccessToken())->toBe('fresh-token');
});
