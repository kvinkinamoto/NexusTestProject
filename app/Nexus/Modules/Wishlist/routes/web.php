<?php

use App\Nexus\Modules\Wishlist\Http\Controllers\Public\WishlistPageController;
use App\Nexus\Modules\Wishlist\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/wishlist', [WishlistPageController::class, 'index'])->name('shop.wishlist');
});

Route::prefix('api/wishlist')
    ->middleware(['web'])
    ->group(function () {
        Route::get('/', [WishlistController::class, 'getList']);
        Route::post('/add', [WishlistController::class, 'add']);
        Route::post('/remove', [WishlistController::class, 'remove']);
        Route::post('/sync', [WishlistController::class, 'sync']);
        Route::post('/resolve', [WishlistController::class, 'resolve']);
    });

// getDetailsForUser() is strictly typed to a real User — kept auth-only
// (it's only ever called for the account wishlist page's own data, not from
// the guest-facing heart-toggle/list endpoints above). JSON endpoint, so
// auth:sanctum rather than the plain session guard — see bootstrap/app.php.
Route::middleware(['web', 'auth:sanctum'])
    ->prefix('api/wishlist')
    ->group(function () {
        Route::get('/details', [WishlistController::class, 'getDetails']);
    });
