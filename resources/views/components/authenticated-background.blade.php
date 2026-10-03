{{-- Authenticated Background — Layer 3 of the global stack (visual only).
     Sits above the global space/galaxy (.gsb, z-0) and below the left-side
     focus light (.page-lights), the global 3D world (.global-3d), the
     shield/glove background layer (.global-3d-fg) and the glass UI content.
     ONE 2D canvas for all nine environments; the scene is selected via
     body[data-auth-env] by resources/js/role-bg.js. Static CSS fallback
     (.auth-bg-fallback) covers no-canvas / reduced-motion / low-power. --}}
<div class="auth-bg" aria-hidden="true">
    <div class="auth-bg-fallback role-bg-fallback"></div>
    <canvas id="role-bg-canvas" aria-hidden="true"></canvas>
    <div class="auth-bg-vignette"></div>
    <div class="role-bg-overlay" aria-hidden="true"></div>
</div>
