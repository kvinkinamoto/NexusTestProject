<?php

namespace App\Nexus\Modules\PaymentMethod\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request, string $provider)
    {
        Log::info('Payment callback info', [
            'provider' => $provider,
            'data' => $request->all(),
        ]);

        try {
            $driver = app(PaymentManager::class)->driver($provider);

            $driver->handleCallback($request->all());

            // Якщо браузер потрапив сюди (Monobank redirect) — перенаправляємо на результат
            if ($request->acceptsHtml()) {
                $orderId = $request->input('store_order_id') ?? $request->input('orderId') ?? $request->input('order_id');
                if ($orderId) {
                    return redirect()->route('payment.result', ['orderId' => $orderId]);
                }

                return redirect('/');
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Payment callback error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            if ($request->acceptsHtml()) {
                return redirect('/');
            }

            return response()->json(['status' => 'error'], 500);
        }
    }
}
