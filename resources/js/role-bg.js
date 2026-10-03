/**
 * role-bg.js — Authenticated unique-environment background (visual only).
 *
 * ONE lightweight 2D canvas renders NINE genuinely different enterprise rooms.
 * Each environment has its own composition: architecture, object arrangement,
 * perspective origin, horizon, lighting direction and depth structure — never
 * a mere recolor. Driven by body[data-auth-env] (resolved server-side by
 * App\Support\AuthenticatedBackgroundManager); body[data-auth-variant]
 * (a/b/c/d) shifts secondary composition for sibling pages in one family.
 * Legacy body[data-rolebg] keys are mapped for backwards compatibility.
 *
 * Layer order (bottom → top): .gsb galaxy → .auth-bg (this canvas) →
 * .auth-bg-vignette + .role-bg-overlay (readability gradients) →
 * .page-lights (left focus light) → .global-3d WebGL world →
 * .global-3d-fg shield/glove background → content → nav/modals.
 *
 * Performance: DPR clamped to 1.5, node counts scaled by viewport, reduced
 * geometry on small screens, single static frame under
 * prefers-reduced-motion / low-power devices, loop paused when tab hidden.
 */

const LEGACY_TO_ENV = {
    command: 'command',
    fintech: 'fintech',
    soc: 'support',
    datacenter: 'network',
    business: 'intelligence',
    secure: 'operations',
    login: 'operations',
};

const ENVS = {
    // 1) CUSTOMER — Global IT Operations Center: wide ops wall, world-map
    //    dots, distant rack silhouettes, symmetric deep perspective.
    operations:  { node: '#A7F3D0', link: 'rgba(167,243,208,', particle: '#BFDBFE', grid: 'rgba(37,99,235,', accent: 'rgba(52,211,153,', density: 0.55, speed: 0.6, gridOn: true, pulses: false, room: 'opsWall', horizon: 0.58, vanish: 0.5, floorAlpha: 0.10 },
    // 2) ADMIN — Enterprise Command Center: curved display arc, central
    //    command-table ellipse, layered wall depth.
    command:     { node: '#22C55E', link: 'rgba(34,197,94,', particle: '#60A5FA', grid: 'rgba(30,58,138,', accent: 'rgba(96,165,250,', density: 1.0, speed: 1.0, gridOn: true, pulses: true, room: 'commandArc', horizon: 0.62, vanish: 0.5, floorAlpha: 0.14 },
    // 3) IT SUPPORT — Network Operations Room: side rack colonnades with
    //    fiber lights, topology displays, ceiling light bars.
    network:     { node: '#4ADE80', link: 'rgba(74,222,128,', particle: '#93C5FD', grid: 'rgba(30,64,175,', accent: 'rgba(147,197,253,', density: 0.85, speed: 0.8, gridOn: true, pulses: true, room: 'rackColonnade', horizon: 0.55, vanish: 0.5, floorAlpha: 0.12 },
    // 4) CYBERSECURITY — SOC: monitoring-wall screen grid, topology arcs,
    //    restrained blue/white light (no hacker clichés).
    soc:         { node: '#7DD3FC', link: 'rgba(125,211,252,', particle: '#A7F3D0', grid: 'rgba(30,58,138,', accent: 'rgba(191,219,254,', density: 0.9, speed: 0.7, gridOn: true, pulses: true, room: 'socWall', horizon: 0.6, vanish: 0.5, floorAlpha: 0.12 },
    // 5) PROJECTS — Digital Engineering Lab: floating panels at varied
    //    depths, architecture lines, soft studio wash.
    engineering: { node: '#6EE7B7', link: 'rgba(110,231,183,', particle: '#93C5FD', grid: 'rgba(30,64,175,', accent: 'rgba(147,197,253,', density: 0.8, speed: 0.75, gridOn: false, pulses: true, room: 'floatingPanels', horizon: 0.52, vanish: 0.42, floorAlpha: 0.08 },
    // 6) FINANCE — Fintech Center: metallic columns, chart skyline (abstract
    //    bars + trend line), calm symmetric order.
    fintech:     { node: '#6EE7B7', link: 'rgba(110,231,183,', particle: '#BFDBFE', grid: 'rgba(23,37,84,', accent: 'rgba(191,219,254,', density: 0.6, speed: 0.55, gridOn: false, pulses: false, room: 'chartHall', horizon: 0.64, vanish: 0.5, floorAlpha: 0.10 },
    // 7) AI/KNOWLEDGE — Intelligence Center: neural hub with spokes and
    //    orbiting knowledge nodes, concentric cognition rings.
    intelligence:{ node: '#5EEAD4', link: 'rgba(94,234,212,', particle: '#BFDBFE', grid: 'rgba(30,58,138,', accent: 'rgba(167,243,208,', density: 0.75, speed: 0.65, gridOn: false, pulses: true, room: 'neuralHub', horizon: 0.5, vanish: 0.62, floorAlpha: 0.09 },
    // 8) SUPPORT/TICKETS — Support Operations: comm wall band, global arcs
    //    between ground stations, warm-cool balance.
    support:     { node: '#86EFAC', link: 'rgba(134,239,172,', particle: '#93C5FD', grid: 'rgba(30,58,138,', accent: 'rgba(147,197,253,', density: 0.7, speed: 0.7, gridOn: true, pulses: true, room: 'supportArcs', horizon: 0.66, vanish: 0.5, floorAlpha: 0.11 },
    // 9) SETTINGS/PROFILE — Private Suite: minimal vanishing corridor, two
    //    glass panels, calmest composition of all rooms.
    suite:       { node: '#BBF7D0', link: 'rgba(187,247,208,', particle: '#DBEAFE', grid: 'rgba(37,99,235,', accent: 'rgba(219,234,254,', density: 0.4, speed: 0.45, gridOn: false, pulses: false, room: 'quietCorridor', horizon: 0.5, vanish: 0.5, floorAlpha: 0.07 },
};

