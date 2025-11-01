<?php

namespace Zaplish\Core\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Zaplish\Core\Http\Controllers\Controller;
use Zaplish\Core\Services\ListService;
use Zaplish\Core\Services\FormService;

class ContentController extends Controller
{
    public function list()
    {
        $type = request()->attributes->get('type');

        if (!$type) {
            abort(404);
        }

        return ListService::getView($type);
    }

    public function listType(string $type)
    {
        return ListService::getView($type);
    }

    public function edit(?int $id = null)
    {
        $type = request()->attributes->get('type');

        if (!$type) {
            abort(404);
        }

        if ($type === 'profile') {
            $id = Auth::user()->id;
        }

        return FormService::getView($type, $id);
    }

    public function editType(string $type, ?int $id = null)
    {
        if ($type === 'profile') {
            $id = Auth::user()->id;
        }

        return FormService::getView($type, $id);
    }
}
