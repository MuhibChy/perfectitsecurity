{{-- Shared terminal section header: NUMBER / LABEL:// / TITLE / DESC
     Usage: <x-section-head num="01" label="SERVICES://CORE" title="..." desc="..." /> --}}
@props(['num' => '01', 'label' => 'SECTION://', 'title' => '', 'desc' => ''])
<div class="max-w-3xl reveal">
    <div class="term-sec-head" aria-hidden="true">
        <span class="term-sec-num">{{ $num }}</span>
        <span class="term-sec-label">{{ $label }}</span>
        <span class="hidden sm:inline-block h-px flex-1 bg-gradient-to-r from-accent/30 to-transparent"></span>
    </div>
    @if($title)
    <h2 class="term-sec-title text-3xl sm:text-4xl lg:text-5xl text-balance">{{ $title }}</h2>
    @endif
    @if($desc)
    <p class="term-sec-desc mt-4 text-base sm:text-lg max-w-2xl">{{ $desc }}</p>
    @endif
</div>
