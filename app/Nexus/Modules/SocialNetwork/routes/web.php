<?php

use App\Nexus\Modules\SocialNetwork\Http\Controllers\Admin\AuthorizeSocialNetworkController;
use App\Nexus\Modules\SocialNetwork\Http\Controllers\SocialOAuthController;
use App\Nexus\Modules\SocialNetwork\Http\Controllers\SocialPostController;
use Illuminate\Support\Facades\Route;

Route::get('/social-network/{provider}/callback', [SocialOAuthController::class, 'callback'])
    ->whereIn('provider', ['facebook', 'tiktok', 'instagram'])
    ->middleware('web')
    ->name('social-network.callback');

Route::get('/api/social/posts', [SocialPostController::class, 'index'])
    ->middleware('web');

Route::middleware(config('nexus.admin_middleware'))
    ->prefix(config('nexus.admin_prefix'))
    ->group(function () {
        Route::post('/social-network/{id}/authorize', AuthorizeSocialNetworkController::class)->name('nexus.social-network.authorize');
    });
