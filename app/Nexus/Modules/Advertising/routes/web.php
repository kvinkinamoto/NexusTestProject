<?php

use App\Nexus\Modules\Advertising\Http\Controllers\Public\AdClickController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/ads/{advertising}/click', AdClickController::class)->name('advertising.click');
});
