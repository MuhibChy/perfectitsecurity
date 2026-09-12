import Alpine from 'alpinejs';
import ScrollAnimations from './scroll-animations.js';

window.Alpine = Alpine;

// 3D Cosmic Experience & Canvas Initialization
// Role-based 3D environment for authenticated areas (lightweight 2D canvas,
// lazy-loaded; static CSS fallback carries the scene if this fails).
if (document.getElementById('role-bg-canvas')) {
    import('./role-bg.js').then(({ default: RoleBackground }) => {
        window._roleBg = new RoleBackground('role-bg-canvas');
    }).catch((err) => {
        console.warn('Role background canvas fallback:', err);
        document.body.classList.add('role-bg-static');
    });
}

if (document.getElementById('hero-canvas') && !window._heroScene) {
    import('./hero-3d.js').then(({ default: HeroScene }) => {
        if (!window._heroScene) window._heroScene = new HeroScene('hero-canvas');
    }).catch((err) => {
        console.warn('Hero 3D WebGL initialization fallback:', err);
        const fallback = document.querySelector('.hero-fallback-bg');
        if (fallback) fallback.style.display = 'block';
    });
} else if (document.getElementById('cosmic-canvas') || document.body.dataset.cosmic !== 'false') {
    import('./cosmic-bg.js').then(({ default: CosmicBackground }) => {
        window._cosmicBg = new CosmicBackground('cosmic-canvas');
    }).catch((err) => {
        console.warn('Cosmic background canvas error:', err);
    });
}

// Initialise scroll animations on every page
document.addEventListener('DOMContentLoaded', () => {
    window._scrollAnim = new ScrollAnimations();
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
