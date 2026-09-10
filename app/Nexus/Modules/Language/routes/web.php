<?php

use App\Nexus\Modules\Language\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/api/languages', [LanguageController::class, 'index'])
    ->middleware('web');
