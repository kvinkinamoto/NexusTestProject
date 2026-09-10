<?php

namespace App\Nexus\Modules\Language\Http\Controllers;

use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Http\JsonResponse;

/**
 * The old ZentaraStartProject module's LanguageFrontendRepository fed a
 * Blade view composer (MainLayoutComposer -> siteFrontend::layouts.layout)
 * — no equivalent frontend theme exists in Nexus (same fact behind several
 * earlier phases' cuts: Cart, Wishlist, SocialNetwork posts, Order all
 * expose a plain public API instead of a composed view for the same
 * reason). Whatever storefront eventually gets built can consume this.
 */
class LanguageController
{
    public function index(): JsonResponse
    {
        $languages = Language::query()->publish()->orderByDesc('is_default')->get(['id', 'language', 'code', 'is_default', 'is_rtl']);

        return response()->json([
            'languages' => $languages,
            'current_locale' => app()->getLocale(),
        ]);
    }
}
