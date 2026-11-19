/*
 * Kreo, the Creatium Lab assistant, as a three.js model. Shared by the home page story (g-kreo.js), the AI agents still image and the
 * render script in tools/kreo. Colours are the G tokens only: white glossy body, black glass face, orange eyes and antenna tip.
 * Only what is used is imported from three, so the bundle can be tree-shaken.
 */
import {
    CanvasTexture, CapsuleGeometry, CircleGeometry, CylinderGeometry, Group, LatheGeometry, Mesh, MeshBasicMaterial, MeshPhysicalMaterial,
    PlaneGeometry, SRGBColorSpace, SphereGeometry, TorusGeometry, Vector2,
} from 'three';
import { RoundedBoxGeometry } from 'three/examples/jsm/geometries/RoundedBoxGeometry.js';

export const gloss = (color, roughness = 0.18) => new MeshPhysicalMaterial({ color, roughness, metalness: 0.05, clearcoat: 1, clearcoatRoughness: 0.08 });

export const glass = () => new MeshPhysicalMaterial({ color: '#07080B', roughness: 0.06, metalness: 0.2, clearcoat: 1, clearcoatRoughness: 0.02 });

const BODIES = { white: '#E9EBEE', ink: '#15171D', orange: '#E8431B' };

const radials = {};
/** A soft round blob (alpha falls off from the centre): the contact shadow under a robot and the floor pool of light. */
export function radialTexture(stops) {
    const key = JSON.stringify(stops);
    if (radials[key]) return radials[key];
    const c = document.createElement('canvas');
    c.width = c.height = 128;
    const x = c.getContext('2d');
    const g = x.createRadialGradient(64, 64, 0, 64, 64, 64);
    stops.forEach(([at, color]) => g.addColorStop(at, color));
    x.fillStyle = g;
    x.fillRect(0, 0, 128, 128);
    const tex = new CanvasTexture(c);
    tex.colorSpace = SRGBColorSpace;
    radials[key] = tex;

    return tex;
}

const shadowTex = () => radialTexture([[0, 'rgba(8,10,14,.55)'], [0.45, 'rgba(8,10,14,.22)'], [1, 'rgba(8,10,14,0)']]);

function hand(mat, side) {
    // A rounded mitten: a soft ellipsoid and a small thumb nub.
    const g = new Group();
    const palm = new Mesh(new SphereGeometry(0.074, 28, 20), mat);
    palm.scale.set(1, 1.12, 0.86);
    palm.castShadow = true;
    const thumb = new Mesh(new SphereGeometry(0.031, 18, 14), mat);
    thumb.position.set(side * 0.066, 0.018, 0.034);
    thumb.scale.set(1, 1.25, 1);
    thumb.castShadow = true;
    g.add(palm, thumb);

    return g;
}

