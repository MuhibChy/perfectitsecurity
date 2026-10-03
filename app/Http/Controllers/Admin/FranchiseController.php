<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Franchise;
use App\Models\User;
use Illuminate\Http\Request;

/** Franchise accounts: separate P&L, consolidated reporting via relations. */
class FranchiseController extends Controller
{
    public function index()
    {
        $franchises = Franchise::with('owner')->latest()->paginate(20);

        return view('admin.franchises.index', compact('franchises'));
    }

    public function create()
    {
        $owners = User::where('is_active', true)->orderBy('name')->limit(100)->get();

        return view('admin.franchises.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'legal_name' => 'nullable|string|max:255',
            'owner_id' => 'required|exists:users,id', 'territory' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255', 'contact_phone' => 'nullable|string|max:30',
        ]);
        $franchise = Franchise::create($data + ['status' => 'active']);
        AuditLog::log('franchise.created', 'franchises', $franchise, "Franchise {$franchise->franchise_code} created for {$franchise->name}.");

        return redirect()->route('admin.franchises.show', $franchise->id)->with('success', 'Franchise account created.');
    }

    public function show(Franchise $franchise)
    {
        $franchise->load('owner', 'members', 'transfers.beneficiary');
        $memberIds = $franchise->members()->pluck('id');
        $orders = \App\Models\ServiceOrder::whereIn('customer_id', $memberIds)->with(['customer', 'service'])->latest()->limit(50)->get();
        $payments = \App\Models\Payment::whereIn('customer_id', $memberIds)->latest()->limit(50)->get();
        $revenue = (float) $payments->where('status', 'completed')->sum('amount');

        return view('admin.franchises.show', compact('franchise', 'orders', 'payments', 'revenue'));
    }
}
