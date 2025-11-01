<?php

namespace Zaplish\Core\Http\Controllers\Admin;

use Zaplish\Core\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function view()
    {
        return view('admin::pages.dashboard.view');
    }
}
