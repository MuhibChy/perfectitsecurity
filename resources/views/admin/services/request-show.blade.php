@extends('layouts.app')
@section('page-title', 'Request ' . $serviceRequest->request_number)

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex justify-between items-start">
        <div>
            <a href="{{ route('admin.service-requests.index') }}" class="text-blue-600 hover:underline text-sm">← Back to Requests</a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $serviceRequest->request_number }}</h1>
            <p class="text-sm text-gray-500">Submitted {{ $serviceRequest->created_at->format('M d, Y \a\t g:ia') }}</p>
        </div>
        @php
            $priorityColors = ['low' => 'gray', 'medium' => 'blue', 'high' => 'amber', 'urgent' => 'red'];
            $statusColors = ['new' => 'blue', 'under_review' => 'amber', 'accepted' => 'green', 'rejected' => 'red', 'converted' => 'purple'];
        @endphp
        <span class="px-3 py-1 bg-{{ $priorityColors[$serviceRequest->priority] }}-100 dark:bg-{{ $priorityColors[$serviceRequest->priority] }}-900/30 text-{{ $priorityColors[$serviceRequest->priority] }}-700 dark:text-{{ $priorityColors[$serviceRequest->priority] }}-400 text-sm rounded-full font-medium">{{ ucfirst($serviceRequest->priority) }}</span>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Customer Info -->
        <div class="glass-card p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Customer Information</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-500">Name:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->name }}</span></div>
                <div><span class="text-gray-500">Email:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->email }}</span></div>
                @if($serviceRequest->phone)
                    <div><span class="text-gray-500">Phone:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->phone }}</span></div>
                @endif
                @if($serviceRequest->company)
                    <div><span class="text-gray-500">Company:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->company }}</span></div>
                @endif
                @if($serviceRequest->user)
                    <div><span class="text-gray-500">Account:</span> <a href="#" class="text-blue-600 hover:underline">{{ $serviceRequest->user->email }}</a></div>
                @endif
            </div>
        </div>

        <!-- Service Info -->
        <div class="glass-card p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Service Details</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-500">Service:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->service->name ?? 'General Request' }}</span></div>
                @if($serviceRequest->country)
                    <div><span class="text-gray-500">Market:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->country->name }} ({{ $serviceRequest->country->currency_code }})</span></div>
                @endif
                @if($serviceRequest->budget)
                    <div><span class="text-gray-500">Budget:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->currency_symbol ?? '$' }}{{ number_format($serviceRequest->budget, 0) }}</span></div>
                @endif
                @if($serviceRequest->quoted_price)
                    <div><span class="text-gray-500">Quoted:</span> <span class="text-green-600 font-medium">{{ $serviceRequest->currency_symbol ?? '$' }}{{ number_format($serviceRequest->quoted_price, 0) }}</span></div>
                @endif
                @if($serviceRequest->preferred_start_date)
                    <div><span class="text-gray-500">Preferred Start:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->preferred_start_date }}</span></div>
                @endif
                @if($serviceRequest->estimated_delivery)
                    <div><span class="text-gray-500">Est. Delivery:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->estimated_delivery }}</span></div>
                @endif
            </div>
        </div>

        <!-- Workflow Status -->
        <div class="glass-card p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Workflow Status</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-gray-500">Review Status:</span>
                    <span class="px-2 py-1 bg-{{ $statusColors[$serviceRequest->review_status] }}-100 dark:bg-{{ $statusColors[$serviceRequest->review_status] }}-900/30 text-{{ $statusColors[$serviceRequest->review_status] }}-700 dark:text-{{ $statusColors[$serviceRequest->review_status] }}-400 text-xs rounded-full">{{ str_replace('_', ' ', ucfirst($serviceRequest->review_status)) }}</span>
                </div>
                <div><span class="text-gray-500">Assigned To:</span> <span class="text-gray-900 dark:text-white">{{ $serviceRequest->assignedTo->name ?? 'Unassigned' }}</span></div>
                @if($serviceRequest->quotation)
                    <div><span class="text-gray-500">Quotation:</span> <a href="#" class="text-blue-600 hover:underline">{{ $serviceRequest->quotation->quotation_number }}</a></div>
                @endif
            </div>
        </div>
    </div>

    <!-- Requirements -->
    <div class="glass-card p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Requirements</h3>
        <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{!! nl2br(e($serviceRequest->requirements)) !!}</div>
        @if($serviceRequest->scope_details)
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/10 rounded-lg">
                <div class="text-xs font-medium text-blue-700 dark:text-blue-400 mb-1">Scope Details</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{!! nl2br(e($serviceRequest->scope_details)) !!}</div>
            </div>
        @endif
        @if($serviceRequest->exclusions)
            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/10 rounded-lg">
                <div class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Exclusions</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{!! nl2br(e($serviceRequest->exclusions)) !!}</div>
            </div>
        @endif
    </div>

    <!-- Update Form -->
    <div class="glass-card p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Update Request</h3>
        <form method="POST" action="{{ route('admin.service-requests.update', $serviceRequest->id) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Review Status</label>
                    <select name="review_status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        @foreach(['new', 'under_review', 'awaiting_info', 'scope_clarification', 'pricing_in_progress', 'pending_approval', 'sent_to_customer', 'accepted', 'rejected', 'expired', 'cancelled', 'converted'] as $s)
                            <option value="{{ $s }}" {{ $serviceRequest->review_status === $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                            <option value="{{ $p }}" {{ $serviceRequest->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Quoted Price</label>
                    <input type="number" name="quoted_price" step="0.01" min="0" value="{{ $serviceRequest->quoted_price }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Estimated Delivery</label>
                    <input type="text" name="estimated_delivery" value="{{ $serviceRequest->estimated_delivery }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Internal Notes</label>
                    <textarea name="internal_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ $serviceRequest->internal_notes }}</textarea>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Update Request</button>
            </div>
        </form>
    </div>

    <!-- Internal Notes -->
    @if($serviceRequest->internal_notes)
    <div class="glass-card p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">📝 Internal Notes</h3>
        <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed bg-yellow-50 dark:bg-yellow-900/10 p-3 rounded-lg">{!! nl2br(e($serviceRequest->internal_notes)) !!}</div>
    </div>
    @endif
</div>
@endsection
