<?php

use Illuminate\Support\Facades\Route;

use Zaplish\Core\Http\Controllers\Frontend\FrontendController;
use Zaplish\Core\Http\Controllers\MediaController;

// Main frontend routes
Route::middleware(['web', 'zaplish.installed'])->group(function () {
    // Home
    Route::get('/', [FrontendController::class, 'home'])->name('frontend.home');
});

// Media
Route::middleware(['web'])->name('media.')->group(function () {
    Route::get('/media/{uuid}/{size?}/{slug?}', [MediaController::class, 'show'])
        ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
        ->where('size', '^(large|medium|small|thumb|preview)?$')
        ->name('show');
});

// Fallback: all other frontend pages
Route::middleware(['web', 'zaplish.installed'])
    ->get('/{slug?}', [FrontendController::class, 'page'])
    ->where('slug', '.*')
    ->name('frontend.page');