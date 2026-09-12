<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerDocument::where('user_id', auth()->id());

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('original_name', 'like', "%{$request->search}%");
            });
        }

        $documents = $query->latest()->paginate(20);
        $categories = CustomerDocument::where('user_id', auth()->id())
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get();

        return view('customer.documents.index', compact('documents', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,gif,webp,txt,doc,docx,xls,xlsx,csv,zip|mimetypes:application/pdf,image/jpeg,image/png,image/gif,image/webp,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/zip,application/x-zip-compressed',
            'category' => 'required|in:general,project,ticket,invoice,contract,other',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'zip'];
        $ext = strtolower($file->getClientOriginalExtension());
        abort_unless(in_array($ext, $allowedExtensions, true), 422, 'File type not allowed.');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents', $fileName, 'private');

        CustomerDocument::create([
            'user_id' => auth()->id(),
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'category' => $request->category,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully!');
    }

    public function download(CustomerDocument $document)
    {
        abort_unless($document->user_id === auth()->id(), 403);

        // Consistently serve via the private disk (confined to its root),
        // never via raw storage_path() concatenation.
        abort_unless(\Illuminate\Support\Facades\Storage::disk('private')->exists($document->path), 404);

        return \Illuminate\Support\Facades\Storage::disk('private')->download($document->path, $document->original_name);
    }

    public function destroy(CustomerDocument $document)
    {
        abort_unless($document->user_id === auth()->id(), 403);

        $fullPath = storage_path('app/' . $document->path);
        if (file_exists($fullPath)) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($document->path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Document deleted.');
    }
}
