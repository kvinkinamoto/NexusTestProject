<?php

use App\Nexus\Modules\Order\Http\Controllers\OrderController;
use App\Nexus\Modules\Shipment\Http\Controllers\Admin\RefreshShipmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/orders')
    ->middleware(['web'])
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
