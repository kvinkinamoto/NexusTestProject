<?php

namespace App\Nexus\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Order\Http\Resources\OrderResource;
use App\Nexus\Modules\Order\Requests\CheckoutRequest;
use App\Nexus\Modules\Order\Requests\QuickOrderRequest;
use App\Nexus\Modules\Order\Services\OrderService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function store(CheckoutRequest $request)
    {
        try {
            $guestSessionId = auth()->check() ? null : $request->session()->getId();
            $order = $this->orderService->createOrder(auth()->user(), $request->validated(), $guestSessionId);

            return response()->json([
                'success' => true,
                'order' => OrderResource::make($order)->resolve(),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Order creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('order::translate.something_went_wrong'),
            ], 500);
        }
    }

    public function quick(QuickOrderRequest $request)
    {
        try {
            $order = $this->orderService->createQuickOrder(auth()->user(), $request->validated());

            return response()->json([
                'success' => true,
                'order' => OrderResource::make($order)->resolve(),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Quick order creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('order::translate.something_went_wrong'),
            ], 500);
        }
    }
}
