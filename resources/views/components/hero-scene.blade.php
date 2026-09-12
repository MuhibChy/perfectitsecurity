{{-- Global reusable 3D hero scene — the exact implementation premiered on
     the Home Page (Three.js starfield, nebula, planet, holo shield, network).
     Usage: place as the first child of a `relative overflow-hidden` hero
     section: <x-hero-scene />. One instance per page; app.js initializes it
     lazily and hero-3d.js self-guards against double initialization.
     Decorative only: pointer-events-none, aria-hidden, with static fallback. --}}
@props(['canvasId' => 'hero-canvas', 'class' => 'absolute inset-0 w-full h-full'])
<canvas id="{{ $canvasId }}" class="{{ $class }} pointer-events-none" aria-hidden="true" data-hero-scene></canvas>
<div class="hero-fallback-bg" style="display: none;" aria-hidden="true"></div>
