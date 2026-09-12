<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\UsefulLink;
use App\Models\LinkSubmission;
use Illuminate\Http\Request;

class UsefulLinksController extends Controller
{
    public function index()
    {
        $featuredLinks = UsefulLink::active()->featured()->ordered()->get();
        $allLinks = UsefulLink::active()->ordered()->get();

        return view('public.useful-links', compact('featuredLinks', 'allLinks'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'submitter_name' => 'nullable|string|max:255',
            'submitter_email' => 'nullable|email|max:255',
        ]);

        LinkSubmission::create($validated);

        return redirect()->route('useful-links')->with('success', 'Thank you! Your link submission has been received and will be reviewed by our team.');
    }
}
