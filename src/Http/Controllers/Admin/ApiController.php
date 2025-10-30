<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\Controller;
use App\Services\ListService;
use App\Services\FormService;
use App\Helpers\MediaHelper;
use App\Helpers\ArrayHelper;

class ApiController extends Controller
{
    /**
     * Get list params
     */
    protected function getListParams()
    {
        return [
            'filters' => request()->input('filters'),
            'orderBy' => request()->input('orderBy'),
            'orderDirection' => request()->input('orderDirection'),
            'searchTerm' => request()->input('searchTerm'),
            'perPage' => request()->input('perPage'),
            'page' => request()->input('page'),
            'trashed' => request()->input('trashed'),
            'view' => request()->input('view'),
        ];
    }

    /**
     * Get list data
     */
    public function list()
    {
        $key = request()->input('key');

        $listData = ListService::getData($key, $this->getListParams());

        return [
            'success' => true,
            'listData' => $listData
        ];
    }

    /**
     * Save form data
     */
    public function saveForm()
    {
        $key = request()->input('key');
        $values = request()->input('values');

        // Authenticate
        if ($key === 'profile') {
            if (empty($values['id'])) {
                abort(403);
            } else if ($values['id'] != Auth::user()->id) {
                abort(403, 'You are not allowed to edit this profile.');
            }
        }

        return FormService::saveForm($key, $values);
    }

    /**
     * Save new list order
     */
    public function reorderList()
    {
        $key = request()->input('key');
        $items = request()->input('items');

        $listConfig = ListService::getConfig($key);

        $modelClassName = $listConfig['model'] ?? null;
        $modelClass = 'App\\Models\\' . $modelClassName;

        foreach ($items as $item) {
            $model = $modelClass::find($item['id']);
            if ($model) {
                $model->timestamps = false;
                $model->order = $item['order'];
                $model->save();
            }
        }

        return [
            'success' => true,
            'message' => __('admin::api.listReorder.successMessage'),
        ];
    }

    /**
     * Reorder item
     */
    public function reorderItem()
    {
        $key = request()->input('key');
        $id = request()->input('id');
        $action = request()->input('action');

        $listConfig = ListService::getConfig($key);

        $modelClassName = $listConfig['model'] ?? null;
        $modelClass = 'App\\Models\\' . $modelClassName;

        $item = $modelClass::find($id);

        if (!$item) {
            return [
                'success' => false,
                'message' => __('admin::api.listReorder.errorItemNotFound'),
            ];
        }

        switch ($action) {
            case 'move-up':
                $swap = $modelClass::where('order', '<', $item->order)
                    ->orderBy('order', 'desc')
                    ->first();
                break;

            case 'move-down':
                $swap = $modelClass::where('order', '>', $item->order)
                    ->orderBy('order', 'asc')
                    ->first();
                break;

            case 'move-to-top':
                $swap = $modelClass::orderBy('order', 'asc')->first();
                break;

            case 'move-to-bottom':
                $swap = $modelClass::orderBy('order', 'desc')->first();
                break;
        }

        if (isset($swap)) {
            if (in_array($action, ['move-up', 'move-down'])) {
                $temp = $item->order;
                $item->order = $swap->order;
                $swap->order = $temp;
                $item->save();
                $swap->save();
            } elseif ($action === 'move-to-top') {
                $item->order = ($swap->order ?? 0) - 1;
                $item->save();
                if ($item->order < 1) {
                    $all = $modelClass::orderBy('order')->get();
                    foreach ($all as $i => $m) {
                        $m->order = $i + 1;
                        $m->save();
                    }
                }
            } elseif ($action === 'move-to-bottom') {
                $item->order = ($swap->order ?? 0) + 1;
                $item->save();
            }
        }

        $listData = ListService::getData($key, $this->getListParams());

        return [
            'success' => true,
            'message' => __('admin::api.listReorder.successMessage'),
            'listData' => $listData
        ];
    }

