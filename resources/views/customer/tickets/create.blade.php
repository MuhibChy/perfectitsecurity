@extends('layouts.app')
@section('page-title', 'Create Ticket')
@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Create Support Ticket</h2>
    <div class="glass-card p-6">
        <form method="POST" action="{{ route('portal.tickets.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Subject *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="input-field" required placeholder="Brief description of your issue">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Category *</label>
                    <select name="category_id" class="input-field" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Priority *</label>
                    <select name="priority" class="input-field" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description *</label>
                <textarea name="description" rows="6" class="input-field" required placeholder="Describe your issue in detail...">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Attachments</label>
                <input type="file" name="attachments[]" multiple class="input-field" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                <p class="text-xs text-gray-500 mt-1">Max 10MB per file. JPG, PNG, PDF, DOC allowed.</p>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('portal.tickets.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection
