@extends('layouts.app')
@section('page-title', 'Backup details')

@section('content')
<div class="space-y-6">
    <x-page-header title="Backup details" sys="SYSTEM://BACKUPS" />
    <div>
        <a href="{{ route('admin.backups.index') }}" class="text-xs text-primary-600 hover:underline">&larr; All backups</a>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ $backup->backup_id }}</h2>
        <p class="text-sm text-gray-500">Type {{ $backup->type }} · scope {{ $backup->scope }} · origin {{ $backup->origin }} · storage {{ $backup->storage }} · {{ $backup->encrypted ? 'encrypted (' . $backup->encryption_cipher . ')' : 'NOT encrypted' }}</p>
    </div>

    @if ($errors->any())
        <div class="term-panel p-4 border-l-4 border-red-500 text-sm text-red-600">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))<div class="term-alert term-alert-ok"><span class="term-alert-tag">SYS.OK</span><span>{{ session('success') }}</span></div>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="term-panel p-4"><span class="text-xs uppercase text-gray-400">Status</span><div class="font-bold">{{ $backup->status }}</div></div>
        <div class="term-panel p-4"><span class="text-xs uppercase text-gray-400">Size</span><div class="font-bold">{{ number_format($backup->size_bytes / 1024, 1) }} KB</div></div>
        <div class="term-panel p-4"><span class="text-xs uppercase text-gray-400">Duration</span><div class="font-bold">{{ $backup->duration_ms ? number_format($backup->duration_ms / 1000, 1) . 's' : '—' }}</div></div>
        <div class="term-panel p-4"><span class="text-xs uppercase text-gray-400">DB</span><div class="font-bold text-xs">{{ $backup->db_version ?: '—' }}</div></div>
    </div>

    <div class="term-panel p-4">
        <h2 class="font-bold mb-2">Artifacts (internal paths only — never public URLs)</h2>
        <ul class="text-sm space-y-1">
            @foreach ($backup->files as $f)
                <li class="font-mono text-xs">{{ $f->kind }} · {{ number_format($f->size_bytes / 1024, 1) }} KB · sha256 {{ substr($f->checksum ?? '', 0, 16) }}… {{ $f->encrypted ? '· encrypted' : '' }}</li>
            @endforeach
        </ul>
        @if ($backup->error_message)
            <p class="mt-2 text-sm text-red-600">{{ $backup->error_message }}</p>
        @endif
    </div>

    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.backups.verify', $backup->id) }}">@csrf<button class="px-4 py-2 bg-gray-600 text-white text-sm">Verify</button></form>
        <form method="POST" action="{{ route('admin.backups.restore-test', $backup->id) }}">@csrf<button class="term-btn term-btn-sm term-btn-ghost" {{ $backup->isVerified() ? '' : 'disabled' }}>Restore test (isolated)</button></form>
        <a href="{{ route('admin.backups.download', $backup->id) }}" class="term-btn term-btn-sm {{ $backup->isVerified() ? '' : 'opacity-50 pointer-events-none' }}">Download</a>
        <form method="POST" action="{{ route('admin.backups.destroy', $backup->id) }}" onsubmit="return confirm('Delete backup {{ $backup->backup_id }}? The audit record is retained.');">@csrf @method('DELETE')<button class="btn btn-destructive term-btn-sm">Delete</button></form>
    </div>

    <div class="term-panel p-4">
        <h2 class="font-bold mb-2 text-red-600">Restore (protected workflow)</h2>
        <p class="text-xs text-gray-500 mb-2">Verified backups only. A pre-restore safety backup is taken automatically. Type the backup ID to confirm.</p>
        <form method="POST" action="{{ route('admin.backups.restore', $backup->id) }}" class="flex flex-col sm:flex-row gap-2" onsubmit="return confirm('Restore {{ $backup->backup_id }}? This overwrites current data (after a safety backup).');">
            @csrf
            <select name="scope" class="border px-3 py-2 text-sm"><option value="full">Database + files</option><option value="db">Database only</option><option value="files">Files only</option></select>
            <input name="confirm_token" placeholder="Type {{ $backup->backup_id }}" class="border px-3 py-2 text-sm font-mono" required>
            <label class="term-field-label"><input type="checkbox" name="acknowledge" value="1" required> I understand</label>
            <button class="px-4 py-2 bg-red-700 text-white text-sm" {{ $backup->isVerified() ? '' : 'disabled' }}>Restore</button>
        </form>
    </div>
</div>
@endsection
