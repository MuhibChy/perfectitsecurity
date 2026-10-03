<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentProvider;
use App\Services\PaymentReadinessService;
use Illuminate\Http\Request;

/**
 * ADMIN → PAYMENTS → LIVE readiness (additive).
 * Finance-gated via route middleware. All views presence-only (no secrets).
 * LIVE activation requires full gates + explicit confirmation + audit entry.
 */
class PaymentGoLiveController extends Controller
{
    public function readiness(PaymentReadinessService $readiness)
    {
        return view('admin.payments.readiness.index', [
            'matrix' => $readiness->matrix(),
            'overall' => $readiness->overallStatus(),
            'appEnv' => app()->environment(),
        ]);
    }

    public function show(string $provider, PaymentReadinessService $readiness)
    {
        $provider = strtolower($provider);
        abort_unless(array_key_exists($provider, PaymentReadinessService::CATALOG), 404);
        $detail = $readiness->forProvider($provider);

        return view('admin.payments.readiness.show', [
            'detail' => $detail,
            'confirmation' => PaymentReadinessService::CONFIRMATION_PHRASE,
            'appEnv' => app()->environment(),
        ]);
    }

    public function activateLive(Request $request, string $provider, PaymentReadinessService $readiness)
    {
        $provider = strtolower($provider);
        abort_unless(array_key_exists($provider, PaymentReadinessService::CATALOG), 404);
        $data = $request->validate([
            'confirmation' => 'required|string',
            'environment' => 'required|in:live',
        ]);
        if ($data['confirmation'] !== PaymentReadinessService::CONFIRMATION_PHRASE) {
            return back()->withErrors(['confirmation' => 'Explicit confirmation phrase is required to activate LIVE mode.'])->withInput();
        }
        $row = PaymentProvider::where('key', $provider)->first();
        $gate = $readiness->canActivateLive($provider, $row);
        if (! $gate['ok']) {
            return back()->withErrors(['provider' => 'LIVE activation blocked: '.implode(' ', $gate['blockers'])]);
        }
        $old = $row->only(['status', 'environment']);
        $row->update(['environment' => 'live', 'status' => 'live', 'updated_by' => auth()->id()]);
        AuditLog::log('provider.live_activated', 'payment_providers', $row,
            "Provider {$provider} activated LIVE by ".(auth()->user()->email ?? auth()->id()).'.',
            ['status' => $old['status'], 'environment' => $old['environment']],
            ['status' => 'live', 'environment' => 'live', 'app_env' => app()->environment(), 'checks_passed' => true]);

        return redirect()->route('admin.payments.readiness.show', $provider)
            ->with('status', strtoupper($row->name ?? $provider).' is now LIVE. Real payments will be processed.');
    }

    public function health(PaymentReadinessService $readiness)
    {
        return view('admin.payments.readiness.health', [
            'monitoring' => $readiness->monitoring(),
            'fx' => $readiness->fxHealth(),
            'matrix' => $readiness->matrix(),
        ]);
    }

    public function configCheck(PaymentReadinessService $readiness)
    {
        return view('admin.payments.readiness.config-check', [
            'check' => $readiness->configCheck(),
            'fx' => $readiness->fxHealth(),
        ]);
    }

    public function checklist(PaymentReadinessService $readiness)
    {
        return view('admin.payments.readiness.checklist', [
            'checklist' => $readiness->checklist(),
            'overall' => $readiness->overallStatus(),
        ]);
    }
}
