<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ContentType;
use App\Helpers\RouteHelper;
use App\Models\Media;

class MediaService
{
    /**
     * Get the list config
     */
    // public static function getConfig(string $key)
    // {
    //     $settingKey = 'list-settings.' . $key;

    //     $config = DB::table('settings')->where('key', $settingKey)->value('value');

    //     if (!$config && $contentType = ContentType::where('key', $key)->first()) {
    //         $config = $contentType->settings['list'] ?? null;
    //     }

    //     if (is_string($config)) {
    //         $config = json_decode($config, true);
    //     }

    //     return $config;
    // }

    /**
     * Get the list data
     */
    public static function getData(array $params = [])
    {
        // $config = self::getConfig($key);
        $config = [];

        // if (!$config) return null;

        // $config['key'] = $key;
        // $config = RouteHelper::addListAndEditUris($config);

        // $modelClassName = $config['model'] ?? null;
        // $modelClass = 'App\\Models\\' . $modelClassName;

        // if (!class_exists($modelClass)) {
        //     return null;
        // }

        // if (!is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
        //     return null;
        // }

        $query = Media::query();

        // Add relations
        // $with = [];

        // foreach ($config['columns'] as $column) {
        //     if (!empty($column['relation'])) {
        //         $with[] = $column['relation'];
        //     }
        // }

        // if (!empty($with)) {
        //     $query->with($with);
        // }

        // Get user settings
        $user = auth()->user();
        $userSettings = $user->settings ?? [];
        $userSettings['list-settings'] = $userSettings['list-settings'] ?? [];
        $userListSettings = $userSettings['list-settings']['media-library'] ?? [];

        // Apply ordering
        $orderBy = $params['orderBy']
            ?? $userListSettings['orderBy']
            ?? $config['defaultOrderBy']
            ?? 'created_at';

        $orderDirection = $params['orderDirection']
            ?? $userListSettings['orderDirection']
            ?? $config['defaultOrderDirection']
            ?? 'desc';

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $config['orderBy'] = $orderBy;
        $config['orderDirection'] = $orderDirection;

        // Apply search
        $searchTerm = $params['searchTerm'] ?? null;

        if ($searchTerm) {
            $searchables = ['title', 'alt_text', 'mime_type'];

            if (!empty($searchables)) {
                $query->where(function ($q) use ($searchables, $searchTerm) {
                    foreach ($searchables as $field) {
                        $q->orWhere($field, 'like', '%' . $searchTerm . '%');
                    }
                });
            }

            $config['searchTerm'] = $searchTerm;
        } else {
            $config['searchTerm'] = null;
        }

        // Get paginated result
        $perPage = $params['perPage']
            ?? $userListSettings['perPage']
            ?? $config['defaultPerPage']
            ?? 50;
        $config['perPage'] = $perPage;

        $page = request()->input('page') ?? 1;

        $items = $query->paginate($perPage, ['*'], 'page', $page);

        // If page is out of bounds (e.g. after deletion), fallback to last available page
        if ($items->lastPage() < $items->currentPage() && $items->lastPage() > 0) {
            $items = $query->paginate($perPage, ['*'], 'page', $items->lastPage());
        }

        $config['page'] = $items->currentPage();

        // Update users config
        $userListSettings['perPage'] = $config['perPage'];
        $userSettings['list-settings']['media-library'] = $userListSettings;
        $user->settings = $userSettings;
        $user->save();

        // Add meta
        $config['meta'] = [
            'total' => $items->total(),
            'perPage' => $items->perPage(),
            'currentPage' => $items->currentPage(),
            'lastPage' => $items->lastPage(),
            'from' => $items->firstItem(),
            'to' => $items->lastItem(),
            'totalCount' => Media::count(),
        ];

        return [
            'config' => $config,
            'items' => $items,
            'texts' => trans('admin::list'),
        ];
    }

    /**
     * Get the list view
     */
    public static function getView()
    {
        $mediaLibraryData = self::getData();

        return view('admin::pages.media.list', [
            'mediaLibraryData' => $mediaLibraryData,
        ]);
    }
}
