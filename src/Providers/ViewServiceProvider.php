<?php

namespace Zaplish\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Zaplish\Core\Models\ContentType;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('admin::*', function ($view) {
            $view->with('contentTypes', ContentType::orderBy('order')->where('active', 1)->get());
        });
    }
}
