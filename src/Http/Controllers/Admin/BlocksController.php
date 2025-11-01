<?php

namespace Zaplish\Core\Http\Controllers\Admin;

use Zaplish\Core\Http\Controllers\Controller;

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
