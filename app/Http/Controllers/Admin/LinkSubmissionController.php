<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkSubmission;
use App\Models\UsefulLink;
use Illuminate\Http\Request;

class LinkSubmissionController extends Controller
{
    public function index()
    {
        $submissions = LinkSubmission::latest()->paginate(20);
        return view('admin.link-submissions.index', compact('submissions'));
    }

    public function show(LinkSubmission $linkSubmission)
    {
        return view('admin.link-submissions.show', ['submission' => $linkSubmission]);
    }

    public function approve(LinkSubmission $linkSubmission)
    {
        $linkSubmission->update(['status' => 'approved']);

        // Create the useful link from the submission
        UsefulLink::create([
            'title' => $linkSubmission->title,
            'url' => $linkSubmission->url,
            'description' => $linkSubmission->description,
            'category' => $linkSubmission->category ?: 'general',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => UsefulLink::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.link-submissions.index')->with('success', "Link \"{$linkSubmission->title}\" approved and added to Useful Links!");
    }

    public function reject(LinkSubmission $linkSubmission)
    {
        $linkSubmission->update(['status' => 'rejected']);
        return redirect()->route('admin.link-submissions.index')->with('success', 'Submission rejected.');
    }
}
