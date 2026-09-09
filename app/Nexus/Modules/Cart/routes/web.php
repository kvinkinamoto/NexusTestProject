<?php

use App\Nexus\Modules\Cart\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/cart')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', [CartController::class, 'get'])->name('nexus.cart.get');
        Route::post('/add', [CartController::class, 'add'])->name('nexus.cart.add');
        Route::post('/remove', [CartController::class, 'remove'])->name('nexus.cart.remove');
        Route::post('/update-quantity', [CartController::class, 'updateQuantity'])->name('nexus.cart.update-quantity');
        Route::post('/sync', [CartController::class, 'sync'])->name('nexus.cart.sync');
        Route::post('/promo-code/apply', [CartController::class, 'applyPromoCode'])->name('nexus.cart.promo-code.apply');
        Route::post('/promo-code/remove', [CartController::class, 'removePromoCode'])->name('nexus.cart.promo-code.remove');
    });
