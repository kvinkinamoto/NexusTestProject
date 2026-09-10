<?php

namespace App\Nexus\Modules\SocialNetwork\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\SocialNetwork\Services\Auth\FacebookAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Auth\InstagramAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Auth\TikTokAuthService;
use Illuminate\Http\Request;

/**
 * OAuth redirect URI for Facebook/Instagram/TikTok. When the browser still
 * carries an authenticated admin session (the normal case: the whole flow
 * happens in one tab, started from AuthorizeSocialNetworkController), the
 * token exchange completes automatically and the admin is bounced back to
 * the module list. Otherwise it falls back to printing the raw code for the
 * "php artisan {provider}:authorize --code=..." CLI flow.
 */
class SocialOAuthController extends Controller
{
    public function callback(Request $request, string $provider)
    {
        if ($request->has('error')) {
            return 'Error: '.e($request->input('error_description'));
        }

        $code = $request->input('code');

        if (! auth()->check()) {
            $safeCode = e($code);
            $safeProvider = e($provider);

            return "<h2>{$safeProvider} Authorization Code:</h2><code style='font-size:18px'>{$safeCode}</code>
                <p>Run command:<br><code>php artisan {$safeProvider}:authorize --code={$safeCode}</code></p>";
        }

        try {
            match ($provider) {
                'facebook' => app(FacebookAuthService::class)->storeTokensFromCode($code),
                'instagram' => app(InstagramAuthService::class)->storeTokensFromCode($code),
                'tiktok' => (function () use ($code) {
                    $tikTok = app(TikTokAuthService::class);
                    $tikTok->storeTokensFromCode($code, $tikTok->getCachedCodeVerifier());
                })(),
                default => throw new \RuntimeException("Unsupported provider: {$provider}"),
            };

            return redirect()
                ->route('nexus.module.action', ['module' => 'socialNetwork', 'action' => 'index'])
                ->with('alert_message', __('socialNetwork::translate.authorize_success', ['provider' => $provider]))
                ->with('alert_type', 'success');
        } catch (\Throwable $e) {
            return redirect()
                ->route('nexus.module.action', ['module' => 'socialNetwork', 'action' => 'index'])
                ->with('alert_message', __('socialNetwork::translate.authorize_failed', ['provider' => $provider, 'message' => $e->getMessage()]))
                ->with('alert_type', 'danger');
        }
    }
}
