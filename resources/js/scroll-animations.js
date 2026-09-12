/**
 * scroll-animations.js — Premium scroll-driven reveal animations
 * Uses IntersectionObserver for performance; no external dependencies required
 * (GSAP is available but not required for these)
 */

class ScrollAnimations {
    constructor() {
        this.observer = null;
        this.counterObserver = null;
        this.init();
    }

    init() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            // Immediately reveal all elements without animation
            document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => {
                el.classList.add('revealed');
            });
            return;
        }

        this.setupRevealObserver();
        this.setupCounterObserver();
        this.setup3DCardTilt();
        this.setupParallax();
        this.setupTypewriter();
    }

    setupRevealObserver() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Stagger children if parent has data-stagger
                    if (entry.target.dataset.stagger) {
                        const children = entry.target.children;
                        Array.from(children).forEach((child, i) => {
                            child.style.transitionDelay = `${i * 80}ms`;
                            child.classList.add('revealed');
                        });
                    }
                    entry.target.classList.add('revealed');
                    this.observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px',
        });

        document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => {
            this.observer.observe(el);
        });
    }

    setupCounterObserver() {
        this.counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    this.counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('[data-count]').forEach(el => {
            this.counterObserver.observe(el);
        });
    }

    animateCounter(el) {
        const target = parseFloat(el.dataset.count);
        const suffix = el.dataset.suffix || '';
        const prefix = el.dataset.prefix || '';
        const duration = parseInt(el.dataset.duration, 10) || 2000;
        const isDecimal = el.dataset.decimal === 'true';
        const start = performance.now();

        const update = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 4); // ease-out-quart
            const current = target * ease;

            el.textContent = prefix + (isDecimal ? current.toFixed(2) : Math.round(current).toLocaleString()) + suffix;

            if (progress < 1) requestAnimationFrame(update);
        };

        requestAnimationFrame(update);
    }

    setup3DCardTilt() {
        const cards = document.querySelectorAll('.card-3d');

        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = ((y - centerY) / centerY) * -6;
                const rotateY = ((x - centerX) / centerX) * 8;

                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(8px)`;

                // Update shine position
                const shine = card.querySelector('.card-3d-shine');
                if (shine) {
                    shine.style.setProperty('--mouse-x', `${(x / rect.width) * 100}%`);
                    shine.style.setProperty('--mouse-y', `${(y / rect.height) * 100}%`);
                }
            }, { passive: true });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            }, { passive: true });
        });
    }

    // Subtle parallax on mouse move for hero elements
    setupParallax() {
        const parallaxEls = document.querySelectorAll('[data-parallax]');
        if (!parallaxEls.length) return;

        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 2;
            const y = (e.clientY / window.innerHeight - 0.5) * 2;

            parallaxEls.forEach(el => {
                const speed = parseFloat(el.dataset.parallax) || 0.03;
                el.style.transform = `translate3d(${x * speed * 60}px, ${y * speed * 40}px, 0)`;
            });
        }, { passive: true });
    }

    // Simple typewriter effect
    setupTypewriter() {
        const typeEls = document.querySelectorAll('[data-typewriter]');
        typeEls.forEach(el => {
            const words = JSON.parse(el.dataset.typewriter || '[]');
            if (!words.length) return;
            this.typewriter(el, words, 0, 0, true);
        });
    }

    typewriter(el, words, wordIdx, charIdx, typing) {
        const word = words[wordIdx];
        const delay = typing ? 80 : 50;

        if (typing) {
            el.textContent = word.substring(0, charIdx + 1);
            if (charIdx < word.length - 1) {
                setTimeout(() => this.typewriter(el, words, wordIdx, charIdx + 1, true), delay);
            } else {
                setTimeout(() => this.typewriter(el, words, wordIdx, charIdx, false), 2000);
            }
        } else {
            el.textContent = word.substring(0, charIdx - 1);
            if (charIdx > 0) {
                setTimeout(() => this.typewriter(el, words, wordIdx, charIdx - 1, false), delay);
            } else {
                const next = (wordIdx + 1) % words.length;
                setTimeout(() => this.typewriter(el, words, next, 0, true), 400);
            }
        }
    }
}

export default ScrollAnimations;
