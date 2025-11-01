<?php

namespace Zaplish\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Zaplish\Core\Providers\AppServiceProvider;
use Zaplish\Core\Providers\RouteServiceProvider;
use Zaplish\Core\Providers\ViewServiceProvider;
use Zaplish\Core\Http\Middleware\CmsInstalled;
use Zaplish\Core\Http\Middleware\SetLocale;
use Zaplish\Core\Http\Middleware\Authenticate;
use Zaplish\Core\Http\Middleware\AuthGuard;
use Zaplish\Core\Http\Middleware\AccessAdmin;
use Zaplish\Core\Http\Middleware\AccessDeveloper;
use Zaplish\Core\Http\Middleware\UpdateLastActivity;
use Zaplish\Core\Http\Middleware\InjectContentType;
use Zaplish\Core\Console\Commands\LinkAssetsCommand;

class ZaplishServiceProvider extends ServiceProvider
{
    /**
     * Register bindings and internal providers.
     */
    public function register(): void
    {
        // Register internal service providers
        $this->app->register(AppServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);

        // Merge core config defaults
        $this->mergeConfigFrom(__DIR__ . '/../config/cms.php', 'cms');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Middlewares
        Route::aliasMiddleware('cms.installed', CmsInstalled::class);
        Route::aliasMiddleware('locale.set', SetLocale::class);
        Route::aliasMiddleware('auth', Authenticate::class);
        Route::aliasMiddleware('auth.guard', AuthGuard::class);
        Route::aliasMiddleware('access.admin', AccessAdmin::class);
        Route::aliasMiddleware('access.developer', AccessDeveloper::class);
        Route::aliasMiddleware('user.last-activity', UpdateLastActivity::class);
        Route::aliasMiddleware('content.inject-type', InjectContentType::class);

        // Core files
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views/admin', 'admin');
        $this->loadTranslationsFrom(__DIR__ . '/../lang/admin', 'admin');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/cms.php' => config_path('cms.php'),
        ], 'config');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../public/admin' => public_path('vendor/zaplish/admin'),
        ], 'zaplish-assets');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([LinkAssetsCommand::class]);
        }
    }   
}
