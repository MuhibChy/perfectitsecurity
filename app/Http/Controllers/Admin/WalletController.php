<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;

/**
 * Finance/admin wallet oversight. Read for finance tier; adjustments and
 * freeze for finance tier; no arbitrary balance editing anywhere.
 */
class WalletController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Wallet::class);
        $query = Wallet::with('owner')->latest();
        if ($request->filled('q')) {
            $q = addcslashes(mb_substr(trim((string) $request->input('q')), 0, 100), '%_\\');
            $query->where(fn ($w) => $w->where('wallet_reference', 'like', "%{$q}%")
                ->orWhereHas('owner', fn ($o) => $o->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $wallets = $query->paginate(20)->withQueryString();

        $stats = [
            'deposits' => WalletTransaction::where('type', 'deposit')->where('status', 'completed')->sum('amount'),
            'payments' => WalletTransaction::where('type', 'invoice_payment')->where('status', 'completed')->sum('amount'),
            'refunds' => WalletTransaction::where('type', 'refund')->where('status', 'completed')->sum('amount'),
            'count' => WalletTransaction::where('status', 'completed')->count(),
            'mismatches' => Wallet::where('status', 'active')->take(500)->get()->filter(fn ($w) => ! $w->reconcile()['match'])->count(),
        ];

        return view('admin.wallets.index', compact('wallets', 'stats'));
    }

    public function show(Wallet $wallet)
    {
        $this->authorize('view', $wallet);
        $wallet->load('owner');
        $transactions = $wallet->transactions()->latest()->paginate(25);
        $reconciliation = $wallet->reconcile();

        return view('admin.wallets.show', compact('wallet', 'transactions', 'reconciliation'));
    }

    public function adjust(Request $request, Wallet $wallet)
    {
        $this->authorize('adjust', $wallet);
        $data = $request->validate([
            'direction' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'reason' => 'required|string|min:5|max:1000',
        ]);
        try {
            app(WalletService::class)->adjust($wallet, $data['direction'], (float) $data['amount'], $data['reason'], auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Adjustment posted with full audit trail.');
    }

    public function freeze(Request $request, Wallet $wallet)
    {
        $this->authorize('freeze', $wallet);
        $data = $request->validate(['reason' => 'nullable|string|max:1000']);
        app(WalletService::class)->freeze($wallet, auth()->id(), $data['reason'] ?? '');

        return back()->with('success', 'Wallet frozen. History remains accessible.');
    }

    public function unfreeze(Wallet $wallet)
    {
        $this->authorize('freeze', $wallet);
        app(WalletService::class)->unfreeze($wallet, auth()->id());

        return back()->with('success', 'Wallet reactivated.');
    }

    public function refund(Request $request, WalletTransaction $transaction)
    {
        $this->authorize('adjust', $transaction->wallet);
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000']);
        abort_unless($transaction->type === 'invoice_payment', 422, 'Only wallet invoice payments can be refunded here.');
        try {
            app(WalletService::class)->refundWalletPayment($transaction, auth()->id(), $data['reason']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Refund credited to wallet with linked records updated.');
    }

    public function userWallets(User $user)
    {
        $this->authorize('viewAny', \App\Models\Wallet::class);
        $wallets = Wallet::where('user_id', $user->id)->withCount('transactions')->get();

        return view('admin.wallets.user', compact('user', 'wallets'));
    }

    /** Exceptional ownership correction: eligible customer + reason + audit. */
    public function correctOwner(Request $request, Wallet $wallet)
    {
        $this->authorize('correctOwner', $wallet);
        $data = $request->validate([
            'new_owner_email' => 'required|email|exists:users,email',
            'reason' => 'required|string|min:10|max:1000',
        ]);
        $newOwner = User::where('email', $data['new_owner_email'])->firstOrFail();
        try {
            app(\App\Services\WalletService::class)->correctOwner($wallet, $newOwner, $data['reason'], auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ownership corrected with full audit trail. Ledger preserved.');
    }
}
