<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\BlogPost;
use App\Models\KbArticle;

class HomeController extends Controller
{
    public function index()
    {
        $featuredServices = Service::where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->limit(8)
            ->get();

        $latestPosts = BlogPost::published()->with('author', 'category')->latest('published_at')->limit(3)->get();

        return view('public.home', compact('featuredServices', 'latestPosts'));
    }
}
