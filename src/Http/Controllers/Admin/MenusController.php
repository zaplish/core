<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MenusController extends Controller
{
    public function list()
    {
        return view('admin::pages.menus.list');
    }
}
