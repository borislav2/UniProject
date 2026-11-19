/*
 * The "Как работим" world: one long floor with four stations (the whiteboard, the desk, the desk again for the details, the launch pedestal),
 * one Kreo that walks between them and a camera that glides along. Everything is a pure function of the scene value t (0 to 3), the clock and
 * the pointer, so scrolling back plays the story backwards. Used live by g-kreo.js and for the stills by tools/kreo/render.mjs.
 * The canvas is transparent: the page keeps its own surface colour (light #ECEEF0, dark #0B0C0F) and only the floor pool and the shadows are drawn.
 */
import {
    ACESFilmicToneMapping, AdditiveBlending, BoxGeometry, CanvasTexture, CapsuleGeometry, CylinderGeometry, DirectionalLight, DoubleSide, Group, HemisphereLight,
    InstancedMesh, Mesh, MeshBasicMaterial, MeshStandardMaterial, Object3D, PCFSoftShadowMap, PMREMGenerator, PerspectiveCamera, PlaneGeometry, SRGBColorSpace,
    Scene, ShadowMaterial, SphereGeometry, TextureLoader, TorusGeometry, Vector3, WebGLRenderer, Color,
} from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { RoundedBoxGeometry } from 'three/examples/jsm/geometries/RoundedBoxGeometry.js';
import { glass, gloss, makeRobot, radialTexture } from './robot.js';

const X = [0, 6.8, 6.8, 13.6]; // station centres along the floor
const FOV = 30;
const SCALE = 1.1;

const lerp = (a, b, k) => a + (b - a) * k;
const clamp = (v, lo = 0, hi = 1) => Math.min(hi, Math.max(lo, v));
const smooth = (k) => { const c = clamp(k); return c * c * (3 - 2 * c); };

function canvasTex(w, h, draw) {
    const c = document.createElement('canvas');
    c.width = w;
    c.height = h;
    draw(c.getContext('2d'), w, h);
    const t = new CanvasTexture(c);
    t.colorSpace = SRGBColorSpace;
    t.anisotropy = 8;

    return t;
}

function wrap(x, text, maxWidth) {
    const lines = [];
    let line = '';
    for (const word of String(text).split(/\s+/)) {
        const next = line ? line + ' ' + word : word;
        if (line && x.measureText(next).width > maxWidth) { lines.push(line); line = word; } else { line = next; }
    }
    if (line) lines.push(line);

    return lines;
}

// Poses of Kreo per scene: station-relative position, turn, both arms (x lifts forward, z opens out), head turn.
const POSES = [
    { x: X[0] + 1.95, z: 0.5, ry: -0.72, l: [0.1, 0.12], r: [-1.95, 0.25], hy: 0.12, hx: 0.02 },
    { x: X[1] + 1.3, z: 0.5, ry: -1.05, l: [-1.35, 0.12], r: [-1.35, 0.12], hy: 0.25, hx: 0.05 },
    { x: X[2] + 1.25, z: 0.7, ry: -0.9, l: [-0.2, 0.15], r: [-1.5, 0.05], hy: 0.3, hx: 0.04 },
    { x: X[3] - 0.95, z: 0.45, ry: 1.0, l: [-0.35, 0.2], r: [-1.45, -0.05], hy: -0.1, hx: 0.08 },
];

// Camera keyframes: where to look, from which side, and how much of the floor must fit (the distance follows the aspect of the canvas).
const CAMS = [
    { t: [X[0] + 1.0, 0.98, 0], d: [0.1, 0.1, 1], w: 3.8, h: 2.7 },
    { t: [X[1] + 0.65, 0.98, 0], d: [-0.05, 0.12, 1], w: 3.9, h: 2.5 },
    { t: [X[2] + 0.35, 1.15, 0.2], d: [-0.2, 0.1, 1], w: 3.9, h: 2.7 },
    { t: [X[3] + 0.1, 0.9, 0], d: [0.08, 0.14, 1], w: 4.4, h: 3.0 },
];

