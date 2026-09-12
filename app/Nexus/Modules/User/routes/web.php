<?php

use App\Nexus\Modules\User\Http\Controllers\Public\AccountProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/profile', [AccountProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [AccountProfileController::class, 'update'])->name('profile.update');
    Route::post('/password', [AccountProfileController::class, 'updatePassword'])->name('password.update');
});
