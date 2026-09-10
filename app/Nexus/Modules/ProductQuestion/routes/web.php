<?php

use App\Nexus\Modules\ProductQuestion\Http\Controllers\ProductQuestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/questions')
    ->middleware(['web'])
    ->group(function () {
        Route::post('/', [ProductQuestionController::class, 'store']);
        Route::delete('{id}', [ProductQuestionController::class, 'destroy'])->middleware('auth');
        Route::post('reactions/toggle', [ProductQuestionController::class, 'toggleReaction'])->middleware('throttle:20,1');
    });
