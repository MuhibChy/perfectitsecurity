<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class CustomerManualController extends Controller
{
    public function index()
    {
        return view('manual.customer');
    }
}