class RoleBackground {
    constructor(canvasId = 'role-bg-canvas') {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;

        const body = document.body;
        const envName = body.dataset.authEnv || LEGACY_TO_ENV[body.dataset.rolebg] || 'operations';
        this.envName = ENVS[envName] ? envName : 'operations';
        this.theme = ENVS[this.envName];
        this.variant = body.dataset.authVariant || 'a';

        this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.lowPower = (navigator.hardwareConcurrency || 8) <= 2;
        this.smallScreen = Math.min(window.innerWidth, window.innerHeight) < 500;

        this.ctx = this.canvas.getContext('2d');
        if (!this.ctx) { this.hide(); return; }

        this.nodes = [];
        this.pulses = [];
        this.orbits = [];
        this.animId = null;
        this.running = false;
        this.width = 0;
        this.height = 0;
        this.scrollY = 0;
        this.mouseX = 0;
        this.t0 = performance.now();

        this.resize();
        this.seed();
        window.addEventListener('resize', () => { this.resize(); this.seed(); }, { passive: true });
        window.addEventListener('scroll', () => { this.scrollY = window.scrollY || 0; }, { passive: true });
        window.addEventListener('mousemove', (e) => {
            this.mouseX = (e.clientX / Math.max(1, window.innerWidth) - 0.5) * 2;
        }, { passive: true });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) this.stop();
            else if (!this.reduced && !this.lowPower) this.start();
        });

        if (this.reduced || this.lowPower) {
            this.renderFrame(0, true);
        } else {
            this.start();
        }
    }

    hide() {
        if (this.canvas) this.canvas.style.display = 'none';
        document.body.classList.add('role-bg-static');
    }

    resize() {
        const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
        this.width = window.innerWidth;
        this.height = window.innerHeight;
        this.canvas.width = Math.floor(this.width * dpr);
        this.canvas.height = Math.floor(this.height * dpr);
        this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    seed() {
        const area = this.width * this.height;
        let count = Math.floor((area / 16000) * this.theme.density);
        if (this.smallScreen) count = Math.floor(count * 0.5);
        count = Math.max(20, Math.min(count, 100));
        this.nodes = [];
        for (let i = 0; i < count; i++) {
            this.nodes.push({
                x: Math.random() * this.width,
                y: Math.random() * this.height,
                r: Math.random() * 1.6 + 0.6,
                vx: (Math.random() - 0.5) * 0.22 * this.theme.speed,
                vy: (Math.random() - 0.5) * 0.22 * this.theme.speed,
                phase: Math.random() * Math.PI * 2,
            });
        }
        // Deterministic-ish room dressing (seeded by env + variant + size).
        this.roomSeed = this.hashStr(this.envName + ':' + this.variant + ':' + Math.round(this.width / 200));
        this.orbits = [];
        const orbitCount = this.smallScreen ? 4 : 9;
        for (let i = 0; i < orbitCount; i++) {
            this.orbits.push({
                f: this.rand(), p: this.rand() * Math.PI * 2,
                s: 0.4 + this.rand() * 0.6, r: 1 + this.rand() * 1.8,
            });
        }
    }

    hashStr(s) {
        let h = 2166136261;
        for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); }
        return h >>> 0;
    }

    rand() {
        this.roomSeed = (Math.imul(this.roomSeed, 1664525) + 1013904223) >>> 0;
        return this.roomSeed / 4294967296;
    }

    start() {
        if (this.running) return;
        this.running = true;
        const loop = () => {
            if (!this.running) return;
            this.renderFrame((performance.now() - this.t0) / 1000, false);
            this.animId = requestAnimationFrame(loop);
        };
        this.animId = requestAnimationFrame(loop);
    }

    stop() {
        this.running = false;
        if (this.animId) cancelAnimationFrame(this.animId);
        this.animId = null;
    }

    // ── shared helpers ────────────────────────────────────────────
    panel(x, y, w, h, alpha) {
        const { ctx, theme } = this;
        ctx.save();
        ctx.globalAlpha = alpha;
        ctx.strokeStyle = theme.grid + '0.35)';
        ctx.lineWidth = 1;
        ctx.strokeRect(x, y, w, h);
        ctx.globalAlpha = alpha * 0.5;
        ctx.fillStyle = theme.accent;
        ctx.fillRect(x, y, w, 2);
        ctx.restore();
    }

    glowDot(x, y, r, color, alpha) {
        const { ctx } = this;
        ctx.save();
        ctx.globalAlpha = alpha;
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(x, y, r, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    floorReflection(vanishX, horizonY, alpha) {
        const { ctx, width: w, height: h, theme } = this;
        const g = ctx.createLinearGradient(0, horizonY, 0, h);
        g.addColorStop(0, theme.accent + alpha + ')');
        g.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.save();
        ctx.globalAlpha = 1;
        ctx.fillStyle = g;
        ctx.fillRect(0, horizonY, w, h - horizonY);
        // reflection streak under vanishing point
        ctx.globalAlpha = alpha * 1.4;
        ctx.fillStyle = theme.accent;
        const streakW = Math.max(60, w * 0.06);
        ctx.fillRect(vanishX - streakW / 2, horizonY, streakW, (h - horizonY) * 0.35);
        ctx.restore();
    }

    perspectiveGrid(vanishX, horizonY, alpha) {
        const { ctx, width: w, height: h, theme } = this;
        ctx.save();
        ctx.strokeStyle = theme.grid + alpha + ')';
        ctx.lineWidth = 1;
        const stepX = Math.max(48, w / 22);
        for (let x = 0; x < w; x += stepX) {
            ctx.beginPath();
            ctx.moveTo(vanishX + (x - vanishX) * 0.15, horizonY);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        for (let i = 0; i < 7; i++) {
            const y = horizonY + ((h - horizonY) * Math.pow(i / 7, 1.8));
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(w, y);
            ctx.stroke();
        }
        ctx.restore();
    }

    // ── room painters (each a distinct composition) ───────────────
    paintRoom(t, staticFrame) {
        const { width: w, height: h, theme } = this;
        const horizonY = h * theme.horizon;
        const vanishX = w * theme.vanish + (staticFrame ? 0 : this.mouseX * w * 0.01);
        const drift = staticFrame ? 0 : Math.sin(t * 0.25) * h * 0.004;
        const scrollShift = Math.min(120, this.scrollY * 0.03);

        // Scroll: rooms breathe vertically; camera never jumps.
        this.ctx.save();
        this.ctx.translate(0, drift - scrollShift * 0.15);

        switch (theme.room) {
            case 'opsWall': this.roomOpsWall(vanishX, horizonY, t, staticFrame); break;
            case 'commandArc': this.roomCommandArc(vanishX, horizonY, t, staticFrame); break;
            case 'rackColonnade': this.roomRackColonnade(vanishX, horizonY, t, staticFrame); break;
            case 'socWall': this.roomSocWall(vanishX, horizonY, t, staticFrame); break;
            case 'floatingPanels': this.roomFloatingPanels(vanishX, horizonY, t, staticFrame); break;
            case 'chartHall': this.roomChartHall(vanishX, horizonY, t, staticFrame); break;
            case 'neuralHub': this.roomNeuralHub(vanishX, horizonY, t, staticFrame); break;
            case 'supportArcs': this.roomSupportArcs(vanishX, horizonY, t, staticFrame); break;
            case 'quietCorridor': this.roomQuietCorridor(vanishX, horizonY, t, staticFrame); break;
            default: break;
        }
        this.ctx.restore();

        if (theme.gridOn && !this.smallScreen) {
            this.perspectiveGrid(vanishX, horizonY, '0.10');
        }
        this.floorReflection(vanishX, horizonY, theme.floorAlpha);
    }

    // 1) Operations: grand centered ops wall + world dots + far racks.
    roomOpsWall(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        const wallW = w * 0.62, wallH = h * 0.30;
        const x0 = vx - wallW / 2, y0 = hy - wallH - h * 0.04;
        this.panel(x0, y0, wallW, wallH, 0.5);
        // world-map dots inside the wall
        ctx.save();
        ctx.globalAlpha = 0.5;
        ctx.fillStyle = this.theme.accent;
        const cols = 26, rows = 8;
        for (let i = 0; i < cols; i++) {
            for (let j = 0; j < rows; j++) {
                const px = x0 + (i + 0.5) * (wallW / cols) + Math.sin(i * 1.7 + j) * 3;
                const py = y0 + (j + 0.5) * (wallH / rows) + Math.cos(i + j * 2.1) * 2;
                if (this.rand2(i, j) > 0.45) { ctx.fillRect(px, py, 2, 2); }
            }
        }
        ctx.restore();
        // side glass info panels
        this.panel(x0 - w * 0.13, y0 + wallH * 0.2, w * 0.09, wallH * 0.6, 0.35);
        this.panel(x0 + wallW + w * 0.04, y0 + wallH * 0.2, w * 0.09, wallH * 0.6, 0.35);
        // distant rack silhouettes converging to vanish
        ctx.save();
        ctx.globalAlpha = 0.4;
        ctx.strokeStyle = this.theme.grid + '0.4)';
        for (let i = -3; i <= 3; i++) {
            if (i === 0) continue;
            const rx = vx + i * w * 0.09;
            const rw = w * 0.035, rh = h * 0.16;
            ctx.strokeRect(rx - rw / 2, hy - rh, rw, rh);
            for (let s = 0; s < 4; s++) {
                this.glowDot(rx - rw / 4 + (s % 2) * rw / 2, hy - rh + 8 + s * (rh - 12) / 3, 1.2, this.theme.node, 0.5);
            }
        }
        ctx.restore();
    }

    rand2(i, j) {
        let x = Math.sin(i * 127.1 + j * 311.7) * 43758.5453;
        return x - Math.floor(x);
    }

    // 2) Command: curved arc of displays + central table ellipse.
    roomCommandArc(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        ctx.save();
        // arc panels (7 segments following a curve, center largest)
        const n = 7;
        for (let i = 0; i < n; i++) {
            const k = i - (n - 1) / 2;
            const px = vx + k * w * 0.115;
            const pw = w * (0.10 - Math.abs(k) * 0.008);
            const ph = h * (0.20 - Math.abs(k) * 0.018);
            const py = hy - ph - h * 0.10 - Math.abs(k) * h * 0.012;
            this.panel(px - pw / 2, py, pw, ph, 0.55 - Math.abs(k) * 0.05);
            // status ticks on center panels
            if (Math.abs(k) <= 1 && !this.smallScreen) {
                ctx.save();
                ctx.globalAlpha = 0.5;
                ctx.fillStyle = this.theme.particle;
                for (let s = 0; s < 5; s++) ctx.fillRect(px - pw / 2 + 6, py + 8 + s * 7, pw - 12, 2);
                ctx.restore();
            }
        }
        // command table: concentric ellipses
        ctx.globalAlpha = 0.4;
        ctx.strokeStyle = this.theme.accent;
        for (let r = 0; r < 3; r++) {
            ctx.beginPath();
            ctx.ellipse(vx, hy + h * 0.10, w * 0.11 - r * w * 0.02, h * 0.035 - r * h * 0.007, 0, 0, Math.PI * 2);
            ctx.stroke();
        }
        this.glowDot(vx, hy + h * 0.10, 3, this.theme.node, 0.7);
        ctx.restore();
    }

    // 3) Network: left/right rack colonnades + ceiling bars + topology band.
    roomRackColonnade(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        ctx.save();
        // ceiling light bars
        ctx.globalAlpha = 0.35;
        ctx.fillStyle = this.theme.accent;
        for (let i = 0; i < 4; i++) {
            const bw = w * (0.16 - i * 0.025);
            ctx.fillRect(vx - bw / 2, h * 0.06 + i * h * 0.045, bw, 2);
        }
        // colonnades receding to vanish
        for (const side of [-1, 1]) {
            for (let i = 0; i < 4; i++) {
                const depth = i / 4;
                const rx = vx + side * (w * 0.10 + i * w * 0.085);
                const rw = w * (0.075 - depth * 0.03);
                const rh = h * (0.30 - depth * 0.10);
                const ry = hy - rh;
                ctx.globalAlpha = 0.55 - depth * 0.25;
                ctx.strokeStyle = this.theme.grid + '0.5)';
                ctx.strokeRect(Math.min(rx, rx + (side < 0 ? rw : -rw)) - (side < 0 ? 0 : rw), ry, rw, rh);
                // fiber LEDs
                for (let s = 0; s < 6; s++) {
                    const flick = sf ? 0.5 : 0.35 + 0.3 * Math.sin(t * 2 + s * 1.3 + i + (side > 0 ? 5 : 0));
                    this.glowDot(rx + (side < 0 ? rw / 2 : -rw / 2), ry + 10 + s * (rh - 16) / 5, 1.4, s % 3 ? this.theme.node : this.theme.particle, Math.max(0.15, flick));
                }
            }
        }
        // topology display band across the back
        this.panel(vx - w * 0.16, hy - h * 0.30, w * 0.32, h * 0.07, 0.4);
        ctx.restore();
    }

    // 4) SOC: wall of monitoring screens + topology arcs (calm, no red).
    roomSocWall(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        ctx.save();
        const cols = this.smallScreen ? 4 : 6, rows = 2;
        const gw = w * 0.60, gh = h * 0.24;
        const x0 = vx - gw / 2, y0 = hy - gh - h * 0.08;
        const cw = gw / cols, ch = gh / rows;
        for (let i = 0; i < cols; i++) {
            for (let j = 0; j < rows; j++) {
                const center = i === 2 && j === 0;
                this.panel(x0 + i * cw + 3, y0 + j * ch + 3, cw - 6, ch - 6, center ? 0.6 : 0.38);
                // event ticks
                if (!this.smallScreen) {
                    ctx.save();
                    ctx.globalAlpha = 0.45;
                    ctx.fillStyle = (i + j) % 3 ? this.theme.particle : this.theme.node;
                    for (let s = 0; s < 3; s++) {
                        const tw = (cw - 16) * (0.3 + this.rand2(i * 7 + s, j * 3 + s) * 0.7);
                        ctx.fillRect(x0 + i * cw + 8, y0 + j * ch + 8 + s * 6, tw, 2);
                    }
                    ctx.restore();
                }
            }
        }
        // topology arcs beneath the wall
        ctx.globalAlpha = 0.35;
        ctx.strokeStyle = this.theme.accent;
        for (let a = 0; a < 3; a++) {
            ctx.beginPath();
            ctx.arc(vx, hy + h * 0.02, w * (0.10 + a * 0.05), Math.PI, 0);
            ctx.stroke();
        }
        ctx.restore();
    }

    // 5) Engineering: floating panels at varied depths + arch lines.
    roomFloatingPanels(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        ctx.save();
        const panels = [
            { dx: -0.30, dy: -0.22, pw: 0.16, ph: 0.20, a: 0.5 },
            { dx: -0.10, dy: -0.30, pw: 0.20, ph: 0.13, a: 0.6 },
            { dx: 0.14, dy: -0.24, pw: 0.13, ph: 0.22, a: 0.45 },
            { dx: 0.32, dy: -0.14, pw: 0.11, ph: 0.15, a: 0.4 },
            { dx: 0.02, dy: -0.10, pw: 0.24, ph: 0.09, a: 0.35 },
        ];
        panels.forEach((p, i) => {
            const float = sf ? 0 : Math.sin(t * 0.6 + i * 1.4) * h * 0.006;
            const px = vx + p.dx * w, py = hy + p.dy * h + float;
            this.panel(px, py, p.pw * w, p.ph * h, p.a);
            // code-like lines
            if (!this.smallScreen) {
                ctx.save();
                ctx.globalAlpha = p.a * 0.8;
                ctx.fillStyle = this.theme.particle;
                for (let s = 0; s < 4; s++) {
                    ctx.fillRect(px + 8, py + 10 + s * 8, (p.pw * w - 16) * (0.4 + this.rand2(i, s) * 0.6), 2);
                }
                ctx.restore();
            }
        });
        // architecture connector lines
        ctx.globalAlpha = 0.25;
        ctx.strokeStyle = this.theme.accent;
        ctx.beginPath();
        ctx.moveTo(vx - w * 0.30, hy - h * 0.12);
        ctx.lineTo(vx + w * 0.02, hy - h * 0.10);
        ctx.lineTo(vx + w * 0.32, hy - h * 0.06);
        ctx.stroke();
        ctx.restore();
    }

    // 6) Finance: metallic columns + abstract chart skyline + trend line.
    roomChartHall(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        ctx.save();
        // columns
        for (const k of [-2, -1, 1, 2]) {
            const cx = vx + k * w * 0.17;
            ctx.globalAlpha = 0.35;
            const grd = ctx.createLinearGradient(cx - 12, 0, cx + 12, 0);
            grd.addColorStop(0, 'rgba(0,0,0,0)');
            grd.addColorStop(0.5, this.theme.accent);
            grd.addColorStop(1, 'rgba(0,0,0,0)');
            ctx.fillStyle = grd;
            ctx.fillRect(cx - 12, hy - h * 0.34, 24, h * 0.34);
        }
        // bar skyline
        const bars = 14, bw = (w * 0.5) / bars;
        const x0 = vx - w * 0.25, base = hy - h * 0.02;
        ctx.globalAlpha = 0.5;
        for (let i = 0; i < bars; i++) {
            const bh = h * (0.04 + this.rand2(i, 3) * 0.13);
            ctx.fillStyle = i % 4 === 3 ? this.theme.accent : this.theme.grid + '0.4)';
            ctx.fillRect(x0 + i * bw + 2, base - bh, bw - 4, bh);
        }
        // trend line
        ctx.globalAlpha = 0.6;
        ctx.strokeStyle = this.theme.node;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        for (let i = 0; i <= bars; i++) {
            const px = x0 + i * bw;
            const py = base - h * (0.05 + (i / bars) * 0.11 + Math.sin(i * 0.9) * 0.015);
            if (i === 0) ctx.moveTo(px, py); else ctx.lineTo(px, py);
        }
        ctx.stroke();
        ctx.lineWidth = 1;
        ctx.restore();
    }

    // 7) Intelligence: neural hub + spokes + orbiting nodes + rings.
    roomNeuralHub(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        const cx = vx + w * 0.12, cy = hy - h * 0.20;
        const R = Math.min(w, h) * 0.16;
        ctx.save();
        // cognition rings
        ctx.globalAlpha = 0.35;
        ctx.strokeStyle = this.theme.accent;
        for (let r = 1; r <= 3; r++) {
            ctx.beginPath();
            ctx.arc(cx, cy, (R * r) / 1.6, 0, Math.PI * 2);
            ctx.stroke();
        }
        // spokes + satellite nodes
        const sats = this.smallScreen ? 5 : 8;
        for (let i = 0; i < sats; i++) {
            const ang = (i / sats) * Math.PI * 2 + (sf ? 0 : t * 0.12);
            const sx = cx + Math.cos(ang) * R * 1.35;
            const sy = cy + Math.sin(ang) * R * 0.9;
            ctx.globalAlpha = 0.3;
            ctx.strokeStyle = this.theme.link + '0.5)';
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.lineTo(sx, sy);
            ctx.stroke();
            this.glowDot(sx, sy, 2.2, i % 2 ? this.theme.node : this.theme.particle, 0.7);
        }
        // core
        this.glowDot(cx, cy, 5, this.theme.node, 0.8);
        this.glowDot(cx, cy, 10, this.theme.accent, 0.3);
        // knowledge panel to the left
        this.panel(cx - w * 0.34, cy - h * 0.09, w * 0.15, h * 0.18, 0.45);
        ctx.restore();
    }

    // 8) Support: comm wall band + global arcs between ground stations.
    roomSupportArcs(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        ctx.save();
        // comm wall band
        this.panel(vx - w * 0.34, hy - h * 0.34, w * 0.68, h * 0.075, 0.5);
        if (!this.smallScreen) {
            ctx.save();
            ctx.globalAlpha = 0.5;
            ctx.fillStyle = this.theme.particle;
            for (let i = 0; i < 12; i++) ctx.fillRect(vx - w * 0.32 + i * w * 0.055, hy - h * 0.315, w * 0.03, 2);
            ctx.restore();
        }
        // ground stations + arcs
        const stations = 5;
        const pts = [];
        for (let i = 0; i < stations; i++) {
            const px = vx + (i - (stations - 1) / 2) * w * 0.14;
            const py = hy + h * 0.06;
            pts.push([px, py]);
            this.glowDot(px, py, 2.5, this.theme.node, 0.7);
        }
        ctx.globalAlpha = 0.4;
        ctx.strokeStyle = this.theme.accent;
        for (let i = 0; i < pts.length; i++) {
            for (let j = i + 1; j < pts.length; j++) {
                const mx = (pts[i][0] + pts[j][0]) / 2;
                const lift = Math.abs(pts[j][0] - pts[i][0]) * 0.35;
                ctx.beginPath();
                ctx.moveTo(pts[i][0], pts[i][1]);
                ctx.quadraticCurveTo(mx, pts[i][1] - lift, pts[j][0], pts[j][1]);
                ctx.stroke();
            }
        }
        // travelling pulse along center arc
        if (!sf) {
            const p = (t * 0.25) % 1;
            const x = pts[0][0] + (pts[pts.length - 1][0] - pts[0][0]) * p;
            const lift = (pts[pts.length - 1][0] - pts[0][0]) * 0.35;
            const y = pts[0][1] - Math.sin(p * Math.PI) * lift;
            this.glowDot(x, y, 2.5, this.theme.particle, 0.9);
        }
        ctx.restore();
    }

    // 9) Suite: minimal corridor, two glass panels, single glow.
    roomQuietCorridor(vx, hy, t, sf) {
        const { ctx, width: w, height: h } = this;
        ctx.save();
        // corridor lines to vanish
        ctx.globalAlpha = 0.3;
        ctx.strokeStyle = this.theme.grid + '0.5)';
        for (const k of [-0.35, -0.18, 0.18, 0.35]) {
            ctx.beginPath();
            ctx.moveTo(vx + k * w, h);
            ctx.lineTo(vx, hy - h * 0.05);
            ctx.stroke();
        }
        ctx.strokeRect(vx - w * 0.20, hy - h * 0.16, w * 0.40, h * 0.11);
        // two glass panels
        this.panel(vx - w * 0.26, hy - h * 0.10, w * 0.10, h * 0.22, 0.35);
        this.panel(vx + w * 0.16, hy - h * 0.10, w * 0.10, h * 0.22, 0.35);
        // single calm glow at vanish
        this.glowDot(vx, hy - h * 0.05, 3.5, this.theme.accent, 0.5);
        ctx.restore();
    }

    // ── main frame ────────────────────────────────────────────────
    renderFrame(t, staticFrame) {
        const { ctx, width, height, theme } = this;
        ctx.clearRect(0, 0, width, height);

        this.paintRoom(t, staticFrame);

        // Ambient node field (per-env density/tint).
        const maxDist = 130;
        ctx.save();
        for (let i = 0; i < this.nodes.length; i++) {
            const a = this.nodes[i];
            for (let j = i + 1; j < this.nodes.length; j++) {
                const b = this.nodes[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const d2 = dx * dx + dy * dy;
                if (d2 < maxDist * maxDist) {
                    const alpha = (1 - Math.sqrt(d2) / maxDist) * 0.20;
                    ctx.strokeStyle = theme.link + alpha.toFixed(3) + ')';
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }
        }
        ctx.restore();

        for (const n of this.nodes) {
            if (!staticFrame) {
                n.x += n.vx;
                n.y += n.vy;
                n.phase += 0.02;
                if (n.x < -10) n.x = width + 10;
                if (n.x > width + 10) n.x = -10;
                if (n.y < -10) n.y = height + 10;
                if (n.y > height + 10) n.y = -10;
            }
            const tw = staticFrame ? 0.7 : 0.55 + 0.35 * Math.sin(n.phase);
            ctx.globalAlpha = Math.max(0.15, tw);
            ctx.fillStyle = theme.node;
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;

        // Data pulses (only rooms that opt in).
        if (theme.pulses && !staticFrame && !this.smallScreen) {
            if (Math.random() < 0.03 && this.pulses.length < 3 && this.nodes.length > 1) {
                const a = this.nodes[Math.floor(Math.random() * this.nodes.length)];
                let b = this.nodes[Math.floor(Math.random() * this.nodes.length)];
                if (b === a) b = this.nodes[(this.nodes.indexOf(a) + 7) % this.nodes.length];
                this.pulses.push({ ax: a.x, ay: a.y, bx: b.x, by: b.y, p: 0, sp: 0.02 + Math.random() * 0.02 });
            }
            for (let i = this.pulses.length - 1; i >= 0; i--) {
                const pu = this.pulses[i];
                pu.p += pu.sp;
                if (pu.p >= 1) { this.pulses.splice(i, 1); continue; }
                const x = pu.ax + (pu.bx - pu.ax) * pu.p;
                const y = pu.ay + (pu.by - pu.ay) * pu.p;
                ctx.globalAlpha = 0.8 * Math.sin(pu.p * Math.PI);
                ctx.fillStyle = theme.particle;
                ctx.beginPath();
                ctx.arc(x, y, 2, 0, Math.PI * 2);
                ctx.fill();
            }
            ctx.globalAlpha = 1;
        }

        // Floating dust motes (orbital drift, subtle).
        if (!staticFrame && !this.smallScreen) {
            ctx.save();
            this.orbits.forEach((o, i) => {
                const x = (o.f * width + Math.sin(t * 0.3 * o.s + o.p) * 20 + width) % width;
                const y = (height * 0.15 + o.f * height * 0.7 + Math.cos(t * 0.22 * o.s + o.p) * 14 + height) % height;
                ctx.globalAlpha = 0.25;
                ctx.fillStyle = theme.particle;
                ctx.beginPath();
                ctx.arc(x, y, o.r, 0, Math.PI * 2);
                ctx.fill();
            });
            ctx.restore();
            ctx.globalAlpha = 1;
        }
    }
}

export default RoleBackground;
