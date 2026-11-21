/* =========================================================================
   Bibliothèque 3D — les livres de l'auteur en anneau, qui tourne au défilement.
   Chargé à la demande (import dynamique) : n'alourdit pas la page d'accueil.
   ========================================================================= */
import * as THREE from '../vendor/three/three.module.min.js';

const css = (n) => getComputedStyle(document.documentElement).getPropertyValue(n).trim();

/* Compose chaque couverture sur un canevas 2:3 : image sans déformation,
   posée sur un fond flouté si elle est carrée ou trop petite ; carte typographique sinon. */
function coverCanvas(book, img) {
  const W = 512, H = 768;
  const c = document.createElement('canvas'); c.width = W; c.height = H;
  const x = c.getContext('2d');
  const ink = css('--ink') || '#0e0c0a', ocre = css('--ocre') || '#b8860b', cream = css('--cream') || '#fbfaf6';
  if (img) {
    const r = img.width / img.height;
    if (Math.abs(r - 2 / 3) < 0.07 && img.width >= 300) {
      x.drawImage(img, 0, 0, W, H);
    } else {
      x.filter = 'blur(24px) saturate(1.2)';
      const s = Math.max(W / img.width, H / img.height) * 1.2;
      x.drawImage(img, (W - img.width * s) / 2, (H - img.height * s) / 2, img.width * s, img.height * s);
      x.filter = 'none';
      x.fillStyle = 'rgba(14,12,10,.25)'; x.fillRect(0, 0, W, H);
      const k = Math.min((W * .86) / img.width, (H * .86) / img.height);
      const w = img.width * k, h = img.height * k;
      x.shadowColor = 'rgba(0,0,0,.45)'; x.shadowBlur = 30; x.shadowOffsetY = 12;
      x.drawImage(img, (W - w) / 2, (H - h) / 2, w, h);
      x.shadowColor = 'transparent';
    }
  } else {
    x.fillStyle = ink; x.fillRect(0, 0, W, H);
    x.strokeStyle = ocre; x.lineWidth = 3;
    [[580, 0], [620, 34]].forEach(([y, d]) => { x.beginPath(); x.moveTo(-20, y + d); x.bezierCurveTo(180, 470 + d, 330, 780 + d, 540, 560 + d); x.stroke(); });
    x.fillStyle = ocre; x.font = '700 18px Manrope, sans-serif'; x.letterSpacing = '4px';
    x.fillText((book.a || '').toUpperCase(), 46, 80);
    x.fillStyle = cream; x.font = '400 46px Fraunces, Georgia, serif'; x.letterSpacing = '0px';
    const words = book.t.split(' '); let line = '', y = 160;
    words.forEach((w) => { const test = line ? line + ' ' + w : w; if (x.measureText(test).width > W - 92) { x.fillText(line, 46, y); line = w; y += 54; } else line = test; });
    x.fillText(line, 46, y);
    x.globalAlpha = .75; x.font = '600 18px Manrope, sans-serif';
    x.fillText((book.p || '').toUpperCase().slice(0, 34), 46, H - 60);
    x.globalAlpha = 1;
  }
  // reflet de reliure
  const g = x.createLinearGradient(0, 0, 40, 0);
  g.addColorStop(0, 'rgba(0,0,0,.35)'); g.addColorStop(.3, 'rgba(255,255,255,.18)'); g.addColorStop(1, 'rgba(0,0,0,0)');
  x.fillStyle = g; x.fillRect(0, 0, 40, H);
  return c;
}

function edgesCanvas() {
  const c = document.createElement('canvas'); c.width = 64; c.height = 256;
  const x = c.getContext('2d');
  x.fillStyle = '#efe6d2'; x.fillRect(0, 0, 64, 256);
  for (let i = 0; i < 64; i += 2) { x.fillStyle = `rgba(120,100,70,${Math.random() * .25})`; x.fillRect(i, 0, 1, 256); }
  return c;
}

function spineCanvas(book, color) {
  const c = document.createElement('canvas'); c.width = 64; c.height = 768;
  const x = c.getContext('2d');
  x.fillStyle = color || '#1a1612'; x.fillRect(0, 0, 64, 768);
  x.fillStyle = 'rgba(0,0,0,.35)'; x.fillRect(0, 0, 64, 768);
  x.save(); x.translate(40, 40); x.rotate(Math.PI / 2);
  x.fillStyle = '#fbfaf6'; x.font = '400 26px Fraunces, Georgia, serif';
  x.fillText(book.t.length > 40 ? book.t.slice(0, 38) + '…' : book.t, 0, 0);
  x.restore();
  return c;
}

