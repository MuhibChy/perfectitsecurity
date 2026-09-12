@extends('layouts.app')
@section('page-title', 'Case Studies')
@section('content')
<div class="glass-card p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Case Studies</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Publish customer success stories. Published items appear on the public site.</p>
    <form method="POST" action="{{ route('admin.content.case-studies.store') }}" class="grid sm:grid-cols-2 gap-3 mb-6 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50">
        @csrf
        <div class="sm:col-span-2"><label class="form-label">Title *</label><input name="title" required class="form-input w-full" value="{{ old('title') }}">@error('title')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">Client</label><input name="client_name" class="form-input w-full"></div>
        <div><label class="form-label">Industry</label><input name="industry" class="form-input w-full"></div>
        <div class="sm:col-span-2"><label class="form-label">Summary</label><input name="summary" maxlength="500" class="form-input w-full"></div>
        <div class="sm:col-span-2"><label class="form-label">Challenge / Solution / Results</label><textarea name="challenge" rows="2" placeholder="Challenge" class="form-input w-full mb-2"></textarea><textarea name="solution" rows="2" placeholder="Solution" class="form-input w-full mb-2"></textarea><textarea name="results" rows="2" placeholder="Results" class="form-input w-full"></textarea></div>
        <div><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" class="rounded"> Publish immediately</label></div>
        <div><button class="btn-primary btn-sm">Save Case Study</button></div>
    </form>
    <div class="overflow-x-auto"><table class="min-w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2 pr-4">Title</th><th class="py-2 pr-4">Client</th><th class="py-2 pr-4">Status</th><th class="py-2">Actions</th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr class="border-t border-gray-100 dark:border-gray-800">
                <td class="py-2 pr-4 font-semibold">{{ $item->title }}<div class="text-xs text-gray-500 font-normal">{{ $item->slug }}</div></td>
                <td class="py-2 pr-4">{{ $item->client_name ?? '—' }}</td>
                <td class="py-2 pr-4"><span class="badge">{{ $item->is_published ? 'Published' : 'Draft' }}</span></td>
                <td class="py-2"><form method="POST" action="{{ route('admin.content.case-studies.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this case study?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">Delete</button></form></td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-8 text-center text-gray-500">No case studies yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
