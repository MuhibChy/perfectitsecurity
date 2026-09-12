/**
 * hero-3d.js — Cinematic 3D Cosmic Experience & Holographic Tech Core
 * Powered by Three.js WebGL
 *
 * Features:
 * - 2,500 multi-spectral twinkling cosmic stars & distant star clusters
 * - Volumetric colored nebula cloud layers (cyan, violet, royal blue)
 * - 3D Celestial Planet with atmosphere horizon glow & orbital rings
 * - Holographic Cybersecurity Tech Shield with segmented data rings & energy core
 * - Interconnected cyber network nodes with glowing energy pulses
 * - Periodic cosmic shooting stars with light trails
 * - Smooth interactive mouse parallax & responsive camera scaling
 * - Accessible fallback when WebGL is unavailable or reduced-motion is requested
 */

import * as THREE from 'three';

class HeroScene {
    constructor(canvasId = 'hero-canvas') {
        this.canvasId = canvasId;
        this.canvas = null;
        this.renderer = null;
        this.scene = null;
        this.camera = null;
        this.frameId = null;
        this.clock = new THREE.Clock();

        this.mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };
        this.windowHalf = { x: window.innerWidth / 2, y: window.innerHeight / 2 };

        this.objects = {};
        this.shootingStars = [];
        this.isReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.isDestroyed = false;

