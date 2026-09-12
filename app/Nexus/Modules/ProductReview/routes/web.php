<?php

use App\Nexus\Modules\ProductReview\Http\Controllers\ReviewCommentController;
use App\Nexus\Modules\ProductReview\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/reviews')
    ->middleware(['web'])
    ->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::delete('{id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum');
        Route::post('comments', [ReviewCommentController::class, 'store'])->middleware('throttle:5,1');
        Route::delete('comments/{id}', [ReviewCommentController::class, 'destroy'])->middleware('auth:sanctum');
        Route::post('reactions/toggle', [ReviewController::class, 'toggleReaction'])->middleware('throttle:20,1');
    });