export function createStory({ canvas, width, height, pixelRatio = 1.5, planTitles = [], siteImage = '', dark = false, antialias = true } = {}) {
    const renderer = new WebGLRenderer({ canvas, antialias, alpha: true, preserveDrawingBuffer: false, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(pixelRatio, 1.5));
    renderer.setSize(width, height, false);
    renderer.setClearColor(0x000000, 0);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = PCFSoftShadowMap;
    renderer.toneMapping = ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.outputColorSpace = SRGBColorSpace;

    const scene = new Scene();
    const pmrem = new PMREMGenerator(renderer);
    const env = pmrem.fromScene(new RoomEnvironment(), 0.04);
    scene.environment = env.texture;
    pmrem.dispose();

    const camera = new PerspectiveCamera(FOV, width / height, 0.1, 80);

    // Light: one key light with a soft shadow that follows the camera focus, a cool fill, a warm rim from behind.
    const key = new DirectionalLight('#ffffff', 2.6);
    key.castShadow = true;
    key.shadow.mapSize.set(2048, 2048);
    key.shadow.radius = 8;
    key.shadow.blurSamples = 20;
    Object.assign(key.shadow.camera, { left: -4.5, right: 4.5, top: 4.5, bottom: -4.5, near: 0.5, far: 20 });
    key.shadow.bias = -0.0004;
    scene.add(key, key.target);
    const hemi = new HemisphereLight('#ffffff', '#c9cdd3', 0.55);
    const rim = new DirectionalLight('#ffd9cc', 1.2);
    scene.add(hemi, rim);

    // Floor: a pool of light under each station that fades out (no hard plane), and a shadow catcher on top of it.
    const poolTex = radialTexture([[0, 'rgba(255,255,255,1)'], [0.4, 'rgba(255,255,255,.5)'], [0.85, 'rgba(255,255,255,0)']]);
    const pools = [X[0] + 0.8, X[1] + 0.6, X[3] + 0.1].map((x) => {
        const m = new Mesh(new PlaneGeometry(8, 5.6), new MeshBasicMaterial({ map: poolTex, transparent: true, depthWrite: false, toneMapped: false }));
        m.rotation.x = -Math.PI / 2;
        m.position.set(x, 0.001, 0);
        m.renderOrder = 0;
        scene.add(m);

        return m;
    });
    const catcher = new Mesh(new PlaneGeometry(60, 20), new ShadowMaterial({ opacity: 0.16 }));
    catcher.rotation.x = -Math.PI / 2;
    catcher.position.y = 0.002;
    catcher.receiveShadow = true;
    catcher.renderOrder = 1;
    scene.add(catcher);

    // The orange line on the floor, the timeline the story walks along.
    const line = new Mesh(new BoxGeometry(X[3] - X[0] + 8, 0.004, 0.022), new MeshBasicMaterial({ color: '#FF5A33', toneMapped: false }));
    line.position.set((X[0] + X[3]) / 2, 0.005, 1.55);
    const lineGlow = new Mesh(new BoxGeometry(X[3] - X[0] + 8, 0.003, 0.12), new MeshBasicMaterial({ color: '#FF5A33', transparent: true, opacity: 0.12, depthWrite: false, toneMapped: false }));
    lineGlow.position.copy(line.position);
    scene.add(line, lineGlow);

    const ink = () => gloss('#15171D', 0.3);
    const white = () => gloss('#ffffff', 0.35);

    // ---- Station 0: the whiteboard with the plan (the four step titles)
    const board = new Group();
    board.position.set(X[0] + 0.35, 0, -0.55);
    board.rotation.y = -0.08;
    board.scale.setScalar(0.92);
    scene.add(board);
    const boardTex = canvasTex(2100, 1350, (x, w, h) => {
        x.scale(1.5, 1.5);
        x.fillStyle = '#fff';
        x.fillRect(0, 0, w, h);
        const list = planTitles.length ? planTitles : ['', '', '', ''];
        list.slice(0, 4).forEach((title, i) => {
            const y = 150 + i * 185;
            x.strokeStyle = '#0C0E12';
            x.lineWidth = 5;
            x.strokeRect(64, y - 44, 56, 56);
            if (i === 0) { x.strokeStyle = '#E8431B'; x.lineWidth = 9; x.beginPath(); x.moveTo(74, y - 18); x.lineTo(92, y - 2); x.lineTo(116, y - 38); x.stroke(); }
            x.fillStyle = '#C4300F';
            x.font = '500 34px "JetBrains Mono", monospace';
            x.fillText(String(i + 1).padStart(2, '0'), 146, y - 2);
            x.fillStyle = '#0C0E12';
            // Big, regular weight and one line: readable from the camera distance.
            let size = 74;
            x.font = `400 ${size}px Onest, Geologica, sans-serif`;
            while (size > 46 && x.measureText(title).width > 720) { size -= 4; x.font = `400 ${size}px Onest, Geologica, sans-serif`; }
            const lines = wrap(x, title, 720).slice(0, 2);
            lines.forEach((ln, k) => x.fillText(ln, 216, y + 10 + k * (size + 6) - (lines.length > 1 ? (size + 6) / 2 : 0)));
        });
        // A page sketch on the right, boxes only: a header, a picture, three blocks.
        x.strokeStyle = '#0C0E12';
        x.lineWidth = 5;
        x.strokeRect(980, 120, 340, 600);
        x.beginPath(); x.moveTo(980, 190); x.lineTo(1320, 190); x.stroke();
        x.strokeStyle = '#E8431B';
        x.strokeRect(1010, 226, 280, 170);
        x.beginPath(); x.moveTo(1010, 226); x.lineTo(1290, 396); x.moveTo(1290, 226); x.lineTo(1010, 396); x.stroke();
        x.strokeStyle = '#0C0E12';
        for (let i = 0; i < 3; i++) { x.strokeRect(1010 + i * 96, 430, 88, 110); }
        x.beginPath(); x.moveTo(1010, 590); x.lineTo(1290, 590); x.moveTo(1010, 630); x.lineTo(1220, 630); x.stroke();
        x.fillStyle = '#E8431B';
        x.fillRect(1010, 660, 150, 36);
    });
    const panel = new Mesh(new RoundedBoxGeometry(2.3, 1.48, 0.05, 4, 0.02), [0, 1, 2, 3, 4, 5].map((i) => (i === 4 ? new MeshBasicMaterial({ map: boardTex, toneMapped: false }) : new MeshStandardMaterial({ color: '#0C0E12', roughness: 0.4 }))));
    panel.position.y = 1.52;
    panel.castShadow = true;
    board.add(panel);
    for (const sx of [-1, 1]) {
        const leg = new Mesh(new CylinderGeometry(0.02, 0.02, 0.8, 14), new MeshStandardMaterial({ color: '#0C0E12' }));
        leg.position.set(sx * 0.95, 0.4, 0);
        leg.castShadow = true;
        const foot = new Mesh(new BoxGeometry(0.06, 0.03, 0.6), new MeshStandardMaterial({ color: '#0C0E12' }));
        foot.position.set(sx * 0.95, 0.015, 0);
        board.add(leg, foot);
    }

    // ---- Station 1 and 2: the desk with the monitor (the site is built on it, then polished)
    const desk = new Group();
    desk.position.set(X[1], 0, -0.1);
    scene.add(desk);
    const top = new Mesh(new RoundedBoxGeometry(2.0, 0.06, 0.85, 4, 0.02), white());
    top.position.y = 0.74;
    top.castShadow = top.receiveShadow = true;
    desk.add(top);
    for (const sx of [-1, 1]) {
        const leg = new Mesh(new RoundedBoxGeometry(0.06, 0.72, 0.7, 3, 0.02), white());
        leg.position.set(sx * 0.92, 0.36, 0);
        leg.castShadow = true;
        desk.add(leg);
    }
    const keyboard = new Mesh(new RoundedBoxGeometry(0.6, 0.025, 0.2, 3, 0.01), gloss('#E7E9EC', 0.4));
    keyboard.position.set(0.15, 0.785, 0.22);
    keyboard.castShadow = true;
    desk.add(keyboard);

    const mon = new Group();
    mon.position.set(0, 0.77, -0.15);
    desk.add(mon);
    const frame = new Mesh(new RoundedBoxGeometry(1.5, 0.96, 0.05, 4, 0.02), new MeshStandardMaterial({ color: '#0C0E12', roughness: 0.3 }));
    frame.position.y = 0.62;
    frame.castShadow = true;
    mon.add(frame);
    const screenMat = new MeshBasicMaterial({ color: '#ffffff', toneMapped: false });
    const screen = new Mesh(new PlaneGeometry(1.42, 0.888), screenMat);
    screen.position.set(0, 0.62, 0.027);
    mon.add(screen);
    // A skeleton of the page that the real page replaces from the top down while the story runs.
    const skeleton = new Mesh(new PlaneGeometry(1.42, 0.888), new MeshBasicMaterial({
        toneMapped: false,
        map: canvasTex(568, 355, (x, w, h) => {
            x.fillStyle = '#F4F5F7'; x.fillRect(0, 0, w, h);
            x.fillStyle = '#DDE0E5';
            x.fillRect(24, 22, 110, 14); x.fillRect(w - 190, 22, 50, 14); x.fillRect(w - 124, 22, 50, 14);
            x.fillRect(24, 70, 300, 30); x.fillRect(24, 112, 220, 14); x.fillRect(24, 134, 260, 14);
            x.fillStyle = '#E8431B'; x.fillRect(24, 170, 90, 26);
            x.fillStyle = '#E3E6EA'; x.fillRect(w - 230, 66, 206, 140);
            for (let i = 0; i < 3; i++) { x.fillStyle = '#E3E6EA'; x.fillRect(24 + i * 180, 240, 164, 90); }
        }),
    }));
    skeleton.position.set(0, 0.62, 0.03);
    mon.add(skeleton);
    const scan = new Mesh(new PlaneGeometry(1.42, 0.012), new MeshBasicMaterial({ color: '#FF5A33', toneMapped: false }));
    scan.position.z = 0.034;
    mon.add(scan);
    const stem = new Mesh(new CylinderGeometry(0.03, 0.03, 0.16, 16), new MeshStandardMaterial({ color: '#2a2e36' }));
    stem.position.y = 0.1;
    const base = new Mesh(new RoundedBoxGeometry(0.4, 0.02, 0.22, 3, 0.01), new MeshStandardMaterial({ color: '#2a2e36' }));
    base.position.y = 0.02;
    mon.add(stem, base);

    // Small cards beside the monitor that appear with the details (a tick on each, no figures).
    const detailCards = [];
    [[-0.95, 1.7, 0.2, 0.2], [0.55, 2.05, -0.35, -0.1], [-0.2, 2.1, -0.5, 0.1]].forEach(([cx, cy, cz, ry], i) => {
        const tex = canvasTex(256, 140, (x, w, h) => {
            x.fillStyle = '#fff'; x.fillRect(0, 0, w, h);
            x.strokeStyle = '#E8431B'; x.lineWidth = 12; x.lineCap = 'round'; x.lineJoin = 'round';
            x.beginPath(); x.moveTo(34, 72); x.lineTo(56, 96); x.lineTo(96, 42); x.stroke();
            x.fillStyle = '#DDE0E5'; x.fillRect(120, 46, 100, 12); x.fillRect(120, 74, 70, 12);
        });
        const card = new Mesh(new RoundedBoxGeometry(0.46, 0.25, 0.02, 3, 0.008), [0, 1, 2, 3, 4, 5].map((s) => (s === 4 ? new MeshStandardMaterial({ map: tex, roughness: 0.4 }) : new MeshStandardMaterial({ color: '#ffffff' }))));
        card.position.set(X[2] + cx, cy, cz);
        card.rotation.y = ry;
        card.castShadow = true;
        card.userData = { base: card.position.clone(), phase: i * 1.7 };
        scene.add(card);
        detailCards.push(card);
    });

    // The magnifier of step 3, held in Kreo's right hand.
    const magnifier = new Group();
    const ring = new Mesh(new TorusGeometry(0.14, 0.02, 14, 40), ink());
    const lens = new Mesh(new SphereGeometry(0.135, 28, 16), new MeshBasicMaterial({ color: '#ffffff', transparent: true, opacity: 0.16, depthWrite: false }));
    lens.scale.z = 0.06;
    const handle = new Mesh(new CapsuleGeometry(0.018, 0.14, 6, 12), ink());
    handle.position.y = -0.24;
    magnifier.add(ring, lens, handle);
    // handle at the hand, the ring toward the monitor (the group is placed in the world after the pose, see update)
    ring.position.y = 0.33;
    lens.position.y = 0.33;
    handle.position.y = 0.1;
    handle.scale.y = 1.2;
    scene.add(magnifier);

    // ---- Station 3: the launch pedestal with the big button and the finished site
    const launch = new Group();
    launch.position.set(X[3], 0, 0);
    scene.add(launch);
    const ped = new Mesh(new CylinderGeometry(0.26, 0.3, 0.52, 48), white());
    ped.position.set(-0.2, 0.26, 0.4);
    ped.castShadow = ped.receiveShadow = true;
    const cap = new Mesh(new CylinderGeometry(0.28, 0.28, 0.04, 48), ink());
    cap.position.set(-0.2, 0.54, 0.4);
    const buttonMat = gloss('#E8431B', 0.2);
    const button = new Mesh(new CylinderGeometry(0.17, 0.19, 0.1, 48), buttonMat);
    button.position.set(-0.2, 0.61, 0.4);
    button.castShadow = true;
    launch.add(ped, cap, button);
    const bigMon = new Group();
    bigMon.position.set(0.85, 0, -0.45);
    bigMon.rotation.y = -0.18;
    launch.add(bigMon);
    const bigFrame = new Mesh(new RoundedBoxGeometry(2.0, 1.28, 0.06, 4, 0.025), new MeshStandardMaterial({ color: '#0C0E12', roughness: 0.3 }));
    bigFrame.position.y = 1.22;
    bigFrame.castShadow = true;
    const bigScreenMat = new MeshBasicMaterial({ color: '#ffffff', toneMapped: false });
    const bigScreen = new Mesh(new PlaneGeometry(1.9, 1.188), bigScreenMat);
    bigScreen.position.set(0, 1.22, 0.034);
    const bigStem = new Mesh(new CylinderGeometry(0.04, 0.04, 0.4, 16), new MeshStandardMaterial({ color: '#2a2e36' }));
    bigStem.position.y = 0.2;
    const bigBase = new Mesh(new RoundedBoxGeometry(0.6, 0.025, 0.3, 3, 0.01), new MeshStandardMaterial({ color: '#2a2e36' }));
    bigBase.position.y = 0.013;
    const glowTex = radialTexture([[0, 'rgba(255,90,51,.9)'], [0.35, 'rgba(255,90,51,.35)'], [1, 'rgba(255,90,51,0)']]);
    const glow = new Mesh(new PlaneGeometry(4.2, 2.4), new MeshBasicMaterial({ map: glowTex, transparent: true, blending: AdditiveBlending, depthWrite: false, toneMapped: false }));
    glow.position.set(0, 1.22, -0.12);
    bigMon.add(bigFrame, bigScreen, bigStem, bigBase, glow);

    // Confetti of squares, thrown by the press and driven by the scroll like everything else.
    const COUNT = 40;
    const confetti = new InstancedMesh(new PlaneGeometry(0.075, 0.075), new MeshBasicMaterial({ side: DoubleSide, toneMapped: false }), COUNT);
    const seeds = Array.from({ length: COUNT }, (_, i) => ({ a: (i * 2.399) % (Math.PI * 2), v: 1.1 + ((i * 37) % 10) / 9, up: 1.4 + ((i * 53) % 10) / 6, s: 0.6 + ((i * 17) % 10) / 14, r: ((i * 29) % 10) - 5 }));
    const palette = ['#E8431B', '#0C0E12', '#ffffff', '#FF5A33'].map((c) => new Color(c));
    seeds.forEach((_, i) => confetti.setColorAt(i, palette[i % 4]));
    confetti.frustumCulled = false;
    confetti.position.set(X[3] - 0.2, 0.75, 0.4);
    scene.add(confetti);
    const dummy = new Object3D();

    // ---- Kreo
    const kreo = makeRobot({ body: 'white', mood: 'happy' });
    kreo.scale.setScalar(SCALE);
    scene.add(kreo);
    const { head, eyes, arms, shadow, hands } = kreo.userData;
    const marker = new Mesh(new CapsuleGeometry(0.014, 0.09, 6, 12), new MeshBasicMaterial({ color: '#0C0E12' }));
    marker.rotation.x = Math.PI / 2;
    marker.position.set(0, -0.02, 0.05);
    hands.r.add(marker);

    const siteTex = { map: null };
    const loadSite = siteImage
        ? new Promise((resolve) => new TextureLoader().load(siteImage, (t) => {
            t.colorSpace = SRGBColorSpace;
            t.anisotropy = 8;
            screenMat.map = bigScreenMat.map = t;
            screenMat.needsUpdate = bigScreenMat.needsUpdate = true;
            siteTex.map = t;
            resolve();
        }, undefined, () => resolve()))
        : Promise.resolve();

    function setTheme(isDark) {
        const tint = isDark ? '#1B1F27' : '#F7F8F9';
        pools.forEach((p) => { p.material.color.set(tint); p.material.opacity = isDark ? 0.95 : 0.9; });
        catcher.material.opacity = isDark ? 0.5 : 0.16;
        key.intensity = isDark ? 2.2 : 2.6;
        hemi.intensity = isDark ? 0.45 : 0.55;
        scene.environmentIntensity = isDark ? 0.8 : 1;
        rim.intensity = isDark ? 1.6 : 1.2;
        lineGlow.material.opacity = isDark ? 0.2 : 0.12;
    }
    setTheme(dark);

    const tmpV = new Vector3();
    const handOffset = new Vector3(-0.3, 0.1, 0.05);
    const camPos = new Vector3();
    const camTarget = new Vector3();
    const smoothHead = { x: 0, y: 0 };
    let aspect = width / height;

    /** t: 0 to 3 (the scene), time: seconds (idle life), px/py: the pointer in -1..1 (the head follows a little), idle: false for the stills. */
    function update(t, time = 0, px = 0, py = 0, idle = true) {
        const i = Math.min(2, Math.floor(t));
        const f = smooth(t - i);
        const f2 = smooth((t - i - 0.08) / 0.9); // Kreo starts a moment after the camera
        const a = POSES[i];
        const b = POSES[i + 1];

        // Camera
        const ca = CAMS[i];
        const cb = CAMS[i + 1];
        camTarget.set(lerp(ca.t[0], cb.t[0], f), lerp(ca.t[1], cb.t[1], f), lerp(ca.t[2], cb.t[2], f));
        const fitH = lerp(ca.h, cb.h, f);
        const fitW = lerp(ca.w, cb.w, f);
        const tan = Math.tan((FOV * Math.PI) / 360);
        const dist = Math.max(fitH / 2 / tan, fitW / 2 / (tan * aspect));
        tmpV.set(lerp(ca.d[0], cb.d[0], f), lerp(ca.d[1], cb.d[1], f), lerp(ca.d[2], cb.d[2], f)).normalize();
        camPos.copy(camTarget).addScaledVector(tmpV, dist);
        camera.position.copy(camPos);
        camera.lookAt(camTarget);

        // Light follows the focus so the shadow map stays sharp
        key.position.set(camTarget.x - 3, 6, 4);
        key.target.position.set(camTarget.x, 0, 0);
        rim.position.set(camTarget.x + 4, 3, -4);

        // Kreo: position and arms blend between the poses, with a small hop while walking
        const kx = lerp(a.x, b.x, f2);
        const hop = idle && f2 > 0 && f2 < 1 ? Math.abs(Math.sin(f2 * Math.PI * 5)) * 0.07 : 0;
        const bob = idle ? Math.sin(time * 1.7) * 0.012 : 0;
        kreo.position.set(kx, hop + bob, lerp(a.z, b.z, f2));
        kreo.rotation.y = lerp(a.ry, b.ry, f2);
        arms.l.rotation.x = lerp(a.l[0], b.l[0], f2);
        arms.l.rotation.z = -(0.18 + lerp(a.l[1], b.l[1], f2));
        arms.r.rotation.x = lerp(a.r[0], b.r[0], f2);
        arms.r.rotation.z = 0.18 + lerp(a.r[1], b.r[1], f2);
        // The typing fingers of step 2 and the press of step 4
        const typing = idle ? Math.sin(time * 14) * 0.04 * smooth(1 - Math.abs(t - 1) * 2) : 0;
        arms.l.rotation.x += typing;
        arms.r.rotation.x -= typing;
        const launchK = smooth((t - 2.55) / 0.45);
        arms.r.rotation.x += launchK * 0.18;

        // Eyes blink, the head follows the pointer a little
        if (idle) {
            smoothHead.x += (px - smoothHead.x) * 0.08;
            smoothHead.y += (py - smoothHead.y) * 0.08;
        }
        head.rotation.y = lerp(a.hy, b.hy, f2) + smoothHead.x * 0.28;
        head.rotation.x = lerp(a.hx, b.hx, f2) - smoothHead.y * 0.12;
        head.rotation.z = Math.sin(time * 0.9) * 0.015;
        eyes.scale.y = idle && (time % 3.7) < 0.13 ? 0.12 : 1;
        shadow.scale.setScalar(1 - hop * 2.2);
        shadow.material.opacity = 1 - hop * 3;

        // Props
        marker.visible = t < 0.7;
        kreo.updateMatrixWorld(true);
        hands.r.getWorldPosition(tmpV);
        magnifier.position.copy(tmpV).add(handOffset);
        magnifier.rotation.set(0, 0, 1.15);
        const mag = smooth((t - 1.6) / 0.35) * (1 - smooth((t - 2.5) / 0.3));
        magnifier.visible = mag > 0.01;
        magnifier.scale.setScalar(Math.max(0.01, mag));
        hands.l.visible = true;

        // The page builds on the monitor from the top down (60% at the first desk view, complete at the details)
        const build = t < 1 ? 0.6 * smooth((t - 0.35) / 0.65) : 0.6 + 0.4 * smooth((t - 1) / 0.75);
        skeleton.scale.y = Math.max(0.0001, 1 - build);
        skeleton.position.y = 0.62 - (0.888 * build) / 2;
        scan.position.y = 0.62 + 0.444 - 0.888 * build;
        scan.visible = build > 0.02 && build < 0.985;
        skeleton.visible = build < 0.999;
        // skeleton texture follows the lower part of the page
        skeleton.material.map.repeat.set(1, 1 - build);
        skeleton.material.map.offset.set(0, 0);

        // Detail cards float in around step 3
        const reveal = smooth((t - 1.55) / 0.5) * (1 - smooth((t - 2.6) / 0.4));
        detailCards.forEach((c) => {
            c.visible = reveal > 0.01;
            c.scale.setScalar(Math.max(0.01, reveal));
            c.position.copy(c.userData.base);
            c.position.y += (idle ? Math.sin(time * 1.3 + c.userData.phase) * 0.03 : 0) + (1 - reveal) * -0.2;
        });

        // Launch: the button goes down, the screen glows, confetti flies
        button.position.y = 0.61 - launchK * 0.045;
        buttonMat.emissive.set('#E8431B');
        buttonMat.emissiveIntensity = launchK * 0.5;
        glow.material.opacity = smooth((t - 2.6) / 0.4);
        glow.visible = glow.material.opacity > 0.01;
        const fall = smooth((t - 2.62) / 0.38);
        confetti.visible = fall > 0.001;
        if (confetti.visible) {
            const tau = fall * 0.85;
            seeds.forEach((s, n) => {
                dummy.position.set(Math.cos(s.a) * s.v * tau * 0.9, s.up * tau - 1.9 * tau * tau, Math.sin(s.a) * s.v * tau * 0.25);
                dummy.rotation.set(s.r * tau, s.r * tau * 0.7, s.r * tau * 0.4);
                dummy.scale.setScalar(s.s);
                dummy.updateMatrix();
                confetti.setMatrixAt(n, dummy.matrix);
            });
            confetti.instanceMatrix.needsUpdate = true;
        }
    }

    function render() {
        renderer.render(scene, camera);
    }

    function resize(w, h) {
        aspect = w / h;
        camera.aspect = aspect;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h, false);
    }

    function dispose() {
        scene.traverse((o) => {
            if (o.geometry) o.geometry.dispose();
            const mats = o.material ? (Array.isArray(o.material) ? o.material : [o.material]) : [];
            mats.forEach((m) => { if (m.map) m.map.dispose(); m.dispose(); });
        });
        env.dispose();
        renderer.dispose();
        renderer.forceContextLoss();
    }

    update(0);

    return { update, render, resize, setTheme, dispose, ready: loadSite, renderer };
}
