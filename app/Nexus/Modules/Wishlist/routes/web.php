<?php

use App\Nexus\Modules\Wishlist\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/wishlist')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', [WishlistController::class, 'getList']);
        Route::get('/details', [WishlistController::class, 'getDetails']);
        Route::post('/add', [WishlistController::class, 'add']);
        Route::post('/remove', [WishlistController::class, 'remove']);
        Route::post('/sync', [WishlistController::class, 'sync']);
    });

Route::prefix('api/wishlist')
    ->middleware(['web'])
    ->group(function () {
        Route::post('/resolve', [WishlistController::class, 'resolve']);
    });
