<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsefulLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsefulLinkController extends Controller
{
    public function index()
    {
        $links = UsefulLink::orderBy('sort_order')->orderBy('title')->paginate(20);
        return view('admin.useful-links.index', compact('links'));
    }

    public function create()
    {
        return view('admin.useful-links.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|url|max:500',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        UsefulLink::create($validated);

        return redirect()->route('admin.useful-links.index')->with('success', 'Link added successfully!');
    }

    public function edit(UsefulLink $usefulLink)
    {
        return view('admin.useful-links.edit', ['link' => $usefulLink]);
    }

    public function update(Request $request, UsefulLink $usefulLink)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|url|max:500',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);

        $usefulLink->update($validated);

        return redirect()->route('admin.useful-links.index')->with('success', 'Link updated successfully!');
    }

    public function destroy(UsefulLink $usefulLink)
    {
        $usefulLink->delete();
        return redirect()->route('admin.useful-links.index')->with('success', 'Link deleted.');
    }

    public function toggle(UsefulLink $usefulLink)
    {
        $usefulLink->update(['is_active' => !$usefulLink->is_active]);
        return redirect()->route('admin.useful-links.index')->with('success', 'Link status toggled.');
    }
}