        this.init();
    }

    init() {
        this.canvas = document.getElementById(this.canvasId);
        if (!this.canvas) return;

        // Singleton guard: never initialize two scenes on the same canvas
        // (prevents duplicate loops/listeners on re-navigation or HMR).
        if (this.canvas.dataset.heroInit === '1') return;
        this.canvas.dataset.heroInit = '1';

        const fallback = document.querySelector('.hero-fallback-bg');

        if (!this.isWebGLAvailable()) {
            if (fallback) fallback.style.display = 'block';
            return;
        }

        if (fallback) fallback.style.display = 'none';

        if (this.isReduced) {
            if (fallback) fallback.style.display = 'block';
            return;
        }

        this.setupRenderer();
        this.setupScene();
        this.setupCamera();
        this.setupLights();

        // Cosmic Environment
        this.createCosmicStarfield();
        this.createNebulaClouds();
        this.createCelestialPlanet();

        // 3D Holographic Tech Core & Shield
        this.createHoloShield();
        this.createCyberNetwork();
        this.createShootingStars();

        this.bindEvents();
        this.animate();
    }

    isWebGLAvailable() {
        try {
            const testCanvas = document.createElement('canvas');
            return !!(
                window.WebGLRenderingContext &&
                (testCanvas.getContext('webgl') || testCanvas.getContext('experimental-webgl'))
            );
        } catch (e) {
            return false;
        }
    }

    setupRenderer() {
        this.renderer = new THREE.WebGLRenderer({
            canvas: this.canvas,
            alpha: true,
            antialias: true,
            powerPreference: 'high-performance',
        });
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.setSize(this.canvas.clientWidth || window.innerWidth, this.canvas.clientHeight || window.innerHeight);
        this.renderer.setClearColor(0x000000, 0);
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.3;
    }

    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.fog = new THREE.FogExp2(0x040714, 0.022);
    }

    setupCamera() {
        const width = this.canvas.clientWidth || window.innerWidth;
        const height = this.canvas.clientHeight || window.innerHeight;
        const aspect = width / height;

        this.camera = new THREE.PerspectiveCamera(50, aspect, 0.1, 1000);
        this.camera.position.set(0, 0, 9);
    }

    setupLights() {
        // Deep ambient space light
        const ambient = new THREE.AmbientLight(0x0d1a33, 1.2);
        this.scene.add(ambient);

        // Core cyan spot/point light
        const cyanKey = new THREE.PointLight(0x06b6d4, 6, 25);
        cyanKey.position.set(3.5, 2.5, 4);
        this.scene.add(cyanKey);
        this.objects.cyanKey = cyanKey;

        // Violet-magenta rim light
        const violetRim = new THREE.PointLight(0xa855f7, 5, 22);
        violetRim.position.set(-4, -2, 3);
        this.scene.add(violetRim);
        this.objects.violetRim = violetRim;

        // Deep blue atmospheric backlight
        const blueBack = new THREE.DirectionalLight(0x3b82f6, 2.5);
        blueBack.position.set(5, 5, -5);
        this.scene.add(blueBack);
    }

    // ─── 1. Cosmic Deep Space Starfield ───────────────────────────
    createCosmicStarfield() {
        const starCount = 2400;
        const positions = new Float32Array(starCount * 3);
        const colors = new Float32Array(starCount * 3);
        const scales = new Float32Array(starCount);

        const colorPalette = [
            new THREE.Color(0xffffff), // White
            new THREE.Color(0xa5f3fc), // Cyan
            new THREE.Color(0x93c5fd), // Soft Blue
            new THREE.Color(0xd8b4fe), // Lavender
            new THREE.Color(0x38bdf8), // Bright Azure
            new THREE.Color(0xfde047), // Distant warm star
        ];

        for (let i = 0; i < starCount; i++) {
            // Spherical distribution around the viewer
            const r = 30 + Math.random() * 80;
            const theta = Math.random() * Math.PI * 2;
            const phi = Math.acos((Math.random() * 2) - 1);

            positions[i * 3]     = r * Math.sin(phi) * Math.cos(theta);
            positions[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
            positions[i * 3 + 2] = r * Math.cos(phi);

            const chosenColor = colorPalette[Math.floor(Math.random() * colorPalette.length)];
            colors[i * 3]     = chosenColor.r;
            colors[i * 3 + 1] = chosenColor.g;
            colors[i * 3 + 2] = chosenColor.b;

            scales[i] = Math.random() * 1.5 + 0.5;
        }

        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

        // Procedural circular star texture via canvas
        const starTexture = this.createStarTexture();

        const material = new THREE.PointsMaterial({
            size: 0.8,
            map: starTexture,
            vertexColors: true,
            transparent: true,
            opacity: 0.9,
            blending: THREE.AdditiveBlending,
            depthWrite: false,
        });

        const starField = new THREE.Points(geometry, material);
        this.scene.add(starField);
        this.objects.starField = starField;
    }

    createStarTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 64;
        canvas.height = 64;
        const ctx = canvas.getContext('2d');

        const grad = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
        grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
        grad.addColorStop(0.2, 'rgba(165, 243, 252, 0.8)');
        grad.addColorStop(0.5, 'rgba(92, 124, 250, 0.3)');
        grad.addColorStop(1, 'rgba(0, 0, 0, 0)');

        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 64, 64);

        const texture = new THREE.CanvasTexture(canvas);
        texture.needsUpdate = true;
        return texture;
    }

    // ─── 2. Nebula Glow Clouds ─────────────────────────────────────
    createNebulaClouds() {
        const nebulaGroup = new THREE.Group();
        const cloudTexture = this.createNebulaTexture();

        const cloudConfigs = [
            { pos: [5, 2, -15], scale: 18, color: 0x06b6d4, opacity: 0.22 },
            { pos: [-4, -3, -18], scale: 22, color: 0x6366f1, opacity: 0.20 },
            { pos: [8, -4, -12], scale: 15, color: 0xa855f7, opacity: 0.25 },
            { pos: [0, 6, -20], scale: 25, color: 0x0284c7, opacity: 0.18 },
        ];

        cloudConfigs.forEach(cfg => {
            const mat = new THREE.SpriteMaterial({
                map: cloudTexture,
                color: cfg.color,
                transparent: true,
                opacity: cfg.opacity,
                blending: THREE.AdditiveBlending,
                depthWrite: false,
            });
            const sprite = new THREE.Sprite(mat);
            sprite.position.set(...cfg.pos);
            sprite.scale.set(cfg.scale, cfg.scale * 0.8, 1);
            nebulaGroup.add(sprite);
        });

        this.scene.add(nebulaGroup);
        this.objects.nebulaGroup = nebulaGroup;
    }

    createNebulaTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 128;
        canvas.height = 128;
        const ctx = canvas.getContext('2d');

        const grad = ctx.createRadialGradient(64, 64, 4, 64, 64, 64);
        grad.addColorStop(0, 'rgba(255, 255, 255, 0.9)');
        grad.addColorStop(0.3, 'rgba(200, 225, 255, 0.5)');
        grad.addColorStop(0.7, 'rgba(100, 150, 255, 0.15)');
        grad.addColorStop(1, 'rgba(0, 0, 0, 0)');

        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 128, 128);

        const texture = new THREE.CanvasTexture(canvas);
        texture.needsUpdate = true;
        return texture;
    }

    // ─── 3. 3D Celestial Planet with Horizon Atmosphere Glow ───────
    createCelestialPlanet() {
        const planetGroup = new THREE.Group();
        // Position on the right side of the hero (aligned with text on the left)
        planetGroup.position.set(3.2, 0.2, -1.0);

        // Planet core sphere
        const planetRadius = 1.9;
        const planetGeo = new THREE.SphereGeometry(planetRadius, 48, 48);

        // Procedural cyber-topography canvas texture
        const planetTexture = this.createPlanetSurfaceTexture();

        const planetMat = new THREE.MeshStandardMaterial({
            map: planetTexture,
            roughness: 0.7,
            metalness: 0.3,
            color: 0x111c3a,
            emissive: 0x052244,
            emissiveIntensity: 0.4,
        });

        const planetMesh = new THREE.Mesh(planetGeo, planetMat);
        planetGroup.add(planetMesh);
        this.objects.planetMesh = planetMesh;

        // Glowing Atmosphere Rim (Fresnel outer halo)
        const atmosphereGeo = new THREE.SphereGeometry(planetRadius * 1.08, 36, 36);
        const atmosphereMat = new THREE.MeshBasicMaterial({
            color: 0x06b6d4,
            transparent: true,
            opacity: 0.25,
            side: THREE.BackSide,
            blending: THREE.AdditiveBlending,
        });
        const atmosphereMesh = new THREE.Mesh(atmosphereGeo, atmosphereMat);
        planetGroup.add(atmosphereMesh);

        // Outer planetary rings (Saturn-like futuristic data ring)
        const ringGeo = new THREE.RingGeometry(planetRadius * 1.35, planetRadius * 2.2, 64);
        // Rotate ring to give cinematic tilt
        ringGeo.rotateX(Math.PI / 2.5);
        ringGeo.rotateZ(Math.PI / 7);

        const ringMat = new THREE.MeshBasicMaterial({
            color: 0x38bdf8,
            side: THREE.DoubleSide,
            transparent: true,
            opacity: 0.35,
            blending: THREE.AdditiveBlending,
            wireframe: false,
        });

        const planetRing = new THREE.Mesh(ringGeo, ringMat);
        planetGroup.add(planetRing);
        this.objects.planetRing = planetRing;

        // Thin orbital satellite track rings
        for (let i = 0; i < 2; i++) {
            const trackGeo = new THREE.TorusGeometry(planetRadius * (1.8 + i * 0.4), 0.01, 8, 80);
            const trackMat = new THREE.MeshBasicMaterial({
                color: i === 0 ? 0x06b6d4 : 0xa855f7,
                transparent: true,
                opacity: 0.4 - i * 0.15,
                blending: THREE.AdditiveBlending,
            });
            const track = new THREE.Mesh(trackGeo, trackMat);
            track.rotation.x = Math.PI / 3 + i * 0.3;
            track.rotation.y = i * 0.5;
            planetGroup.add(track);
            this.objects[`track${i}`] = track;
        }

        this.scene.add(planetGroup);
        this.objects.planetGroup = planetGroup;
    }

    createPlanetSurfaceTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 256;
        const ctx = canvas.getContext('2d');

        // Deep ocean/space ground
        const bgGrad = ctx.createLinearGradient(0, 0, 512, 256);
        bgGrad.addColorStop(0, '#040b1e');
        bgGrad.addColorStop(0.5, '#0a1d3d');
        bgGrad.addColorStop(1, '#081326');
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, 512, 256);

        // Tech grid continents & glowing cyber latitudes
        ctx.strokeStyle = 'rgba(6, 182, 212, 0.25)';
        ctx.lineWidth = 1;

        // Longitudes & latitudes
        for (let y = 20; y < 256; y += 24) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(512, y);
            ctx.stroke();
        }
        for (let x = 30; x < 512; x += 40) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, 256);
            ctx.stroke();
        }

        // Bioluminescent tech nodes on planet surface
        for (let i = 0; i < 45; i++) {
            const px = Math.random() * 512;
            const py = Math.random() * 256;
            const r = Math.random() * 3 + 1;
            ctx.fillStyle = Math.random() > 0.4 ? 'rgba(34, 211, 238, 0.8)' : 'rgba(168, 85, 247, 0.7)';
            ctx.beginPath();
            ctx.arc(px, py, r, 0, Math.PI * 2);
            ctx.fill();
        }

        const texture = new THREE.CanvasTexture(canvas);
        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.ClampToEdgeWrapping;
        return texture;
    }

    // ─── 4. Holographic Cybersecurity Tech Shield ─────────────────
    createHoloShield() {
        const shieldGroup = new THREE.Group();
        shieldGroup.position.set(3.2, 0.2, 0.8);

        // Outer Floating 3D Geometric Energy Shield
        const shieldGeo = new THREE.IcosahedronGeometry(1.2, 2);
        const shieldMat = new THREE.MeshStandardMaterial({
            color: 0x06b6d4,
            wireframe: true,
            transparent: true,
            opacity: 0.35,
            emissive: 0x06b6d4,
            emissiveIntensity: 0.5,
        });
        const shieldWire = new THREE.Mesh(shieldGeo, shieldMat);
        shieldGroup.add(shieldWire);
        this.objects.shieldWire = shieldWire;

        // Inner glowing translucent core
        const coreGeo = new THREE.OctahedronGeometry(0.7, 1);
        const coreMat = new THREE.MeshStandardMaterial({
            color: 0x1e1b4b,
            emissive: 0x4f46e5,
            emissiveIntensity: 0.8,
            roughness: 0.1,
            metalness: 0.9,
            transparent: true,
            opacity: 0.85,
        });
        const shieldCore = new THREE.Mesh(coreGeo, coreMat);
        shieldGroup.add(shieldCore);
        this.objects.shieldCore = shieldCore;

        // 3 Segmented Data Rings orbiting the core
        this.dataRings = [];
        const ringRadii = [1.4, 1.65, 1.9];
        const ringColors = [0x22d3ee, 0x5c7cfa, 0xa855f7];

        ringRadii.forEach((radius, idx) => {
            const ringGeo = new THREE.TorusGeometry(radius, 0.012, 6, 64);
            const ringMat = new THREE.MeshBasicMaterial({
                color: ringColors[idx],
                transparent: true,
                opacity: 0.6 - idx * 0.12,
                blending: THREE.AdditiveBlending,
            });
            const ring = new THREE.Mesh(ringGeo, ringMat);
            ring.rotation.x = (idx * 0.6) + Math.PI / 4;
            ring.rotation.y = (idx * 0.4);
            shieldGroup.add(ring);
            this.dataRings.push(ring);
        });

        // 3D Cybersecurity Crest / Shield Silhouette in front of core
        const crestShape = new THREE.Shape();
        // Draw crisp shield polygon shape
        crestShape.moveTo(0, 0.4);
        crestShape.lineTo(0.35, 0.25);
        crestShape.lineTo(0.35, -0.15);
        crestShape.lineTo(0, -0.45);
        crestShape.lineTo(-0.35, -0.15);
        crestShape.lineTo(-0.35, 0.25);
        crestShape.closePath();

        const extrudeSettings = {
            depth: 0.04,
            bevelEnabled: true,
            bevelSegments: 2,
            steps: 1,
            bevelSize: 0.02,
            bevelThickness: 0.02,
        };

        const crestGeo = new THREE.ExtrudeGeometry(crestShape, extrudeSettings);
        const crestMat = new THREE.MeshStandardMaterial({
            color: 0x06b6d4,
            emissive: 0x22d3ee,
            emissiveIntensity: 0.9,
            metalness: 0.95,
            roughness: 0.1,
            transparent: true,
            opacity: 0.95,
        });

        const crestMesh = new THREE.Mesh(crestGeo, crestMat);
        crestMesh.position.set(0, 0, 0.7);
        shieldGroup.add(crestMesh);
        this.objects.crestMesh = crestMesh;

        this.scene.add(shieldGroup);
        this.objects.shieldGroup = shieldGroup;
    }

    // ─── 5. Cyber Infrastructure Network ─────────────────────────
    createCyberNetwork() {
        const netGroup = new THREE.Group();
        const nodePositions = [
            [-3.8, 1.6, -1.0],
            [-2.4, -1.8, -0.5],
            [-4.2, -0.8, -1.5],
            [-1.5, 2.2, -0.8],
            [-0.8, -2.1, -0.2],
            [1.2, 2.5, -1.2],
            [0.5, -1.2, -0.4],
        ];

        const nodeGeo = new THREE.SphereGeometry(0.09, 14, 14);
        const lineMat = new THREE.LineBasicMaterial({
            color: 0x22d3ee,
            transparent: true,
            opacity: 0.35,
            blending: THREE.AdditiveBlending,
        });

        this.nodes = [];
        nodePositions.forEach((pos, i) => {
            const col = i % 2 === 0 ? 0x06b6d4 : 0x5c7cfa;
            const nodeMat = new THREE.MeshBasicMaterial({ color: col });
            const node = new THREE.Mesh(nodeGeo, nodeMat);
            node.position.set(...pos);

            // Halo glow
            const haloGeo = new THREE.SphereGeometry(0.22, 10, 10);
            const haloMat = new THREE.MeshBasicMaterial({
                color: col,
                transparent: true,
                opacity: 0.18,
                blending: THREE.AdditiveBlending,
            });
            node.add(new THREE.Mesh(haloGeo, haloMat));

            netGroup.add(node);
            this.nodes.push(node);
        });

        // Interconnecting lines
        const connections = [
            [0, 1], [1, 2], [0, 3], [3, 5], [1, 4], [4, 6], [0, 2], [3, 0]
        ];

        connections.forEach(([a, b]) => {
            if (this.nodes[a] && this.nodes[b]) {
                const points = [this.nodes[a].position, this.nodes[b].position];
                const lineGeo = new THREE.BufferGeometry().setFromPoints(points);
                const line = new THREE.Line(lineGeo, lineMat);
                netGroup.add(line);
            }
        });

        this.scene.add(netGroup);
        this.objects.netGroup = netGroup;
    }

    // ─── 6. Periodic Cosmic Shooting Stars ────────────────────────
    createShootingStars() {
        this.shootingStars = [];
        for (let i = 0; i < 3; i++) {
            const star = this.spawnShootingStar();
            this.shootingStars.push(star);
        }
    }

    spawnShootingStar() {
        const lineGeo = new THREE.BufferGeometry();
        const pts = [
            new THREE.Vector3(0, 0, 0),
            new THREE.Vector3(-1.8, -0.9, -0.4)
        ];
        lineGeo.setFromPoints(pts);

        const lineMat = new THREE.LineBasicMaterial({
            color: 0xa5f3fc,
            transparent: true,
            opacity: 0,
            blending: THREE.AdditiveBlending,
        });

        const mesh = new THREE.Line(lineGeo, lineMat);
        mesh.position.set(
            (Math.random() - 0.2) * 20,
            6 + Math.random() * 8,
            -10 - Math.random() * 15
        );
        mesh.visible = false;
        this.scene.add(mesh);

        return {
            mesh,
            active: false,
            speed: 0.3 + Math.random() * 0.2,
            timer: Math.random() * 4 + 1,
        };
    }

    bindEvents() {
        this.onMouseMove = (e) => {
            this.mouse.targetX = (e.clientX - this.windowHalf.x) / this.windowHalf.x;
            this.mouse.targetY = (e.clientY - this.windowHalf.y) / this.windowHalf.y;
        };

        this.onResize = () => {
            if (!this.canvas || !this.renderer || !this.camera) return;
            const w = this.canvas.clientWidth || window.innerWidth;
            const h = this.canvas.clientHeight || window.innerHeight;

            this.windowHalf.x = w / 2;
            this.windowHalf.y = h / 2;

            this.camera.aspect = w / h;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(w, h);

            // Responsive scale/position for mobile vs wide desktop
            if (w < 768) {
                if (this.objects.planetGroup) {
                    this.objects.planetGroup.position.set(0, -1.2, -2.5);
                    this.objects.planetGroup.scale.set(0.65, 0.65, 0.65);
                }
                if (this.objects.shieldGroup) {
                    this.objects.shieldGroup.position.set(0, -1.2, -1.0);
                    this.objects.shieldGroup.scale.set(0.7, 0.7, 0.7);
                }
                if (this.objects.netGroup) {
                    this.objects.netGroup.visible = false;
                }
            } else if (w < 1200) {
                if (this.objects.planetGroup) {
                    this.objects.planetGroup.position.set(2.0, 0, -1.2);
                    this.objects.planetGroup.scale.set(0.8, 0.8, 0.8);
                }
                if (this.objects.shieldGroup) {
                    this.objects.shieldGroup.position.set(2.0, 0, 0.5);
                    this.objects.shieldGroup.scale.set(0.85, 0.85, 0.85);
                }
                if (this.objects.netGroup) {
                    this.objects.netGroup.visible = true;
                }
            } else {
                if (this.objects.planetGroup) {
                    this.objects.planetGroup.position.set(3.2, 0.2, -1.0);
                    this.objects.planetGroup.scale.set(1.0, 1.0, 1.0);
                }
                if (this.objects.shieldGroup) {
                    this.objects.shieldGroup.position.set(3.2, 0.2, 0.8);
                    this.objects.shieldGroup.scale.set(1.0, 1.0, 1.0);
                }
                if (this.objects.netGroup) {
                    this.objects.netGroup.visible = true;
                }
            }
        };

        window.addEventListener('mousemove', this.onMouseMove, { passive: true });
        window.addEventListener('resize', this.onResize, { passive: true });
        this.onResize(); // Initial call

        // Pause expensive rendering when the tab is hidden or the canvas
        // scrolls out of view; resume automatically. Critical for pages
        // that stay open long (dashboards) and multi-section pages.
        this.isVisible = true;
        this.onVisibility = () => {
            this.isVisible = !document.hidden && this.canvasInViewport();
            if (this.isVisible && !this.isDestroyed && !this.frameId) this.animate();
        };
        document.addEventListener('visibilitychange', this.onVisibility);
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                this.isVisible = entries[0].isIntersecting && !document.hidden;
                if (this.isVisible && !this.isDestroyed && !this.frameId) this.animate();
            }, { threshold: 0 });
            this.observer.observe(this.canvas);
        }
    }

    canvasInViewport() {
        if (!this.canvas || !('IntersectionObserver' in window)) return true;
        const r = this.canvas.getBoundingClientRect();
        return r.bottom > 0 && r.top < window.innerHeight;
    }

    animate() {
        if (this.isDestroyed) return;
        // Skip frames while hidden; the visibility handlers resume the loop.
        if (this.isVisible === false) { this.frameId = null; return; }
        this.frameId = requestAnimationFrame(() => this.animate());

        const elapsed = this.clock.getElapsedTime();
        const delta = Math.min(this.clock.getDelta(), 0.1);

        // Smooth mouse damping
        this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.05;
        this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.05;

        // Camera gentle parallax
        this.camera.position.x += (this.mouse.x * 0.8 - this.camera.position.x) * 0.03;
        this.camera.position.y += (-this.mouse.y * 0.5 - this.camera.position.y) * 0.03;
        this.camera.lookAt(new THREE.Vector3(1.2, 0, 0));

        // 1. Slow cosmic star drift
        if (this.objects.starField) {
            this.objects.starField.rotation.y = elapsed * 0.012;
            this.objects.starField.rotation.x = elapsed * 0.005;
        }

        // 2. Slow nebula breathe
        if (this.objects.nebulaGroup) {
            this.objects.nebulaGroup.rotation.z = elapsed * 0.008;
        }

        // 3. Planet rotation & orbital ring dynamics
        if (this.objects.planetMesh) {
            this.objects.planetMesh.rotation.y = elapsed * 0.04;
        }
        if (this.objects.planetRing) {
            this.objects.planetRing.rotation.z = elapsed * 0.02;
        }
        if (this.objects.track0) {
            this.objects.track0.rotation.z = elapsed * 0.15;
        }
        if (this.objects.track1) {
            this.objects.track1.rotation.y = elapsed * -0.12;
        }

        // 4. Holographic Shield & Data Rings animation
        if (this.objects.shieldWire) {
            this.objects.shieldWire.rotation.x = elapsed * 0.15;
            this.objects.shieldWire.rotation.y = elapsed * 0.22;
        }
        if (this.objects.shieldCore) {
            this.objects.shieldCore.rotation.x = -elapsed * 0.18;
            this.objects.shieldCore.rotation.y = -elapsed * 0.28;
            const pulse = 0.7 + Math.sin(elapsed * 2.5) * 0.25;
            this.objects.shieldCore.material.emissiveIntensity = pulse;
        }
        if (this.objects.crestMesh) {
            this.objects.crestMesh.position.z = 0.7 + Math.sin(elapsed * 2.0) * 0.06;
            this.objects.crestMesh.rotation.y = Math.sin(elapsed * 1.2) * 0.12;
        }

        // Counter-rotating data rings
        if (this.dataRings) {
            this.dataRings.forEach((ring, idx) => {
                ring.rotation.z = elapsed * (0.2 + idx * 0.15) * (idx % 2 === 0 ? 1 : -1);
                ring.rotation.x += delta * 0.05;
            });
        }

        // 5. Network nodes floating pulse
        if (this.nodes) {
            this.nodes.forEach((node, idx) => {
                node.position.y += Math.sin(elapsed * 1.5 + idx * 1.3) * 0.0015;
            });
        }

        // 6. Shooting Stars logic
        this.shootingStars.forEach(star => {
            star.timer -= delta;
            if (star.timer <= 0 && !star.active) {
                star.active = true;
                star.mesh.visible = true;
                star.mesh.material.opacity = 1;
                star.mesh.position.set(
                    (Math.random() - 0.2) * 24,
                    8 + Math.random() * 5,
                    -12 - Math.random() * 10
                );
            }

            if (star.active) {
                star.mesh.position.x -= star.speed;
                star.mesh.position.y -= star.speed * 0.55;
                star.mesh.material.opacity -= delta * 0.8;

                if (star.mesh.material.opacity <= 0.02) {
                    star.active = false;
                    star.mesh.visible = false;
                    star.timer = Math.random() * 6 + 3;
                }
            }
        });

        // Dynamic light breathing
        if (this.objects.cyanKey) {
            this.objects.cyanKey.intensity = 5.5 + Math.sin(elapsed * 2.0) * 1.8;
        }
        if (this.objects.violetRim) {
            this.objects.violetRim.intensity = 4.5 + Math.cos(elapsed * 1.7) * 1.5;
        }

        this.renderer.render(this.scene, this.camera);
    }

    destroy() {
        this.isDestroyed = true;
        if (this.frameId) cancelAnimationFrame(this.frameId);
        this.frameId = null;
        if (this.observer) { this.observer.disconnect(); this.observer = null; }
        if (this.renderer) {
            this.renderer.dispose();
            this.renderer = null;
        }
        window.removeEventListener('mousemove', this.onMouseMove);
        window.removeEventListener('resize', this.onResize);
        document.removeEventListener('visibilitychange', this.onVisibility);
        if (this.canvas) delete this.canvas.dataset.heroInit;
    }
}

export default HeroScene;
