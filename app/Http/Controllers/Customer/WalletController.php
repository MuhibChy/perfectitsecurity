<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Services\Wallet\StripeWalletGateway;
use App\Services\Wallet\TestWalletGateway;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Customer wallet: own wallets only (every lookup scoped to auth user).
 */
class WalletController extends Controller
{
    protected function gatewayFor(Wallet $wallet)
    {
        $stripe = new StripeWalletGateway();
        if ($stripe->isAvailable($wallet)) return $stripe;
        $test = new TestWalletGateway();
        if ($test->isAvailable($wallet)) return $test;
        return null;
    }

    public function index()
    {
        $user = auth()->user();
        $wallets = Wallet::where('user_id', $user->id)->withCount('transactions')->get();
        if ($wallets->isEmpty()) {
            $wallets = collect([app(WalletService::class)->for($user)]);
        }
        $recent = WalletTransaction::whereIn('wallet_id', $wallets->pluck('id'))->latest()->take(10)->get();
        $payable = Invoice::where('customer_id', $user->id)
            ->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])
            ->where('amount_due', '>', 0)->latest()->take(10)->get();
        return view('customer.wallet.index', compact('wallets', 'recent', 'payable'));
    }

    public function show($id)
    {
        $wallet = Wallet::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $transactions = $wallet->transactions()->latest()->paginate(20);
        return view('customer.wallet.show', compact('wallet', 'transactions'));
    }

    public function topUp(Request $request, $id)
    {
        $wallet = Wallet::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $data = $request->validate(['amount' => 'required|numeric|min:1|max:100000']);
        if ($wallet->status !== 'active') {
            return back()->with('error', 'Wallet is not active and cannot receive funds.');
        }
        $gateway = $this->gatewayFor($wallet);
        if (!$gateway) {
            return back()->with('error', 'Online top-ups are currently unavailable. Please contact support for manual payment options.');
        }
        try {
            $session = $gateway->createTopUpSession(
                $wallet, (float) $data['amount'],
                route('portal.wallet.topup.return', ['provider' => $gateway->name()]),
                route('portal.wallet.show', $wallet),
                auth()->id()
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->away($session['redirect_url']);
    }

    /** Server-side verified return: never credits on browser word alone. */
    public function topUpReturn(Request $request, string $provider)
    {
        $gateway = $provider === 'test' ? new TestWalletGateway() : new StripeWalletGateway();
        $verified = $gateway->verifyReturn($request->all() + ['user_id' => auth()->id()]);
        if (empty($verified['verified'])) {
            return redirect()->route('portal.wallet.index')->with('error', 'Top-up could not be verified (' . ($verified['reason'] ?? 'unknown') . '). No money was moved.');
        }
        $wallet = Wallet::where('id', $verified['wallet_id'])->where('user_id', auth()->id())->firstOrFail();
        if (strtoupper($verified['currency']) !== strtoupper($wallet->currency)) {
            return redirect()->route('portal.wallet.index')->with('error', 'Currency mismatch. No money was moved.');
        }
        try {
            if (!empty($verified['topup_id'])) {
                $pending = WalletTransaction::where('id', $verified['topup_id'])->where('wallet_id', $wallet->id)->firstOrFail();
                $result = app(WalletService::class)->completeTopUp($pending, $verified['provider_txn'] ?? null);
            } else {
                $result = app(WalletService::class)->creditDeposit(
                    $wallet, (float) $verified['amount'], 'stripe',
                    $verified['provider_txn'] ?? null, $verified['provider_event'],
                    auth()->id(), ['verified_return' => true]
                );
            }
        } catch (\Throwable $e) {
            return redirect()->route('portal.wallet.index')->with('error', $e->getMessage());
        }
        $msg = !empty($result['duplicate']) ? 'Top-up already recorded. No duplicate credit.' : 'Wallet credited successfully.';
        return redirect()->route('portal.wallet.show', $wallet)->with('success', $msg);
    }

    public function payInvoice(Request $request, $id)
    {
        $wallet = Wallet::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'nullable|numeric|min:0.01',
        ]);
        $invoice = Invoice::where('id', $data['invoice_id'])->where('customer_id', auth()->id())->firstOrFail();
        $amount = isset($data['amount']) ? (float) $data['amount'] : round((float) $invoice->amount_due, 2);
        try {
            $result = app(WalletService::class)->payInvoice($wallet, $invoice, $amount, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('portal.invoices.show', $invoice)->with('success', "Paid {$invoice->currency} {$amount} from wallet. Due now {$result['invoice']->currency} {$result['invoice']->amount_due}.");
    }

    public function statement(Request $request, $id)
    {
        $wallet = Wallet::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $filters = $request->validate([
            'type' => 'nullable|string|max:50',
            'direction' => 'nullable|in:credit,debit',
            'status' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'export' => 'nullable|in:pdf',
        ]);
        $query = $wallet->transactions()->latest();
        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        if (!empty($filters['direction'])) {
            $query->whereIn('type', $filters['direction'] === 'credit' ? Wallet::CREDIT_TYPES : Wallet::DEBIT_TYPES);
        }
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['reference'])) $query->where('transaction_reference', 'like', '%' . addcslashes($filters['reference'], '%_\\') . '%');
        if (!empty($filters['from'])) $query->whereDate('created_at', '>=', $filters['from']);
        if (!empty($filters['to'])) $query->whereDate('created_at', '<=', $filters['to']);
        $transactions = $query->paginate(25)->withQueryString();

        if (($filters['export'] ?? null) === 'pdf') {
            $pdf = Pdf::loadView('customer.wallet.statement-pdf', ['wallet' => $wallet, 'transactions' => (clone $query)->take(200)->get()])
                ->setPaper('a4');
            return $pdf->download('wallet-statement-' . $wallet->wallet_reference . '.pdf');
        }
        return view('customer.wallet.statement', compact('wallet', 'transactions'));
    }
}
