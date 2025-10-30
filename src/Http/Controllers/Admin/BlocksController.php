<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BlocksController extends Controller
{
    public function list()
    {
        return view('admin::pages.blocks.list');
    }

    public function groups()
    {
        return view('admin::pages.blocks.groups');
    }
}
