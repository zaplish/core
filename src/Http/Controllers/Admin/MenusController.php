<?php

namespace Zaplish\Core\Http\Controllers\Admin;

use Zaplish\Core\Http\Controllers\Controller;

class MenusController extends Controller
{
    public function list()
    {
        return view('admin::pages.menus.list');
    }
}
