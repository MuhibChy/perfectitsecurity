{{-- Global 3D Scene — ONE persistent world, TWO controlled layers, ONE loop.
     Background canvas (Layer 1, behind content): stars, planet, network.
     Shield canvas (Layer 3, z-5, strictly behind ALL content): the shield
     ("gloves") — visually behind, pointer-transparent, never blocking.
     Decorative only: pointer-events-none, aria-hidden, static CSS fallback
     (.global-3d-fallback) when WebGL / motion / power require it. --}}
<div class="global-3d" aria-hidden="true">
    <div class="global-3d-fallback"></div>
    <canvas id="global-3d-canvas" class="global-3d-canvas" aria-hidden="true" data-global-3d></canvas>
</div>
<div class="global-3d-fg" aria-hidden="true">
    <canvas id="global-3d-canvas-fg" class="global-3d-canvas-fg" aria-hidden="true" data-global-3d-fg></canvas>
</div>
