{{-- Global Space Background — one reusable full-page universe.
     Loaded once from the application layouts (public, app, auth), so every
     page inherits the same continuous environment automatically.
     Layers (all fixed, z-0, pointer-events-none, aria-hidden):
       stars → galaxies/nebula → planet + moon → orbits → dust →
       ambient wash → LEFT spotlight (PerfectITSecurity lighting the universe).
     Pure CSS/SVG: covers any document height (fixed viewport layer),
     zero JS cost, static fallback when motion is reduced. --}}
<div class="gsb" aria-hidden="true">
    <div class="gsb-stars gsb-stars-a"></div>
    <div class="gsb-stars gsb-stars-b"></div>
    <div class="gsb-nebula gsb-nebula-a"></div>
    <div class="gsb-nebula gsb-nebula-b"></div>
    <div class="gsb-orbit gsb-orbit-a"></div>
    <div class="gsb-orbit gsb-orbit-b"></div>
    <div class="gsb-planet"></div>
    <div class="gsb-moon"></div>
    <div class="gsb-dust"></div>
    <div class="gsb-wash"></div>
    <div class="gsb-spotlight"></div>
</div>
