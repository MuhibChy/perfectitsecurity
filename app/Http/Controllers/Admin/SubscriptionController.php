<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Service;
use App\Models\User;
use App\Models\Country;
use App\Services\BusinessHoursService;
use App\Services\RecurringBillingService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with('customer', 'service')->latest()->paginate(20);
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $customers = User::customers()->active()->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $countries = Country::active()->orderBy('sort_order')->get();
        return view('admin.subscriptions.create', compact('customers', 'services', 'countries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'service_id' => 'nullable|exists:services,id',
            'name' => 'required|string|max:255',
            'interval' => 'required|in:monthly,yearly',
            'interval_count' => 'nullable|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'country_id' => 'nullable|exists:countries,id',
            'tax_rate' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
        ]);

        if ($request->filled('country_id') && !isset($data['tax_rate'])) {
            $country = Country::find($data['country_id']);
            $data['tax_rate'] = app(BusinessHoursService::class)->taxRateForCountry($country);
        }

        $starts = $data['starts_at'] ?? now()->toDateString();
        $subscription = Subscription::create([
            'customer_id' => $data['customer_id'],
            'service_id' => $data['service_id'] ?? null,
            'name' => $data['name'],
            'interval' => $data['interval'],
            'interval_count' => $data['interval_count'] ?? 1,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'tax_rate' => $data['tax_rate'] ?? 0,
            'status' => 'active',
            'starts_at' => $starts,
            'next_billing_at' => $starts,
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)->with('success', 'Subscription created.');
    }

    public function show(Subscription $subscription)
    {
        $subscription->load('customer', 'service', 'invoices');
        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'status' => 'nullable|in:trial,active,past_due,paused,cancelled',
            'amount' => 'nullable|numeric|min:0',
            'next_billing_at' => 'nullable|date',
        ]);

        if (($data['status'] ?? null) === 'cancelled') {
            $data['cancelled_at'] = now();
        }

        $subscription->update(array_filter($data, fn ($v) => !is_null($v)));
        return back()->with('success', 'Subscription updated.');
    }

    public function billNow(Subscription $subscription, RecurringBillingService $billing)
    {
        $invoice = $billing->generateInvoice($subscription);
        return redirect()->route('admin.invoices.edit', $invoice)->with('success', 'Invoice generated from subscription.');
    }
}
