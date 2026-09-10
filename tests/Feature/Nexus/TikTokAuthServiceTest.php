<?php

use App\Nexus\Modules\SocialNetwork\Models\SocialToken;
use App\Nexus\Modules\SocialNetwork\Services\Auth\TikTokAuthService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'SocialNetwork'])->run();

    config(['services.tiktok.client_key' => 'key', 'services.tiktok.client_secret' => 'secret']);

    $this->auth = app(TikTokAuthService::class);
});

it('caches a PKCE code verifier when building the auth url', function () {
    $url = $this->auth->getAuthUrl();

    expect($url)->toContain('code_challenge=');
    expect($this->auth->getCachedCodeVerifier())->not->toBeNull();
});

it('throws until authorized', function () {
    $this->auth->getAccessToken();
})->throws(RuntimeException::class, 'TikTok not authorized');

it('exchanges a code for tokens and stores them', function () {
    Http::fake([
        '*oauth/token*' => Http::response(['access_token' => 'access-1', 'refresh_token' => 'refresh-1', 'expires_in' => 86400], 200),
    ]);

    $this->auth->storeTokensFromCode('a-code', 'verifier');

    expect(SocialToken::where('provider', 'tiktok')->firstOrFail()->access_token)->toBe('access-1');
    expect($this->auth->getAccessToken())->toBe('access-1');
});

it('deletes the token and asks for re-authorization when the refresh token itself is expired', function () {
    SocialToken::create(['provider' => 'tiktok', 'access_token' => 'old', 'refresh_token' => 'old-refresh', 'expires_at' => now()->subDay()]);

    Http::fake([
        '*oauth/token*' => Http::response([], 401),
    ]);

    $this->auth->getAccessToken();
})->throws(RuntimeException::class, 'refresh token expired');
