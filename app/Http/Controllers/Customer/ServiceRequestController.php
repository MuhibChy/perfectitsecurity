<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\Country;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function create()
    {
        $services = Service::where('is_active', true)->with('category')->orderBy('sort_order')->get();
        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();

        // Pre-select service if passed as query param
        $selectedServiceId = request('service_id');

        return view('customer.service-request.create', compact('services', 'countries', 'selectedServiceId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'country_id' => 'nullable|exists:countries,id',
            'requirements' => 'required|string|min:20',
            'budget' => 'nullable|numeric|min:0',
            'preferred_start_date' => 'nullable|date|after:today',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'scope_details' => 'nullable|string',
            'exclusions' => 'nullable|string',
        ]);

        $user = auth()->user();
        $country = $validated['country_id'] ? Country::find($validated['country_id']) : null;

        ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $validated['service_id'] ?? null,
            'country_id' => $validated['country_id'] ?? null,
            'currency' => $country ? $country->currency_code : null,
            'currency_symbol' => $country ? $country->currency_symbol : null,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'company' => $user->company_name,
            'requirements' => $validated['requirements'],
            'budget' => $validated['budget'] ?? null,
            'preferred_start_date' => $validated['preferred_start_date'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'scope_details' => $validated['scope_details'] ?? null,
            'exclusions' => $validated['exclusions'] ?? null,
            'review_status' => 'new',
            'status' => 'new',
        ]);

        return redirect()->route('portal.dashboard')->with('success', 'Service request submitted! Our team will review it shortly.');
    }
}
