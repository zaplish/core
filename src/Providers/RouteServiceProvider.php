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
            // Admin routes
            Route::prefix(config('zaplish.admin_prefix'))
                ->middleware(['web'])
                ->as('admin.')
                ->group(__DIR__ . '/../../routes/admin.php');

            // Core frontend routes
            Route::middleware('web')
                ->group(__DIR__ . '/../../routes/web.php');

            // Load theme routes if they exist
            $theme = config('zaplish.theme', 'zaplish');
            $themeRoutes = base_path("themes/{$theme}/routes/web.php");

            if (file_exists($themeRoutes)) {
                Route::middleware('web')->group($themeRoutes);
            }
        });
    }
}
