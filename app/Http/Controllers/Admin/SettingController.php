<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TicketCategory;
use App\Models\SlaPolicy;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $ticketCategories = TicketCategory::all();
        $slaPolicies = SlaPolicy::all();
        $expenseCategories = ExpenseCategory::all();
        return view('admin.settings.index', compact('settings', 'ticketCategories', 'slaPolicies', 'expenseCategories'));
    }

    public function update(Request $request)
    {
        $settings = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string',
            'company_city' => 'nullable|string',
            'company_state' => 'nullable|string',
            'company_country' => 'nullable|string',
            'company_website' => 'nullable|url',
            'currency' => 'nullable|string|max:3',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'business_hours' => 'nullable|string',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string',
        ]);

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'general');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated!');
    }
}
