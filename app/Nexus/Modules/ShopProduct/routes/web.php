<?php

use App\Nexus\Modules\ShopProduct\Http\Controllers\Public\CatalogController;
use App\Nexus\Modules\ShopProduct\Http\Controllers\Public\ProductController;
use App\Nexus\Modules\ShopProduct\Http\Controllers\Public\SearchController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::middleware(['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'])
    ->prefix(LaravelLocalization::setLocale())
    ->group(function () {
        Route::get('/catalog', [CatalogController::class, 'index'])->name('shop.catalog');
        Route::get('/catalog/{category:slug}', [CatalogController::class, 'index'])->name('shop.catalog.category');
        Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('shop.product');
    });

Route::middleware(['web'])->group(function () {
    Route::get('/api/search/products', [SearchController::class, 'suggest']);
});
