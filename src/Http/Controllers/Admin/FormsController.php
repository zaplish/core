<?php

namespace Zaplish\Core\Http\Controllers\Admin;

use Zaplish\Core\Http\Controllers\Controller;

class FormsController extends Controller
{
    public function list()
    {
        return view('admin::pages.forms.list');
    }

    public function submissions()
    {
        return view('admin::pages.forms.submissions');
    }
}
