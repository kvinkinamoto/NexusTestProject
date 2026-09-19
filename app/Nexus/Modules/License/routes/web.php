<?php

use App\Nexus\Modules\License\Http\Controllers\Public\AccountLicensesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/licenses', [AccountLicensesController::class, 'index'])->name('licenses');
    Route::post('/licenses/{license}/activate', [AccountLicensesController::class, 'activate'])->name('licenses.activate');
    Route::delete('/licenses/{license}/activations/{activation}', [AccountLicensesController::class, 'deactivate'])->name('licenses.deactivate');
});

// `signed` on top of the usual web/auth stack — see AccountLicensesController::signedDownloadUrl().
// throttle caps repeated large-file downloads even from a legitimately signed link.
Route::middleware(['web', 'auth', 'signed', 'throttle:20,1'])->prefix('account')->name('account.')->group(function () {
    Route::get('/licenses/{license}/download', [AccountLicensesController::class, 'download'])->name('licenses.download');
});
