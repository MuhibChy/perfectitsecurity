/**
 * role-bg.js — Role-based ambient 2D canvas background for authenticated areas.
 *
 * Lightweight by design (no WebGL engine): network nodes + links, drifting
 * particles, a subtle perspective grid and periodic data pulses, tinted per
 * user-role theme. The canvas always sits behind a black overlay + glass UI.
 *
 * Layers (bottom → top): #role-bg-canvas (this) → .role-bg-overlay (CSS
 * gradient to black) → .role-bg-fallback (static CSS, visible only when the
 * canvas cannot run) → application UI.
 *
 * Fallbacks: if 2D context is unavailable, reduced-motion is preferred, or
 * the device is low-power, the canvas hides itself and the static CSS
 * fallback (.role-bg-fallback, themed via body[data-rolebg]) shows instead.
 * Animation pauses when the tab is hidden and scales down on small screens.
 */

const ROLE_THEMES = {
    // Green + Blue identity: emerald nodes with deep-blue grids and accents.
    // Admin — Security Command Center: emerald command + blue depth.
    command:  { node: '#22C55E', link: 'rgba(34,197,94,', particle: '#60A5FA', grid: 'rgba(30,58,138,', density: 1.0, speed: 1.0, gridOn: true, pulses: true },
    // Customer — Trusted Client Workspace: soft mint + light blue, welcoming.
    secure:   { node: '#A7F3D0', link: 'rgba(167,243,208,', particle: '#BFDBFE', grid: 'rgba(37,99,235,', density: 0.55, speed: 0.6, gridOn: false, pulses: false },
    // IT staff — Infrastructure: green nodes over blue grid.
    datacenter: { node: '#4ADE80', link: 'rgba(74,222,128,', particle: '#93C5FD', grid: 'rgba(30,64,175,', density: 0.85, speed: 0.8, gridOn: true, pulses: true },
    // Security ops — SOC: cyber-green highlights on navy grid.
    soc:      { node: '#00FF66', link: 'rgba(0,255,102,', particle: '#93C5FD', grid: 'rgba(30,58,138,', density: 0.9, speed: 0.9, gridOn: true, pulses: true },
    // Sales/agents — Business: emerald + blue links, lighter motion.
    business: { node: '#34D399', link: 'rgba(52,211,153,', particle: '#BFDBFE', grid: 'rgba(30,58,138,', density: 0.65, speed: 0.7, gridOn: false, pulses: true },
    // Finance — Enterprise Finance: deep green + navy blue restraint.
    fintech:  { node: '#6EE7B7', link: 'rgba(110,231,183,', particle: '#BFDBFE', grid: 'rgba(23,37,84,', density: 0.6, speed: 0.55, gridOn: false, pulses: false },
    // Login / generic — balanced green + blue.
    login:    { node: '#22C55E', link: 'rgba(34,197,94,', particle: '#60A5FA', grid: 'rgba(30,58,138,', density: 0.7, speed: 0.7, gridOn: true, pulses: false },
};

class RoleBackground {
    constructor(canvasId = 'role-bg-canvas') {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;

        const role = document.body.dataset.rolebg || 'login';
        this.theme = ROLE_THEMES[role] || ROLE_THEMES.login;

        this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.lowPower = (navigator.hardwareConcurrency || 8) <= 2;
        this.smallScreen = Math.min(window.innerWidth, window.innerHeight) < 500;

        this.ctx = this.canvas.getContext('2d');
        if (!this.ctx) { this.hide(); return; }

        this.nodes = [];
        this.pulses = [];
        this.animId = null;
        this.running = false;
        this.width = 0;
        this.height = 0;

        this.resize();
        this.seed();
        window.addEventListener('resize', () => { this.resize(); this.seed(); }, { passive: true });
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
        count = Math.max(24, Math.min(count, 110));
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
    }

    start() {
        if (this.running) return;
        this.running = true;
        const loop = (t) => {
            if (!this.running) return;
            this.renderFrame(t || 0, false);
            this.animId = requestAnimationFrame(loop);
        };
        this.animId = requestAnimationFrame(loop);
    }

    stop() {
        this.running = false;
        if (this.animId) cancelAnimationFrame(this.animId);
        this.animId = null;
    }

    renderFrame(t, staticFrame) {
        const { ctx, width, height, theme } = this;
        ctx.clearRect(0, 0, width, height);

        // Perspective grid (floor) — subtle, technical.
        if (theme.gridOn && !this.smallScreen) {
            ctx.save();
            ctx.strokeStyle = theme.grid + '0.10)';
            ctx.lineWidth = 1;
            const horizon = height * 0.62;
            const stepX = Math.max(48, width / 22);
            for (let x = (width / 2) % stepX; x < width; x += stepX) {
                ctx.beginPath();
                ctx.moveTo(width / 2 + (x - width / 2) * 0.2, horizon);
                ctx.lineTo(x, height);
                ctx.stroke();
            }
            for (let i = 0; i < 7; i++) {
                const y = horizon + ((height - horizon) * Math.pow(i / 7, 1.8));
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(width, y);
                ctx.stroke();
            }
            ctx.restore();
        }

        // Links between near nodes.
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
                    const alpha = (1 - Math.sqrt(d2) / maxDist) * 0.22;
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

        // Nodes (twinkle).
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

        // Data pulses travelling along random links.
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
    }
}

export default RoleBackground;
