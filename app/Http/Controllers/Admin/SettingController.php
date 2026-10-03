<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use App\Models\SlaPolicy;
use App\Models\TicketCategory;
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
            // Payment system (Admin → Settings → Payments).
            'payments_default_currency' => 'nullable|string|size:3',
            'payments_supported_currencies' => 'nullable|string|max:255',
            'payments_timeout_minutes' => 'nullable|integer|min:5|max:1440',
            'payments_invoice_deadline_days' => 'nullable|integer|min:1|max:365',
            'payments_platform_fee_percent' => 'nullable|numeric|min:0|max:100',
            'payments_require_webhook_secret' => 'nullable|boolean',
        ]);

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'general');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated!');
    }

    /**
     * ADMIN → SETTINGS → PAYMENTS
     * Structured configuration covering General, Bangladesh, International, Fees, Refunds, and Security.
     */
    public function payments()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $providers = \App\Models\PaymentProvider::ordered()->get();
        $bankAccounts = \App\Models\BankAccount::ordered()->get();

        return view('admin.settings.payments', compact('settings', 'providers', 'bankAccounts'));
    }

    public function updatePayments(Request $request)
    {
        $data = $request->validate([
            // General
            'payments_default_currency' => 'nullable|string|size:3',
            'payments_supported_currencies' => 'nullable|string|max:255',
            'payments_timeout_minutes' => 'nullable|integer|min:5|max:1440',
            'payments_invoice_deadline_days' => 'nullable|integer|min:1|max:365',
            // Fees
            'payments_default_provider_fee_percent' => 'nullable|numeric|min:0|max:100',
            'payments_platform_fee_percent' => 'nullable|numeric|min:0|max:100',
            // Refunds
            'payments_refund_require_approval' => 'nullable|boolean',
            'payments_refund_allow_customer_request' => 'nullable|boolean',
            // Security
            'payments_require_webhook_secret' => 'nullable|boolean',
            'payments_force_https' => 'nullable|boolean',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value, 'payments');
        }

        \App\Models\AuditLog::log('settings.payments_updated', 'settings', null, 'Payment configuration updated by administrator.');

        return redirect()->route('admin.settings.payments')->with('success', 'Payment system settings successfully updated.');
    }

    /**
     * ADMIN → SETTINGS → PROMOTION (additive).
     * Configures the limited-time service discount campaign. Disabling or
     * expiry never rewrites historical quotations (snapshots are stored).
     */
    public function promotion()
    {
        $promo = app(\App\Services\PromotionService::class);
        $campaign = $promo->campaign();
        $isActive = $promo->isActive();
        $categories = \App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();
        $eligible = $promo->eligibleCategoryIds();

        return view('admin.settings.promotion', compact('campaign', 'isActive', 'categories', 'eligible'));
    }

    public function updatePromotion(Request $request)
    {
        $data = $request->validate([
            'promo_enabled' => 'nullable|boolean',
            'promo_name' => 'required|string|max:160',
            'promo_percent' => 'required|numeric|min:0|max:100',
            'promo_category_ids' => 'nullable|array',
            'promo_category_ids.*' => 'exists:service_categories,id',
            'promo_starts_at' => 'nullable|date',
            'promo_ends_at' => 'nullable|date|after_or_equal:promo_starts_at',
            'promo_timezone' => 'nullable|string|max:64',
            'promo_terms' => 'nullable|string|max:2000',
        ]);

        Setting::set('promo.enabled', ! empty($data['promo_enabled']) ? '1' : '0', 'promo');
        Setting::set('promo.name', $data['promo_name'], 'promo');
        Setting::set('promo.percent', (string) $data['promo_percent'], 'promo');
        Setting::set('promo.category_ids', json_encode(array_values($data['promo_category_ids'] ?? [])), 'promo');
        Setting::set('promo.starts_at', $data['promo_starts_at'] ?? '', 'promo');
        Setting::set('promo.ends_at', $data['promo_ends_at'] ?? '', 'promo');
        Setting::set('promo.timezone', $data['promo_timezone'] ?? 'UTC', 'promo');
        Setting::set('promo.terms', $data['promo_terms'] ?? '', 'promo');

        \App\Models\AuditLog::log('settings.promotion_updated', 'settings', null, 'Promotion campaign configuration updated by administrator.');

        return redirect()->route('admin.settings.promotion')->with('success', 'Promotion campaign saved.');
    }
}
