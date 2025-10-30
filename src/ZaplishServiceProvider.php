<?php

namespace Zaplish\Core;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Zaplish\Core\Providers\AppServiceProvider;
use Zaplish\Core\Providers\RouteServiceProvider;

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

        // Merge core config defaults (optional)
        $this->mergeConfigFrom(__DIR__ . '/../config/cms.php', 'cms');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Load routes, views, and translations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'zaplish');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'zaplish');

        // Publish resources for app usage
        $this->publishes([
            __DIR__ . '/../config/cms.php' => config_path('cms.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/zaplish'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../lang' => resource_path('lang/vendor/zaplish'),
        ], 'lang');

        // Share global view data
        View::share('zaplishVersion', '1.0.0');

        // Optional: only load heavy logic if the app is booted
        if ($this->app->runningInConsole()) {
            // console-only tasks (artisan commands, publishables, etc.)
        }
    }
}
