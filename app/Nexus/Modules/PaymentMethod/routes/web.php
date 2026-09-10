<?php

use App\Nexus\Modules\PaymentMethod\Http\Controllers\CheckoutController;
use App\Nexus\Modules\PaymentMethod\Http\Controllers\PaymentCallbackController;
use App\Nexus\Modules\PaymentMethod\Http\Controllers\PaymentResultController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('payment/{orderId}', [CheckoutController::class, 'pay'])->name('payment.pay');
    Route::get('payment/result/{orderId}', [PaymentResultController::class, 'handle'])->name('payment.result');
});

// No CSRF/session middleware — this is a server-to-server webhook from the payment gateway.
Route::post('payment/callback/{provider}', [PaymentCallbackController::class, 'handle'])->name('payment.callback');
