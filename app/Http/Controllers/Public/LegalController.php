<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class LegalController extends Controller
{
    public function privacy()
    {
        return view('public.legal.privacy');
    }

    public function terms()
    {
        return view('public.legal.terms');
    }

    public function cookies()
    {
        return view('public.legal.cookies');
    }

    public function accessibility()
    {
        return view('public.legal.accessibility');
    }
}
