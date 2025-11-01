<?php

use Illuminate\Support\Facades\Route;

use Zaplish\Core\Http\Controllers\Frontend\FrontendController;
use Zaplish\Core\Http\Controllers\MediaController;


Route::middleware(['web', 'isCmsInstalled'])->group(function () {
    // Home
    Route::get('/', [FrontendController::class, 'index']);
});

// Media
Route::middleware(['web'])->name('media.')->group(function () {
    Route::get('/media/{uuid}/{size?}/{slug?}', [MediaController::class, 'show'])
        ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
        ->where('size', '^(large|medium|small|thumb|preview)?$')
        ->name('show');
});
