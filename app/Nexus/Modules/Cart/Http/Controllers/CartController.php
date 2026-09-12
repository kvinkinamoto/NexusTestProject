<?php

namespace App\Nexus\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Cart\Http\Resources\CartProductResource;
use App\Nexus\Modules\Cart\Requests\AddCartRequest;
use App\Nexus\Modules\Cart\Requests\ApplyPromoCodeRequest;
use App\Nexus\Modules\Cart\Requests\RemoveCartRequest;
use App\Nexus\Modules\Cart\Requests\SyncCartRequest;
use App\Nexus\Modules\Cart\Requests\UpdateQuantityRequest;
use App\Nexus\Modules\Cart\Services\CartService;
use App\Nexus\Modules\Currency\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    /**
     * A guest is identified by their session id (no auth/token of their
     * own) — the same session cookie the `web` middleware group already
     * requires for CSRF, so no new client-side identity to manage.
     */
    private function sessionId(Request $request): ?string
    {
        return auth()->check() ? null : $request->session()->getId();
    }

    public function get(Request $request): JsonResponse
    {
        $cart = $this->cartService->resolveCart(auth()->user(), $this->sessionId($request));
        $products = $cart->products()->with('product')->get();
        $breakdown = $this->cartService->getBreakdownForCart($cart);

        return response()->json([
            ...$breakdown,
            'total_price_converted' => CurrencyService::convertToSessionCurrency($breakdown['total_price']),
            'products' => CartProductResource::collection($products)->resolve(),
        ]);
    }

    public function add(AddCartRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->cartService->add(auth()->user(), $this->sessionId($request), $data['id'], $data['quantity']);

            return response()->json(['success' => true]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function updateQuantity(UpdateQuantityRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->cartService->updateQuantity(auth()->user(), $this->sessionId($request), $data['id'], $data['quantity']);

            return response()->json(['success' => true]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function remove(RemoveCartRequest $request): JsonResponse
    {
        $this->cartService->remove(auth()->user(), $this->sessionId($request), $request->validated('id'));

        return response()->json(['success' => true]);
    }

    public function sync(SyncCartRequest $request): JsonResponse
    {
        $this->cartService->sync(auth()->user(), $this->sessionId($request), $request->validated('products'));

        return response()->json(['success' => true]);
    }

    public function applyPromoCode(ApplyPromoCodeRequest $request): JsonResponse
    {
        try {
            $this->cartService->applyPromoCode(auth()->user(), $this->sessionId($request), $request->validated('code'));

            return response()->json(['success' => true, 'message' => __('promoCode::translate.applied')]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function removePromoCode(Request $request): JsonResponse
    {
        $this->cartService->removePromoCode(auth()->user(), $this->sessionId($request));

        return response()->json(['success' => true, 'message' => __('promoCode::translate.removed')]);
    }
}
