<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Country;

class PortalServiceController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::where('is_active', true)
            ->with(['services' => function ($q) {
                $q->where('is_active', true)
                  ->with('countryPrices.country')
                  ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $featured = Service::where('is_active', true)
            ->where('is_featured', true)
            ->with('category', 'countryPrices.country')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();

        $totalServices = Service::where('is_active', true)->count();

        return view('customer.services.index', compact('categories', 'featured', 'countries', 'totalServices'));
    }

    public function show($slug)
    {
        $service = Service::where('slug', $slug)
            ->where('is_active', true)
            ->with('category', 'countryPrices.country')
            ->firstOrFail();

        $related = Service::where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->with('countryPrices.country')
            ->limit(3)
            ->get();

        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();

        return view('customer.services.show', compact('service', 'related', 'countries'));
    }
}
