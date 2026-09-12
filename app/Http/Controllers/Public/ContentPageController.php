<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\CareerPost;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;

class ContentPageController extends Controller
{
    public function caseStudies(Request $request)
    {
        $items = CaseStudy::published()->orderBy('sort_order')->latest('published_at');
        if ($request->filled('search')) {
            $s = addcslashes($request->search, '%_\\');
            $items->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('summary', 'like', "%{$s}%")
                  ->orWhere('industry', 'like', "%{$s}%");
            });
        }
        if ($request->filled('industry')) {
            $items->where('industry', $request->industry);
        }
        $items = $items->paginate(12)->withQueryString();
        $industries = CaseStudy::published()->select('industry')
            ->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry');
        return view('public.case-studies', compact('items', 'industries'));
    }

    public function caseStudy(string $slug)
    {
        $item = CaseStudy::published()->where('slug', $slug)->firstOrFail();
        return view('public.case-study-show', compact('item'));
    }

    public function careers()
    {
        $items = CareerPost::published()->latest('published_at')->paginate(12);
        return view('public.careers', compact('items'));
    }

    public function career(string $slug)
    {
        $item = CareerPost::published()->where('slug', $slug)->firstOrFail();
        return view('public.career-show', compact('item'));
    }

    public function portfolio(Request $request)
    {
        $base = PortfolioItem::published();
        if ($request->filled('search')) {
            $s = addcslashes($request->search, '%_\\');
            $base->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('summary', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }
        if ($request->filled('category')) {
            $base->where('category', $request->category);
        }
        $items = (clone $base)->orderBy('sort_order')->latest('published_at')->paginate(12)->withQueryString();
        $featured = PortfolioItem::published()->where('is_featured', true)->orderBy('sort_order')->limit(6)->get();
        $categories = PortfolioItem::published()->select('category')
            ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        return view('public.portfolio', compact('items', 'featured', 'categories'));
    }

    public function portfolioItem(string $slug)
    {
        $item = PortfolioItem::published()->where('slug', $slug)->firstOrFail();
        return view('public.portfolio-show', compact('item'));
    }
}
