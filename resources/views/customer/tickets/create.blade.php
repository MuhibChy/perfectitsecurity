@extends('layouts.app')
@section('page-title', 'Create Ticket')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-page-header title="Create Support Ticket" subtitle="Describe the issue and our engineers will triage it." sys="SUPPORT://TICKETS" num="02" />
    <div class="term-panel p-6">
        <form method="POST" action="{{ route('portal.tickets.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="term-field-label">Subject *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="term-input" required placeholder="Brief description of your issue">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Category *</label>
                    <select name="category_id" class="term-input" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="term-field-label">Priority *</label>
                    <select name="priority" class="term-input" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="term-field-label">Description *</label>
                <textarea name="description" rows="6" class="term-input" required placeholder="Describe your issue in detail...">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="term-field-label">Attachments</label>
                <input type="file" name="attachments[]" multiple class="term-input" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                <p class="term-hint">Max 10MB per file. JPG, PNG, PDF, DOC allowed.</p>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('portal.tickets.index') }}" class="term-btn term-btn-ghost">Cancel</a>
                <button type="submit" class="term-btn">Create Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection
