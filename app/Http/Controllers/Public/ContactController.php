<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index()
    {
        return view('public.contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'company' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'service_interest' => 'nullable|string|max:100',
            'budget_range' => 'nullable|string|max:50',
            'timeline' => 'nullable|string|max:50',
            'country_id' => 'nullable|exists:countries,id',
            'service_id' => 'nullable|exists:services,id',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $serviceRequest = DB::transaction(function () use ($validated) {
            $serviceRequest = ServiceRequest::create([
                'user_id' => auth()->id(),
                'service_id' => $validated['service_id'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company' => $validated['company'] ?? null,
                'subject' => $validated['subject'],
                'service_interest' => $validated['service_interest'] ?? null,
                'requirements' => $validated['message'],
                'budget' => $validated['budget'] ?? null,
                'budget_range' => $validated['budget_range'] ?? null,
                'timeline' => $validated['timeline'] ?? null,
                'lead_source' => 'contact',
                'status' => 'new',
                'review_status' => 'new',
            ]);

            Lead::create([
                'service_request_id' => $serviceRequest->id,
                'customer_id' => auth()->id(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company_name' => $validated['company'] ?? null,
                'source' => 'contact',
                'status' => 'new',
                'notes' => $validated['subject']."\n\n".$validated['message'],
                'estimated_value' => $validated['budget'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
            ]);

            return $serviceRequest;
        });

        // Staff alert: contact enquiries must reach a human (in-app notification).
        $staff = \App\Models\User::whereIn('role', ['super_admin', 'admin', 'support_manager'])
            ->where('is_active', true)->limit(10)->get();
        foreach ($staff as $member) {
            \App\Models\Notification::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'contact_enquiry',
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id' => $member->id,
                'data' => ['title' => 'New contact enquiry', 'message' => "{$validated['name']} ({$validated['email']}): {$validated['subject']}", 'service_request_id' => $serviceRequest->id],
            ]);
        }

        return redirect()->back()->with('success', 'Your message has been sent. We will get back to you shortly!');
    }
}