    /**
     * Toggle active state
     */
    public function toggle()
    {
        $key = request()->input('key');
        $id = request()->input('id');
        $ids = request()->input('ids', []);
        $action = request()->input('action');

        $listConfig = ListService::getConfig($key);
        $modelClassName = $listConfig['model'] ?? null;
        $modelClass = 'App\\Models\\' . $modelClassName;

        $idList = collect($ids);
        if ($id && !$idList->contains($id)) {
            $idList->push($id);
        }

        if ($idList->isEmpty()) {
            return ['success' => false];
        }

        $models = $modelClass::whereIn('id', $idList)->get();
        $lastValue = null;

        foreach ($models as $model) {
            $model->timestamps = false;

            if ($action === 'activate') {
                $model->active = true;
            } elseif ($action === 'deactivate') {
                $model->active = false;
            } else {
                $model->active = !$model->active;
            }

            $model->save();
            $lastValue = $model->active;
        }

        return [
            'success' => true,
            'value' =>  $idList->count() > 1 ? ($action == 'activate' ? 1 : 0) : $model->active,
            'message' => __('admin::api.toggle.successMessage.' . ($idList->count() > 1 ? ($action == 'activate' ? 'onBulk' : 'offBulk') : ($lastValue ? 'on' : 'off')), ['items' => $idList->count()]),
        ];
    }

    /**
     * Duplicate item
     */
    public function duplicate()
    {
        $key = request()->input('key');
        $id = request()->input('id');

        $listConfig = ListService::getConfig($key);

        $modelClassName = $listConfig['model'] ?? null;
        $modelClass = 'App\\Models\\' . $modelClassName;

        $model = $modelClass::find($id);

        if ($model) {
            $newModel = $model->replicate();

            $attributes = $model->getAttributes();
            $labelKey = array_key_exists('title', $attributes) ? 'title' : (array_key_exists('name', $attributes) ? 'name' : null);

            if ($labelKey) {
                $original = $model->$labelKey;
                $base = preg_replace('/\s\((Copy(?: \d+)?)\)$/i', '', $original);

                $existing = $modelClass::where($labelKey, 'like', $base . ' (Copy%')->pluck($labelKey);

                $max = 0;
                foreach ($existing as $existingValue) {
                    if (preg_match('/\s\(Copy(?: (\d+))?\)$/i', $existingValue, $matches)) {
                        $n = isset($matches[1]) ? (int) $matches[1] : 1;
                        if ($n > $max) $max = $n;
                    }
                }

                $newLabel = $base . ' (Copy' . ($max + 1 > 1 ? ' ' . ($max + 1) : '') . ')';
                $newModel->$labelKey = $newLabel;
            }

            $table = $model->getTable();
            $columns = Schema::getColumnListing($table);

            if (in_array('active', $columns)) {
                $newModel->active = 0;
            }

            if (in_array('order', $columns)) {
                $maxOrder = $model->newQuery()->max('order');
                $newModel->order = $maxOrder + 1;
            }

            if (!empty($listConfig['duplicate']['uniqueColumns'])) {
                foreach ($listConfig['duplicate']['uniqueColumns'] as $col) {
                    if (isset($col['column']) && in_array($col['column'], $columns)) {
                        $columnName = $col['column'];
                        $original = $model->$columnName;
                        $base = preg_replace('/-\d+$/', '', $original);

                        $existing = $model->newQuery()
                            ->withTrashed()
                            ->where($columnName, 'like', $base . '-%')
                            ->pluck($columnName);

                        $max = 1;
                        foreach ($existing as $val) {
                            if (preg_match('/-(\d+)$/', $val, $m)) {
                                $n = (int)$m[1];
                                $max = max($max, $n + 1);
                            }
                        }

                        $newModel->$columnName = $base . '-' . $max;
                    }
                }
            }

            $newModel->push();
        }

        // TODO Also duplicate relations

        $listData = ListService::getData($key, $this->getListParams());

        return [
            'success' => true,
            'listData' => $listData,
            'message' => __('admin::api.duplicate.successMessage'),
        ];
    }

