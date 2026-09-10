<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FacebookAuthService
{
    private const ACCESS_KEY = 'facebook:page_token';

    private const PAGE_ID_KEY = 'facebook:page_id';

    private const AUTH_URL = 'https://www.facebook.com/v19.0/dialog/oauth';

    private const TOKEN_URL = 'https://graph.facebook.com/v19.0/oauth/access_token';

    private const GRAPH_URL = 'https://graph.facebook.com/v19.0';

    public function getToken(): string
    {
        if ($token = Cache::get(self::ACCESS_KEY)) {
            return $token;
        }

        throw new RuntimeException('Facebook not authorized. Run: php artisan facebook:authorize');
    }

    public function getPageId(): string
    {
        if ($pageId = Cache::get(self::PAGE_ID_KEY)) {
            return $pageId;
        }

        throw new RuntimeException('Facebook page ID not found. Run: php artisan facebook:authorize');
    }

    public function getAuthUrl(): string
    {
        return self::AUTH_URL.'?'.http_build_query([
            'client_id' => config('services.facebook.app_id'),
            'redirect_uri' => route('social-network.callback', ['provider' => 'facebook']),
            'scope' => 'pages_read_engagement,pages_read_user_content,pages_show_list',
            'response_type' => 'code',
            'state' => 'auth',
        ]);
    }

    public function storeTokensFromCode(string $code): void
    {
        $response = Http::get(self::TOKEN_URL, [
            'client_id' => config('services.facebook.app_id'),
            'client_secret' => config('services.facebook.app_secret'),
            'redirect_uri' => route('social-network.callback', ['provider' => 'facebook']),
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Facebook token error: '.$response->body());
        }

        $shortUserToken = $response->json('access_token');
        $longUserToken = $this->exchangeForLongLived($shortUserToken);

        $pageId = config('services.facebook.page_id');
        $pageToken = $this->getPageToken($longUserToken, $pageId);

        $this->store($pageToken, $pageId);
    }

    public function refreshIfNeeded(): void
    {
        if (Cache::get(self::ACCESS_KEY)) {
            return;
        }

        $userToken = Cache::get('facebook:user_token_backup');

        if (! $userToken) {
            throw new RuntimeException('Facebook token lost. Run: php artisan facebook:authorize');
        }

        $pageId = config('services.facebook.page_id');
        $pageToken = $this->getPageToken($userToken, $pageId);
        $this->store($pageToken, $pageId);
    }

    private function exchangeForLongLived(string $shortToken): string
    {
        $response = Http::get(self::TOKEN_URL, [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.app_id'),
            'client_secret' => config('services.facebook.app_secret'),
            'fb_exchange_token' => $shortToken,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Facebook exchange error: '.$response->body());
        }

        $longToken = $response->json('access_token');

        Cache::put('facebook:user_token_backup', $longToken, now()->addDays(59));

        return $longToken;
    }

    private function getPageToken(string $userToken, string $pageId): string
    {
        $response = Http::get(self::GRAPH_URL.'/me/accounts', [
            'access_token' => $userToken,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Facebook pages error: '.$response->body());
        }

        $pages = collect($response->json('data', []));
        $page = $pages->firstWhere('id', $pageId);

        if (! $page) {
            $page = $pages->first();

            if (! $page) {
                throw new RuntimeException('No Facebook pages found for this account.');
            }

            Log::info('Available Facebook pages: '.$pages->pluck('name', 'id')->toJson());
        }

        return $page['access_token'];
    }

    private function store(string $pageToken, string $pageId): void
    {
        Cache::put(self::ACCESS_KEY, $pageToken, now()->addYear());
        Cache::put(self::PAGE_ID_KEY, $pageId, now()->addYear());
    }
}
