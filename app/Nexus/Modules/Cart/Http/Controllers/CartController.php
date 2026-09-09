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

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function get(): JsonResponse
    {
        $cart = $this->cartService->getCart(auth()->user());
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
            $this->cartService->add(auth()->user(), $data['id'], $data['quantity']);

            return response()->json(['success' => true]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function updateQuantity(UpdateQuantityRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->cartService->updateQuantity(auth()->user(), $data['id'], $data['quantity']);

            return response()->json(['success' => true]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function remove(RemoveCartRequest $request): JsonResponse
    {
        $this->cartService->remove(auth()->user(), $request->validated('id'));

        return response()->json(['success' => true]);
    }

    public function sync(SyncCartRequest $request): JsonResponse
    {
        $this->cartService->sync(auth()->user(), $request->validated('products'));

        return response()->json(['success' => true]);
    }

    public function applyPromoCode(ApplyPromoCodeRequest $request): JsonResponse
    {
        try {
            $this->cartService->applyPromoCode(auth()->user(), $request->validated('code'));

            return response()->json(['success' => true, 'message' => __('promoCode::translate.applied')]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function removePromoCode(): JsonResponse
    {
        $this->cartService->removePromoCode(auth()->user());

        return response()->json(['success' => true, 'message' => __('promoCode::translate.removed')]);
    }
}
