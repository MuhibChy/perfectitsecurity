<?php

namespace App\Http\Controllers;

use App\Models\IdentityDocument;
use App\Services\IdentityVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Member self-service identity verification (portal + staff).
 * Uploads are validated images/PDFs on the private disk; uploading never
 * verifies — only an authorized reviewer can change verification state.
 */
class IdentityController extends Controller
{
    public function __construct(private IdentityVerificationService $identity)
    {
    }

    public function index()
    {
        $user = auth()->user();
        $documents = $user->identityDocuments()->latest()->get();
        $types = IdentityDocument::TYPES;

        return view('identity.index', compact('user', 'documents', 'types'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'document_type' => 'required|in:'.implode(',', IdentityDocument::TYPES),
            'issuing_country' => 'nullable|string|size:3',
            'expiry_date' => 'nullable|date|after:today',
            // Real content check: image/pdf MIME + 8MB cap; executable content rejected.
            'document' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:8192',
        ]);

        $doc = $this->identity->submit($user, $request->file('document'), $data);

        return back()->with('success', "Document received (reference #{$doc->id}). Status: submitted — review pending.");
    }

    /** Owner views own submission metadata; file download is owner-only here. */
    public function download(IdentityDocument $document)
    {
        $user = auth()->user();
        abort_unless((int) $document->user_id === (int) $user->id, 404);
        abort_unless(Storage::disk('private')->exists($document->path), 404);
        $this->identity->recordAccess($document, $user, 'downloaded_own');

        return Storage::disk('private')->download($document->path, $document->original_name ?? 'identity-document');
    }
}
