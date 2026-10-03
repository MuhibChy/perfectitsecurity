@extends('layouts.app')
@section('title', 'Customer Service History')
@section('page-title', 'Service History')

@section('content')
<x-page-header sys="OPS://TRACEABILITY" :title="$user->name . ' — Service History'" :subtitle="'Customer since ' . $user->created_at->format('d M Y') . ' · ' . ($user->company_name ?? $user->email)" :breadcrumbs="['Customers' => route('admin.users.index'), $user->name => null]">
    <x-role-badge :role="$user->role" />
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Services',$overview['services']],['Orders (£'.$overview['orders_total'].')',$overview['orders']],['Projects',$overview['projects'].' ('.$overview['projects_completed'].' done)'],['Tickets ('.$overview['tickets_open'].' open)',$overview['tickets']],['Invoices (£'.$overview['invoiced_total'].')',$overview['invoices']],['Paid (£)',$overview['paid_total']],['Outstanding (£)',$overview['outstanding']],['Documents',$overview['documents']]] as [$label,$val])
    <div class="term-panel"><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
    @endforeach
</div>

<h2 class="heading-sm mb-3">Chronological Timeline (from actual records)</h2>
<x-timeline :timeline="$timeline" />
@endsection
