<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRule;
use Illuminate\Http\Request;

class CommissionRuleController extends Controller
{
    public function index()
    {
        $rules = CommissionRule::all();
        return view('admin.commissions.rules', compact('rules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fixed,percentage,per_task,per_sale,tiered,performance,team',
            'rate' => 'required|numeric|min:0',
            'maximum_payout' => 'nullable|numeric|min:0',
        ]);

        CommissionRule::create($validated);
        return redirect()->route('admin.commission-rules.index')->with('success', 'Commission rule created!');
    }

    public function update(Request $request, $id)
    {
        $rule = CommissionRule::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);
        return redirect()->back()->with('success', 'Rule updated!');
    }
}
