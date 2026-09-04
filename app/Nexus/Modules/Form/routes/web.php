<?php

use App\Nexus\Modules\Form\Http\Controllers\FormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'throttle:10,1'])->group(function () {
    Route::post('/forms/{slug}/submit', [FormSubmissionController::class, 'store'])->name('nexus.form.submit');
});
