<?php

namespace Zaplish\Core\Http\Controllers\Frontend;

use Zaplish\Core\Http\Controllers\Controller;

class FrontendController extends Controller
{
    public function index()
    {
        // TODO
        return view('theme::home');
    }
}
