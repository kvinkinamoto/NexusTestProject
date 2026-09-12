<?php

use App\Nexus\Modules\Storefront\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('shop.home');
});
