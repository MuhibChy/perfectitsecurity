{{-- Role badge: always text + color (never color-only). --}}
@props(['role'])
@php
    $label = \App\Support\RoleRegistry::displayName($role);
    $color = match ($role) {
        'super_admin', 'admin' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border-red-500/20',
        'finance_manager' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-500/20',
        'support_manager', 'support_agent' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 border-sky-500/20',
        'project_manager' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300 border-violet-500/20',
        'training_manager' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-500/20',
        'customer' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/40 dark:text-primary-300 border-primary-500/20',
        default => 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300 border-slate-500/20',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {$color}"]) }}>{{ $label }}</span>
