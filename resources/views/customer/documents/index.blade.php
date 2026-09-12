@extends('layouts.app')

@section('title', 'My Documents — Customer Portal')
@section('page-title', 'My Documents')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload and manage your documents securely.</p>
        </div>
        <button onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Upload Document
        </button>
    </div>

    {{-- Category Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('portal.documents.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium {{ !request('category') ? 'bg-blue-500/10 text-blue-400 border border-blue-500/30' : 'bg-white/[0.02] text-gray-400 border border-white/10 hover:text-white' }}">All</a>
        @foreach(['general', 'project', 'ticket', 'invoice', 'contract', 'other'] as $cat)
        <a href="{{ route('portal.documents.index', ['category' => $cat]) }}" class="px-4 py-2 rounded-xl text-sm font-medium {{ request('category') === $cat ? 'bg-blue-500/10 text-blue-400 border border-blue-500/30' : 'bg-white/[0.02] text-gray-400 border border-white/10 hover:text-white' }}">{{ ucfirst($cat) }}</a>
        @endforeach
    </div>

    {{-- Documents List --}}
    @if($documents->count() > 0)
    <div class="space-y-3">
        @foreach($documents as $doc)
        <div class="flex items-center gap-4 p-4 rounded-xl border border-white/10 bg-white/[0.02] hover:border-white/20 transition-all">
            {{-- Icon --}}
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                @if($doc->icon === 'pdf') bg-red-500/10 text-red-400
                @elseif($doc->icon === 'image') bg-purple-500/10 text-purple-400
                @elseif($doc->icon === 'doc') bg-blue-500/10 text-blue-400
                @elseif($doc->icon === 'xls') bg-green-500/10 text-green-400
                @else bg-gray-500/10 text-gray-400 @endif">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-white truncate">{{ $doc->original_name }}</h4>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                    <span class="px-2 py-0.5 rounded bg-white/5">{{ ucfirst($doc->category) }}</span>
                    <span>{{ $doc->size_formatted }}</span>
                    <span>{{ $doc->created_at->diffForHumans() }}</span>
                </div>
                @if($doc->description)
                <p class="text-xs text-gray-500 mt-1">{{ $doc->description }}</p>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('portal.documents.download', $doc) }}" class="p-2 rounded-lg text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 transition-colors" title="Download">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </a>
                <form action="{{ route('portal.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Delete">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{ $documents->links() }}
    @else
    <div class="text-center py-16">
        <div class="w-16 h-16 rounded-2xl bg-gray-800/50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No Documents Yet</h3>
        <p class="text-gray-400 text-sm mb-4">Upload your first document to get started.</p>
        <button onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors text-sm">Upload Document</button>
    </div>
    @endif

    {{-- Upload Modal --}}
    <div id="upload-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-lg mx-4 bg-gray-900 rounded-2xl border border-white/10 shadow-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white">Upload Document</h3>
                <button onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('portal.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">File *</label>
                    <input type="file" name="file" required class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-500/20 file:text-blue-400 hover:file:bg-blue-500/30">
                    <p class="text-xs text-gray-500 mt-1">Max 20MB. PDF, images, documents, spreadsheets.</p>
                    @error('file') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Category *</label>
                    <select name="category" required class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white">
                        <option value="general" class="bg-gray-900">General</option>
                        <option value="project" class="bg-gray-900">Project</option>
                        <option value="ticket" class="bg-gray-900">Ticket</option>
                        <option value="invoice" class="bg-gray-900">Invoice</option>
                        <option value="contract" class="bg-gray-900">Contract</option>
                        <option value="other" class="bg-gray-900">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white placeholder-gray-500" placeholder="Optional description..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.toggle('hidden')" class="px-4 py-2 rounded-xl text-sm text-gray-400 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors text-sm font-semibold">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
