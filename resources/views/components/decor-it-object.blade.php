{{-- Decorative IT objects: monochrome line-art (currentColor), aria-neutral by
     parent zone. Premium enterprise feel — no gaming/cartoon styling. --}}
@props(['object' => 'server-rack'])
@switch($object)
@case('shield-lock')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M32 4l20 8v14c0 13-8.5 24-20 30C20.5 50 12 39 12 26V12l20-8z"/><rect x="25" y="28" width="14" height="11" rx="2"/><path d="M28 28v-3a4 4 0 018 0v3"/><circle cx="32" cy="33.5" r="1.4" fill="currentColor" stroke="none"/></svg>
@break
@case('database')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><ellipse cx="32" cy="14" rx="18" ry="7"/><path d="M14 14v36c0 3.9 8.1 7 18 7s18-3.1 18-7V14"/><path d="M14 32c0 3.9 8.1 7 18 7s18-3.1 18-7"/></svg>
@break
@case('network-nodes')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="32" cy="32" r="6"/><circle cx="12" cy="14" r="4"/><circle cx="52" cy="14" r="4"/><circle cx="12" cy="50" r="4"/><circle cx="52" cy="50" r="4"/><path d="M27 28l-11-10M37 28l11-10M27 36l-11 10M37 36l11 10"/></svg>
@break
@case('laptop-code')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="12" y="10" width="40" height="28" rx="2"/><path d="M26 24l-5 4 5 4M38 24l5 4-5 4M34 22l-4 12"/><path d="M6 46h52l-4 8H10l-4-8z"/></svg>
@break
@case('document-stack')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M16 8h20l10 10v34H16V8z"/><path d="M36 8v10h10"/><path d="M22 26h14M22 32h14M22 38h9"/></svg>
@break
@case('chart-analytics')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M8 8v44h48"/><path d="M16 40v-8M26 40V24M36 40V16M46 40V28"/><path d="M14 22l12-6 8 5 12-9" stroke-dasharray="3 2"/></svg>
@break
@case('cloud-wifi')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M18 40a10 10 0 01-1-20 14 14 0 0127-2 10 10 0 015 22H18z"/><path d="M26 48a8 8 0 0112 0M29 53a4 4 0 016 0"/><circle cx="32" cy="57" r="1" fill="currentColor" stroke="none"/></svg>
@break
@case('cpu-chip')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><rect x="20" y="20" width="24" height="24" rx="2"/><rect x="27" y="27" width="10" height="10"/><path d="M26 20v-8M32 20v-8M38 20v-8M26 52v-8M32 52v-8M38 52v-8M20 26h-8M20 32h-8M20 38h-8M52 26h-8M52 32h-8M52 38h-8"/></svg>
@break
@case('terminal-window')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="8" y="12" width="48" height="40" rx="3"/><path d="M8 22h48"/><circle cx="14" cy="17" r="1.4" fill="currentColor" stroke="none"/><path d="M16 32l6 4-6 4M26 40h10"/></svg>
@break
@case('invoice-receipt')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M16 6h32v46l-5-4-5 4-6-4-6 4-5-4-5 4V6z"/><path d="M23 20h18M23 27h18M23 34h11"/><circle cx="41" cy="36" r="5"/><path d="M41 34v2l1.5 1.5"/></svg>
@break
@case('headset-support')
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M10 38V30a22 22 0 0144 0v8"/><rect x="8" y="36" width="8" height="12" rx="3"/><rect x="48" y="36" width="8" height="12" rx="3"/><path d="M52 48a12 12 0 01-12 8h-6"/></svg>
@break
@case('server-rack')
@default
<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><rect x="16" y="6" width="32" height="52" rx="2"/><path d="M16 18h32M16 30h32M16 42h32"/><circle cx="22" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="22" cy="24" r="1.4" fill="currentColor" stroke="none"/><circle cx="22" cy="36" r="1.4" fill="currentColor" stroke="none"/><circle cx="22" cy="48" r="1.4" fill="currentColor" stroke="none"/><path d="M30 12h12M30 24h12M30 36h12M30 48h12"/></svg>
@break
@endswitch
