import Alpine from 'alpinejs';
import ScrollAnimations from './scroll-animations.js';

window.Alpine = Alpine;

// PERFECTITSECURITY 3D identity: the terminal CSS layer (.term-bg) stays as
// the base, and the single global WebGL world (global-3d.js — starfield,
// planet, network, shield/glove) renders strictly behind all content as the
// main visual identity. One instance, one loop, loaded as a SEPARATE async chunk
// so the main bundle stays lean; static fallback when WebGL, reduced-motion
// or low-power require it (handled inside Global3DScene).

// Initialise scroll animations on every page
document.addEventListener('DOMContentLoaded', () => {
    window._scrollAnim = new ScrollAnimations();
    try {
        if (document.querySelector('[data-global-3d]') && !window._global3D) {
            import('./global-3d.js')
                .then((m) => { window._global3D = new m.default(); })
                .catch(() => { /* 3D decorative only — never break the page */ });
        }
    } catch (e) { /* 3D decorative only — never break the page */ }
});

// Dark mode persistence
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: this.dark } }));
    }
});

// Sidebar state
Alpine.store('sidebar', {
    open: window.innerWidth >= 1024,
    mobileOpen: false,
    toggle() {
        this.open = !this.open;
    },
    toggleMobile() {
        this.mobileOpen = !this.mobileOpen;
    },
    closeMobile() {
        this.mobileOpen = false;
    }
});

// Notification dropdown
Alpine.data('notifications', () => ({
    open: false,
    notifications: [],
    unreadCount: 0,
    async init() {
        await this.fetchNotifications();
    },
    async fetchNotifications() {
        try {
            const res = await fetch('/api/notifications/unread');
            if (res.ok) {
                const data = await res.json();
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
            }
        } catch (e) {
            console.error('Failed to fetch notifications');
        }
    }
}));

// Toast notifications
Alpine.data('toast', () => ({
    toasts: [],
    success(message) {
        this.addToast(message, 'success');
    },
    error(message) {
        this.addToast(message, 'error');
    },
    info(message) {
        this.addToast(message, 'info');
    },
    addToast(message, type) {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }, 5000);
    }
}));

// Animated counter
Alpine.data('counter', (target = 0) => ({
    current: 0,
    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animate();
                    observer.unobserve(entry.target);
                }
            });
        });
        observer.observe(this.$el);
    },
    animate() {
        const duration = 2000;
        const steps = 60;
        const increment = target / steps;
        let step = 0;
        const timer = setInterval(() => {
            step++;
            this.current = Math.round(increment * step);
            if (step >= steps) {
                this.current = target;
                clearInterval(timer);
            }
        }, duration / steps);
    }
}));

// Sidebar collapse
Alpine.data('sidebar', () => ({
    collapsed: window.innerWidth < 1024,
    mobileOpen: false,
    init() {
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.mobileOpen = false;
            }
        });
    },
    toggle() {
        this.collapsed = !this.collapsed;
    },
    toggleMobile() {
        this.mobileOpen = !this.mobileOpen;
    }
}));



Alpine.start();
