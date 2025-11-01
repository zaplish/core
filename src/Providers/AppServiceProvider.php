<?php

namespace Zaplish\Core\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\ServiceProvider;
use Zaplish\Core\Services\Settings;
use Zaplish\Core\Helpers\AssetHelper;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Dynamic settings
        try {
            $theme = app(Settings::class)->get('cms.theme', 'zaplish');
            $name = app(Settings::class)->get('cms.name', 'Zaplish');
            Config::set('cms.theme', $theme);
            Config::set('cms.name', $name);
        } catch (\Throwable $e) {
            Config::set('cms.theme', 'zaplish');
            Config::set('cms.name', 'Zaplish');
        }

        $this->registerViewsAndTranslations();

        // Shared data
        View::share('assetHelper', AssetHelper::class);

        // Custom validation rules
        $this->registerCustomValidationRules();
    }

    /**
     * Register views and translations
     */
    protected function registerViewsAndTranslations(): void
    {
        $theme = config('cms.theme');

        // Theme views
        View::addNamespace('theme', resource_path('themes/' . $theme . '/views'));

        // Translations
        $this->loadTranslationsFrom(__DIR__ . '/../../lang/admin', 'admin');
        $this->loadTranslationsFrom(resource_path('themes/' . $theme . '/lang'), 'theme');
    }

    /**
     * Register custom validation rules
     */
    protected function registerCustomValidationRules(): void
    {
        // Secure password
        Validator::extend('securePassword', function ($attribute, $value) {
            $data = [$attribute => $value];
            $rules = [$attribute => Password::default()->uncompromised()];
            return Validator::make($data, $rules)->passes();
        }, __('admin::form.validation.securePassword'));
    }
}
