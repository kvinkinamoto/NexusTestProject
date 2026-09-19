<?php

use App\Nexus\Modules\ProductRelease\Http\Controllers\GithubWebhookController;
use Illuminate\Support\Facades\Route;

// No CSRF/session middleware — server-to-server webhook from GitHub,
// verified by HMAC signature inside the controller instead (same pattern
// as PaymentMethod's payment.callback route). throttle caps abuse of an
// unauthenticated public endpoint before signature verification runs.
Route::post('webhooks/github', [GithubWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhooks.github');
