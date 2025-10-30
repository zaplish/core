<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ContentType;
use App\Helpers\RouteHelper;

class ListService
{
    /**
     * Get the list config
     */
    public static function getConfig(string $key)
    {
        $settingKey = 'list-settings.' . $key;

        $config = DB::table('settings')->where('key', $settingKey)->value('value');

        if (!$config && $contentType = ContentType::where('key', $key)->first()) {
            $config = $contentType->settings['list'] ?? null;
        }

        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        return $config;
    }

    /**
     * Get the list data
     */
    public static function getData(string $key, array $params = [])
    {
        $config = self::getConfig($key);

        if (!$config) return null;

        $config['key'] = $key;
        $config = RouteHelper::addListAndEditUris($config);

        $modelClassName = $config['model'] ?? null;
        $modelClass = 'App\\Models\\' . $modelClassName;

        if (!class_exists($modelClass)) {
            return null;
        }

        if (!is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
            return null;
        }

        $query = $modelClass::query();

        // Item type trashed
        $trashed = false;
        if (!empty($config['hasSoftDelete'])) {
            $trashed = $params['trashed'] ?? false;
            if ($trashed) {
                $query->onlyTrashed();
            }
            $config['trashed'] = $trashed;
        }

        // Add relations
        $with = [];

        foreach ($config['columns'] as $column) {
            if (!empty($column['relation']['key'])) {
                if (!empty($column['relation']['where'])) {
                    $with[$column['relation']['key']] = function ($query) use ($column) {
                        $query->where($column['relation']['where']);
                    };
                } else {
                    $with[] = $column['relation']['key'];
                }
            }
        }

        if (!empty($with)) {
            $query->with($with);
        }

        // Get user settings
        $user = Auth::user();
        $userSettings = $user->settings ?? [];
        $userSettings['list-settings'] = $userSettings['list-settings'] ?? [];
        $userListSettings = $userSettings['list-settings'][$key] ?? [];

        // Apply ordering
        if ($trashed) {
            $orderBy = $params['orderBy'] ?? 'deleted_at';
            $orderDirection = $params['orderDirection'] ?? 'desc';
        } else {
            $orderBy = $params['orderBy']
                ?? $userListSettings['orderBy']
                ?? $config['defaultOrderBy']
                ?? 'id';

            $orderDirection = $params['orderDirection']
                ?? $userListSettings['orderDirection']
                ?? $config['defaultOrderDirection']
                ?? 'asc';
        }

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $config['orderBy'] = $orderBy;
        $config['orderDirection'] = $orderDirection;

        $orderColumn = collect($config['columns'])->firstWhere('source', $orderBy);

        if (isset($orderColumn['relation']) && str_contains($orderColumn['source'], '.')) {
            [$relation, $field] = explode('.', $orderColumn['source'], 2);
            $relationMethod = $query->getModel()->{$relation}();
            $relatedTable = $relationMethod->getRelated()->getTable();
            $relatedAlias = $relation;
            $foreignKey = $relationMethod->getQualifiedForeignKeyName();
            $query
                ->leftJoin("{$relatedTable} as {$relatedAlias}", $foreignKey, '=', "{$relatedAlias}.id")
                ->orderBy("{$relatedAlias}.{$field}", $orderDirection)
                ->select($query->getModel()->getTable() . '.*');
        } else {
            $query->orderBy($orderBy, $orderDirection);
        }

        // Apply search
        $searchTerm = $params['searchTerm'] ?? null;

        if ($searchTerm) {
            $searchables = $config['searchables'] ?? [];

            if (empty($searchables)) {
                if (Schema::hasColumn($modelClass::getModel()->getTable(), 'title')) {
                    $searchables[] = ['column' => 'title'];
                } elseif (Schema::hasColumn($modelClass::getModel()->getTable(), 'name')) {
                    $searchables[] = ['column' => 'name'];
                }
            }

            if (!empty($searchables)) {
                $query->where(function ($q) use ($searchables, $searchTerm) {
                    foreach ($searchables as $field) {
                        if (!empty($field['type']) && $field['type'] == 'jsonColumn') {
                            [$relation, $field] = explode('.', $field['column'], 2);
                            $q->orWhereJsonContains($relation, [$field => $searchTerm]);
                        } else {
                            $q->orWhere($field['column'], 'like', '%' . $searchTerm . '%');
                        }
                    }
                });
            }

            $config['searchTerm'] = $searchTerm;
        } else {
            $config['searchTerm'] = null;
        }

        // Add filters
        if (!empty($config['filters'])) {
            foreach ($config['filters'] as $key => $filter) {
                if (!empty($filter['getOptions'])) {
                    $filterModelClassName = $filter['getOptions']['model'];
                    $filterModelClass = 'App\\Models\\' . $filterModelClassName;

                    $options = $filterModelClass::query();

                    if (!empty($filter['getOptions']['where'])) {
                        $options->where($filter['getOptions']['where']);
                    }

                    if (!empty($filter['getOptions']['prioritizeBy'])) {
                        $prioritizeByKey = $filter['getOptions']['prioritizeBy'][0];
                        $prioritizeByValue = $filter['getOptions']['prioritizeBy'][1];
                        if ($prioritizeByValue == 'auth-id') {
                            $prioritizeByValue = Auth::user()->id;
                        }
                        $options->orderByRaw('CASE WHEN ' . $prioritizeByKey . ' = ? THEN 0 ELSE 1 END', [$prioritizeByValue]);
                    }

                    if (!empty($filter['getOptions']['orderBy'])) {
                        $options->orderBy($filter['getOptions']['orderBy'], $filter['getOptions']['orderDirection'] ?? 'asc');
                    }

                    if (!empty($filter['getOptions']['select'])) {
                        $options->select(array_merge(['id'], $filter['getOptions']['select']));
                    }

                    $options = $options->get();

                    $options = $options->map(function ($option) use ($filter) {
                        return [
                            'value' => $option->{$filter['valueColumn'] ?? 'id'},
                            'label' => $option->{$filter['labelColumn'] ?? 'title'},
                        ];
                    });

                    $config['filters'][$key]['options'] = $options;
                }
            }
        }

        // Apply filters
        $filters = $params['filters'] ?? [];

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                switch ($filter['type']) {
                    case 'radio':
                        $query->where($filter['column'], $filter['value']);
                        break;
                    case 'checkbox':
                        $query->whereIn($filter['column'], $filter['value']);
                        break;
                }
            }
        }

        // Get paginated result
        $perPage = $params['perPage']
            ?? $userListSettings['perPage']
            ?? $config['defaultPerPage']
            ?? 25;
        $config['perPage'] = $perPage;

        $page = $params['page'] ?? 1;

        $items = $query->paginate($perPage, ['*'], 'page', $page);

        // If page is out of bounds (e.g. after deletion), fallback to last available page
        if ($items->lastPage() < $items->currentPage() && $items->lastPage() > 0) {
            $items = $query->paginate($perPage, ['*'], 'page', $items->lastPage());
        }

        $config['page'] = $items->currentPage();
        $config['view'] = $params['view'] ?? $userListSettings['view'] ?? $config['defaultView'] ?? 'list';

        // Update users config
        $userListSettings['perPage'] = $config['perPage'];
        $userListSettings['view'] = $config['view'];

        if (!$trashed) {
            $userListSettings['orderBy'] = $config['orderBy'];
            $userListSettings['orderDirection'] = $config['orderDirection'];
        }

        $userSettings['list-settings'][$config['key']] = $userListSettings;
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
            'totalCount' => $modelClass::count(),
        ];

        if (!empty($config['hasSoftDelete'])) {
            $config['meta']['trashCount'] = $modelClass::onlyTrashed()->count();
        }

        return [
            'config' => $config,
            'items' => $items,
            'texts' => trans('admin::list'),
        ];
    }

    /**
     * Get the list view
     */
    public static function getView(string $key)
    {
        $listData = self::getData($key);

        return view('admin::pages.list', [
            'key' => $key,
            'listData' => $listData,
        ]);
    }
}
