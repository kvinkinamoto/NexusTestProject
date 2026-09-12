<?php

use App\Nexus\Modules\Delivery\Http\Controllers\Public\DeliveryAddressController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/api/delivery/{deliveryKey}/cities', [DeliveryAddressController::class, 'cities']);
    Route::get('/api/delivery/cities/{cityId}/warehouses', [DeliveryAddressController::class, 'warehouses']);
});
