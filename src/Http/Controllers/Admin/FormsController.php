<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
