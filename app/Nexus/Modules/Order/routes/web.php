<?php

use App\Nexus\Modules\Order\Http\Controllers\OrderController;
use App\Nexus\Modules\Order\Http\Controllers\Public\AccountOrdersController;
use App\Nexus\Modules\Order\Http\Controllers\Public\CheckoutController;
use App\Nexus\Modules\Order\Http\Controllers\Public\GiftRedemptionController;
use App\Nexus\Modules\Shipment\Http\Controllers\Admin\RefreshShipmentController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::middleware(['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'])
    ->prefix(LaravelLocalization::setLocale())
    ->group(function () {
        Route::get('/checkout/thank-you', [CheckoutController::class, 'thankYou'])->name('shop.checkout.thankyou');

        /**
         * License purchases require an account (a License row needs a user_id
         * to be issued to and to appear under /account/licenses) — checkout
         * itself, not just order history, is gated behind auth so a guest is
         * bounced to /login (and redirect()->intended() back here after)
         * before they can pay at all, instead of paying and never receiving
         * a license.
         */
        Route::middleware(['auth'])->group(function () {
            Route::get('/checkout', [CheckoutController::class, 'index'])->name('shop.checkout');
        });

        Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
            Route::get('/orders', [AccountOrdersController::class, 'index'])->name('orders');
            Route::get('/redeem-gift', [GiftRedemptionController::class, 'show'])->name('redeem-gift.show');
            Route::post('/redeem-gift', [GiftRedemptionController::class, 'store'])->name('redeem-gift.store');
        });
    });

Route::prefix('api/orders')
    ->middleware(['web', 'auth', 'throttle:10,1'])
    ->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::post('/quick', [OrderController::class, 'quick']);
    });

// OrderShipment isn't a registered Nexus module of its own (see its model
// docblock), so its one admin action is registered here instead.
Route::middleware(config('nexus.admin_middleware'))
    ->prefix(config('nexus.admin_prefix'))
    ->group(function () {
        Route::post('/shipment/{order}/refresh', RefreshShipmentController::class)->name('nexus.shipment.refresh');
    });
