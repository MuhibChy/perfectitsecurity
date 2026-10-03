{{-- Decorative3DZone: optional ambient IT object for genuinely empty areas.
     Parent section must be position:relative. The zone is inert
     (pointer-events none, aria-hidden), hides on mobile, and must never
     be placed over tables, forms, invoices or other data. --}}
@props([
    'position' => 'bottom-right',
    'object' => 'terminal-window',
    'size' => 'md',
    'page' => null,
])
@php
    $show = true;
    if ($page) {
        $cfg = \App\Support\DecorativeZone::for($page);
        $show = ($cfg['max'] ?? 0) >= 1;
        $object = $object === 'terminal-window' ? $cfg['object'] : $object;
        $position = $cfg['zone'];
        $size = $cfg['size'];
    }
    if (!\App\Support\DecorativeZone::isKnownObject($object)) $object = 'terminal-window';
@endphp
@if($show)
<div class="decor-zone decor-zone--{{ $position }} decor-zone--{{ $size }}" aria-hidden="true" data-decor-zone="{{ $position }}">
    <div class="decor-float">
        <x-decor-it-object :object="$object" />
    </div>
</div>
@endif
