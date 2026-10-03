<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\CaseStudy;
use App\Models\KbArticle;
use App\Models\PortfolioItem;
use App\Models\Service;
use App\Models\ServiceCategory;

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

        // Real catalogue data only — no invented statistics anywhere on the page.
        $serviceCategories = ServiceCategory::where('is_active', true)
            ->with(['services' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        $portfolioItems = PortfolioItem::published()
            ->orderBy('sort_order')->latest('published_at')->limit(6)->get();

        $caseStudies = CaseStudy::published()
            ->orderBy('sort_order')->latest('published_at')->limit(3)->get();

        $kbArticles = KbArticle::published()->where('visibility', 'public')
            ->orderByDesc('views_count')->limit(6)->get();

        $kbCategories = \App\Models\KbCategory::where('is_active', true)
            ->orderBy('sort_order')->limit(8)->get();

        return view('public.home', compact(
            'featuredServices', 'latestPosts', 'serviceCategories',
            'portfolioItems', 'caseStudies', 'kbArticles', 'kbCategories'
        ));
    }
}
