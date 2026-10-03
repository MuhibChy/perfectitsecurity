@extends('layouts.app')
@section('page-title', 'Proposals Management')
@section('content')

    <x-page-header title="Proposals Management" sys="OPS://PROPOSALS" />
<div class="term-panel p-6">
    <div class="flex items-center justify-between mb-4">
        <div><h2 class="text-xl font-bold text-gray-900 dark:text-white">Proposals</h2><p class="text-sm text-gray-500">Scope, deliverables, versioned pricing. Status changes are revision-tracked.</p></div>
        <a href="{{ route('admin.proposals.create') }}" class="term-btn term-btn-sm">New Proposal</a>
    </div>
    <div class="overflow-x-auto term-table-wrap"><table class="data-table min-w-full text-sm term-table">
        <thead><tr class="text-left text-gray-500"><th class="py-2 pr-4">Proposal</th><th class="py-2 pr-4">Customer</th><th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Total</th><th class="py-2">Updated</th></tr></thead>
        <tbody>@forelse($proposals as $p)<tr class="border-t border-gray-100 dark:border-gray-800">
            <td class="py-2 pr-4" data-label="Proposal"><a href="{{ route('admin.proposals.show', $p) }}" class="font-semibold text-primary-600">{{ $p->proposal_number }} — {{ $p->title }}</a><div class="text-xs text-gray-500">v{{ $p->version }}</div></td>
            <td class="py-2 pr-4" data-label="Customer">{{ $p->customer?->name ?? '—' }}</td><td class="py-2 pr-4" data-label="Status"><span class="term-tag">{{ $p->status }}</span></td>
            <td class="py-2 pr-4" data-label="Total">{{ $p->currency }} {{ number_format($p->total, 2) }}</td><td class="py-2 text-gray-500" data-label="Updated">{{ $p->updated_at?->diffForHumans() }}</td>
        </tr>@empty<tr><td colspan="5" class="py-8 text-center text-gray-500">No proposals yet.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="mt-4">{{ $proposals->links() }}</div>
</div>
@endsection
