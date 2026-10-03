@extends('layouts.app')

@section('title', 'My Documents — Customer Portal')
@section('page-title', 'My Documents')

@section('content')
<div class="space-y-6">
    <x-page-header title="Document Vault" subtitle="Upload and manage your documents securely." sys="DOCUMENT://VAULT" num="07">
        <x-slot:actions>
            <button onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="term-btn term-btn-sm">
                Upload Document
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Category Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('portal.documents.index') }}" class="term-btn term-btn-sm {{ !request('category') ? '' : 'term-btn-ghost' }}">All</a>
        @foreach(['general', 'project', 'ticket', 'invoice', 'contract', 'other'] as $cat)
        <a href="{{ route('portal.documents.index', ['category' => $cat]) }}" class="term-btn term-btn-sm {{ request('category') === $cat ? '' : 'term-btn-ghost' }}">{{ ucfirst($cat) }}</a>
        @endforeach
    </div>

    {{-- Documents List --}}
    @if($documents->count() > 0)
    <div class="space-y-3">
        @foreach($documents as $doc)
        <div class="term-panel p-4 flex items-center gap-4">
            {{-- Icon --}}
            <div class="w-10 h-10 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $doc->original_name }}</h4>
                <div class="flex items-center gap-3 mt-1 text-xs text-slate-600 dark:text-term-800">
                    <span class="term-tag">{{ ucfirst($doc->category) }}</span>
                    <span class="font-mono">{{ $doc->size_formatted }}</span>
                    <span>{{ $doc->created_at->diffForHumans() }}</span>
                </div>
                @if($doc->description)
                <p class="text-xs text-slate-600 dark:text-term-800 mt-1">{{ $doc->description }}</p>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('portal.documents.download', $doc) }}" class="term-btn term-btn-ghost term-btn-sm" title="Download">
                    ↓
                </a>
                <form action="{{ route('portal.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-destructive btn-sm" title="Delete">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{ $documents->links() }}
    @else
    <x-empty-state-3d type="documents" title="No Documents Yet"
        message="Upload your first document to get started.">
        <button onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="term-btn term-btn-sm">Upload Document</button>
    </x-empty-state-3d>
    @endif

    {{-- Upload Modal --}}
    <div id="upload-modal" tabindex="-1" onkeydown="if(event.key==='Escape')document.getElementById('upload-modal').classList.add('hidden')" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 overflow-y-auto">
        <div class="term-modal w-full max-w-lg mx-auto my-auto p-6 max-h-[calc(100dvh-2rem)] overflow-y-auto">
            <div class="term-modal-head !px-0 !pt-0 mb-6">
                <h3 class="term-modal-title">Upload Document</h3>
                <button onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="text-slate-600 dark:text-term-800 hover:text-white font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('portal.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="term-field-label">File *</label>
                    <input type="file" name="file" required class="term-input">
                    <p class="term-hint">Max 20MB. PDF, images, documents, spreadsheets.</p>
                    @error('file') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Category *</label>
                    <select name="category" required class="term-input">
                        <option value="general">General</option>
                        <option value="project">Project</option>
                        <option value="ticket">Ticket</option>
                        <option value="invoice">Invoice</option>
                        <option value="contract">Contract</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="term-field-label">Description</label>
                    <textarea name="description" rows="2" class="term-input" placeholder="Optional description..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="term-btn term-btn-ghost term-btn-sm">Cancel</button>
                    <button type="submit" class="term-btn term-btn-sm">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
