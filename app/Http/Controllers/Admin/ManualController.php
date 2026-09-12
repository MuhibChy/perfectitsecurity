<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ManualController extends Controller
{
    public function index()
    {
        return view('manual.index');
    }
}
