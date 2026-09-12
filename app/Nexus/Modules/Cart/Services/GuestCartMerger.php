<?php

namespace App\Nexus\Modules\Cart\Services;

use App\Models\User;
use App\Nexus\Modules\Cart\Models\Cart;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductStockAction;
use Illuminate\Support\Facades\DB;

/**
 * Called explicitly right after a successful login/register/social-login —
 * NOT via a Login event listener. Illuminate\Auth\SessionGuard::login()
 * calls $this->session->regenerate(true) *before* it fires the Login event
 * (see updateSession()), so by the time a Login listener runs,
 * session()->getId() already returns the new post-regenerate id and the
 * guest's original id is unrecoverable from there. Every auth entry point
 * (AuthService::login()/register(), SocialAuthService::callback()) instead
 * captures session()->getId() itself before calling Auth::attempt()/login(),
 * and passes that pre-regenerate id in here.
 */
class GuestCartMerger
{
    public function merge(User $user, ?string $guestSessionId): void
    {
        if (! $guestSessionId) {
            return;
        }

        $guestCart = Cart::where('session_id', $guestSessionId)->first();
        if (! $guestCart) {
            return;
        }

        DB::transaction(function () use ($user, $guestCart) {
            $userCart = $user->cart()->firstOrCreate([]);
            $existing = $userCart->products()->get()->keyBy('product_id');

            foreach ($guestCart->products()->with('product')->get() as $guestItem) {
                $targetQuantity = ($existing->get($guestItem->product_id)?->quantity ?? 0) + $guestItem->quantity;

                $stock = $guestItem->product ? ResolveProductStockAction::handle($guestItem->product) : null;
                if ($stock !== null) {
                    $targetQuantity = min($targetQuantity, $stock);
                }

                $userCart->products()->updateOrCreate(
                    ['product_id' => $guestItem->product_id],
                    ['quantity' => $targetQuantity],
                );
            }

            $guestCart->delete();
        });
    }
}
