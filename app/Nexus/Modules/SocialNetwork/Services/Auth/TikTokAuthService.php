<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Auth;

use App\Nexus\Modules\SocialNetwork\Models\SocialToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TikTokAuthService
{
    private const PROVIDER = 'tiktok';

    private const AUTH_URL = 'https://www.tiktok.com/v2/auth/authorize';

    private const TOKEN_URL = 'https://open.tiktokapis.com/v2/oauth/token/';

    private const BUFFER = 300;

    private const CODE_VERIFIER_KEY = 'tiktok:code_verifier';

    public function getAccessToken(): string
    {
        $token = $this->getToken();

        if ($token->isExpired(self::BUFFER)) {
            $token = $this->refresh($token);
        }

        return $token->access_token;
    }

    /**
     * Builds the OAuth authorize URL and caches the PKCE code_verifier for
     * 10 minutes so storeTokensFromCode() can pick it up once the provider
     * redirects back with the authorization code.
     */
    public function getAuthUrl(): string
    {
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        Cache::put(self::CODE_VERIFIER_KEY, $codeVerifier, now()->addMinutes(10));

        return self::AUTH_URL.'?'.http_build_query([
            'client_key' => config('services.tiktok.client_key'),
            'scope' => 'user.info.profile,user.info.stats,video.list',
            'response_type' => 'code',
            'redirect_uri' => route('social-network.callback', ['provider' => 'tiktok']),
            'state' => 'auth',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);
    }

    public function getCachedCodeVerifier(): ?string
    {
        return Cache::get(self::CODE_VERIFIER_KEY);
    }

    public function storeTokensFromCode(string $code, ?string $codeVerifier = null): string
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_key' => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => route('social-network.callback', ['provider' => 'tiktok']),
            'code_verifier' => $codeVerifier,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('TikTok token error: '.$response->body());
        }

        return $this->store($response->json());
    }

    private function getToken(): SocialToken
    {
        $token = SocialToken::where('provider', self::PROVIDER)->first();

        if (! $token) {
            throw new RuntimeException('TikTok not authorized. Run: php artisan tiktok:authorize');
        }

        return $token;
    }

    private function refresh(SocialToken $token): SocialToken
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_key' => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
        ]);

        if ($response->status() === 401) {
            $token->delete();

            throw new RuntimeException('TikTok refresh token expired. Run: php artisan tiktok:authorize');
        }

        if ($response->failed()) {
            throw new RuntimeException('TikTok refresh error: '.$response->body());
        }

        return $this->update($token, $response->json());
    }

    private function store(array $data): string
    {
        $expiresIn = $data['expires_in'] ?? 86400;

        $token = SocialToken::updateOrCreate(
            ['provider' => self::PROVIDER],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($expiresIn),
            ]
        );

        return $token->access_token;
    }

    private function update(SocialToken $token, array $data): SocialToken
    {
        $expiresIn = $data['expires_in'] ?? 86400;

        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token,
            'expires_at' => now()->addSeconds($expiresIn),
        ]);

        return $token;
    }

    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}