const loadImg = (src) => new Promise((res) => {
  if (!src) return res(null);
  const i = new Image(); i.crossOrigin = 'anonymous'; i.decoding = 'async';
  i.onload = () => res(i); i.onerror = () => res(null); i.src = src;
});

export default async function init(section) {
  const canvas = section.querySelector('.library__canvas');
  const data = JSON.parse(section.querySelector('[data-library-data]').textContent);
  const author = document.querySelector('.brand')?.textContent.trim() || '';
  data.forEach((b) => (b.a = author));
  const titleEl = section.querySelector('[data-lib-title]'), metaEl = section.querySelector('[data-lib-meta]');

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(devicePixelRatio, 1.75));
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;

  const scene = new THREE.Scene();
  const inkColor = new THREE.Color(css('--ink') || '#0e0c0a');
  scene.fog = new THREE.Fog(inkColor, 9, 22);
  const camera = new THREE.PerspectiveCamera(34, 1, .1, 100);

  scene.add(new THREE.HemisphereLight(0xfff4e0, 0x1a120a, 1.4));
  const key = new THREE.DirectionalLight(0xfff1dc, 2.4); key.position.set(2, 6, 9); scene.add(key);
  const rim = new THREE.PointLight(new THREE.Color(css('--ocre') || '#b8860b'), 60, 30); rim.position.set(-6, 3, -2); scene.add(rim);
  const warm = new THREE.PointLight(new THREE.Color(css('--red') || '#cc0000'), 25, 18); warm.position.set(5, -2, 4); scene.add(warm);

  const N = data.length;
  const R = Math.max(5.2, N * 0.34);
  const ring = new THREE.Group(); scene.add(ring);
  const geo = new THREE.BoxGeometry(1.4, 2.1, 0.2);
  const edgeTex = new THREE.CanvasTexture(edgesCanvas()); edgeTex.colorSpace = THREE.SRGBColorSpace;
  const edgeMat = new THREE.MeshStandardMaterial({ map: edgeTex, roughness: .9 });
  const backMat = new THREE.MeshStandardMaterial({ color: 0x1b1611, roughness: .6 });
  const maxAniso = renderer.capabilities.getMaxAnisotropy();

  await document.fonts?.ready;
  const imgs = await Promise.all(data.map((b) => loadImg(b.c)));
  const books = data.map((b, i) => {
    const tex = new THREE.CanvasTexture(coverCanvas(b, imgs[i]));
    tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = maxAniso;
    const spine = new THREE.CanvasTexture(spineCanvas(b, b.col)); spine.colorSpace = THREE.SRGBColorSpace;
    const front = new THREE.MeshStandardMaterial({ map: tex, roughness: .48, metalness: .02 });
    const spineMat = new THREE.MeshStandardMaterial({ map: spine, roughness: .55 });
    // ordre des faces : +x, -x, +y, -y, +z (recto), -z (verso)
    const mesh = new THREE.Mesh(geo, [edgeMat, spineMat, edgeMat, edgeMat, front, backMat]);
    const a = (i / N) * Math.PI * 2;
    const pivot = new THREE.Group();
    pivot.rotation.y = -a;
    mesh.position.set(0, 0, R);
    mesh.userData = { i, book: b, lift: 0, baseY: Math.sin(i * 1.7) * .18 };
    pivot.add(mesh); ring.add(pivot);
    return mesh;
  });

  // Anneau ocre et poussière dorée
  const ringLine = new THREE.Mesh(new THREE.TorusGeometry(R, .006, 6, 220), new THREE.MeshBasicMaterial({ color: new THREE.Color(css('--ocre') || '#b8860b'), transparent: true, opacity: .7 }));
  ringLine.rotation.x = Math.PI / 2; ringLine.position.y = -1.35; scene.add(ringLine);
  const dustGeo = new THREE.BufferGeometry(); const P = 520; const pos = new Float32Array(P * 3);
  for (let i = 0; i < P; i++) { const r = 2 + Math.random() * (R + 6), t = Math.random() * Math.PI * 2; pos[i * 3] = Math.cos(t) * r; pos[i * 3 + 1] = (Math.random() - .5) * 9; pos[i * 3 + 2] = Math.sin(t) * r; }
  dustGeo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  const dust = new THREE.Points(dustGeo, new THREE.PointsMaterial({ color: new THREE.Color(css('--ocre') || '#b8860b'), size: .035, transparent: true, opacity: .75, depthWrite: false }));
  scene.add(dust);

  /* Taille et cadrage : la caméra regarde l'anneau de face, légèrement au-dessus */
  const resize = () => {
    const w = canvas.clientWidth, h = canvas.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    const mobile = w < 760;
    camera.fov = mobile ? 48 : 32;
    camera.position.set(mobile ? 0 : 1.2, mobile ? 1.5 : 1.25, R + (mobile ? 8.2 : 8.4));
    camera.lookAt(mobile ? 0 : 1.6, mobile ? -0.1 : -0.25, R - 1);
    camera.updateProjectionMatrix();
  };
  resize(); addEventListener('resize', resize);

  /* Défilement → rotation de l'anneau */
  let target = 0, current = 0, front = -1;
  const ST = window.ScrollTrigger;
  if (ST) ST.create({ trigger: section, start: 'top top', end: 'bottom bottom', scrub: true, onUpdate: (s) => { target = s.progress; } });

  /* Survol / clic */
  const ray = new THREE.Raycaster(); const mouse = new THREE.Vector2(9, 9); let hovered = null, px = 0, py = 0;
  canvas.addEventListener('pointermove', (e) => {
    const r = canvas.getBoundingClientRect();
    mouse.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
    px = mouse.x; py = mouse.y;
  });
  canvas.addEventListener('pointerleave', () => { mouse.set(9, 9); px = py = 0; });
  canvas.addEventListener('click', () => { if (hovered) location.href = hovered.userData.book.u; });
  section.querySelector('.library__overlay').style.pointerEvents = 'none';
  section.querySelectorAll('.library__overlay a').forEach((a) => (a.style.pointerEvents = 'auto'));

  const setFront = (i) => {
    if (i === front) return; front = i;
    const b = data[i];
    titleEl.textContent = b.t; metaEl.textContent = [b.y, b.p].filter(Boolean).join(' · ');
    window.gsap?.fromTo([titleEl, metaEl], { yPercent: 35, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, duration: .6, ease: 'expo.out', stagger: .05, overwrite: true });
  };

  let visible = true, raf = 0; const clock = new THREE.Clock();
  new IntersectionObserver(([en]) => { visible = en.isIntersecting; if (visible && !raf) raf = requestAnimationFrame(tick); }).observe(section);

  const camBase = camera.position.clone();
  function tick() {
    raf = 0;
    if (!visible) return;
    const t = clock.getElapsedTime();
    current += (target - current) * 0.08;
    const rot = current * Math.PI * 2 * (N - 1) / N;
    ring.rotation.y = rot + Math.sin(t * .2) * .02;
    const idx = ((Math.round(rot / (Math.PI * 2 / N)) % N) + N) % N;
    setFront(idx);

    ray.setFromCamera(mouse, camera);
    const hit = ray.intersectObjects(books, false)[0];
    hovered = hit ? hit.object : null;
    canvas.style.cursor = hovered ? 'pointer' : '';
    const cur = window.__cursor;
    if (cur) { cur.classList.toggle('is-label', !!hovered); if (hovered) cur.querySelector('em').textContent = 'Voir'; }

    books.forEach((m) => {
      const isFront = m.userData.i === idx;
      const goal = (m === hovered ? .55 : 0) + (isFront ? .35 : 0);
      m.userData.lift += (goal - m.userData.lift) * .1;
      m.position.y = m.userData.baseY + Math.sin(t * .8 + m.userData.i) * .06 + m.userData.lift * .5;
      m.position.z = R + m.userData.lift;
      m.rotation.y = Math.sin(t * .5 + m.userData.i) * .05 - (isFront ? 0 : .12);
    });
    dust.rotation.y = t * .015;
    camera.position.x = camBase.x + px * .5;
    camera.position.y = camBase.y + py * .3;
    renderer.render(scene, camera);
    raf = requestAnimationFrame(tick);
  }
  raf = requestAnimationFrame(tick);
  if (window.gsap) window.gsap.from(canvas, { autoAlpha: 0, duration: 1.6, ease: 'power2.out' });
}
