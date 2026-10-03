<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdentityDocument;
use App\Services\IdentityVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Identity verification queue (admin-only). Document bodies are served
 * from the private disk after authorization; every view/download/review
 * action is audited with actor, timestamp and reason.
 */
class IdentityVerificationController extends Controller
{
    public function __construct(private IdentityVerificationService $identity) {}

    public function index(Request $request)
    {
        $query = IdentityDocument::with(['user', 'reviewer'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = addcslashes(mb_substr(trim((string) $request->search), 0, 100), '%_\\');
            $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('member_number', 'like', "%{$s}%"));
        }
        $documents = $query->paginate(20)->withQueryString();
        $stats = [
            'submitted' => IdentityDocument::where('status', 'submitted')->count(),
            'under_review' => IdentityDocument::where('status', 'under_review')->count(),
            'verified' => IdentityDocument::where('status', 'verified')->count(),
            'rejected' => IdentityDocument::where('status', 'rejected')->count(),
        ];
        return view('admin.identity.index', compact('documents', 'stats'));
    }

    public function show(IdentityDocument $document)
    {
        $document->load(['user', 'reviewer']);
        $this->identity->recordAccess($document, auth()->user(), 'viewed');
        return view('admin.identity.show', compact('document'));
    }

    public function download(IdentityDocument $document)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        abort_unless(Storage::disk('private')->exists($document->path), 404);
        $this->identity->recordAccess($document, auth()->user(), 'downloaded');
        return Storage::disk('private')->download($document->path, $document->original_name ?? 'identity-document');
    }

    public function review(IdentityDocument $document)
    {
        $this->identity->startReview($document, auth()->user());
        return back()->with('success', 'Review started.');
    }

    public function approve(IdentityDocument $document)
    {
        $this->identity->approve($document, auth()->user());
        return redirect()->route('admin.identity.index')->with('success', 'Identity verified.');
    }

    public function reject(Request $request, IdentityDocument $document)
    {
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        $this->identity->reject($document, auth()->user(), $data['reason']);
        return redirect()->route('admin.identity.index')->with('success', 'Document rejected with reason.');
    }
}
