<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentProvider;
use App\Services\PaymentProviderService;
use Illuminate\Http\Request;

/**
 * ADMIN → SYSTEM → PAYMENTS → PROVIDERS (finance/admin only via route middleware).
 * Provider rows are data: credentials stored encrypted, never displayed back.
 */
class PaymentProviderController extends Controller
{
    public function index(PaymentProviderService $providers)
    {
        $rows = PaymentProvider::ordered()->get()->map(function ($p) use ($providers) {
            $p->mode_label = $providers->modeLabel($p);
            $p->webhook_endpoint = $providers->webhookUrl($p);
            return $p;
        });
        return view('admin.payments.providers.index', ['providers' => $rows]);
    }

    public function create()
    {
        return view('admin.payments.providers.form', ['provider' => new PaymentProvider(['status' => 'disabled', 'environment' => 'test', 'priority' => 100, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $provider = new PaymentProvider($this->scalar($data));
        $provider->credentials = $this->credentialsInput($request);
        $provider->webhook_secret = $request->input('webhook_secret') ?: null;
        $provider->created_by = auth()->id();
        $provider->updated_by = auth()->id();
        $provider->save();
        AuditLog::log('provider.created', 'payment_providers', $provider, "Provider {$provider->key} created.");
        return redirect()->route('admin.payment-providers.index')->with('success', 'Provider saved. Secrets are stored encrypted.');
    }

    public function edit(PaymentProvider $provider)
    {
        return view('admin.payments.providers.form', compact('provider'));
    }

    public function update(Request $request, PaymentProvider $provider)
    {
        $data = $this->validated($request, $provider->id);
        $provider->fill($this->scalar($data));
        $creds = $this->credentialsInput($request);
        if ($creds !== null) $provider->credentials = $creds; // blank = keep existing
        if ($request->filled('webhook_secret')) $provider->webhook_secret = $request->input('webhook_secret');
        $provider->updated_by = auth()->id();
        $provider->save();
        AuditLog::log('provider.updated', 'payment_providers', $provider, "Provider {$provider->key} updated.");
        return redirect()->route('admin.payment-providers.index')->with('success', 'Provider updated.');
    }

    public function toggle(PaymentProvider $provider)
    {
        $provider->is_active = !$provider->is_active;
        $provider->updated_by = auth()->id();
        $provider->save();
        AuditLog::log('provider.toggled', 'payment_providers', $provider, "Provider {$provider->key} " . ($provider->is_active ? 'enabled' : 'disabled') . '.');
        return back()->with('success', 'Provider status updated.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'key' => 'required|string|max:60|regex:/^[a-z0-9_]+$/|unique:payment_providers,key' . ($ignoreId ? ',' . $ignoreId : ''),
            'name' => 'required|string|max:120',
            'type' => 'required|in:' . implode(',', PaymentProvider::TYPES),
            'country' => 'nullable|string|max:100',
            'currencies' => 'nullable|string|max:255',
            'payment_methods' => 'nullable|string|max:255',
            'environment' => 'required|in:test,live',
            'status' => 'required|in:' . implode(',', PaymentProvider::STATUSES),
            'priority' => 'required|integer|min:1|max:1000',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'fee_type' => 'nullable|in:fixed,percent,mixed,none',
            'fee_value' => 'nullable|numeric|min:0',
            'platform_fee_value' => 'nullable|numeric|min:0',
            'webhook_url' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function scalar(array $data): array
    {
        foreach (['currencies', 'payment_methods'] as $k) {
            $data[$k] = empty($data[$k]) ? null : array_values(array_filter(array_map(fn ($v) => strtoupper(trim($v)), preg_split('/[,\s]+/', (string) $data[$k]))));
        }
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        return $data;
    }

    /**
     * Credential fields from the form. Returns null when all blank
     * (meaning: keep the stored secrets on update).
     */
    protected function credentialsInput(Request $request): ?array
    {
        $out = [];
        foreach (['api_key', 'secret_key', 'merchant_id', 'account_id', 'public_key', 'username'] as $k) {
            $v = trim((string) $request->input('cred_' . $k, ''));
            if ($v !== '') $out[$k] = $v;
        }
        $extra = trim((string) $request->input('cred_extra', ''));
        if ($extra !== '') {
            $decoded = json_decode($extra, true);
            abort_unless(is_array($decoded), 422, 'Extra credentials must be valid JSON.');
            $out = array_merge($out, $decoded);
        }
        return $out === [] ? null : $out;
    }
}
