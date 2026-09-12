@extends('layouts.app')
@section('page-title', 'Backup & Disaster Recovery Center')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Backup &amp; Disaster Recovery</h1>
            <p class="text-sm text-gray-500 mt-1">Last successful: {{ $last && $last->isVerified() ? $last->created_at->diffForHumans() . ' (' . $last->backup_id . ')' : 'none yet' }}</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.backups.connection-test') }}">
                @csrf
                <button class="px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-xl transition-colors">Test storage</button>
            </form>
            <form method="POST" action="{{ route('admin.backups.store') }}" onsubmit="return confirm('Start a manual backup now?');">
                @csrf
                <input type="hidden" name="type" value="full">
                <input type="hidden" name="confirm" value="1">
                <button class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">Start manual backup</button>
            </form>
        </div>
    </div>

    @if ($errors->any())
        <div class="glass-card p-4 border-l-4 border-red-500 text-sm text-red-600">{{ $errors->first() }}</div>
    @endif
    @if (session('success'))
        <div class="glass-card p-4 border-l-4 border-emerald-500 text-sm text-emerald-600">{{ session('success') }}</div>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Verified backups</span>
            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['verified'] }} / {{ $stats['total'] }}</div>
            <span class="text-xs text-gray-500">{{ number_format($stats['bytes'] / 1048576, 2) }} MB stored</span>
        </div>
        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Failed</span>
            <div class="mt-1 text-2xl font-bold {{ $stats['failed'] ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">{{ $stats['failed'] }}</div>
            <span class="text-xs text-gray-500">{{ $lastFailed ? 'Last: ' . $lastFailed->backup_id : 'No failures' }}</span>
        </div>
        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Storage</span>
            <div class="mt-1 text-sm font-bold text-gray-900 dark:text-white">Local: {{ $storage['local']['ok'] ? 'OK' : 'DOWN' }}</div>
            <span class="text-xs text-gray-500">S3: {{ ! $storage['s3']['configured'] ? 'not configured' : ($storage['s3']['ok'] ? 'OK' : 'DOWN') }}</span>
        </div>
        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Encryption</span>
            <div class="mt-1 text-sm font-bold {{ $encryptionOk ? 'text-emerald-600' : 'text-red-600' }}">{{ $encryptionOk ? 'Configured' : 'MISSING KEY' }}</div>
            <span class="text-xs text-gray-500">AES-256-CBC · key never shown</span>
        </div>
    </div>

    <div class="glass-card p-4 text-xs text-gray-500">
        Schedule: full <code>{{ $schedules['full'] ?? '' }}</code> · db <code>{{ $schedules['db'] ?? '' }}</code> · retention D{{ $retention['keep_daily'] ?? 7 }}/W{{ $retention['keep_weekly'] ?? 4 }}/M{{ $retention['keep_monthly'] ?? 12 }} · pre-deploy held
    </div>

    <div class="glass-card p-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase text-gray-400">
                <th class="py-2">Backup</th><th>Type</th><th>Status</th><th>Size</th><th>Verified</th><th>Restore test</th><th>Created</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($backups as $b)
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    <td class="py-2 font-mono text-xs">{{ $b->backup_id }}</td>
                    <td>{{ $b->type }}</td>
                    <td>{{ $b->status }}</td>
                    <td>{{ number_format($b->size_bytes / 1024, 1) }} KB</td>
                    <td>{{ $b->verification_status }}</td>
                    <td>{{ $b->restore_test_status }}</td>
                    <td class="text-gray-500">{{ $b->created_at?->diffForHumans() }}</td>
                    <td><a href="{{ route('admin.backups.show', $b->id) }}" class="text-primary-600 hover:underline text-xs">Details</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-6 text-center text-gray-500">No backups yet. Start a manual backup above.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $backups->links() }}</div>
    </div>
</div>
@endsection
