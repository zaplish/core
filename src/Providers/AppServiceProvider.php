<?php

namespace Zaplish\Core\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Helpers\AssetHelper;
use App\Services\Settings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- Safe guard: only run if the environment is ready ---
        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            // During composer install / artisan package:discover
            // Skip database-related logic to prevent "no such table" errors
            $this->registerViewsAndTranslations();
            return;
        }
    
        try {
            // Settings (wrapped in try so failure won’t kill composer install)
            $theme = app(Settings::class)->get('cms.theme', 'laracms');
            $name = app(Settings::class)->get('cms.name', 'laraCMS');
    
            Config::set('cms.theme', $theme);
            Config::set('cms.name', $name);
        } catch (\Throwable $e) {
            // Fallback if DB/cache not ready
            Config::set('cms.theme', 'laracms');
            Config::set('cms.name', 'laraCMS');
        }
    
        $this->registerViewsAndTranslations();
    
        // Helpers
        View::share('theme', config('cms.theme'));
        View::share('assetHelper', AssetHelper::class);
    
        // Validator rules
        $this->registerCustomValidationRules();
    }
    
    protected function registerViewsAndTranslations(): void
    {
        // View namespaces
        View::addNamespace('admin', resource_path('admin/views'));
        View::addNamespace('theme', resource_path('themes/' . config('cms.theme') . '/views'));
    
        // Language namespaces
        $this->loadTranslationsFrom(base_path('lang/admin'), 'admin');
        $this->loadTranslationsFrom(resource_path('themes/' . config('cms.theme') . '/lang'), 'theme');
    }
    

    /**
     * Custom validators
     */
    protected function registerCustomValidationRules(): void
    {
        Validator::extend('securePassword', function ($attribute, $value) {
            $data = [$attribute => $value];
            $rules = [$attribute => Password::default()->uncompromised()];
            return Validator::make($data, $rules)->passes();
        }, __('admin::form.validation.securePassword'));
    }
}
