{{-- hero-scene — RETIRED compatibility shim.
     The 3D experience is now ONE global fixed scene (<x-global-3d-scene /> in
     the layouts, resources/js/global-3d.js). This component intentionally
     renders nothing so the ~20 pages still referencing <x-hero-scene /> keep
     compiling without spawning hero-local absolute canvases that disappear on
     scroll or duplicate WebGL instances. Do not re-add a canvas here. --}}
@props(['canvasId' => 'hero-canvas', 'class' => ''])
