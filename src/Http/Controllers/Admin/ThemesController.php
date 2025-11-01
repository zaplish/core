<?php

namespace Zaplish\Core\Http\Controllers\Admin;

use Zaplish\Core\Http\Controllers\Controller;

class ThemesController extends Controller
{
    public function select()
    {
        return view('admin::pages.themes.select');
    }

    public function variables()
    {
        return view('admin::pages.themes.variables');
    }
}
