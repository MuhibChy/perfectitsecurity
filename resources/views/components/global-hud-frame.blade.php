{{-- Global HUD frame — ONE fixed command-center backdrop for the whole site.
     Mounted once per layout shell (never per page). Purely decorative:
     fixed viewport position, behind content, pointer-transparent, and the
     3D engine centers the globe inside .global-hud-box every frame. --}}
<div id="global-hud" class="global-hud" aria-hidden="true">
    <div class="global-hud-box">
        <span class="global-hud-corner tl"></span>
        <span class="global-hud-corner tr"></span>
        <span class="global-hud-corner bl"></span>
        <span class="global-hud-corner br"></span>
        <div class="global-hud-ring"></div>
        <div class="global-hud-ring global-hud-ring-2"></div>
        <div class="global-hud-core">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.2 12l1.9 1.9L15 10"/></svg>
        </div>
        <div class="global-hud-chip chip-a">
            <span class="term-status-dot"></span><span>24/7 MONITORING</span>
        </div>
        <div class="global-hud-chip chip-b">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M5 12.55a11 11 0 0114.08 0M8.53 16.11a6 6 0 016.95 0M12 20h.01"/></svg><span>NETWORK SECURE</span>
        </div>
        <div class="global-hud-chip chip-c">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>SHIELD ACTIVE</span>
        </div>
        <div class="global-hud-chip chip-d">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg><span>FAST RESPONSE</span>
        </div>
    </div>
</div>
