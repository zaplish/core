<?php

namespace Zaplish\Core\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Called during the application's bootstrapping process.
     */
    public function boot(): void
    {
        $this->routes(function () {

            // Admin routes (for CMS backend)
            Route::prefix(config('cms.admin_prefix'))
                ->middleware(['web'])
                ->as('admin.')
                ->group(__DIR__ . '/../../routes/admin.php');

            // API routes (for CMS data access)
            // TODO
            // Route::prefix('api')
            //     ->middleware('api')
            //     ->as('api.')
            //     ->group(__DIR__ . '/../../routes/api.php');
        });
    }
}
