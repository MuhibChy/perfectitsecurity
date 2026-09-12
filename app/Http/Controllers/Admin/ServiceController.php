<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Country;
use App\Models\ServiceCountryPrice;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('category', 'countryPrices.country');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('short_description', 'like', "%{$request->search}%");
            });
        }
        if ($request->has('is_featured')) {
            $query->where('is_featured', true);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->has('allows_custom_quote')) {
            $query->where('allows_custom_quote', true);
        }

        $services = $query->latest()->paginate(20)->withQueryString();
        $categories = ServiceCategory::withCount('services')->orderBy('sort_order')->get();

        return view('admin.services.index', compact('services', 'categories'));
    }

    public function create()
    {
        $categories = ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();
        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.services.create', compact('categories', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'subcategory' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'scope' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'price_type' => 'required|string',
            'complexity_level' => 'nullable|string',
            'starting_price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'allows_custom_quote' => 'boolean',
            'estimated_completion' => 'nullable|string',
            'features' => 'nullable|string',
            'faq' => 'nullable|string',
            'tags' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'icon' => 'nullable|string',
            'image' => 'nullable|string',
            // Country prices
            'country_prices' => 'nullable|array',
            'country_prices.*.country_id' => 'required_with:country_prices|exists:countries,id',
            'country_prices.*.pricing_type' => 'required_with:country_prices|string',
            'country_prices.*.price' => 'required_with:country_prices|numeric|min:0',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name']);

        // Handle JSON fields
        foreach (['deliverables', 'features', 'faq', 'tags'] as $field) {
            if (!empty($validated[$field])) {
                $validated[$field] = array_map('trim', explode("\n", $validated[$field]));
                $validated[$field] = array_filter($validated[$field]);
                $validated[$field] = array_values($validated[$field]);
            } else {
                $validated[$field] = null;
            }
        }

        $countryPrices = $validated['country_prices'] ?? [];
        unset($validated['country_prices']);
        foreach (['description', 'full_description'] as $htmlField) {
            if (!empty($validated[$htmlField])) {
                $validated[$htmlField] = \App\Services\HtmlSanitizer::clean($validated[$htmlField]);
            }
        }

        $service = Service::create($validated);

        // Save country prices
        if (!empty($countryPrices)) {
            foreach ($countryPrices as $cp) {
                ServiceCountryPrice::create([
                    'service_id' => $service->id,
                    'country_id' => $cp['country_id'],
                    'pricing_type' => $cp['pricing_type'],
                    'price' => $cp['price'],
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully!');
    }

    public function show($id)
    {
        $service = Service::with('category', 'countryPrices.country', 'relatedServices')->findOrFail($id);
        return view('admin.services.show', compact('service'));
    }

    public function edit($id)
    {
        $service = Service::with('countryPrices')->findOrFail($id);
        $categories = ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();
        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();

        // Convert JSON arrays to newline-separated strings for textarea
        foreach (['deliverables', 'features', 'faq', 'tags'] as $field) {
            if (is_array($service->$field)) {
                $service->$field = implode("\n", $service->$field);
            }
        }

        return view('admin.services.edit', compact('service', 'categories', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'subcategory' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'scope' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'price_type' => 'required|string',
            'complexity_level' => 'nullable|string',
            'starting_price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'allows_custom_quote' => 'boolean',
            'estimated_completion' => 'nullable|string',
            'features' => 'nullable|string',
            'faq' => 'nullable|string',
            'tags' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'icon' => 'nullable|string',
            'image' => 'nullable|string',
            // Country prices
            'country_prices' => 'nullable|array',
            'country_prices.*.country_id' => 'required_with:country_prices|exists:countries,id',
            'country_prices.*.pricing_type' => 'required_with:country_prices|string',
            'country_prices.*.price' => 'required_with:country_prices|numeric|min:0',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name'], $service->id);

        // Handle JSON fields
        foreach (['deliverables', 'features', 'faq', 'tags'] as $field) {
            if (!empty($validated[$field])) {
                $validated[$field] = array_map('trim', explode("\n", $validated[$field]));
                $validated[$field] = array_filter($validated[$field]);
                $validated[$field] = array_values($validated[$field]);
            } else {
                $validated[$field] = null;
            }
        }

        $countryPrices = $validated['country_prices'] ?? [];
        unset($validated['country_prices']);
        foreach (['description', 'full_description'] as $htmlField) {
            if (!empty($validated[$htmlField])) {
                $validated[$htmlField] = \App\Services\HtmlSanitizer::clean($validated[$htmlField]);
            }
        }

        $service->update($validated);

        // Sync country prices
        if (!empty($countryPrices)) {
            foreach ($countryPrices as $cp) {
                ServiceCountryPrice::updateOrCreate(
                    ['service_id' => $service->id, 'country_id' => $cp['country_id']],
                    [
                        'pricing_type' => $cp['pricing_type'],
                        'price' => $cp['price'],
                        'is_active' => true,
                    ]
                );
            }
        }

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully!');
    }

    public function destroy($id)
    {
        Service::findOrFail($id)->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }

    /**
     * Build a unique service slug, appending -2, -3, ... on collision
     * instead of letting the database throw a 500 error.
     */
    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Service::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    // ─── SERVICE REQUESTS ──────────────────────────────────────────────────────
    public function requests(Request $request)
    {
        $query = ServiceRequest::with('service', 'user', 'country', 'assignedTo', 'quotation');

        if ($request->status) {
            $query->where('review_status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $serviceRequests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.services.requests', compact('serviceRequests'));
    }

    public function showRequest($id)
    {
        $serviceRequest = ServiceRequest::with('service', 'user', 'country', 'assignedTo', 'quotation')->findOrFail($id);
        return view('admin.services.request-show', compact('serviceRequest'));
    }

    public function updateRequest(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $validated = $request->validate([
            'review_status' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'scope_details' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'estimated_delivery' => 'nullable|string',
            'quoted_price' => 'nullable|numeric|min:0',
        ]);

        $serviceRequest->update($validated);

        return redirect()->back()->with('success', 'Request updated successfully!');
    }

    public function pipeline(Request $request)
    {
        $query = ServiceRequest::with(['service', 'user', 'country', 'assignedTo', 'quotation']);

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $allRequests = $query->latest()->get();

        $stages = [
            'inbound' => [
                'title' => 'Inbound & New',
                'color' => 'blue',
                'statuses' => ['new', 'awaiting_info'],
                'items' => collect(),
            ],
            'scoping' => [
                'title' => 'Scoping & Review',
                'color' => 'amber',
                'statuses' => ['under_review', 'scope_clarification'],
                'items' => collect(),
            ],
            'pricing' => [
                'title' => 'Pricing & Estimation',
                'color' => 'cyan',
                'statuses' => ['pricing_in_progress', 'pending_approval'],
                'items' => collect(),
            ],
            'quoted' => [
                'title' => 'Proposal & Quoted',
                'color' => 'indigo',
                'statuses' => ['sent_to_customer', 'quoted'],
                'items' => collect(),
            ],
            'won' => [
                'title' => 'Won / Converted',
                'color' => 'emerald',
                'statuses' => ['accepted', 'converted'],
                'items' => collect(),
            ],
            'lost' => [
                'title' => 'Declined / Closed',
                'color' => 'rose',
                'statuses' => ['rejected', 'cancelled', 'expired'],
                'items' => collect(),
            ],
        ];

        foreach ($allRequests as $sr) {
            $status = $sr->review_status ?? 'new';
            $assigned = false;
            foreach ($stages as $key => &$stage) {
                if (in_array($status, $stage['statuses'])) {
                    $stage['items']->push($sr);
                    $assigned = true;
                    break;
                }
            }
            if (!$assigned) {
                $stages['inbound']['items']->push($sr);
            }
        }

        $totalValue = $allRequests->sum(function ($r) {
            return $r->quoted_price ?: ($r->budget ?: 0);
        });
        $totalCount = $allRequests->count();
        $staffMembers = User::staff()->get();

        return view('admin.services.pipeline', compact('stages', 'totalValue', 'totalCount', 'staffMembers'));
    }

    public function updateRequestStatus(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $validated = $request->validate([
            'review_status' => 'required|string',
        ]);

        $serviceRequest->update([
            'review_status' => $validated['review_status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stage updated successfully.',
                'new_status' => $serviceRequest->review_status,
            ]);
        }

        return redirect()->back()->with('success', 'Lead stage updated successfully!');
    }

    public function graduateToQuote($id)
    {
        $serviceRequest = ServiceRequest::with('service', 'user')->findOrFail($id);

        // Locate or create customer account
        $customerId = $serviceRequest->user_id;
        if (!$customerId && $serviceRequest->email) {
            $user = User::where('email', $serviceRequest->email)->first();
            if ($user) {
                $customerId = $user->id;
                $serviceRequest->update(['user_id' => $user->id]);
            } else {
                $user = User::create([
                    'name' => $serviceRequest->name ?: 'Customer',
                    'email' => $serviceRequest->email,
                    'phone' => $serviceRequest->phone,
                    'password' => \Illuminate\Support\Str::random(16),
                    'role' => 'customer',
                    'is_active' => true,
                ]);
                $customerId = $user->id;
                $serviceRequest->update(['user_id' => $user->id]);
            }
        }

        // If quotation already exists, navigate to it
        if ($serviceRequest->quotation_id) {
            return redirect()->route('admin.quotations.show', $serviceRequest->quotation_id)
                ->with('info', 'A quotation is already attached to this request.');
        }

        $unitPrice = $serviceRequest->quoted_price ?: ($serviceRequest->budget ?: 500);
        $country = $serviceRequest->country;
        $taxRate = $country
            ? app(\App\Services\BusinessHoursService::class)->taxRateForCountry($country)
            : 0;
        $taxAmount = round($unitPrice * ($taxRate / 100), 2);

        // Create draft Quotation
        $quotation = Quotation::create([
            'customer_id' => $customerId,
            'company_id' => $serviceRequest->user?->company_id,
            'service_request_id' => $serviceRequest->id,
            'notes' => $serviceRequest->requirements,
            'terms' => 'Standard 30 days payment terms upon completion of deliverables.',
            'valid_until' => now()->addDays(30),
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'subtotal' => $unitPrice,
            'total' => $unitPrice + $taxAmount,
            'currency' => $serviceRequest->currency ?: ($country?->currency_code ?? 'USD'),
            'country_id' => $serviceRequest->country_id,
            'status' => 'draft',
            'assigned_to' => $serviceRequest->assigned_to ?? auth()->id(),
        ]);

        $quotation->items()->create([
            'description' => $serviceRequest->service ? $serviceRequest->service->name : 'Specialized IT Service Execution',
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'discount' => 0,
            'total' => $unitPrice,
        ]);

        $serviceRequest->update([
            'quotation_id' => $quotation->id,
            'review_status' => 'quoted',
        ]);

        return redirect()->route('admin.quotations.show', $quotation->id)
            ->with('success', "Lead #{$serviceRequest->request_number} graduated to Quote #{$quotation->quotation_number}!");
    }
}
