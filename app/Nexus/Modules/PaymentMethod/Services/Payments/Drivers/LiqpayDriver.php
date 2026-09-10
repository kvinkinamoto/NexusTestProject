<?php

namespace App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers;

use App\Nexus\Modules\Currency\Services\CurrencyService;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentDriverInterface;
use Illuminate\Support\Facades\Log;
use LiqPay;

class LiqpayDriver implements PaymentDriverInterface
{
    protected string $publicKey;

    protected string $privateKey;

    public function __construct()
    {
        $this->publicKey = config('payment.liqpay.public_key');
        $this->privateKey = config('payment.liqpay.private_key');
    }

    public function createPayment(Order $order)
    {
        $liqpay = new LiqPay($this->publicKey, $this->privateKey);

        $baseCurrency = CurrencyService::getBaseCurrency();
        $currencyCode = $baseCurrency?->code ?? 'UAH';

        $params = [
            'action' => 'pay',
            'amount' => $order->total_price,
            'currency' => $currencyCode,
            'description' => 'Оплата замовлення #'.$order->id,
            'order_id' => $order->id,
            'version' => '3',
            'result_url' => route('payment.result', ['orderId' => $order->id]),
            'server_url' => route('payment.callback', ['provider' => $order->payment_method]),
            'language' => 'uk',
        ];

        $order->update(['bank_order_id' => $params['order_id']]);

        return response('
            <html><body>
                '.$liqpay->cnb_form($params).'
                <script>document.forms[0].submit();</script>
            </body></html>
        ');
    }

    public function handleCallback(array $requestData)
    {
        $dataEncoded = $requestData['data'] ?? null;
        $signatureReceived = $requestData['signature'] ?? null;

        if (! $dataEncoded || ! $signatureReceived) {
            Log::warning('LiqPay callback missing data or signature', $requestData);

            return;
        }

        $signatureLocal = base64_encode(sha1(
            $this->privateKey.$dataEncoded.$this->privateKey,
            true
        ));

        if (! hash_equals($signatureLocal, $signatureReceived)) {
            Log::warning('LiqPay callback signature mismatch', [
                'local' => $signatureLocal,
                'received' => $signatureReceived,
            ]);

            return;
        }

        $data = json_decode(base64_decode($dataEncoded), true);

        $orderId = $data['order_id'] ?? null;
        if (! $orderId) {
            Log::warning('LiqPay callback missing order_id', $data);

            return;
        }

        $order = Order::find($orderId);
        if (! $order) {
            Log::warning('LiqPay callback order not found', ['order_id' => $orderId]);

            return;
        }

        if ($data['status'] === 'success') {
            $order->update(['payment_status' => PaymentStatus::PAID]);
            Log::info("Order {$order->id} marked as paid via LiqPay");
        }

        if (in_array($data['status'], ['error', 'failure'], true)) {
            $order->update(['payment_status' => PaymentStatus::CANCELLED]);
        }
    }

    public function checkPayment(Order $order)
    {
        $liqpay = new LiqPay($this->publicKey, $this->privateKey);

        $response = $liqpay->api('request', [
            'action' => 'status',
            'order_id' => $order->id,
            'version' => '3',
        ]);

        if (! $response->status) {
            return;
        }

        if ($response->status === 'success') {
            $order->update(['payment_status' => PaymentStatus::PAID]);
        }

        if (in_array($response->status, ['error', 'failure'], true)) {
            $order->update(['payment_status' => PaymentStatus::CANCELLED]);
        }
    }
}
