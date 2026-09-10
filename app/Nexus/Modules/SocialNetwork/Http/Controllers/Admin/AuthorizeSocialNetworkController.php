<?php

namespace App\Nexus\Modules\SocialNetwork\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\SocialNetwork\Enums\SocialPlatformEnum;
use App\Nexus\Modules\SocialNetwork\Models\SocialNetwork;
use App\Nexus\Modules\SocialNetwork\Services\Auth\FacebookAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Auth\InstagramAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Auth\TikTokAuthService;
use Illuminate\Http\RedirectResponse;

/**
 * Admin-panel trigger for the OAuth flow — SocialNetwork isn't its own
 * registered route target (no equivalent of the old bespoke `authorize` row
 * action exists in the current #[TableAction] system), so this is a plain
 * admin route instead, same pattern as Shipment's refresh action (Phase 7).
 */
class AuthorizeSocialNetworkController extends Controller
{
    public function __invoke(int $id): RedirectResponse
    {
        $social = SocialNetwork::find($id);

        if (! $social || ! $social->platform) {
            return redirect()->back()
                ->with('alert_message', __('socialNetwork::translate.authorize_missing_platform'))
                ->with('alert_type', 'danger');
        }

        try {
            $url = match ($social->platform->value) {
                SocialPlatformEnum::FACEBOOK->value => app(FacebookAuthService::class)->getAuthUrl(),
                SocialPlatformEnum::INSTAGRAM->value => app(InstagramAuthService::class)->getAuthUrl(),
                SocialPlatformEnum::TIKTOK->value => app(TikTokAuthService::class)->getAuthUrl(),
                default => throw new \RuntimeException(
                    __('socialNetwork::translate.authorize_not_oauth', ['platform' => $social->platform->value])
                ),
            };
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('alert_message', $e->getMessage())
                ->with('alert_type', 'danger');
        }

        return redirect()->away($url);
    }
}
