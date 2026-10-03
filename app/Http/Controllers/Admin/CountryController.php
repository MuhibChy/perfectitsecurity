<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('sort_order')->paginate(25);

        return view('admin.countries.index', compact('countries'));
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'is_active' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'decimal_places' => 'required|integer|min:0|max:3',
            'region' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
            'timezone' => 'nullable|string|max:50',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $country->update($validated);
        \App\Services\Money::flush();

        return redirect()->route('admin.countries.index')->with('success', 'Currency settings updated.');
    }
}
