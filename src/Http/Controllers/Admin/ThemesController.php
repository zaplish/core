<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