    /**
     * Delete item
     */
    public function delete()
    {
        $key = request()->input('key');
        $id = request()->input('id');
        $ids = request()->input('ids', []);
        $force = request()->input('force');

        $listConfig = ListService::getConfig($key);
        $modelClassName = $listConfig['model'] ?? null;
        $modelClass = 'App\\Models\\' . $modelClassName;

        $idList = collect($ids);
        if ($id && !$idList->contains($id)) {
            $idList->push($id);
        }

        if ($idList->isEmpty()) {
            return ['success' => false];
        }

        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($modelClass));

        $query = $usesSoftDeletes
            ? $modelClass::withTrashed()
            : $modelClass::query();

        $models = $query->whereIn('id', $idList)->get();

        foreach ($models as $model) {
            $model->timestamps = false;
            $force ? $model->forceDelete() : $model->delete();
        }

        $listData = ListService::getData($key, $this->getListParams());

        return [
            'success' => true,
            'listData' => $listData,
            'message' => __('admin::api.delete.successMessage' . ($idList->count() > 1 ? 'Bulk' : ''), ['items' => $idList->count()]),
        ];
    }

    /**
     * Restore item
     */
    public function restore()
    {
        $key = request()->input('key');
        $id = request()->input('id');
        $ids = request()->input('ids', []);

        $listConfig = ListService::getConfig($key);
        $modelClassName = $listConfig['model'] ?? null;
        $modelClass = 'App\\Models\\' . $modelClassName;

        $idList = collect($ids);
        if ($id && !$idList->contains($id)) {
            $idList->push($id);
        }

        if ($idList->isEmpty()) {
            return ['success' => false];
        }

        $models = $modelClass::withTrashed()->whereIn('id', $idList)->get();

        foreach ($models as $model) {
            $model->timestamps = false;
            $model->restore();
        }

        $listData = ListService::getData($key, $this->getListParams());

        return [
            'success' => true,
            'listData' => $listData,
            'message' => __('admin::api.restore.successMessage' . ($idList->count() > 1 ? 'Bulk' : ''), ['items' => $idList->count()]),
        ];
    }

    /**
     * Upload files for list
     */
    public function mediaUpload()
    {
        $file = request()->file('file');

        $response = MediaHelper::store($file);

        if ($response['success']) {
            $listData = ListService::getData('media', [
                'orderBy' => 'updated_at',
                'orderDirection' => 'desc',
            ]);

            return [
                'success' => true,
                'listData' => $listData,
            ];
        }

        return response()->json($response);
    }

    /**
     * Update user config for a list
     */
    public function updateUserConfig()
    {
        $data = request()->input('data');

        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'Data is empty.',
            ];
        }

        $user = Auth::user();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        $userSettings = $user->settings ?? [];
        $userSettings = ArrayHelper::mergeRecursiveDistinct($userSettings, $data);
        $user->settings = $userSettings;
        $user->save();

        return [
            'success' => true,
        ];
    }

    /**
     * Save user settings
     */
    public function saveUserSettings()
    {
        $data = request()->input('data');
        
        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'Data is empty.',
            ];
        }
        
        $user = Auth::user();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }
        
        $userSettings = $user->settings ?? [];
        $userSettings = ArrayHelper::mergeRecursiveDistinct($userSettings, $data);
        $user->settings = $userSettings;
        $user->save();

        return [
            'success' => true,
        ];
    }

    /**
     * Remove user settings
     */
    public function removeUserSettings()
    {
        $data = request()->input('data');

        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'Data is empty.',
            ];
        }
        
        $user = Auth::user();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }
        
        $userSettings = $user->settings ?? [];
        $userSettings = ArrayHelper::removeRecursive($userSettings, $data);
        $user->settings = $userSettings;
        $user->save();

        return [
            'success' => true,
        ];
    }
}
