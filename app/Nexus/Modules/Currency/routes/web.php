<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/currency/{code}', function (string $code) {
        session(['currency' => $code]);

        return redirect()->back();
    })->name('nexus.currency.switch');
});
