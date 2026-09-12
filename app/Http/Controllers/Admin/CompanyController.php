<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withCount('users');
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $companies = $query->latest()->paginate(20);
        return view('admin.companies.index', compact('companies'));
    }

    public function create() { return view('admin.companies.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        Company::create($validated);
        return redirect()->route('admin.companies.index')->with('success', 'Company created!');
    }

    public function edit(Company $company) { return view('admin.companies.edit', compact('company')); }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
        ]);
        $company->update($validated);
        return redirect()->route('admin.companies.index')->with('success', 'Company updated!');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('admin.companies.index')->with('success', 'Company deleted.');
    }
}
