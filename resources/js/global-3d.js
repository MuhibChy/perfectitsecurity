/**
 * global-3d.js — ONE persistent WebGL environment for the entire website.
 *
 * Architecture: a single fixed-position canvas (Layer 1: above the space
 * background, below content) owned by the global layout. It never unmounts
 * on scroll, never duplicates per section, and interpolates smoothly between
 * scroll/section states instead of recreating anything.
 *
 * Scroll behavior: normalized page progress (0 → 1) drives a waypoint journey
 * (position / rotation / depth / light) with per-frame lerping. Sections marked
 * with [data-3d-state] bias the journey so hero → services → … → footer feels
 * like travelling through one continuous world. Content always scrolls
 * normally; the scene only observes.
 *
 * Restraint & safety: DPR clamped, counts reduced on mobile, single static
 * frame under prefers-reduced-motion / low-power, loop paused when the tab is
 * hidden, subtle mode on finance/payment/auth dashboards via body[data-3d].
 */

import * as THREE from 'three';

const STATE_BIAS = {
    hero: 0.0,
    stats: 0.12,
    services: 0.26,
    features: 0.4,
    process: 0.54,
    testimonials: 0.68,
    portfolio: 0.78,
    cta: 0.9,
    contact: 0.95,
    footer: 1.0,
};

class Global3DScene {
    constructor(canvasId = 'global-3d-canvas') {
        this.canvasId = canvasId;
        this.canvas = null;
        // Shield layer: second fixed canvas BEHIND all content (z-5) carrying
        // only the shield ("gloves"). Same instance, same loop, same scroll
        // state — one glove group, no duplicated animation.
        this.canvasFg = null;
        this.rendererFg = null;
        this.sceneFg = null;
        this.renderer = null;
        this.scene = null;
        this.camera = null;
        this.frameId = null;
        this.clock = null;
        this.objects = {};
        this.nodes = [];

        this.mouse = { x: 0, y: 0, tx: 0, ty: 0 };
        this.scroll = { progress: 0, target: 0, sectionBias: 0, sectionTarget: 0 };
        this.ticking = false;
        this.isDestroyed = false;
        this.isVisible = true;

        this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.lowPower = (navigator.hardwareConcurrency || 8) <= 2;
        this.smallScreen = Math.min(window.innerWidth, window.innerHeight) < 500;
        this.subtle = document.body ? document.body.getAttribute('data-3d') === 'subtle' : false;

        // Foreground glove ("shield") user control: moving (default journey),
        // fixed (viewport-anchored), positioning (drag preview). Geometry,
        // materials and idle motion below are never altered by these modes.
        this.gloveCtl = { mode: 'moving', x: null, y: null, positioning: false, dragging: false, tx: null, ty: null };
        this.readGlovePrefs();

        this.init();
    }

    init() {
        this.canvas = document.getElementById(this.canvasId);
        if (!this.canvas) return;
        if (this.canvas.dataset.init === '1' || window._global3D) return;
        this.canvas.dataset.init = '1';
        // Foreground canvas is optional: layouts include it, but if absent
        // the shield falls back into the background scene (never lost).
        this.canvasFg = document.getElementById('global-3d-canvas-fg');

        if (!this.webglAvailable()) { this.fallback(); return; }

        this.setupRenderer();
        this.setupScene();
        this.setupCamera();
        this.setupLights();
        this.createStarfield();
        this.createPlanet();
        this.createShield();
        this.createNetwork();
        this.createServerFarm();
        this.createCloudCluster();
        this.createHoloMonitors();
        this.createGridFloor();
        this.createLightShafts();
        this.bindEvents();
        this.updateScrollTarget(true);
        this.updateHudAnchor();

        this.t0 = performance.now();

        this.clock = new THREE.Clock();

        // Reduced motion / low power: one calm static frame, no loop.
        if (this.reduced || this.lowPower) {
            this.applyState(0, true);
            this.renderer.render(this.scene, this.camera);
            if (this.rendererFg) this.rendererFg.render(this.sceneFg, this.camera);
            return;
        }

        window._global3D = this;
        this.animate();
    }

