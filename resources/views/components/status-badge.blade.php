@props([
    'status',
    'label' => null,
    'size' => 'sm'
])

@php
$normalized = strtolower(trim((string)$status));
$displayLabel = $label ?? ucfirst(str_replace(['_', '-'], ' ', $normalized));

$statusStyles = [
    // Positive / Completed / Active / Paid
    'active' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-500/20 dot-emerald',
    'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-500/20 dot-emerald',
    'completed' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-500/20 dot-emerald',
    'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-500/20 dot-emerald',
    'accepted' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-500/20 dot-emerald',
    'verified' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-500/20 dot-emerald',
    'resolved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-500/20 dot-emerald',

    // Pending / In Progress / Waiting / Review
    'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-500/20 dot-amber',
    'in_progress' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-500/20 dot-blue',
    'planning' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400 border-cyan-500/20 dot-cyan',
    'review' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border-purple-500/20 dot-purple',
    'sent' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-500/20 dot-blue',
    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-500/20 dot-gray',
    'new' => 'bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-400 border-primary-500/20 dot-primary',

    // High / Urgent / Warning
    'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 border-orange-500/20 dot-orange',
    'urgent' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-500/20 dot-rose',
    'critical' => 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-400 border-red-500/30 dot-red',
    'overdue' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-500/20 dot-rose',

    // Danger / Cancelled / Rejected / Suspended / Inactive
    'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-500/20 dot-gray',
    'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-500/20 dot-rose',
    'suspended' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-500/20 dot-rose',
    'blocked' => 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-400 border-red-500/30 dot-red',
    'inactive' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-500/20 dot-gray',
];

$classes = $statusStyles[$normalized] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-500/20 dot-gray';

$dotColor = match(true) {
    str_contains($classes, 'dot-emerald') => 'bg-emerald-500',
    str_contains($classes, 'dot-amber') => 'bg-amber-500',
    str_contains($classes, 'dot-blue') => 'bg-blue-500',
    str_contains($classes, 'dot-cyan') => 'bg-cyan-500',
    str_contains($classes, 'dot-purple') => 'bg-purple-500',
    str_contains($classes, 'dot-orange') => 'bg-orange-500',
    str_contains($classes, 'dot-rose'), str_contains($classes, 'dot-red') => 'bg-rose-500',
    str_contains($classes, 'dot-primary') => 'bg-primary-500',
    default => 'bg-gray-400'
};
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-medium border text-xs {{ $classes }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} flex-shrink-0"></span>
    <span class="truncate">{{ $displayLabel }}</span>
</span>
