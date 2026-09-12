<?php

use App\Nexus\Modules\Page\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/pages/{slug}', [PageController::class, 'show'])->name('nexus.page.show');

    // Friendly aliases so the storefront header/footer links read /about and
    // /contacts instead of /pages/{slug} — the pages themselves are authored
    // through this module's CMS like any other Page.
    Route::get('/about', fn () => redirect()->route('nexus.page.show', 'about'));
    Route::get('/contacts', fn () => redirect()->route('nexus.page.show', 'contacts'));
    Route::get('/delivery', fn () => redirect()->route('nexus.page.show', 'delivery'));
});
