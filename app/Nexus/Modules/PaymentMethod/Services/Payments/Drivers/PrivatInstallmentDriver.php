<?php

namespace App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers;

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrivatInstallmentDriver implements PaymentDriverInterface
{
    protected string $storeId;

    protected string $password;

    protected string $url;

    public function __construct()
    {
        $this->storeId = config('payment.privat.store_id');
        $this->password = config('payment.privat.password');
        $this->url = config('payment.privat.api_url');
    }

    public function createPayment(Order $order)
    {
        $products = [];
        $productsTotal = 0;
        foreach ($order->products as $product) {
            if ((float) $product->price <= 0) {
                continue;
            }
            $products[] = [
                'name' => $product->localizedField('name'),
                'count' => $product->quantity,
                'price' => (float) $product->price,
            ];
            $productsTotal = round($productsTotal + round($product->price * $product->quantity, 2), 2);
        }
        if ($order->delivery_price && $order->delivery_price != 0) {
            $products[] = [
                'name' => 'Доставка',
                'count' => 1,
                'price' => (float) $order->delivery_price,
            ];
            $productsTotal = round($productsTotal + $order->delivery_price, 2);
        }

        $payload = [
            'storeId' => $this->storeId,
            'orderId' => (string) $order->id,
            'amount' => (float) $productsTotal,
            'partsCount' => $order->paymentMethod->count_installment,
            'merchantType' => 'PP',
            'products' => $products,
            'responseUrl' => route('payment.callback', ['provider' => 'privat_installment']),
            'redirectUrl' => route('payment.result', ['orderId' => $order->id]),
        ];

        $payload['signature'] = $this->generateSignature($payload);

        Log::info('PrivatInstallment payload', ['payload' => $payload]);

        $response = Http::withHeaders(['Accept' => 'application/json'])
            ->post($this->url.'/payment/create', $payload);

        $data = $response->json();

        if (($data['state'] ?? null) !== 'SUCCESS') {
            Log::error('PrivatInstallment createPayment failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $data,
            ]);

            return redirect('/')->with('payment_error', 'Не вдалося ініціювати оплату через ПриватБанк. Спробуйте ще раз.');
        }

        $order->update(['bank_order_id' => $data['orderId']]);

        return redirect()->away($this->url.'/payment?token='.$data['token']);
    }

    public function handleCallback(array $data)
    {
        Log::info('PrivatInstallment callback', $data);

        if (! $this->verifyCallbackSignature($data)) {
            Log::warning('Privat invalid signature', $data);

            return;
        }

        if ($data['paymentState'] === 'SUCCESS') {
            Order::where('id', $data['orderId'])->update(['payment_status' => PaymentStatus::PAID]);
        }

        if ($data['paymentState'] === 'FAIL') {
            Order::where('id', $data['orderId'])->update(['payment_status' => PaymentStatus::CANCELLED]);
        }
    }

    public function checkPayment(Order $order)
    {
        $payload = ['storeId' => $this->storeId, 'orderId' => $order->bank_order_id];
        $payload['signature'] = $this->generateStatusSignature($order->bank_order_id);

        $response = Http::withHeaders(['Accept' => 'application/json'])
            ->post($this->url.'/payment/state', $payload);

        $data = $response->json();

        if ($data['paymentState'] === 'SUCCESS') {
            $order->update(['payment_status' => PaymentStatus::PAID]);
        }

        if ($data['paymentState'] === 'FAIL') {
            $order->update(['payment_status' => PaymentStatus::CANCELLED]);
        }
    }

    private function generateSignature(array $data): string
    {
        $amount = $this->withoutFloatingPoint($data['amount']);
        $productsString = $this->buildProductsString($data['products']);

        $string = $this->password.
            $data['storeId'].
            $data['orderId'].
            $amount.
            $data['partsCount'].
            $data['merchantType'].
            $data['responseUrl'].
            $data['redirectUrl'].
            $productsString.
            $this->password;

        return base64_encode(sha1($string, true));
    }

    private function generateStatusSignature(string $bankOrderId): string
    {
        $string = $this->password.$this->storeId.$bankOrderId.$this->password;

        return base64_encode(sha1($string, true));
    }

    private function withoutFloatingPoint(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function buildProductsString(array $products): string
    {
        $string = '';

        foreach ($products as $product) {
            $price = $this->withoutFloatingPoint($product['price']);
            $string .= $product['name'].$product['count'].$price;
        }

        return $string;
    }

    private function verifyCallbackSignature(array $data): bool
    {
        $string = $this->password.
            $data['storeId'].
            $data['orderId'].
            $data['paymentState'].
            $data['message'].
            $this->password;

        $signature = base64_encode(sha1($string, true));

        return hash_equals($signature, $data['signature']);
    }
}
