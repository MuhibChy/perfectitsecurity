<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BankAccount;
use Illuminate\Http\Request;

/** Company receiving accounts (sensitive numbers encrypted, masked in UI). */
class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::ordered()->get();

        return view('admin.payments.bank-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('admin.payments.bank-accounts.form', ['account' => new BankAccount(['is_active' => true, 'sort_order' => 100, 'currency' => 'BDT'])]);
    }

    public function store(Request $request)
    {
        $account = new BankAccount($this->validated($request));
        $account->account_number = $request->input('account_number') ?: null;
        $account->created_by = auth()->id();
        $account->save();
        AuditLog::log('bank_account.created', 'bank_accounts', $account, "Bank account {$account->label} created.");

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank account saved.');
    }

    public function edit(BankAccount $account)
    {
        return view('admin.payments.bank-accounts.form', ['account' => $account]);
    }

    public function update(Request $request, BankAccount $account)
    {
        $account->fill($this->validated($request));
        if ($request->filled('account_number')) {
            $account->account_number = $request->input('account_number');
        }
        $account->save();
        AuditLog::log('bank_account.updated', 'bank_accounts', $account, "Bank account {$account->label} updated.");

        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank account updated.');
    }

    public function toggle(BankAccount $account)
    {
        $account->is_active = ! $account->is_active;
        $account->save();
        AuditLog::log('bank_account.toggled', 'bank_accounts', $account, "Bank account {$account->label} ".($account->is_active ? 'activated' : 'deactivated').'.');

        return back()->with('success', 'Bank account status updated.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => 'required|string|max:120',
            'country' => 'required|string|max:100',
            'currency' => 'required|string|size:3',
            'account_name' => 'required|string|max:150',
            'bank_name' => 'required|string|max:150',
            'branch' => 'nullable|string|max:150',
            'routing_number' => 'nullable|string|max:60',
            'swift_bic' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:60',
            'payment_instructions' => 'nullable|string|max:2000',
            'purpose' => 'required|in:collections,payroll,general',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:10000',
        ]);
        $data['currency'] = strtoupper($data['currency']);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}
