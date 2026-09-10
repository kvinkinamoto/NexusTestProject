<?php

use App\Nexus\Modules\SocialNetwork\Services\Auth\FacebookAuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config(['services.facebook.app_id' => 'app-id', 'services.facebook.app_secret' => 'app-secret', 'services.facebook.page_id' => 'page-1']);

    $this->auth = app(FacebookAuthService::class);
});

it('throws until authorized', function () {
    $this->auth->getToken();
})->throws(RuntimeException::class, 'Facebook not authorized');

it('exchanges a short-lived code for a page token and caches it', function () {
    Http::fake([
        '*oauth/access_token*grant_type=fb_exchange_token*' => Http::response(['access_token' => 'long-user-token'], 200),
        '*oauth/access_token*' => Http::response(['access_token' => 'short-user-token'], 200),
        '*me/accounts*' => Http::response(['data' => [
            ['id' => 'page-1', 'name' => 'My Page', 'access_token' => 'page-token-1'],
        ]], 200),
    ]);

    $this->auth->storeTokensFromCode('a-code');

    expect($this->auth->getToken())->toBe('page-token-1');
    expect($this->auth->getPageId())->toBe('page-1');
});

it('falls back to the first page when the configured page id is not found', function () {
    Http::fake([
        '*oauth/access_token*grant_type=fb_exchange_token*' => Http::response(['access_token' => 'long-user-token'], 200),
        '*oauth/access_token*' => Http::response(['access_token' => 'short-user-token'], 200),
        '*me/accounts*' => Http::response(['data' => [
            ['id' => 'other-page', 'name' => 'Another Page', 'access_token' => 'page-token-2'],
        ]], 200),
    ]);

    $this->auth->storeTokensFromCode('a-code');

    expect($this->auth->getToken())->toBe('page-token-2');
});

it('refreshes from the cached user-token backup when the page token is missing', function () {
    Cache::put('facebook:user_token_backup', 'backed-up-user-token', now()->addDay());

    Http::fake([
        '*me/accounts*' => Http::response(['data' => [
            ['id' => 'page-1', 'name' => 'My Page', 'access_token' => 'refreshed-page-token'],
        ]], 200),
    ]);

    $this->auth->refreshIfNeeded();

    expect($this->auth->getToken())->toBe('refreshed-page-token');
});

it('fails to refresh when there is no backed-up user token', function () {
    $this->auth->refreshIfNeeded();
})->throws(RuntimeException::class, 'Facebook token lost');
