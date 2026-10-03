{{-- Terminal status badge: ● MONO LABEL chip. Props: status, label, size. --}}
@props([
    'status',
    'label' => null,
    'size' => 'sm'
])

@php
$normalized = strtolower(trim((string)$status));
$displayLabel = $label ?? ucfirst(str_replace(['_', '-'], ' ', $normalized));

// Status family: drives both the dot and the theme-aware skin class
// (x-status-*, see app.css LIGHT MODE layer for light skins).
$family = match(true) {
    in_array($normalized, ['active','paid','completed','approved','accepted','verified','resolved','enabled','online','success','successful']) => 'success',
    in_array($normalized, ['pending','waiting','on_hold','draft','new','scheduled','queued','medium','moderate','submitted','resubmission_required']) => 'warning',
    in_array($normalized, ['in_progress','processing','sent','planning','review','under_review','partial','in_review','open','low','minor','info']) => 'info',
    in_array($normalized, ['high','urgent','critical','overdue','failed','blocked','error','revoked','rejected']) => 'danger',
    default => 'muted',
};

// Terminal dot color per status family.
$dot = match(true) {
    in_array($normalized, ['active','paid','completed','approved','accepted','verified','resolved','enabled','online','success','successful']) => 'background:#00E67A;box-shadow:0 0 8px rgba(0,230,122,0.8)',
    in_array($normalized, ['pending','waiting','on_hold','draft','new','scheduled','queued']) => 'background:#FFB454;box-shadow:0 0 8px rgba(255,180,84,0.8)',
    in_array($normalized, ['in_progress','processing','sent','planning','review','partial','in_review','open']) => 'background:#4DA3FF;box-shadow:0 0 8px rgba(77,163,255,0.8)',
    in_array($normalized, ['high','urgent','critical','overdue','failed','blocked','error']) => 'background:#FF5C5C;box-shadow:0 0 8px rgba(255,92,92,0.8)',
    in_array($normalized, ['medium','moderate']) => 'background:#FFB454;box-shadow:0 0 8px rgba(255,180,84,0.8)',
    in_array($normalized, ['low','minor','info']) => 'background:#4DA3FF;box-shadow:0 0 8px rgba(77,163,255,0.8)',
    in_array($normalized, ['rejected','revoked','cancelled','suspended','inactive','closed','disabled','expired']) => 'background:#FF5C5C;box-shadow:none',
    in_array($normalized, ['resubmission_required','submitted']) => 'background:#FFB454;box-shadow:0 0 8px rgba(255,180,84,0.8)',
    default => 'background:#8ba595;box-shadow:none',
};

$text = match(true) {
    in_array($normalized, ['active','paid','completed','approved','accepted','verified','resolved','enabled','online','success','successful']) => 'color:#5CEFA8;border-color:rgba(0,230,122,0.35);background:rgba(0,230,122,0.07)',
    in_array($normalized, ['pending','waiting','on_hold','draft','new','scheduled','queued']) => 'color:#FFB454;border-color:rgba(255,180,84,0.35);background:rgba(255,180,84,0.07)',
    in_array($normalized, ['in_progress','processing','sent','planning','review','partial','in_review','open']) => 'color:#4DA3FF;border-color:rgba(77,163,255,0.35);background:rgba(77,163,255,0.07)',
    in_array($normalized, ['high','urgent','critical','overdue','failed','blocked','error']) => 'color:#FF8A8A;border-color:rgba(255,92,92,0.4);background:rgba(255,92,92,0.07)',
    in_array($normalized, ['medium','moderate']) => 'color:#FFB454;border-color:rgba(255,180,84,0.35);background:rgba(255,180,84,0.07)',
    in_array($normalized, ['low','minor','info']) => 'color:#4DA3FF;border-color:rgba(77,163,255,0.35);background:rgba(77,163,255,0.07)',
    default => 'color:#8ba595;border-color:rgba(139,165,149,0.3);background:rgba(139,165,149,0.06)',
};
@endphp

<span class="term-tag x-status-badge x-status-{{ $family }}" style="{{ $text }}">
    <span aria-hidden="true" style="width:0.45rem;height:0.45rem;border-radius:9999px;flex-shrink:0;{{ $dot }}"></span>
    <span>{{ strtoupper($displayLabel) }}</span>
</span>
