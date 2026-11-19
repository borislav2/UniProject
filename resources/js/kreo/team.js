/*
 * The AI agents team still: three robots (ink = sales, white Kreo = support, orange = the internal team) with a name plate each.
 * Never loaded by a page (no WebGL on /uslugi/ai-agenti): tools/kreo/render.mjs renders it to public/images/kreo/team-*.webp.
 */
import {
    ACESFilmicToneMapping, CanvasTexture, DirectionalLight, HemisphereLight, Mesh, MeshBasicMaterial, PCFSoftShadowMap, PMREMGenerator, PerspectiveCamera,
    PlaneGeometry, SRGBColorSpace, Scene, ShadowMaterial, Sprite, SpriteMaterial, WebGLRenderer,
} from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { makeRobot, radialTexture } from './robot.js';

export function createTeam({ canvas, width, height, pixelRatio = 1, dark = false, labels = [] } = {}) {
    const renderer = new WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(pixelRatio);
    renderer.setSize(width, height, false);
    renderer.setClearColor(0x000000, 0);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = PCFSoftShadowMap;
    renderer.toneMapping = ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.outputColorSpace = SRGBColorSpace;

    const scene = new Scene();
    const pmrem = new PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    const camera = new PerspectiveCamera(30, width / height, 0.1, 60);

    const key = new DirectionalLight('#ffffff', 2.6);
    key.position.set(-3, 6, 4);
    key.castShadow = true;
    key.shadow.mapSize.set(2048, 2048);
    key.shadow.radius = 8;
    key.shadow.blurSamples = 20;
    Object.assign(key.shadow.camera, { left: -5, right: 5, top: 5, bottom: -5, near: 0.5, far: 20 });
    key.shadow.bias = -0.0004;
    const rim = new DirectionalLight('#ffd9cc', 1.2);
    rim.position.set(4, 3, -4);
    scene.add(key, rim, new HemisphereLight('#ffffff', '#c9cdd3', 0.55));
    scene.environmentIntensity = dark ? 0.8 : 1;
    if (dark) { key.intensity = 2.2; rim.intensity = 1.6; }

    const pool = new Mesh(new PlaneGeometry(9, 5), new MeshBasicMaterial({
        map: radialTexture([[0, 'rgba(255,255,255,1)'], [0.4, 'rgba(255,255,255,.5)'], [0.85, 'rgba(255,255,255,0)']]),
        color: dark ? '#1B1F27' : '#F7F8F9', transparent: true, opacity: dark ? 0.95 : 0.9, depthWrite: false, toneMapped: false,
    }));
    pool.rotation.x = -Math.PI / 2;
    pool.position.set(1.3, 0.001, 0.1);
    const catcher = new Mesh(new PlaneGeometry(30, 12), new ShadowMaterial({ opacity: dark ? 0.5 : 0.16 }));
    catcher.rotation.x = -Math.PI / 2;
    catcher.position.y = 0.002;
    catcher.receiveShadow = true;
    scene.add(pool, catcher);

    const team = [['ink', '#ECEEF0', 'focus', 0.2], ['white', '#FF7A52', 'happy', 1.3], ['orange', '#ffffff', 'focus', 2.4]];
    team.forEach(([body, eyes, mood, x], i) => {
        const k = makeRobot({ body, eyes, mood });
        k.position.set(x, 0, i === 1 ? 0.35 : 0);
        k.rotation.y = (1 - i) * 0.34 - 0.1;
        k.scale.setScalar(1.12);
        scene.add(k);
        const { arms, head } = k.userData;
        if (i === 0) { arms.l.rotation.x = -0.6; head.rotation.y = 0.28; }
        if (i === 1) { arms.r.rotation.z = 2.4; head.rotation.z = 0.06; }
        if (i === 2) { arms.r.rotation.x = -0.7; head.rotation.y = -0.28; }

        const text = labels[i];
        if (text) {
            const c = document.createElement('canvas');
            c.width = 560;
            c.height = 120;
            const g = c.getContext('2d');
            g.fillStyle = 'rgba(255,255,255,.96)';
            g.beginPath();
            g.roundRect(4, 4, 552, 112, 22);
            g.fill();
            g.strokeStyle = '#0C0E12';
            g.lineWidth = 4;
            g.stroke();
            g.fillStyle = '#0C0E12';
            g.font = '500 44px "JetBrains Mono", monospace';
            g.textAlign = 'center';
            g.fillText(text.toUpperCase(), 280, 76);
            const tex = new CanvasTexture(c);
            tex.colorSpace = SRGBColorSpace;
            const s = new Sprite(new SpriteMaterial({ map: tex, toneMapped: false }));
            s.scale.set(0.84, 0.18, 1);
            s.position.set(x, 1.95, i === 1 ? 0.35 : 0);
            scene.add(s);
        }
    });

    camera.position.set(1.3, 1.25, 5.3);
    camera.lookAt(1.3, 0.97, 0);

    return {
        update() {},
        render() { renderer.render(scene, camera); },
        dispose() { renderer.dispose(); },
    };
}
