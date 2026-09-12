<?php

use App\Nexus\Modules\Auth\Http\Controllers\AuthController;
use App\Nexus\Modules\Auth\Http\Controllers\EmailVerificationController;
use App\Nexus\Modules\Auth\Http\Controllers\PasswordResetController;
use App\Nexus\Modules\Auth\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');

    // throttle:5,1 — 5 attempts/minute, same limiter style already used
    // below for verification-email resend. Neither login endpoint had any
    // brute-force protection before this.
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');

    // JSON login for the SPA — same AuthService::login() as the web form
    // above, only the response shape differs.
    Route::post('/api/login', [AuthController::class, 'apiLogin'])->middleware('throttle:5,1');
});

Route::middleware(['web'])->group(function () {
    Route::get('/register', [AuthController::class, 'registerPage'])->name('shop.register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordPage'])->name('shop.forgot-password');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

    Route::get('/login/{provider}', [SocialAuthController::class, 'redirect'])->name('socialite.redirect');
    Route::get('/login/{provider}/callback', [SocialAuthController::class, 'callback'])->name('socialite.callback');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Everything below is JSON, not a page — auth:sanctum instead of the plain
// session guard, same reasoning as EnsureFrontendRequestsAreStateful's
// docblock in bootstrap/app.php.
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::post('/api/logout', [AuthController::class, 'apiLogout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});
