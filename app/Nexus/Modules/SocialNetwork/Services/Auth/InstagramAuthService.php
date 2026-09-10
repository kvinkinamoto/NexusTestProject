<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Auth;

use App\Nexus\Modules\SocialNetwork\Models\SocialToken;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class InstagramAuthService
{
    private const PROVIDER = 'instagram';

    private const BUFFER = 86400;

    private const EXCHANGE_URL = 'https://graph.instagram.com/access_token';

    private const REFRESH_URL = 'https://graph.instagram.com/refresh_access_token';

    private const AUTH_URL = 'https://api.instagram.com/oauth/authorize';

    private const TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    public function getAccessToken(): string
    {
        $token = $this->getToken();

        if ($token->isExpired(self::BUFFER)) {
            $token = $this->refresh($token);
        }

        return $token->access_token;
    }

    public function getUserId(): string
    {
        $token = $this->getToken();

        return (string) ($token->meta['user_id'] ?? throw new RuntimeException('Instagram user ID not found'));
    }

    public function getAuthUrl(): string
    {
        return self::AUTH_URL.'?'.http_build_query([
            'client_id' => config('services.instagram.client_key'),
            'redirect_uri' => route('social-network.callback', ['provider' => 'instagram']),
            'scope' => 'instagram_business_basic,instagram_business_manage_comments,instagram_business_manage_insights',
            'response_type' => 'code',
            'force_reauth' => 'true',
        ]);
    }

    public function storeTokensFromCode(string $code): string
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => config('services.instagram.client_key'),
            'client_secret' => config('services.instagram.client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => route('social-network.callback', ['provider' => 'instagram']),
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Instagram token error: '.$response->body());
        }

        $shortToken = $response->json('access_token');
        $userId = (string) $response->json('user_id');

        $longToken = $this->exchangeForLongLived($shortToken);

        $this->store($longToken, $userId);

        return $longToken;
    }

    public function refreshIfNeeded(): void
    {
        $token = SocialToken::where('provider', self::PROVIDER)->first();

        if (! $token) {
            throw new RuntimeException('Instagram not authorized');
        }

        if ($token->isExpired(self::BUFFER)) {
            $this->refresh($token);
        }
    }

    private function getToken(): SocialToken
    {
        $token = SocialToken::where('provider', self::PROVIDER)->first();

        if (! $token) {
            throw new RuntimeException('Instagram not authorized. Run: php artisan instagram:authorize');
        }

        return $token;
    }

    private function exchangeForLongLived(string $shortToken): string
    {
        $response = Http::get(self::EXCHANGE_URL, [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token' => $shortToken,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Instagram exchange error: '.$response->body());
        }

        return $response->json('access_token');
    }

    private function refresh(SocialToken $token): SocialToken
    {
        $response = Http::get(self::REFRESH_URL, [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $token->access_token,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Instagram refresh error: '.$response->body());
        }

        $token->update([
            'access_token' => $response->json('access_token'),
            'expires_at' => now()->addSeconds($response->json('expires_in', 5184000)),
        ]);

        return $token;
    }

    private function store(string $token, string $userId): void
    {
        SocialToken::updateOrCreate(
            ['provider' => self::PROVIDER],
            [
                'access_token' => $token,
                'refresh_token' => $token,
                'expires_at' => now()->addSeconds(5184000),
                'meta' => ['user_id' => $userId],
            ]
        );
    }
}
