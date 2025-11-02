<?php

namespace Zaplish\Core\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\ServiceProvider;
use Zaplish\Core\Helpers\AssetHelper;
use Zaplish\Core\Helpers\SettingsHelper;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Dynamic settings
        try {
            $theme = SettingsHelper::get('zaplish.theme', 'zaplish');
            $name = SettingsHelper::get('zaplish.name', 'Zaplish');
            Config::set('zaplish.theme', $theme);
            Config::set('zaplish.name', $name);
        } catch (\Throwable $e) {
            Config::set('zaplish.theme', 'zaplish');
            Config::set('zaplish.name', 'Zaplish');
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
        $theme = config('zaplish.theme');

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
