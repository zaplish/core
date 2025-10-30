<?php

namespace App\Helpers;

class RouteHelper
{
    /**
     * Check if a route has a {type} parameter
     */
    static function routeHasTypeParameter(string $routeName): bool
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName);
        if (!$route) {
            return false;
        }
        
        $uri = $route->uri();
        return str_contains($uri, '{type}');
    }

    /**
     * Add list and edit URIs to 
     */
    static function addListAndEditUris($config)
    {
        if (!empty($config['listRoute'])) {
            $routeParams = self::routeHasTypeParameter($config['listRoute']) ? ['type' => $config['key']] : [];
            $config['listUri'] = route($config['listRoute'], $routeParams);
        }
        
        if (!empty($config['editRoute'])) {
            $routeParams = self::routeHasTypeParameter($config['editRoute']) ? ['type' => $config['key']] : [];
            $routeParams['id'] = '__ID__';
            $config['editUri'] = route($config['editRoute'], $routeParams);
            $config['editUriNew'] =  str_replace('/__ID__', '', $config['editUri']);
        }

        return $config;
    }
}
