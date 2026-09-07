<?php

use App\Nexus\Modules\BlogPost\Http\Controllers\BlogPostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/blog', [BlogPostController::class, 'index'])->name('nexus.blogPost.index');
    Route::get('/blog/category/{slug}', [BlogPostController::class, 'category'])->name('nexus.blogPost.category');
    Route::get('/blog/{slug}', [BlogPostController::class, 'show'])->name('nexus.blogPost.show');
});
