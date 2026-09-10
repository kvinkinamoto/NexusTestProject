<?php

namespace App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers;

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentDriverInterface;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonobankInstallmentDriver implements PaymentDriverInterface
{
    protected string $storeId;

    protected string $secret;

    protected string $url;

    public function __construct()
    {
        $this->storeId = config('payment.monobank.store_id');
        $this->secret = config('payment.monobank.secret');
        $this->url = config('payment.monobank.url');
    }

    public function createPayment(Order $order): RedirectResponse
    {
        $products = [];
        $productsTotal = 0;
        foreach ($order->products as $product) {
            if ((float) $product->total_price <= 0) {
                continue;
            }
            $products[] = [
                'name' => $product->localizedField('name'),
                'count' => $product->quantity,
                'sum' => round((float) $product->total_price, 2),
            ];
            $productsTotal = round($productsTotal + round($product->total_price, 2), 2);
        }

        if ($order->delivery_price && $order->delivery_price != 0) {
            $products[] = [
                'name' => 'Доставка',
                'count' => 1,
                'sum' => (float) $order->delivery_price,
            ];
            $productsTotal = round($productsTotal + $order->delivery_price, 2);
        }

        $payload = [
            'store_order_id' => (string) $order->id,
            'client_phone' => $order->user_phone,
            'total_sum' => (float) $productsTotal,

            'invoice' => [
                'date' => now()->format('Y-m-d'),
                'number' => (string) $order->id,
                'point_id' => 1234,
                'source' => 'INTERNET',
            ],

            'available_programs' => [
                [
                    'available_parts_count' => [$order->paymentMethod->count_installment],
                    'type' => 'payment_installments',
                ],
            ],

            'products' => $products,

            'result_callback' => route('payment.callback', ['provider' => 'mono_installment']),
        ];

        Log::info('Monobank payload', ['payload' => $payload]);

        try {
            $data = $this->request('/api/order/create', $payload);

            $order->update(['bank_order_id' => $data['order_id']]);

            return redirect()->route('payment.result', ['orderId' => $order->id]);
        } catch (Exception $e) {
            Log::error('Monobank createPayment failed', [
                'order_id' => $order->id,
                'status' => $e->getCode(),
                'response' => $e->getMessage(),
            ]);

            return redirect('/')->with('payment_error', 'Не вдалося ініціювати оплату через Monobank. Перевірте Ваш ліміт на оплату частинами або номер телефону.');
        }
    }

    public function handleCallback(array $data): void
    {
        $signature = $data['signature'] ?? '';
        // The signature covers only the payload Monobank actually signed —
        // its own signature field is never part of that, same as the other
        // two drivers keep signature out of the string they hash.
        $rawBody = json_encode(array_diff_key($data, ['signature' => true]), JSON_UNESCAPED_UNICODE);

        if (! $this->verifySignature($rawBody, $signature)) {
            Log::warning('Monobank invalid signature', $data);

            return;
        }

        Log::info('Monobank callback received', $data);

        $order = Order::find($data['store_order_id']);

        if (! $order) {
            Log::warning('Monobank order not found', $data);

            return;
        }

        $this->processStatus($order, $data);
    }

    public function checkPayment(Order $order): array
    {
        $data = $this->request('/api/order/state', ['order_id' => $order->bank_order_id]);

        $this->processStatus($order, $data);

        return $data;
    }

    public function confirmOrder(string $orderId): array
    {
        return $this->request('/api/order/confirm', ['order_id' => $orderId]);
    }

    protected function processStatus(Order $order, array $data): void
    {
        if (($data['state'] ?? null) === 'FAIL') {
            $order->update(['payment_status' => PaymentStatus::CANCELLED]);

            return;
        }

        if (($data['state'] ?? null) === 'IN_PROCESS'
            && ($data['order_sub_state'] ?? null) === 'WAITING_FOR_STORE_CONFIRM') {
            $data = $this->confirmOrder($data['order_id']);
            $this->processStatus($order, $data);

            return;
        }

        if (($data['state'] ?? null) === 'SUCCESS'
            && in_array($data['order_sub_state'] ?? null, ['ACTIVE', 'DONE'], true)) {
            $order->update(['payment_status' => PaymentStatus::PAID]);

            return;
        }

        if (($data['order_sub_state'] ?? null) === 'RETURNED') {
            $order->update(['payment_status' => PaymentStatus::CANCELLED]);
        }
    }

    protected function request(string $endpoint, array $payload): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $signature = base64_encode(hash_hmac('sha256', $body, $this->secret, true));

        $response = Http::retry(3, 200, throw: false)
            ->withHeaders([
                'store-id' => $this->storeId,
                'signature' => $signature,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->withBody($body, 'application/json')
            ->post($this->url.$endpoint);

        if ($response->status() === 400) {
            throw new Exception('Monobank: 400');
        }

        if ($response->status() === 401) {
            throw new Exception('Monobank: invalid signature');
        }

        if ($response->status() === 403) {
            throw new Exception('Monobank: store forbidden');
        }

        if ($response->status() === 429) {
            throw new Exception('Monobank: rate limit exceeded');
        }

        if ($response->failed()) {
            Log::error('Monobank API error', [
                'endpoint' => $endpoint,
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new Exception('Monobank API error');
        }

        return $response->json();
    }

    protected function verifySignature(string $body, string $signature): bool
    {
        $localSignature = base64_encode(hash_hmac('sha256', $body, $this->secret, true));

        return hash_equals($localSignature, $signature);
    }
}