/** body: white | ink | orange. mood: happy (arcs) | focus (pills). Returns the group; g.userData has head, eyes, arms, shadow. */
export function makeRobot({ body = 'white', eyes = '#FF7A52', mood = 'happy' } = {}) {
    const g = new Group();
    const mat = gloss(BODIES[body], body === 'white' ? 0.14 : body === 'ink' ? 0.25 : 0.2);
    const capsule = (r, len) => { const m = new Mesh(new CapsuleGeometry(r, len, 10, 28), mat); m.castShadow = true; return m; };

    // Torso: a soft egg, its top is smaller so the neck collar grows out of it.
    const prof = [];
    for (let i = 0; i <= 24; i++) {
        const t = i / 24;
        const r = 0.3 * Math.sin(Math.PI * (0.08 + t * 0.84)) ** 0.55;
        prof.push(new Vector2(Math.max(r, 0.02), t * 0.62));
    }
    const torso = new Mesh(new LatheGeometry(prof, 56), mat);
    torso.position.y = 0.2;
    torso.castShadow = true;
    g.add(torso);
    const chest = new Mesh(new CircleGeometry(0.035, 24), new MeshBasicMaterial({ color: body === 'orange' ? '#ffffff' : '#FF5A33' }));
    chest.position.set(0, 0.52, 0.292);
    g.add(chest);

    // Neck: a short rounded collar. A ring where it leaves the body, a slim stem and a ring under the head, all in the body material.
    const stem = new Mesh(new CylinderGeometry(0.105, 0.12, 0.2, 40), mat);
    stem.position.y = 0.83;
    stem.castShadow = true;
    const lower = new Mesh(new TorusGeometry(0.125, 0.045, 18, 48), mat);
    lower.rotation.x = Math.PI / 2;
    lower.position.y = 0.76;
    const upper = new Mesh(new TorusGeometry(0.115, 0.04, 18, 48), mat);
    upper.rotation.x = Math.PI / 2;
    upper.position.y = 0.92;
    g.add(stem, lower, upper);

    const head = new Group();
    head.position.y = 1.16;
    g.add(head);
    const skull = new Mesh(new RoundedBoxGeometry(0.68, 0.52, 0.56, 10, 0.22), mat);
    skull.castShadow = true;
    head.add(skull);
    const face = new Mesh(new RoundedBoxGeometry(0.55, 0.38, 0.08, 8, 0.12), glass());
    face.position.z = 0.255;
    head.add(face);

    const eyeGroup = new Group();
    eyeGroup.position.set(0, 0.01, 0.3);
    const eyeMat = new MeshBasicMaterial({ color: eyes });
    for (const sx of [-1, 1]) {
        const e = mood === 'happy'
            ? new Mesh(new TorusGeometry(0.055, 0.018, 12, 28, Math.PI), eyeMat)
            : new Mesh(new CapsuleGeometry(0.03, 0.06, 8, 14), eyeMat);
        e.position.x = sx * 0.12;
        eyeGroup.add(e);
    }
    head.add(eyeGroup);

    const ant = capsule(0.012, 0.12);
    ant.position.y = 0.33;
    const tip = new Mesh(new SphereGeometry(0.036, 24, 24), gloss(body === 'orange' ? '#F2F3F5' : '#E8431B'));
    tip.position.y = 0.425;
    head.add(ant, tip);
    for (const sx of [-1, 1]) {
        const ear = new Mesh(new CylinderGeometry(0.06, 0.06, 0.05, 28), mat);
        ear.rotation.z = Math.PI / 2;
        ear.position.x = sx * 0.345;
        ear.castShadow = true;
        head.add(ear);
    }

    // Arms: a shoulder ball, a capsule and a mitten, so the joint is soft.
    const arms = {};
    for (const sx of [-1, 1]) {
        const sh = new Group();
        sh.position.set(sx * 0.29, 0.62, 0);
        const ball = new Mesh(new SphereGeometry(0.068, 24, 18), mat);
        const a = capsule(0.055, 0.22);
        a.position.y = -0.16;
        const h = hand(mat, sx);
        h.position.y = -0.345;
        sh.add(ball, a, h);
        sh.rotation.z = sx * 0.18;
        g.add(sh);
        arms[sx < 0 ? 'l' : 'r'] = sh;
        sh.userData.hand = h;
    }
    for (const sx of [-1, 1]) {
        const l = capsule(0.07, 0.08);
        l.position.set(sx * 0.12, 0.1, 0);
        const f = new Mesh(new SphereGeometry(0.09, 24, 18), mat);
        f.scale.set(1, 0.55, 1.3);
        f.position.set(sx * 0.12, 0.04, 0.03);
        f.castShadow = true;
        g.add(l, f);
    }

    // A soft contact shadow, a little wider than the feet.
    const shadow = new Mesh(new PlaneGeometry(1.1, 1.1), new MeshBasicMaterial({ map: shadowTex(), transparent: true, depthWrite: false, toneMapped: false }));
    shadow.rotation.x = -Math.PI / 2;
    shadow.position.y = 0.006;
    shadow.renderOrder = 2;
    g.add(shadow);

    g.userData = { head, eyes: eyeGroup, arms, shadow, hands: { l: arms.l.userData.hand, r: arms.r.userData.hand } };

    return g;
}
