{{-- PERFECTITSECURITY terminal environment.
     ONE fixed CSS layer per page: near-black field, fine engineering grid,
     edge vignette, faint scanline texture and a slow drifting scan bar.
     Pure CSS/SVG — zero JS, zero WebGL, zero network cost.
     pointer-events-none + aria-hidden: decorative, never intercepts input.
     Respects prefers-reduced-motion (scan bar disabled in CSS). --}}
<div class="term-bg" aria-hidden="true">
    <div class="term-bg-scan"></div>
</div>