    webglAvailable() {
        try {
            const c = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && (c.getContext('webgl') || c.getContext('experimental-webgl')));
        } catch (e) { return false; }
    }

    fallback() {
        document.body.classList.add('global-3d-static');
    }

    setupRenderer() {
        const dpr = this.smallScreen ? 1 : Math.min(window.devicePixelRatio || 1, 1.5);
        this.renderer = new THREE.WebGLRenderer({ canvas: this.canvas, alpha: true, antialias: !this.smallScreen, powerPreference: 'low-power' });
        this.renderer.setPixelRatio(dpr);
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.setClearColor(0x000000, 0);
        // Previous cinematic response (hero-scene parity): ACES filmic
        // tone mapping so PBR materials keep their rich highlights.
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.25;
        if (this.canvasFg) {
            this.rendererFg = new THREE.WebGLRenderer({ canvas: this.canvasFg, alpha: true, antialias: !this.smallScreen, powerPreference: 'low-power' });
            this.rendererFg.setPixelRatio(dpr);
            this.rendererFg.setSize(window.innerWidth, window.innerHeight);
            this.rendererFg.setClearColor(0x000000, 0);
            this.rendererFg.toneMapping = THREE.ACESFilmicToneMapping;
            this.rendererFg.toneMappingExposure = 1.25;
        }
    }

    setupScene() {
        this.scene = new THREE.Scene();
        // Deep midnight command-center atmosphere; dense enough for depth
        // fog to melt distant racks into layered silhouettes.
        this.scene.fog = new THREE.FogExp2(0x020610, 0.04);
        // Foreground scene: shield only, no fog (crisp, close to viewer).
        if (this.canvasFg) this.sceneFg = new THREE.Scene();
    }

    setupCamera() {
        this.camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 1000);
        this.camera.position.set(0, 0, 9);
    }

    setupLights() {
        // Permanent architectural light from the LEFT (brand metaphor).
        // Cool-white key + electric-blue rim + faint violet crown for the
        // midnight command-center grade; brand green lives on the shield,
        // LEDs and data paths (never floods the scene).
        this.scene.add(new THREE.AmbientLight(0x0d1a33, 1.2));
        const key = new THREE.PointLight(0xdbeafe, 5, 30);
        key.position.set(-6, 2.5, 5);
        this.scene.add(key);
        this.objects.key = key;
        const rim = new THREE.PointLight(0x2563eb, 3.2, 26);
        rim.position.set(5, -2, 3);
        this.scene.add(rim);
        this.objects.rim = rim;
        const crown = new THREE.PointLight(0x8b7cff, 1.6, 30);
        crown.position.set(1, 6, -6);
        this.scene.add(crown);
        this.objects.crown = crown;
        // Shield-layer lights: identical left-key treatment so the shield keeps
        // its approved appearance behind content.
        if (this.sceneFg) {
            this.sceneFg.add(new THREE.AmbientLight(0x0d1a33, 1.2));
            const fgKey = new THREE.PointLight(0xbfdbfe, 5, 30);
            fgKey.position.set(-6, 2.5, 5);
            this.sceneFg.add(fgKey);
            const fgRim = new THREE.PointLight(0x22c55e, 3, 24);
            fgRim.position.set(5, -2, 3);
            this.sceneFg.add(fgRim);
        }
    }

    makeGlowTexture(inner, outer) {
        const c = document.createElement('canvas');
        c.width = 64; c.height = 64;
        const ctx = c.getContext('2d');
        const g = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
        g.addColorStop(0, inner);
        g.addColorStop(0.45, outer);
        g.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, 64, 64);
        const t = new THREE.CanvasTexture(c);
        t.needsUpdate = true;
        return t;
    }

    createStarfield() {
        // Ambient data-dust: cool white / ice blue / faint violet motes
        // drifting in the command-center air. Brand green is deliberately
        // absent here — it is reserved for live infrastructure elements.
        const count = this.smallScreen ? 320 : 850;
        const pos = new Float32Array(count * 3);
        const col = new Float32Array(count * 3);
        const palette = [new THREE.Color(0xe8f1ff), new THREE.Color(0x9cc4ff), new THREE.Color(0x5f7fd6), new THREE.Color(0x8b7cff)];
        const weights = [0.45, 0.3, 0.18, 0.07];
        for (let i = 0; i < count; i++) {
            const r = 26 + Math.random() * 70;
            const th = Math.random() * Math.PI * 2;
            const ph = Math.acos(Math.random() * 2 - 1);
            pos[i * 3] = r * Math.sin(ph) * Math.cos(th);
            pos[i * 3 + 1] = (r * Math.sin(ph) * Math.sin(th)) * 0.7;
            pos[i * 3 + 2] = r * Math.cos(ph);
            let pick = Math.random(), cc = palette[0];
            for (let k = 0; k < weights.length; k++) { pick -= weights[k]; if (pick <= 0) { cc = palette[k]; break; } }
            const dim = 0.35 + Math.random() * 0.65;
            col[i * 3] = cc.r * dim; col[i * 3 + 1] = cc.g * dim; col[i * 3 + 2] = cc.b * dim;
        }
        const geo = new THREE.BufferGeometry();
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        geo.setAttribute('color', new THREE.BufferAttribute(col, 3));
        const mat = new THREE.PointsMaterial({
            size: 0.55, map: this.makeGlowTexture('rgba(232,241,255,1)', 'rgba(120,160,255,0.32)'),
            vertexColors: true, transparent: true, opacity: 0.7, fog: false,
            blending: THREE.AdditiveBlending, depthWrite: false,
        });
        const stars = new THREE.Points(geo, mat);
        this.scene.add(stars);
        this.objects.stars = stars;
    }

    // ─── Network core hub: the "cloud core" of the IT ecosystem ──────────
    // Replaces the old planet in the same group/slot so the scroll journey,
    // camera look-at and layout code keep working untouched. A dark metallic
    // core wrapped in ice-blue holographic shells and twin data orbits —
    // the visual anchor on the right side of the frame.
    createPlanet() {
        const group = new THREE.Group();
        const R = 1.35;
        const mesh = new THREE.Mesh(
            new THREE.IcosahedronGeometry(R, 2),
            new THREE.MeshStandardMaterial({
                color: 0x0a1428, emissive: 0x1d4ed8, emissiveIntensity: 0.55,
                roughness: 0.25, metalness: 0.9, flatShading: true,
            })
        );
        group.add(mesh);
        this.objects.planet = mesh;

        const wire = new THREE.Mesh(
            new THREE.IcosahedronGeometry(R * 1.18, 2),
            new THREE.MeshBasicMaterial({ color: 0x4da3ff, wireframe: true, transparent: true, opacity: 0.16, blending: THREE.AdditiveBlending })
        );
        group.add(wire);
        this.objects.coreWire = wire;

        const halo = new THREE.Mesh(
            new THREE.SphereGeometry(R * 1.28, 32, 32),
            new THREE.MeshBasicMaterial({ color: 0x3b82f6, transparent: true, opacity: 0.1, side: THREE.BackSide, blending: THREE.AdditiveBlending })
        );
        group.add(halo);

        const ringGeo = new THREE.RingGeometry(R * 1.5, R * 1.56, 96);
        ringGeo.rotateX(Math.PI / 2.4);
        ringGeo.rotateZ(Math.PI / 8);
        const ring = new THREE.Mesh(ringGeo, new THREE.MeshBasicMaterial({ color: 0x22d3ee, side: THREE.DoubleSide, transparent: true, opacity: 0.4, blending: THREE.AdditiveBlending }));
        group.add(ring);
        this.objects.ring = ring;

        const ring2Geo = new THREE.RingGeometry(R * 1.9, R * 1.93, 96);
        ring2Geo.rotateX(Math.PI / 1.8);
        ring2Geo.rotateZ(-Math.PI / 6);
        const ring2 = new THREE.Mesh(ring2Geo, new THREE.MeshBasicMaterial({ color: 0x8b7cff, side: THREE.DoubleSide, transparent: true, opacity: 0.22, blending: THREE.AdditiveBlending }));
        group.add(ring2);
        this.objects.ring2 = ring2;

        // Thin vertical data pillar rising from the core (uplink beam).
        const pillar = new THREE.Mesh(
            new THREE.CylinderGeometry(0.02, 0.05, 7, 8, 1, true),
            new THREE.MeshBasicMaterial({ color: 0x4da3ff, transparent: true, opacity: 0.28, blending: THREE.AdditiveBlending, depthWrite: false, side: THREE.DoubleSide })
        );
        pillar.position.y = 3.2;
        group.add(pillar);

        this.scene.add(group);
        this.objects.planetGroup = group;
        this.layoutPlanet();
    }

    layoutPlanet() {
        const g = this.objects.planetGroup;
        if (!g) return;
        // Base anchor only — the per-frame HUD anchor below keeps the core
        // hub centered inside the fixed global HUD box on every page.
        // Mobile framing is unchanged (a fixed-inch shift would clip it).
        if (window.innerWidth < 768) {
            g.position.set(0, 0.4, -2.2);
            g.scale.set(0.62, 0.62, 0.62);
        } else {
            g.position.set(3.4, 0.1, -1.2);
            g.scale.set(1, 1, 1);
        }
        this.basePlanetPos = g.position.clone();
    }

    // ─── Global HUD anchor: globe CENTER ↔ HUD box CENTER ───────────────
    // The HUD box is viewport-fixed; its center (normalized viewport coords)
    // is measured from the live DOM rect, then converted to a camera-relative
    // world target every frame — so the globe core stays glued to the exact
    // box center across scroll, routes and resizes. Group origin IS the core
    // center (rings are symmetric; the uplink beam reads as emanating light).
    // Size, rotation, lighting, materials and animations are untouched.
    updateHudAnchor() {
        try {
            const box = document.querySelector('#global-hud .global-hud-box');
            if (!box) { this.hudAnchor = null; return; }
            const r = box.getBoundingClientRect();
            if (r.width < 2 || r.height < 2) { this.hudAnchor = null; return; }
            this.hudAnchor = {
                x: (r.left + r.width / 2) / Math.max(1, window.innerWidth),
                y: (r.top + r.height / 2) / Math.max(1, window.innerHeight),
            };
        } catch (e) { this.hudAnchor = null; }
    }

    hudWorldTarget(nx, ny) {
        const g = this.objects.planetGroup;
        if (!g || !this.camera) return null;
        const depth = Math.max(4, this.camera.position.z - (this.basePlanetPos ? this.basePlanetPos.z : -1.2));
        const halfH = Math.tan(THREE.MathUtils.degToRad(this.camera.fov * 0.5)) * depth;
        const halfW = halfH * this.camera.aspect;
        const lx = (nx - 0.5) * 2 * halfW;
        const ly = (0.5 - ny) * 2 * halfH;
        const look = new THREE.Vector3(g.position.x * 0.35, 0, 0);
        const fwd = look.clone().sub(this.camera.position).normalize();
        const right = new THREE.Vector3().crossVectors(fwd, new THREE.Vector3(0, 1, 0)).normalize();
        const up = new THREE.Vector3().crossVectors(right, fwd).normalize();
        return this.camera.position.clone()
            .addScaledVector(fwd, depth)
            .addScaledVector(right, lx)
            .addScaledVector(up, ly);
    }

    // ─── Holographic shield crest + data rings (updated for solid green terminal design).
    // Updated to match current PerfectITSecurity terminal/cybersecurity design:
    // solid green accent (#00FF00) instead of cyan/blue/violet.
    // SHIELD LAYER: lives in its own scene behind all content (own canvas
    // layer at z-5); falls back to the planet group only if the shield
    // canvas is absent.
    createShield() {
        const fg = this.sceneFg;
        const parent = fg || this.objects.planetGroup;
        if (!parent) return;
        const shield = new THREE.Group();
        if (!fg) shield.position.set(0, 0, 1.8);

        const wire = new THREE.Mesh(
            new THREE.IcosahedronGeometry(1.2, 2),
            new THREE.MeshStandardMaterial({
                color: 0x00FF00, wireframe: true, transparent: true,
                opacity: 0.35, emissive: 0x00FF00, emissiveIntensity: 0.5,
            })
        );
        shield.add(wire);
        this.objects.shieldWire = wire;

        const core = new THREE.Mesh(
            new THREE.OctahedronGeometry(0.7, 1),
            new THREE.MeshStandardMaterial({
                color: 0x000000, emissive: 0x00FF00, emissiveIntensity: 0.8,
                roughness: 0.1, metalness: 0.9, transparent: true, opacity: 0.85,
            })
        );
        shield.add(core);
        this.objects.shieldCore = core;

        this.dataRings = [];
        const radii = [1.4, 1.65, 1.9];
        const colors = [0x00FF00, 0x00CC00, 0x009900];
        radii.forEach((radius, idx) => {
            const ring = new THREE.Mesh(
                new THREE.TorusGeometry(radius, 0.012, 6, 64),
                new THREE.MeshBasicMaterial({
                    color: colors[idx], transparent: true,
                    opacity: 0.6 - idx * 0.12, blending: THREE.AdditiveBlending,
                })
            );
            ring.rotation.x = idx * 0.6 + Math.PI / 4;
            ring.rotation.y = idx * 0.4;
            shield.add(ring);
            this.dataRings.push(ring);
        });

        const shape = new THREE.Shape();
        shape.moveTo(0, 0.4);
        shape.lineTo(0.35, 0.25);
        shape.lineTo(0.35, -0.15);
        shape.lineTo(0, -0.45);
        shape.lineTo(-0.35, -0.15);
        shape.lineTo(-0.35, 0.25);
        shape.closePath();
        const crest = new THREE.Mesh(
            new THREE.ExtrudeGeometry(shape, {
                depth: 0.04, bevelEnabled: true, bevelSegments: 2,
                steps: 1, bevelSize: 0.02, bevelThickness: 0.02,
            }),
            new THREE.MeshStandardMaterial({
                color: 0x00FF00, emissive: 0x00FF00, emissiveIntensity: 0.9,
                metalness: 0.95, roughness: 0.1, transparent: true, opacity: 0.95,
            })
        );
        crest.position.set(0, 0, 0.7);
        shield.add(crest);
        this.objects.crestMesh = crest;

        parent.add(shield);
        this.objects.shieldGroup = shield;
        this.layoutShield();
    }

    // Foreground composition: right-hand side, overlapping content edges,
    // fully inside safe camera framing on every viewport.
    layoutShield() {
        const s = this.objects.shieldGroup;
        if (!s || !this.sceneFg) return;
        if (window.innerWidth < 768) {
            s.position.set(0, 0.2, 0.5);
            s.scale.set(0.7, 0.7, 0.7);
        } else {
            s.position.set(3.2, 0.1, 0.8);
            s.scale.set(1, 1, 1);
        }
        this.baseShieldPos = s.position.clone();
    }

    // ─── Enterprise network topology ────────────────────────────────────
    // Computer → Router → Firewall → Server → Cloud → Backup, with a remote-
    // user branch. Thin illuminated paths, pulsing role nodes, and data
    // packets travelling the links — an infrastructure map, not a hacker
    // movie. Sweeps the mid-ground left→right; headlines stay clean left.
    createNetwork() {
        const group = new THREE.Group();
        // [x, y, z, color, role]
        const defs = [
            [-4.8, -0.5, -3.4, 0xe8f1ff, 'workstation'],
            [-3.3, 0.5, -3.0, 0x22d3ee, 'router'],
            [-1.8, -0.4, -2.8, 0x00e67a, 'firewall'],
            [-0.3, 0.6, -3.0, 0x4da3ff, 'server'],
            [1.3, 1.4, -3.2, 0x8b7cff, 'cloud'],
            [2.6, -1.0, -3.4, 0x2dd4bf, 'backup'],
            [-3.9, 1.9, -3.8, 0xe8f1ff, 'remote'],
        ];
        const links = [[0, 1], [1, 2], [2, 3], [3, 4], [4, 5], [1, 6]];
        const geo = new THREE.SphereGeometry(0.085, 16, 16);
        const glowTex = this.makeGlowTexture('rgba(255,255,255,1)', 'rgba(120,170,255,0.4)');
        this.nodes = defs.map((d) => {
            const color = d[3];
            const n = new THREE.Mesh(geo, new THREE.MeshStandardMaterial({
                color: 0x060b16, emissive: color, emissiveIntensity: 1.4,
                roughness: 0.3, metalness: 0.6,
            }));
            n.position.set(d[0], d[1], d[2]);
            n.userData.role = d[4];
            n.userData.phase = Math.random() * Math.PI * 2;
            group.add(n);
            const halo = new THREE.Sprite(new THREE.SpriteMaterial({
                map: glowTex, color, transparent: true, opacity: 0.5,
                blending: THREE.AdditiveBlending, depthWrite: false,
            }));
            halo.scale.set(0.55, 0.55, 1);
            halo.position.copy(n.position);
            group.add(halo);
            n.userData.halo = halo;
            // Thin orbit tick around key infrastructure nodes.
            if (d[4] === 'server' || d[4] === 'cloud' || d[4] === 'firewall') {
                const tick = new THREE.Mesh(
                    new THREE.TorusGeometry(0.2, 0.008, 6, 40),
                    new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.55, blending: THREE.AdditiveBlending })
                );
                tick.position.copy(n.position);
                tick.rotation.x = Math.PI / 2.3;
                group.add(tick);
                n.userData.tick = tick;
            }
            return n;
        });
        const lineMat = new THREE.LineBasicMaterial({ color: 0x3d6fd6, transparent: true, opacity: 0.42, blending: THREE.AdditiveBlending });
        this.links = links.map(([a, b]) => {
            const g = new THREE.BufferGeometry().setFromPoints([this.nodes[a].position, this.nodes[b].position]);
            const line = new THREE.Line(g, lineMat);
            group.add(line);
            return { a: this.nodes[a].position.clone(), b: this.nodes[b].position.clone(), line };
        });
        this.objects.linkMat = lineMat;
        // Data packets: small light pulses riding the links.
        const packetGeo = new THREE.SphereGeometry(0.035, 8, 8);
        const packetMat = new THREE.MeshBasicMaterial({ color: 0xbfe3ff, transparent: true, opacity: 0.95, blending: THREE.AdditiveBlending, depthWrite: false });
        const packetCount = this.smallScreen ? 0 : 9;
        this.packets = [];
        for (let i = 0; i < packetCount; i++) {
            const m = new THREE.Mesh(packetGeo, packetMat);
            const link = this.links[i % this.links.length];
            group.add(m);
            this.packets.push({ mesh: m, link, offset: Math.random(), speed: 0.1 + Math.random() * 0.12, dir: Math.random() > 0.5 ? 1 : -1 });
        }
        if (this.smallScreen) group.visible = false;
        this.scene.add(group);
        this.objects.network = group;
        this.positionPackets(0);
    }

    /** Park every packet on its link (also the reduced-motion still frame). */
    positionPackets(t) {
        if (!this.packets) return;
        for (const p of this.packets) {
            const tt = (t * p.speed + p.offset) % 1;
            p.mesh.position.lerpVectors(p.link.a, p.link.b, p.dir > 0 ? tt : 1 - tt);
        }
    }

    // ─── Server farm: deep-background rack silhouettes ───────────────────
    // Dark brushed-metal cabinets far behind the content (melted by fog),
    // brought alive by ventilation texture + instanced status LEDs.
    rackFaceTexture() {
        const c = document.createElement('canvas');
        c.width = 128; c.height = 256;
        const ctx = c.getContext('2d');
        ctx.fillStyle = '#05080f';
        ctx.fillRect(0, 0, 128, 256);
        // Ventilation slats.
        ctx.fillStyle = 'rgba(140,170,220,0.10)';
        for (let y = 10; y < 256; y += 12) ctx.fillRect(10, y, 108, 2);
        // Drive-bay separations.
        ctx.fillStyle = 'rgba(140,170,220,0.16)';
        for (let y = 30; y < 256; y += 42) ctx.fillRect(6, y, 116, 1);
        const t = new THREE.CanvasTexture(c);
        return t;
    }

    createServerFarm() {
        const group = new THREE.Group();
        const faceTex = this.rackFaceTexture();
        const cabMat = new THREE.MeshStandardMaterial({
            color: 0x0a101d, roughness: 0.45, metalness: 0.85,
            emissive: 0x0a1830, emissiveIntensity: 0.5, emissiveMap: faceTex,
        });
        const frameMat = new THREE.MeshStandardMaterial({ color: 0x111a2c, roughness: 0.35, metalness: 0.9 });
        const rackDefs = [
            [-5.5, -1.2, -9.5, 1.1], [-3.6, -1.2, -10.5, 1.25], [-1.5, -1.2, -10.0, 1.1],
            [0.7, -1.2, -10.5, 1.25], [2.9, -1.2, -9.8, 1.1], [5.0, -1.2, -10.2, 1.0],
        ];
        const ledGeo = new THREE.PlaneGeometry(0.05, 0.02);
        const ledMat = new THREE.MeshBasicMaterial({ color: 0xffffff, blending: THREE.AdditiveBlending, transparent: true, opacity: 0.9, depthWrite: false });
        const ledMesh = new THREE.InstancedMesh(ledGeo, ledMat, rackDefs.length * 7);
        const dummy = new THREE.Object3D();
        const ledColors = [new THREE.Color(0x00e67a), new THREE.Color(0x4da3ff), new THREE.Color(0x22d3ee)];
        let li = 0;
        rackDefs.forEach(([x, y, z, s], ri) => {
            const cab = new THREE.Mesh(new THREE.BoxGeometry(1.15 * s, 3.4 * s, 0.9), cabMat);
            cab.position.set(x, y + 1.7 * s, z);
            group.add(cab);
            const cap = new THREE.Mesh(new THREE.BoxGeometry(1.25 * s, 0.08, 1.0), frameMat);
            cap.position.set(x, y + 3.44 * s, z);
            group.add(cap);
            // Cool top-edge light bar per rack.
            const bar = new THREE.Mesh(
                new THREE.BoxGeometry(1.0 * s, 0.03, 0.03),
                new THREE.MeshBasicMaterial({ color: ri % 2 ? 0x2f6df0 : 0x00c46a, transparent: true, opacity: 0.85, blending: THREE.AdditiveBlending })
            );
            bar.position.set(x, y + 3.32 * s, z + 0.47);
            group.add(bar);
            // Status LEDs down the right rail.
            for (let k = 0; k < 7; k++) {
                dummy.position.set(x + 0.5 * s, y + (0.5 + k * 0.42) * s, z + 0.47);
                dummy.updateMatrix();
                ledMesh.setMatrixAt(li, dummy.matrix);
                ledMesh.setColorAt(li, ledColors[(ri + k) % ledColors.length]);
                li++;
            }
        });
        ledMesh.instanceMatrix.needsUpdate = true;
        if (ledMesh.instanceColor) ledMesh.instanceColor.needsUpdate = true;
        group.add(ledMesh);
        this.objects.rackLeds = ledMesh;
        this.scene.add(group);
        this.objects.serverFarm = group;
    }

    // ─── Cloud cluster: soft violet-blue vapor shells, top-right deep ────
    createCloudCluster() {
        const group = new THREE.Group();
        const puffs = [
            [4.6, 3.1, -8.5, 1.5], [6.0, 2.6, -9.2, 1.9], [5.3, 3.8, -9.5, 1.3],
            [3.4, 2.5, -8.8, 1.0], [6.9, 3.3, -8.6, 1.1],
        ];
        puffs.forEach(([x, y, z, s], i) => {
            const m = new THREE.Mesh(
                new THREE.SphereGeometry(s, 20, 20),
                new THREE.MeshBasicMaterial({
                    color: i % 2 ? 0x4d6fff : 0x8b7cff, transparent: true,
                    opacity: 0.05, blending: THREE.AdditiveBlending, depthWrite: false,
                })
            );
            m.position.set(x, y, z);
            m.userData.phase = i * 1.3;
            group.add(m);
        });
        group.position.x = 0;
        this.scene.add(group);
        this.objects.cloud = group;
    }

    // ─── Holographic diagnostic monitors: live canvas dashboards ─────────
    // Two floating system dashboards at the far edges (never behind text).
    // Canvas textures redraw a few times per second — premium feel, tiny cost.
    dashTexture() {
        const c = document.createElement('canvas');
        c.width = 256; c.height = 160;
        return { canvas: c, ctx: c.getContext('2d'), seed: Math.random() * 100 };
    }

    drawDash(d, t) {
        const { ctx, seed } = d;
        ctx.fillStyle = 'rgba(3,8,18,0.92)';
        ctx.fillRect(0, 0, 256, 160);
        ctx.strokeStyle = 'rgba(90,140,230,0.18)';
        ctx.lineWidth = 1;
        for (let x = 0; x <= 256; x += 32) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, 160); ctx.stroke(); }
        for (let y = 0; y <= 160; y += 32) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(256, y); ctx.stroke(); }
        // Throughput waveform.
        ctx.strokeStyle = 'rgba(34,211,238,0.9)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        for (let x = 0; x <= 256; x += 4) {
            const y = 70 + Math.sin(x * 0.06 + t * 1.4 + seed) * 18 + Math.sin(x * 0.15 + t * 2.2) * 7;
            if (x === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        }
        ctx.stroke();
        // Capacity bars.
        for (let i = 0; i < 12; i++) {
            const h = 14 + Math.abs(Math.sin(i * 1.7 + t * 0.9 + seed)) * 44;
            ctx.fillStyle = i % 4 === 0 ? 'rgba(0,230,122,0.75)' : 'rgba(77,163,255,0.6)';
            ctx.fillRect(12 + i * 20, 150 - h, 12, h);
        }
        // Status micro-text ticks.
        ctx.fillStyle = 'rgba(232,241,255,0.75)';
        for (let i = 0; i < 8; i++) ctx.fillRect(12 + i * 30, 8, 14, 3);
    }

    createHoloMonitors() {
        if (this.smallScreen) return;
        const group = new THREE.Group();
        this.monitors = [];
        const defs = [
            { p: [-6.4, 0.9, -5.5], ry: 0.5, seed: 1 },
            { p: [6.6, -0.4, -6.0], ry: -0.55, seed: 60 },
        ];
        defs.forEach((def) => {
            const d = this.dashTexture();
            this.drawDash(d, def.seed);
            const tex = new THREE.CanvasTexture(d.canvas);
            const screen = new THREE.Mesh(
                new THREE.PlaneGeometry(1.7, 1.06),
                new THREE.MeshBasicMaterial({ map: tex, transparent: true, opacity: 0.85, blending: THREE.AdditiveBlending, depthWrite: false, side: THREE.DoubleSide })
            );
            screen.position.set(...def.p);
            screen.rotation.y = def.ry;
            group.add(screen);
            const frame = new THREE.Mesh(
                new THREE.PlaneGeometry(1.8, 1.16),
                new THREE.MeshBasicMaterial({ color: 0x1c3a6e, transparent: true, opacity: 0.5, side: THREE.DoubleSide })
            );
            frame.position.set(def.p[0], def.p[1], def.p[2] - 0.02);
            frame.rotation.y = def.ry;
            group.add(frame);
            this.monitors.push({ tex, dash: d, mesh: screen, phase: def.seed });
        });
        this.scene.add(group);
        this.objects.monitors = group;
    }

    // ─── Command-deck grid floor: faint perspective lattice far below ────
    gridTexture() {
        const c = document.createElement('canvas');
        c.width = 128; c.height = 128;
        const ctx = c.getContext('2d');
        ctx.fillStyle = 'rgba(0,0,0,0)';
        ctx.fillRect(0, 0, 128, 128);
        ctx.strokeStyle = 'rgba(70,120,220,0.55)';
        ctx.lineWidth = 2;
        ctx.strokeRect(1, 1, 126, 126);
        const t = new THREE.CanvasTexture(c);
        t.wrapS = THREE.RepeatWrapping;
        t.wrapT = THREE.RepeatWrapping;
        t.repeat.set(24, 24);
        return t;
    }

    createGridFloor() {
        const floor = new THREE.Mesh(
            new THREE.PlaneGeometry(90, 60),
            new THREE.MeshBasicMaterial({ map: this.gridTexture(), transparent: true, opacity: 0.16, blending: THREE.AdditiveBlending, depthWrite: false })
        );
        floor.rotation.x = -Math.PI / 2;
        floor.position.y = -3.4;
        this.scene.add(floor);
        this.objects.gridFloor = floor;
    }

    // ─── Volumetric-feel light shafts from the upper left ────────────────
    createLightShafts() {
        const texC = document.createElement('canvas');
        texC.width = 64; texC.height = 256;
        const ctx = texC.getContext('2d');
        const g = ctx.createLinearGradient(0, 0, 0, 256);
        g.addColorStop(0, 'rgba(150,190,255,0.55)');
        g.addColorStop(1, 'rgba(150,190,255,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, 64, 256);
        const tex = new THREE.CanvasTexture(texC);
        const mat = new THREE.MeshBasicMaterial({ map: tex, transparent: true, opacity: 0.10, blending: THREE.AdditiveBlending, depthWrite: false, side: THREE.DoubleSide });
        [[-4.5, 2.5, -7, 0.35], [-2.0, 3.0, -8, 0.5]].forEach(([x, y, z, w]) => {
            const shaft = new THREE.Mesh(new THREE.PlaneGeometry(2.4 * w + 1.2, 14), mat);
            shaft.position.set(x, y, z);
            shaft.rotation.z = 0.32;
            this.scene.add(shaft);
        });
    }

    // ─── Foreground glove user control (moving / fixed / positioning) ────
    // Normalized viewport coords (0..1) clamped to safe margins so one
    // saved position adapts to every screen size. World mapping anchors to
    // the live camera each frame, so fixed mode survives scroll and drift.
    static gloveClamp(v) {
        const n = Number(v);
        if (!Number.isFinite(n)) return null;
        return Math.min(0.92, Math.max(0.08, n));
    }

    readGlovePrefs() {
        try {
            const body = document.body;
            const mode = body ? body.getAttribute('data-glove-mode') : null;
            const bounds = this.gloveSafeBounds();
            let x = Global3DScene.gloveClamp(body ? body.getAttribute('data-glove-x') : null);
            let y = Global3DScene.gloveClamp(body ? body.getAttribute('data-glove-y') : null);
            // Saved positions predate safe bounds — nudge them into the
            // safe lane instead of parking the glove under navigation.
            if (x !== null) x = Math.min(0.92, Math.max(bounds.xMin, x));
            if (y !== null) y = Math.min(0.92, Math.max(bounds.yMin, y));
            if ((mode === 'fixed' || mode === 'moving') && (mode === 'moving' || (x !== null && y !== null))) {
                this.gloveCtl.mode = mode;
                this.gloveCtl.x = x;
                this.gloveCtl.y = y;
            }
        } catch (e) { /* fall back to moving */ }
    }

    // Content-aware safe bounds for a fixed glove: keep it clear of the
    // sidebar rail (desktop) and the top header bar. The canvas always
    // paints behind content (z-5 < z-10), so this governs visual crowding,
    // not occlusion — content can never be covered regardless.
    gloveSafeBounds() {
        try {
            const w = Math.max(1, window.innerWidth);
            const h = Math.max(1, window.innerHeight);
            let xMin = 0.08;
            let yMin = 0.08;
            if (w >= 1024) {
                const aside = document.querySelector('aside');
                if (aside) {
                    const r = aside.getBoundingClientRect();
                    if (r.width > 0 && r.width < w * 0.5) xMin = Math.min(0.9, (r.width + 24) / w);
                }
            }
            const header = document.querySelector('header');
            if (header) {
                const r = header.getBoundingClientRect();
                if (r.height > 0 && r.height < h * 0.5) yMin = Math.min(0.9, (r.height + 16) / h);
            }
            return { xMin, yMin };
        } catch (e) { return { xMin: 0.08, yMin: 0.08 }; }
    }

    setGloveMode(mode, x = null, y = null) {
        if (mode !== 'moving' && mode !== 'fixed') return false;
        const cx = x === null ? this.gloveCtl.x : Global3DScene.gloveClamp(x);
        const cy = y === null ? this.gloveCtl.y : Global3DScene.gloveClamp(y);
        if (mode === 'fixed' && (cx === null || cy === null)) return false;
        this.gloveCtl.mode = mode;
        this.gloveCtl.x = cx;
        this.gloveCtl.y = cy;
        this.gloveCtl.positioning = false;
        this.gloveCtl.dragging = false;
        if ((this.reduced || this.lowPower) && this.renderer) this.renderOnce();
        return true;
    }

    beginPositioning() {
        if (!this.objects.shieldGroup) return false;
        this.gloveCtl.positioning = true;
        this.gloveCtl.dragging = false;
        // Start preview from the current on-screen glove location.
        const cur = this.gloveScreenPos();
        this.gloveCtl.tx = cur ? cur.x : (this.gloveCtl.x ?? 0.8);
        this.gloveCtl.ty = cur ? cur.y : (this.gloveCtl.y ?? 0.5);
        return true;
    }

    cancelPositioning() {
        this.gloveCtl.positioning = false;
        this.gloveCtl.dragging = false;
        if ((this.reduced || this.lowPower) && this.renderer) this.renderOnce();
    }

    positioningTarget() {
        if (this.gloveCtl.tx === null || this.gloveCtl.ty === null) return null;
        const bounds = this.gloveSafeBounds();
        return {
            x: Math.min(0.92, Math.max(bounds.xMin, Global3DScene.gloveClamp(this.gloveCtl.tx))),
            y: Math.min(0.92, Math.max(bounds.yMin, Global3DScene.gloveClamp(this.gloveCtl.ty))),
        };
    }

    // Current glove location expressed in normalized viewport coords.
    gloveScreenPos() {
        const s = this.objects.shieldGroup;
        if (!s || !this.camera) return null;
        const v = s.position.clone().project(this.camera);
        return {
            x: Global3DScene.gloveClamp(v.x * 0.5 + 0.5),
            y: Global3DScene.gloveClamp(-v.y * 0.5 + 0.5),
        };
    }

    // Viewport-anchored world target for normalized coords (camera-relative,
    // so scroll and camera drift never move a fixed glove).
    gloveWorldTarget(nx, ny) {
        const s = this.objects.shieldGroup;
        if (!s || !this.camera) return null;
        const depth = Math.max(4, this.camera.position.z - (this.baseShieldPos ? this.baseShieldPos.z : 0.8));
        const halfH = Math.tan(THREE.MathUtils.degToRad(this.camera.fov * 0.5)) * depth;
        const halfW = halfH * this.camera.aspect;
        const lx = (nx - 0.5) * 2 * halfW * 0.78;
        const ly = (0.5 - ny) * 2 * halfH * 0.72;
        const g = this.objects.planetGroup;
        const look = new THREE.Vector3(g ? g.position.x * 0.35 : 0, 0, 0);
        const fwd = look.clone().sub(this.camera.position).normalize();
        const right = new THREE.Vector3().crossVectors(fwd, new THREE.Vector3(0, 1, 0)).normalize();
        const up = new THREE.Vector3().crossVectors(right, fwd).normalize();
        return this.camera.position.clone()
            .addScaledVector(fwd, depth)
            .addScaledVector(right, lx)
            .addScaledVector(up, ly);
    }

    renderOnce() {
        if (!this.renderer || !this.scene || !this.camera) return;
        this.applyState(this.scroll.progress || 0, true);
        this.renderer.render(this.scene, this.camera);
        if (this.rendererFg && this.sceneFg) this.rendererFg.render(this.sceneFg, this.camera);
    }

    bindGloveDrag() {
        this.onPointerDown = (e) => {
            if (!this.gloveCtl.positioning || this.gloveCtl.dragging) return;
            if (e.pointerType === 'mouse' && e.button !== 0) return;
            // Never hijack real controls — drag starts only on neutral surface.
            if (e.target && e.target.closest && e.target.closest('a, button, input, select, textarea, [data-glove-ui]')) return;
            this.gloveCtl.dragging = true;
            this.gloveDragMove(e);
        };
        this.onPointerMove = (e) => {
            if (!this.gloveCtl.positioning || !this.gloveCtl.dragging) return;
            this.gloveDragMove(e);
        };
        this.onPointerUp = () => { this.gloveCtl.dragging = false; };
        this.onTouchMove = (e) => {
            // Pause page scroll ONLY while actively dragging in positioning
            // mode; normal scrolling is untouched everywhere else.
            if (this.gloveCtl.positioning && this.gloveCtl.dragging) {
                if (e.cancelable) e.preventDefault();
            }
        };
        window.addEventListener('pointerdown', this.onPointerDown, { passive: true });
        window.addEventListener('pointermove', this.onPointerMove, { passive: true });
        window.addEventListener('pointerup', this.onPointerUp, { passive: true });
        window.addEventListener('pointercancel', this.onPointerUp, { passive: true });
        window.addEventListener('touchmove', this.onTouchMove, { passive: false });
    }

    gloveDragMove(e) {
        const w = Math.max(1, window.innerWidth);
        const h = Math.max(1, window.innerHeight);
        this.gloveCtl.tx = Global3DScene.gloveClamp(e.clientX / w);
        this.gloveCtl.ty = Global3DScene.gloveClamp(e.clientY / h);
        if ((this.reduced || this.lowPower) && this.renderer) this.renderOnce();
    }

    // ─── Scroll + section observation (rAF-throttled, never per-event render) ──
    bindEvents() {
        this.onScroll = () => {
            if (this.ticking) return;
            this.ticking = true;
            requestAnimationFrame(() => { this.updateScrollTarget(); this.ticking = false; });
        };
        this.onMouse = (e) => {
            this.mouse.tx = (e.clientX / window.innerWidth - 0.5) * 2;
            this.mouse.ty = (e.clientY / window.innerHeight - 0.5) * 2;
        };
        this.onResize = () => {
            if (!this.renderer || !this.camera) return;
            this.camera.aspect = window.innerWidth / window.innerHeight;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(window.innerWidth, window.innerHeight);
            if (this.rendererFg) this.rendererFg.setSize(window.innerWidth, window.innerHeight);
            this.smallScreen = Math.min(window.innerWidth, window.innerHeight) < 500;
            this.layoutPlanet();
            this.layoutShield();
            if (this.objects.network) this.objects.network.visible = !this.smallScreen;
            if (this.objects.monitors) this.objects.monitors.visible = !this.smallScreen;
            this.updateScrollTarget(true);
            this.updateHudAnchor();
        };
        this.onVisibility = () => {
            this.isVisible = !document.hidden;
            if (this.isVisible && !this.isDestroyed && !this.frameId && !this.reduced && !this.lowPower) this.animate();
        };
        window.addEventListener('scroll', this.onScroll, { passive: true });
        window.addEventListener('mousemove', this.onMouse, { passive: true });
        window.addEventListener('resize', this.onResize, { passive: true });
        document.addEventListener('visibilitychange', this.onVisibility);
        this.bindGloveDrag();
    }

    pageProgress() {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        if (max <= 0) return 0;
        return Math.min(1, Math.max(0, window.scrollY / max));
    }

    currentSectionBias() {
        const sections = document.querySelectorAll('[data-3d-state]');
        if (!sections.length) return null;
        const mid = window.innerHeight * 0.5;
        let best = null;
        let bestDist = Infinity;
        sections.forEach((el) => {
            const r = el.getBoundingClientRect();
            if (r.bottom < 0 || r.top > window.innerHeight) return;
            const d = Math.abs(r.top + r.height / 2 - mid);
            if (d < bestDist) { bestDist = d; best = el.dataset['3dState'] || el.getAttribute('data-3d-state'); }
        });
        if (!best) return null;
        return STATE_BIAS[best] ?? null;
    }

    updateScrollTarget(immediate = false) {
        this.scroll.target = this.pageProgress();
        const bias = this.currentSectionBias();
        if (bias !== null) this.scroll.sectionTarget = bias;
        else this.scroll.sectionTarget = this.scroll.target;
        if (immediate) {
            this.scroll.progress = this.scroll.target;
            this.scroll.sectionBias = this.scroll.sectionTarget;
        }
    }

    // Waypoint journey across the page: right → center → left → calm settle.
    // Bounded amplitudes keep the shield/planet inside safe camera framing
    // at every scroll position (never travels off-screen).
    journey(p) {
        const g = this.objects.planetGroup;
        const base = this.basePlanetPos || { x: 3.4, y: 0.1, z: -1.2 };
        const isMobile = window.innerWidth < 768;
        const amp = isMobile ? 0.25 : 1;
        return {
            x: base.x - p * 6.0 * amp,
            y: base.y + Math.sin(p * Math.PI * 2) * 0.45,
            z: base.z - Math.sin(p * Math.PI) * 1.0,
            rotY: p * Math.PI * 2.2,
            camX: (p * 1.1 - 0.55) * (isMobile ? 0.5 : 1),
            camY: -p * 0.5 + 0.25,
            light: 4.2 + Math.sin(p * Math.PI * 2) * 1.2,
        };
    }

    applyState(p, snap = false) {
        const g = this.objects.planetGroup;
        if (!g) return;
        const j = this.journey(p);
        const k = snap ? 1 : 0.06;
        // HUD-anchored globe: x/y glued to the fixed box center; z-depth,
        // spin, rings and lighting keep following the scroll journey.
        let tx = j.x, ty = j.y;
        if (this.hudAnchor) {
            const anchor = this.hudWorldTarget(this.hudAnchor.x, this.hudAnchor.y);
            if (anchor) { tx = anchor.x; ty = anchor.y; }
        }
        g.position.x += (tx - g.position.x) * k;
        g.position.y += (ty - g.position.y) * k;
        g.position.z += (j.z - g.position.z) * k;
        if (this.objects.planet) this.objects.planet.rotation.y += snap ? j.rotY : 0.0016 + (j.rotY - this.objects.planet.rotation.y) * 0.002;
        if (this.objects.ring) this.objects.ring.rotation.z += 0.0009;
        if (this.objects.stars) this.objects.stars.rotation.y += 0.00012;
        // Foreground shield: same scroll journey, own composition — travels
        // through foreground space, always inside safe framing.
        // User override: fixed mode anchors to the saved viewport position
        // (scroll-proof); positioning mode previews the drag target live.
        const s = this.objects.shieldGroup;
        if (s && this.sceneFg && this.baseShieldPos) {
            const fixed = this.gloveCtl.mode === 'fixed' && this.gloveCtl.x !== null && this.gloveCtl.y !== null;
            const preview = this.gloveCtl.positioning ? this.positioningTarget() : null;
            const anchor = preview || (fixed ? { x: this.gloveCtl.x, y: this.gloveCtl.y } : null);
            if (anchor) {
                const target = this.gloveWorldTarget(anchor.x, anchor.y);
                if (target) {
                    const fk = snap ? 1 : 0.12;
                    s.position.x += (target.x - s.position.x) * fk;
                    s.position.y += (target.y - s.position.y) * fk;
                    s.position.z += ((this.baseShieldPos.z || 0.8) - s.position.z) * fk;
                }
            } else {
                const amp = window.innerWidth < 768 ? 0.25 : 1;
                const sx = this.baseShieldPos.x - p * 6.0 * amp;
                const sy = this.baseShieldPos.y + Math.sin(p * Math.PI * 2) * 0.45;
                s.position.x += (sx - s.position.x) * k;
                s.position.y += (sy - s.position.y) * k;
            }
        }
        this.mouse.x += (this.mouse.tx - this.mouse.x) * 0.04;
        this.mouse.y += (this.mouse.ty - this.mouse.y) * 0.04;
        this.camera.position.x += ((j.camX + this.mouse.x * 0.5) - this.camera.position.x) * k;
        this.camera.position.y += ((j.camY - this.mouse.y * 0.3) - this.camera.position.y) * k;
        this.camera.lookAt(new THREE.Vector3(g.position.x * 0.35, 0, 0));
        if (this.objects.key) this.objects.key.intensity = j.light;
        const targetOpacity = this.subtle ? 0.55 : 0.85;
        this.canvas.style.opacity = String(targetOpacity);
        // Small screens dim the foreground so text keeps priority.
        const fgBase = this.subtle ? 0.35 : 0.9;
        if (this.canvasFg) this.canvasFg.style.opacity = String(this.smallScreen ? fgBase * 0.55 : fgBase);
    }

    animate() {
        if (this.isDestroyed) return;
        if (this.isVisible === false) { this.frameId = null; return; }
        this.frameId = requestAnimationFrame(() => this.animate());
        const p = this.scroll.progress + (this.scroll.sectionTarget - this.scroll.progress) * 0.5;
        this.scroll.progress += (this.scroll.target - this.scroll.progress) * 0.07;
        this.scroll.sectionBias += (this.scroll.sectionTarget - this.scroll.sectionBias) * 0.06;
        const blended = (p + this.scroll.sectionBias) / 2;
        this.applyState(Math.min(1, Math.max(0, blended)));
        const tSec = (performance.now() - (this.t0 || performance.now())) / 1000;
        this.animateShield(tSec);
        this.animateEcosystem(tSec);
        this.renderer.render(this.scene, this.camera);
        if (this.rendererFg) this.rendererFg.render(this.sceneFg, this.camera);
    }

    // ─── Living infrastructure: slow, elegant, inexpensive ───────────────
    // Packets ride the topology, nodes breathe, LEDs shimmer, dashboards
    // refresh a few times per second. Everything sinusoidal — no aggression.
    animateEcosystem(t) {
        this.frame = (this.frame || 0) + 1;
        this.positionPackets(t);
        if (this.nodes) {
            for (const n of this.nodes) {
                const s = 1 + Math.sin(t * 1.6 + n.userData.phase) * 0.1;
                n.scale.set(s, s, s);
                if (n.userData.halo) n.userData.halo.material.opacity = 0.38 + Math.sin(t * 1.6 + n.userData.phase) * 0.14;
                if (n.userData.tick) n.userData.tick.rotation.z = t * 0.5;
            }
        }
        if (this.objects.linkMat) this.objects.linkMat.opacity = 0.36 + Math.sin(t * 1.3) * 0.08;
        if (this.objects.coreWire) this.objects.coreWire.rotation.y = t * 0.12;
        if (this.objects.ring2) this.objects.ring2.rotation.z = t * 0.1;
        if (this.objects.cloud) this.objects.cloud.rotation.y = Math.sin(t * 0.05) * 0.04;
        if (this.objects.rackLeds) this.objects.rackLeds.material.opacity = 0.82 + Math.sin(t * 2.6) * 0.1;
        if (this.objects.gridFloor && this.objects.gridFloor.material.map) {
            this.objects.gridFloor.material.map.offset.x = t * 0.004;
        }
        if (this.monitors && this.frame % 12 === 0) {
            for (const m of this.monitors) {
                this.drawDash(m.dash, t);
                m.tex.needsUpdate = true;
                m.mesh.position.y += Math.sin(t * 0.8 + m.phase) * 0.0006;
            }
        }
    }

    // Previous shield motion, verbatim (slow, premium, subtle).
    animateShield(t) {
        if (this.objects.shieldWire) {
            this.objects.shieldWire.rotation.x = t * 0.15;
            this.objects.shieldWire.rotation.y = t * 0.22;
        }
        if (this.objects.shieldCore) {
            this.objects.shieldCore.rotation.x = -t * 0.18;
            this.objects.shieldCore.rotation.y = -t * 0.28;
            this.objects.shieldCore.material.emissiveIntensity = 0.7 + Math.sin(t * 2.5) * 0.25;
        }
        if (this.objects.crestMesh) {
            this.objects.crestMesh.position.z = 0.7 + Math.sin(t * 2.0) * 0.06;
            this.objects.crestMesh.rotation.y = Math.sin(t * 1.2) * 0.12;
        }
        if (this.dataRings) {
            this.dataRings.forEach((ring, idx) => {
                ring.rotation.z = t * (0.2 + idx * 0.15) * (idx % 2 === 0 ? 1 : -1);
                ring.rotation.x += 0.0008;
            });
        }
    }

    destroy() {
        this.isDestroyed = true;
        if (this.frameId) cancelAnimationFrame(this.frameId);
        this.frameId = null;
        window.removeEventListener('scroll', this.onScroll);
        window.removeEventListener('mousemove', this.onMouse);
        window.removeEventListener('resize', this.onResize);
        document.removeEventListener('visibilitychange', this.onVisibility);
        window.removeEventListener('pointerdown', this.onPointerDown);
        window.removeEventListener('pointermove', this.onPointerMove);
        window.removeEventListener('pointerup', this.onPointerUp);
        window.removeEventListener('pointercancel', this.onPointerUp);
        window.removeEventListener('touchmove', this.onTouchMove);
        this.scene?.traverse((o) => {
            if (o.geometry) o.geometry.dispose();
            if (o.material) (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => { if (m.map) m.map.dispose(); m.dispose(); });
        });
        this.sceneFg?.traverse((o) => {
            if (o.geometry) o.geometry.dispose();
            if (o.material) (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => { if (m.map) m.map.dispose(); m.dispose(); });
        });
        this.renderer?.dispose();
        this.rendererFg?.dispose();
        this.rendererFg = null;
        this.sceneFg = null;
        if (this.canvas) delete this.canvas.dataset.init;
        if (window._global3D === this) window._global3D = null;
    }
}

export default Global3DScene;
