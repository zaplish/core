<?php

namespace App\Routes;

use Illuminate\Support\Facades\Route;
use Zaplish\Core\Http\Controllers\Admin\AuthController;

Route::middleware(['cms.installed', 'locale.set'])->group(function () {
    // Install
    Route::get('/install', [AuthController::class, 'install'])->name('install');
    Route::post('/install', [AuthController::class, 'installRequest'])->name('install-request');

    // Login
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginRequest'])->name('login-request');

    // Reset password
    Route::get('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    Route::post('/reset-password', [AuthController::class, 'resetPasswordRequest'])->name('reset-password-request');

    // Set new password
    Route::get('/new-password/{userId}-{resetPasswordHash}', [AuthController::class, 'newPassword'])->name('new-password');
    Route::post('/new-password', [AuthController::class, 'newPasswordRequest'])->name('new-password-request');
});

Route::middleware(['auth', 'authGuard', 'updateLastSeen', 'isCmsInstalled', 'setLocale'])->group(function () {
    // Auth
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/delete-account', [AuthController::class, 'deleteAccount'])->name('delete-account');

    // Dashboard
    Route::name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'view'])->name('view');
    });

    // Settings
    Route::middleware('accessAdmin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('site-info', [SettingsController::class, 'siteInfo'])->name('site-info');
        Route::get('site-variables', [SettingsController::class, 'siteVariables'])->name('site-variables');
        Route::get('developer/{tab}', [SettingsController::class, 'developer'])->middleware('accessDeveloper')->name('developer');
    });

    // Lists and form
    Route::controller(ContentController::class)->group(function () {
        // Content
        Route::prefix('content')->name('content.')->group(function () {
            Route::get('{type}', 'listType')->name('list');
            Route::get('{type}/edit/{id?}', 'editType')->name('edit');
        });

        // Content types
        foreach (
            [
                [
                    'name' => 'content-types',
                    'middleware' => ['accessDeveloper'],
                ],
                [
                    'name' => 'users',
                    'middleware' => ['accessAdmin'],
                ],
                [
                    'name' => 'profile',
                ],
                [
                    'name' => 'media',
                ]
            ] as $content
        ) {
            if (empty($content['middleware'])) $content['middleware'] = [];
            $content['middleware'][] = 'injectContentType:' . $content['name'];

            Route::middleware($content['middleware'])->prefix($content['name'])->name($content['name'] . '.')->group(function () {
                Route::get('', 'list')->name('list');
                Route::get('edit/{id?}', 'edit')->name('edit');
            });
        }
    });

    // Themes
    Route::middleware('accessDeveloper')->prefix('themes')->name('themes.')->group(function () {
        Route::get('select', [ThemesController::class, 'select'])->name('select');
        Route::get('variables', [ThemesController::class, 'variables'])->name('variables');
    });

    // Menus
    Route::middleware('accessDeveloper')->prefix('menus')->name('menus.')->group(function () {
        Route::get('list', [MenusController::class, 'list'])->name('list');
    });

    // Forms
    Route::middleware('accessDeveloper')->prefix('forms')->name('forms.')->group(function () {
        Route::get('list', [FormsController::class, 'list'])->name('list');
        Route::get('submissions', [FormsController::class, 'submissions'])->name('submissions');
    });

    // Blocks
    Route::middleware('accessDeveloper')->prefix('blocks')->name('blocks.')->group(function () {
        Route::get('list', [BlocksController::class, 'list'])->name('list');
        Route::get('groups', [BlocksController::class, 'groups'])->name('groups');
    });

    // // Api
    // Route::middleware('api')->prefix('api')
    //     ->name('api.')
    //     ->controller(\App\Http\Controllers\Admin\ApiController::class)
    //     ->group(function () {
    //         Route::post('update-user-config', 'updateUserConfig')->name('update-user-config');
    //         Route::post('list', 'list')->name('list');
    //         Route::post('save-form', 'saveForm')->name('save-form');
    //         Route::post('reorder-list', 'reorderList')->name('reorder-list');
    //         Route::post('reorder-item', 'reorderItem')->name('reorder-item');
    //         Route::post('toggle', 'toggle')->name('toggle');
    //         Route::post('delete', 'delete')->name('delete');
    //         Route::post('restore', 'restore')->name('restore');
    //         Route::post('duplicate', 'duplicate')->name('duplicate');
    //         Route::post('media-upload', 'mediaUpload')->name('media-upload');
    //         Route::post('save-user-settings', 'saveUserSettings')->name('save-user-settings');
    //         Route::post('remove-user-settings', 'removeUserSettings')->name('remove-user-settings');
    //     });
});
