<?php

namespace App\Nexus\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Auth\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class SocialAuthController extends Controller
{
    public function __construct(
        protected SocialAuthService $socialAuthService,
    ) {
    }

    public function redirect(string $provider)
    {
        return $this->socialAuthService->redirect($provider);
    }

    public function callback(string $provider)
    {
        try {
            $this->socialAuthService->callback($provider);
        } catch (Throwable $e) {
            Log::warning('Social auth failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('shop.home')
                ->with('social_auth_error', __('auth::translate.social.failed'));
        }

        return redirect()->route('account.profile.edit', ['justLoggedIn' => 1]);
    }
}
