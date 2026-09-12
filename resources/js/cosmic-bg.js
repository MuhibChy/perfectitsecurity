/**
 * cosmic-bg.js — Global 2D Cosmic Ambient Canvas Engine
 * Lightweight, high-performance canvas starfield & nebula glow for all pages
 */

class CosmicBackground {
    constructor(canvasId = 'cosmic-canvas') {
        this.canvasId = canvasId;
        this.canvas = null;
        this.ctx = null;
        this.stars = [];
        this.shootingStars = [];
        this.animId = null;
        this.width = 0;
        this.height = 0;
        this.isReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        this.init();
    }

    init() {
        this.canvas = document.getElementById(this.canvasId);
        if (!this.canvas) {
            // Auto-create canvas if not present
            this.canvas = document.createElement('canvas');
            this.canvas.id = this.canvasId;
            this.canvas.className = 'fixed inset-0 pointer-events-none z-0';
            this.canvas.style.opacity = '0.7';
            document.body.prepend(this.canvas);
        }

        this.ctx = this.canvas.getContext('2d');
        if (!this.ctx) return;

        this.resize();
        this.createStars();

        window.addEventListener('resize', () => this.resize(), { passive: true });

        if (!this.isReduced) {
            this.animate();
        } else {
            this.renderStatic();
        }
    }

    resize() {
        this.width = window.innerWidth;
        this.height = window.innerHeight;
        this.canvas.width = this.width;
        this.canvas.height = this.height;
    }

    createStars() {
        const count = Math.min(Math.floor((this.width * this.height) / 4500), 400);
        this.stars = [];

        const colors = ['#ffffff', '#a5f3fc', '#93c5fd', '#c084fc', '#67e8f9'];

        for (let i = 0; i < count; i++) {
            this.stars.push({
                x: Math.random() * this.width,
                y: Math.random() * this.height,
                radius: Math.random() * 1.4 + 0.3,
                color: colors[Math.floor(Math.random() * colors.length)],
                alpha: Math.random() * 0.7 + 0.2,
                twinkleSpeed: Math.random() * 0.02 + 0.005,
                twinklePhase: Math.random() * Math.PI * 2,
                vx: (Math.random() - 0.5) * 0.08,
                vy: -Math.random() * 0.12 - 0.02,
            });
        }
    }

    animate() {
        this.animId = requestAnimationFrame(() => this.animate());

        this.ctx.clearRect(0, 0, this.width, this.height);

        // Draw and update stars
        for (let i = 0; i < this.stars.length; i++) {
            const s = this.stars[i];
            s.twinklePhase += s.twinkleSpeed;
            const currentAlpha = s.alpha * (0.6 + 0.4 * Math.sin(s.twinklePhase));

            s.x += s.vx;
            s.y += s.vy;

            if (s.y < 0) s.y = this.height;
            if (s.x < 0) s.x = this.width;
            if (s.x > this.width) s.x = 0;

            this.ctx.beginPath();
            this.ctx.arc(s.x, s.y, s.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = s.color;
            this.ctx.globalAlpha = currentAlpha;
            this.ctx.fill();
        }

        // Random subtle shooting star
        if (Math.random() < 0.006 && this.shootingStars.length < 2) {
            this.shootingStars.push({
                x: Math.random() * this.width,
                y: Math.random() * (this.height * 0.4),
                length: Math.random() * 80 + 40,
                speed: Math.random() * 10 + 6,
                angle: Math.PI / 4 + (Math.random() - 0.5) * 0.2,
                opacity: 1,
            });
        }

        // Draw shooting stars
        for (let i = this.shootingStars.length - 1; i >= 0; i--) {
            const ss = this.shootingStars[i];
            const endX = ss.x - Math.cos(ss.angle) * ss.length;
            const endY = ss.y - Math.sin(ss.angle) * ss.length;

            const grad = this.ctx.createLinearGradient(ss.x, ss.y, endX, endY);
            grad.addColorStop(0, 'rgba(165, 243, 252, ' + ss.opacity + ')');
            grad.addColorStop(1, 'rgba(165, 243, 252, 0)');

            this.ctx.beginPath();
            this.ctx.moveTo(ss.x, ss.y);
            this.ctx.lineTo(endX, endY);
            this.ctx.strokeStyle = grad;
            this.ctx.lineWidth = 1.5;
            this.ctx.globalAlpha = ss.opacity;
            this.ctx.stroke();

            ss.x += Math.cos(ss.angle) * ss.speed;
            ss.y += Math.sin(ss.angle) * ss.speed;
            ss.opacity -= 0.025;

            if (ss.opacity <= 0 || ss.x > this.width || ss.y > this.height) {
                this.shootingStars.splice(i, 1);
            }
        }

        this.ctx.globalAlpha = 1;
    }

    renderStatic() {
        this.ctx.clearRect(0, 0, this.width, this.height);
        for (let i = 0; i < this.stars.length; i++) {
            const s = this.stars[i];
            this.ctx.beginPath();
            this.ctx.arc(s.x, s.y, s.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = s.color;
            this.ctx.globalAlpha = s.alpha;
            this.ctx.fill();
        }
        this.ctx.globalAlpha = 1;
    }
}

export default CosmicBackground;
