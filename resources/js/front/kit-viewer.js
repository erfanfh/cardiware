/**
 * Kit Viewer — 3D kit surface (procedural) with Three.js
 * Component: [data-kit3d] with data-texture / data-accent / data-secondary
 * Auto-rotate until first drag; drag with Pointer Events.
 */
import * as THREE from 'three';

export default function initKitViewers() {
    document.querySelectorAll('[data-kit3d]').forEach((el) => {
        try {
            buildViewer(el);
        } catch (err) {
            console.error('kit-viewer:', err);
        }
    });
}

function buildViewer(el) {
    const canvas = el.querySelector('canvas');
    if (!canvas) return;

    const textureUrl = el.dataset.texture || '';
    const accent = el.dataset.accent || '#00ff7a';
    const secondary = el.dataset.secondary || '#1a1a1e';

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();

    const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 50);
    camera.position.set(0, 0.35, 6.4);
    camera.lookAt(0, 0.1, 0);

    // Lighting: ambient + main + Accent rim from behind
    scene.add(new THREE.HemisphereLight(0xffffff, 0x1c2420, 1.35));
    const key = new THREE.DirectionalLight(0xffffff, 1.7);
    key.position.set(3.2, 4.5, 4);
    scene.add(key);
    const rim = new THREE.DirectionalLight(new THREE.Color(accent), 1.1);
    rim.position.set(-4, 1.5, -3.5);
    scene.add(rim);

    // ---------- Kit procedural ----------
    const shirt = new THREE.Group();
    const flat = 0.6; // Body thickness (ratio z)

    const loader = new THREE.TextureLoader();
    const map = loader.load(textureUrl);
    map.colorSpace = THREE.SRGBColorSpace;
    map.anisotropy = 8;

    const bodyMat = new THREE.MeshStandardMaterial({ map, roughness: 0.82, metalness: 0.02 });
    const plainMat = new THREE.MeshStandardMaterial({ color: new THREE.Color(secondary), roughness: 0.85 });

    // Torso: cylinder with kit texture (u=0.5 must face camera)
    const torso = new THREE.Mesh(
        new THREE.CylinderGeometry(0.98, 0.86, 2.15, 30, 1, true),
        bodyMat,
    );
    torso.material.side = THREE.DoubleSide;
    torso.scale.z = flat;
    torso.rotation.y = Math.PI; // Texture center faces camera
    shirt.add(torso);

    // Yoke
    const collar = new THREE.Mesh(new THREE.TorusGeometry(0.5, 0.1, 12, 28), plainMat);
    collar.rotation.x = Math.PI / 2;
    collar.scale.z = flat * 1.25;
    collar.position.y = 1.12;
    shirt.add(collar);

    // Shoulders (torso cap)
    const cap = new THREE.Mesh(
        new THREE.SphereGeometry(0.98, 26, 12, 0, Math.PI * 2, 0, Math.PI / 2),
        plainMat,
    );
    cap.scale.z = flat;
    cap.position.y = 1.06;
    shirt.add(cap);

    // Sleeves
    [-1, 1].forEach((side) => {
        const sleeve = new THREE.Mesh(
            new THREE.CylinderGeometry(0.34, 0.3, 0.95, 18),
            plainMat,
        );
        sleeve.position.set(side * 1.16, 0.72, 0);
        sleeve.rotation.z = side * -1.05;
        shirt.add(sleeve);
        const cuff = new THREE.Mesh(
            new THREE.TorusGeometry(0.3, 0.05, 10, 20),
            new THREE.MeshStandardMaterial({ color: new THREE.Color(accent), roughness: 0.7 }),
        );
        cuff.rotation.x = Math.PI / 2;
        cuff.rotation.y = side * 1.05;
        cuff.position.set(side * 1.58, 0.5, 0);
        shirt.add(cuff);
    });

    // Bottom hem
    const hem = new THREE.Mesh(new THREE.TorusGeometry(0.87, 0.045, 10, 28), plainMat);
    hem.rotation.x = Math.PI / 2;
    hem.scale.z = flat * 1.15;
    hem.position.y = -1.07;
    shirt.add(hem);

    scene.add(shirt);

    // Debug/test hook: expose internal rotation
    el.__kitViewer = {
        get rotationY() { return shirt.rotation.y; },
        get rotationX() { return shirt.rotation.x; },
        get autoSpin() { return autoSpin; },
    };

    // ---------- Interaction: drag + Auto-rotation ----------
    let autoSpin = true;
    let dragging = false;
    let lastX = 0;
    let lastY = 0;
    let velY = 0.0045;

    el.addEventListener('pointerdown', (e) => {
        dragging = true;
        autoSpin = false;
        lastX = e.clientX;
        lastY = e.clientY;
        el.setPointerCapture(e.pointerId);
        el.classList.add('is-grabbing');
        e.preventDefault();
    });
    el.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        const dx = e.clientX - lastX;
        const dy = e.clientY - lastY;
        lastX = e.clientX;
        lastY = e.clientY;
        shirt.rotation.y += dx * 0.011;
        shirt.rotation.x = THREE.MathUtils.clamp(shirt.rotation.x + dy * 0.006, -0.55, 0.55);
        velY = dx * 0.0022;
    });
    const endDrag = () => {
        dragging = false;
        el.classList.remove('is-grabbing');
    };
    el.addEventListener('pointerup', endDrag);
    el.addEventListener('pointercancel', endDrag);

    // ---------- Resize and render loop ----------
    const resize = () => {
        const w = el.clientWidth;
        const h = el.clientHeight;
        if (!w || !h) return;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };
    resize();
    if (window.ResizeObserver) new ResizeObserver(resize).observe(el);

    const tick = () => {
        if (!dragging) {
            if (autoSpin) {
                shirt.rotation.y += 0.0045;
            } else {
                // Inertia after release
                shirt.rotation.y += velY;
                velY *= 0.94;
            }
        }
        renderer.render(scene, camera);
        requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}
