<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualBankPayment;
use App\Models\PaymentReconciliationRecord;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Services\ProviderReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ADMIN → PAYMENTS → OVERVIEW: today's/month's receipts, pending, failed,
 * refunded, outstanding + per-currency totals, filters, reconciliation.
 */
class PaymentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from',
            'provider' => 'nullable|string|max:60', 'currency' => 'nullable|string|size:3',
            'status' => 'nullable|string|max:30', 'customer' => 'nullable|string|max:150',
        ]);
        $q = PaymentTransaction::with(['customer', 'invoice', 'provider'])->latestFirst();
        foreach (['provider' => 'provider_key', 'currency' => 'original_currency', 'status' => 'status'] as $in => $col) {
            if (! empty($filters[$in])) {
                $q->where($col, $filters[$in]);
            }
        }
        if (! empty($filters['from'])) {
            $q->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $q->whereDate('created_at', '<=', $filters['to']);
        }
        if (! empty($filters['customer'])) {
            $q->whereHas('customer', fn ($qq) => $qq->where('name', 'like', '%'.$filters['customer'].'%')->orWhere('email', 'like', '%'.$filters['customer'].'%'));
        }
        $transactions = $q->paginate(20)->withQueryString();

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $paid = fn ($from = null) => PaymentTransaction::where('status', 'paid')->when($from, fn ($qq) => $qq->whereDate('paid_at', '>=', $from));
        $stats = [
            'today_count' => (clone $paid($today))->count(),
            'today_total' => (clone $paid($today))->sum('gross_amount'),
            'month_count' => (clone $paid($monthStart))->count(),
            'month_total' => (clone $paid($monthStart))->sum('gross_amount'),
            'pending' => PaymentTransaction::whereIn('status', ['initiated', 'pending', 'processing', 'pending_verification', 'requires_verification'])->count(),
            'failed' => PaymentTransaction::whereIn('status', ['failed', 'cancelled'])->count(),
            'refunded_amount' => PaymentTransaction::whereIn('status', ['refunded', 'partially_refunded'])->sum('refunded_amount'),
            'outstanding' => \App\Models\Invoice::whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])->sum('amount_due'),
            'bank_pending' => ManualBankPayment::where('status', 'pending_verification')->count(),
            'refund_requests' => PaymentRefund::where('status', 'requested')->count(),
        ];
        $byCurrency = PaymentTransaction::where('status', 'paid')
            ->select('original_currency', DB::raw('COUNT(*) as n'), DB::raw('SUM(gross_amount) as total'))
            ->groupBy('original_currency')->get();
        $providers = \App\Models\PaymentProvider::ordered()->get();

        return view('admin.payments.overview', compact('transactions', 'stats', 'byCurrency', 'providers', 'filters'));
    }

    public function reconciliation(ProviderReconciliationService $recon)
    {
        $summary = $recon->sweep(200);
        $records = PaymentReconciliationRecord::latest('id')->paginate(25);
        $needsReview = PaymentReconciliationRecord::needsReview()->count();

        return view('admin.payments.reconciliation', compact('summary', 'records', 'needsReview'));
    }
}
