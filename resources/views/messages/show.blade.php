@extends('layouts.app')
@section('page-title', 'Message')
@section('content')
<div class="space-y-6">
<x-page-header :title="$message->subject ?? '(no subject)'" :subtitle="$message->sender->name . ' → ' . $message->recipient->name . ' · ' . $message->created_at->format('Y-m-d H:i')" sys="COMMS://MESSAGES" :breadcrumbs="['Messages' => (auth()->user()->isCustomer() ? route('portal.messages.index') : route('admin.messages.index')), 'Thread' => null]" />
<div class="term-panel p-6">
    @if($message->is_internal)<span class="term-tag mb-3 inline-flex">Internal</span>@endif
    <p class="mt-1 text-sm text-slate-600 dark:text-term-800 whitespace-pre-wrap">{{ $message->body }}</p>
    <form method="POST" action="{{ (auth()->user()->isCustomer() ? route('portal.messages.store') : route('admin.messages.store')) }}" class="mt-4 space-y-3">
        @csrf
        <input type="hidden" name="recipient_id" value="{{ $message->sender_id === auth()->id() ? $message->recipient_id : $message->sender_id }}" />
        <input type="hidden" name="parent_id" value="{{ $message->id }}" />
        <label class="term-field-label">Reply</label>
        <textarea name="body" required rows="3" class="term-input" maxlength="5000" placeholder="Reply…"></textarea>
        <button class="term-btn term-btn-sm">Reply</button>
    </form>
</div>
</div>
@endsection
