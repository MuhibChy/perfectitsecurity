@extends('layouts.app')
@section('page-title', 'SBOM Inventory')
@section('content')

    <x-page-header title="SBOM Inventory" sys="SYSTEM://SECURITY" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Software Bill of Materials</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Read-only inventory parsed from <code>composer.lock</code> and <code>package.json</code>. Run <code>composer audit</code> / <code>npm audit</code> on the server for vulnerability status — results are point-in-time, not live.</p>
    <h3 class="font-bold mt-4 mb-2">PHP packages ({{ count($packages) }})</h3>
    <div class="overflow-x-auto term-table-wrap">
        <table class="min-w-full text-sm data-table term-table">
            <thead><tr><th>Package</th><th>Version</th><th>License</th><th>Dev</th><th>Source</th></tr></thead>
            <tbody>
            @foreach($packages as $p)
                <tr><td class="font-mono" data-label="Package">{{ $p['name'] }}</td><td class="font-mono" data-label="Version">{{ $p['version'] }}</td><td data-label="License">{{ $p['license'] }}</td><td data-label="Dev">{{ $p['dev'] ? 'yes' : 'no' }}</td><td class="text-xs text-gray-500 truncate max-w-xs" data-label="Source">{{ $p['source'] }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <h3 class="font-bold mt-6 mb-2">NPM packages ({{ count($npm) }})</h3>
    <div class="overflow-x-auto term-table-wrap">
        <table class="min-w-full text-sm data-table term-table">
            <thead><tr><th>Package</th><th>Range</th><th>Dev</th></tr></thead>
            <tbody>
            @foreach($npm as $p)
                <tr><td class="font-mono" data-label="Package">{{ $p['name'] }}</td><td class="font-mono" data-label="Range">{{ $p['version'] }}</td><td data-label="Dev">{{ $p['dev'] ? 'yes' : 'no' }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
