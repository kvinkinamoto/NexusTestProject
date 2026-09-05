<?php

use App\Nexus\Modules\Page\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/pages/{slug}', [PageController::class, 'show'])->name('nexus.page.show');
});
