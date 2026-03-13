<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Summer Scene</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { overflow: hidden; background: #87CEEB; }
  canvas { display: block; }
  #info {
    position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%);
    color: white; font-family: sans-serif; font-size: 13px;
    background: rgba(0,0,0,0.35); padding: 6px 18px; border-radius: 20px;
    pointer-events: none;
  }
</style>
</head>
<body>
<div id="info">🌿 Summer Scene — Drag to rotate | Scroll to zoom</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// ─── SCENE SETUP ──────────────────────────────────────────────
const scene = new THREE.Scene();
scene.background = new THREE.Color(0x87CEEB);
scene.fog = new THREE.Fog(0x87CEEB, 40, 120);

const camera = new THREE.PerspectiveCamera(55, innerWidth/innerHeight, 0.1, 200);
camera.position.set(0, 6, 18);
camera.lookAt(0, 1, 0);

const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(innerWidth, innerHeight);
renderer.setPixelRatio(window.devicePixelRatio);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
document.body.appendChild(renderer.domElement);

// ─── LIGHTS ───────────────────────────────────────────────────
const sun = new THREE.DirectionalLight(0xfff5e0, 1.4);
sun.position.set(10, 20, 10);
sun.castShadow = true;
sun.shadow.mapSize.set(2048, 2048);
sun.shadow.camera.near = 0.1;
sun.shadow.camera.far = 50;
sun.shadow.camera.left = -15; sun.shadow.camera.right = 15;
sun.shadow.camera.top = 15; sun.shadow.camera.bottom = -15;
scene.add(sun);
const ambientLight = new THREE.AmbientLight(0xc9e8ff, 0.7);
scene.add(ambientLight);
const fillLight = new THREE.PointLight(0xffffff, 0.4, 40);
fillLight.position.set(-8, 8, 8);
scene.add(fillLight);

// ─── CELESTIAL OBJECTS (Sun/Moon) ─────────────────────────────
const celestialGroup = new THREE.Group();
const sunMesh = new THREE.Mesh(
  new THREE.SphereGeometry(2.5, 32, 16),
  new THREE.MeshBasicMaterial({ color: 0xffdd88, fog: false })
);
sunMesh.name = 'sun';
celestialGroup.add(sunMesh);

const moonMesh = new THREE.Mesh(
  new THREE.SphereGeometry(2.2, 32, 16),
  new THREE.MeshBasicMaterial({ color: 0xe0e0ff, fog: false })
);
moonMesh.name = 'moon';
moonMesh.visible = false;
celestialGroup.add(moonMesh);

scene.add(celestialGroup);

// ─── HELPERS ──────────────────────────────────────────────────
function mat(color, opts={}) {
  return new THREE.MeshLambertMaterial({ color, ...opts });
}
function box(w, h, d, color, opts) {
  const m = new THREE.Mesh(new THREE.BoxGeometry(w,h,d), mat(color, opts));
  m.castShadow = true; m.receiveShadow = true; return m;
}
function cyl(rt, rb, h, seg, color, opts) {
  const m = new THREE.Mesh(new THREE.CylinderGeometry(rt,rb,h,seg), mat(color, opts));
  m.castShadow = true; m.receiveShadow = true; return m;
}
function add(parent, child, x=0, y=0, z=0) {
  child.position.set(x,y,z); parent.add(child); return child;
}
function textMat(text, opts={}) {
  const { font, color, width, height } = {
    font: 'bold 48px sans-serif',
    color: 'white',
    width: 256,
    height: 64,
    ...opts
  };
  const canvas = document.createElement('canvas');
  canvas.width = width;
  canvas.height = height;
  const ctx = canvas.getContext('2d');
  ctx.font = font;
  ctx.fillStyle = color;
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText(text, width / 2, height / 2);
  const texture = new THREE.CanvasTexture(canvas);
  return new THREE.MeshBasicMaterial({ map: texture, transparent: true });
}

// ─── GROUND ───────────────────────────────────────────────────
const ground = new THREE.Mesh(
  new THREE.PlaneGeometry(180, 180, 30, 30),
  new THREE.MeshLambertMaterial({ color: 0x2d8a2d })
);
ground.rotation.x = -Math.PI/2;
ground.receiveShadow = true;
scene.add(ground);

// Ground detail — darker patches
for(let i=0;i<30;i++){
  const patch = new THREE.Mesh(
    new THREE.CircleGeometry(Math.random()*2+0.5, 8),
    new THREE.MeshLambertMaterial({ color: 0x267326 })
  );
  patch.rotation.x = -Math.PI/2;
  patch.position.set((Math.random()-0.5)*60, 0.01, (Math.random()-0.5)*60);
  scene.add(patch);
}

// ─── PLATFORM ─────────────────────────────────────────────────
const platform = cyl(5.5, 5.8, 0.5, 40, 0x8fba6a);
platform.position.set(0, 0.25, 0);
scene.add(platform);

// Platform rim
const rim = cyl(5.6, 5.6, 0.15, 40, 0x7aaa55);
rim.position.set(0, 0.55, 0);
scene.add(rim);

// Gravel ring
const gravel = cyl(6.2, 6.2, 0.05, 40, 0xb0a090);
gravel.position.set(0, 0.03, 0);
scene.add(gravel);

// ─── PATHWAY ──────────────────────────────────────────────────
for(let i=0; i<55; i++){
  const step = box(3.0, 0.1, 1.0, i%2===0 ? 0xd4c4a8 : 0xbcad94);
  step.position.set(0, 0.04, 6.5 + i*1.1);
  scene.add(step);
}

// ─── GAZEBO ───────────────────────────────────────────────────
const gazeboRoot = new THREE.Group();
gazeboRoot.position.set(0, 0.5, 0);
scene.add(gazeboRoot);

// Walls (cylindrical base)

// Roof — cone
const roof = cyl(0.2, 4.1, 2.5, 40, 0x5dc87a);
roof.position.y = 3.45;
gazeboRoot.add(roof);

// Roof underside
const roofUnder = new THREE.Mesh(
  new THREE.ConeGeometry(4.1, 2.5, 40, 1, true),
  new THREE.MeshLambertMaterial({ color: 0x4db860, side: THREE.BackSide })
);
roofUnder.position.y = 3.45;
gazeboRoot.add(roofUnder);

// Roof tip
const tip = cyl(0.12, 0.12, 0.3, 8, 0x3da050);
tip.position.y = 4.75;
gazeboRoot.add(tip);

// Pillars
for(let i=0; i<8; i++){
  const angle = (i/8)*Math.PI*2;
  const pillar = cyl(0.12, 0.12, 2.2, 8, 0x5ab87a);
  pillar.position.set(Math.cos(angle)*3.6, 1.1, Math.sin(angle)*3.6);
  gazeboRoot.add(pillar);
}

// Entrance opening — steps
for(let s=0; s<3; s++){
  const st = box(1.6, 0.12, 0.35, 0x8a7a66);
  st.position.set(0, s*0.12, 3.9 - s*0.38);
  gazeboRoot.add(st);
}

// ─── CURVED SCREENS (left & right) ────────────────────────────
function curvedScreen(side) {
  const curve = new THREE.QuadraticBezierCurve3(
    new THREE.Vector3(side*4, 0, 8),
    new THREE.Vector3(side*14, 0, 2),
    new THREE.Vector3(side*14, 0, -8)
  );
  const pts = curve.getPoints(20);
  const shape = new THREE.Shape();
  shape.moveTo(0, 0);
  shape.lineTo(0, 5.5);
  shape.lineTo(0.01, 5.5);
  shape.lineTo(0.01, 0);
  shape.closePath();
  const extrudeSettings = { steps: 20, bevelEnabled: false,
    extrudePath: new THREE.CatmullRomCurve3(pts) };
  const geo = new THREE.ExtrudeGeometry(shape, extrudeSettings);
  const mesh = new THREE.Mesh(geo,
    new THREE.MeshLambertMaterial({ color: 0x66ffaa, transparent: true, opacity: 0.75, side: THREE.DoubleSide }));
  mesh.castShadow = false;
  scene.add(mesh);
}
curvedScreen(1);
curvedScreen(-1);

// ─── TABLE ────────────────────────────────────────────────────
const tableRoot = new THREE.Group();
tableRoot.position.set(0, 0.5, 0.3);
scene.add(tableRoot);

// Tabletop
const tabletop = box(2.2, 0.1, 1.3, 0x8B5E3C);
tabletop.position.y = 1.55;
tableRoot.add(tabletop);

// Table legs
const legPositions = [[0.9,1.3,-0.5],[0.9,1.3,0.5],[-0.9,1.3,-0.5],[-0.9,1.3,0.5]];
legPositions.forEach(([x,,z]) => {
  const leg = cyl(0.06, 0.06, 1.45, 8, 0x6B4226);
  leg.position.set(x, 0.82, z); tableRoot.add(leg);
  const foot = cyl(0.09, 0.09, 0.05, 8, 0x5a3820);
  foot.position.set(x, 0.07, z); tableRoot.add(foot);
});

// Table cross brace
const brace1 = box(1.9, 0.05, 0.05, 0x6B4226);
brace1.position.set(0, 0.5, -0.5); tableRoot.add(brace1);
const brace2 = box(1.9, 0.05, 0.05, 0x6B4226);
brace2.position.set(0, 0.5, 0.5); tableRoot.add(brace2);

// ─── MILO TIN ─────────────────────────────────────────────────
const miloRoot = new THREE.Group();
miloRoot.position.set(-0.5, 1.6, -0.15);
tableRoot.add(miloRoot);

const miloBody = cyl(0.13, 0.13, 0.38, 20, 0x3d2a0a);
miloBody.position.y = 0.19; miloRoot.add(miloBody);
const miloLabel = cyl(0.135, 0.135, 0.28, 20, 0x2e7d32); // green label
miloLabel.position.y = 0.22; miloRoot.add(miloLabel);
const miloLid = cyl(0.14, 0.14, 0.04, 20, 0x1a1a1a);
miloLid.position.y = 0.4; miloRoot.add(miloLid);
const miloBottom = cyl(0.135, 0.135, 0.04, 20, 0x1a1a1a);
miloBottom.position.y = 0.02; miloRoot.add(miloBottom);
// "MILO" label highlight
const miloStripe = cyl(0.136, 0.136, 0.06, 20, 0xf9a825);
miloStripe.position.y = 0.28; miloRoot.add(miloStripe);
const miloText = new THREE.Mesh(
  new THREE.PlaneGeometry(0.18, 0.05),
  textMat('MILO', { color: '#d50000' }) // Red text
);
miloText.position.set(0, 0.28, 0.137);
miloRoot.add(miloText);

// ─── MILK TIN ─────────────────────────────────────────────────
const milkRoot = new THREE.Group();
milkRoot.position.set(0.5, 1.6, -0.15);
tableRoot.add(milkRoot);

const milkBody = cyl(0.12, 0.12, 0.36, 20, 0xffffff);
milkBody.position.y = 0.18; milkRoot.add(milkBody);
const milkLabel = cyl(0.125, 0.125, 0.26, 20, 0xe3f2fd); // light blue
milkLabel.position.y = 0.2; milkRoot.add(milkLabel);
const milkRed = cyl(0.126, 0.126, 0.05, 20, 0xc62828); // red stripe
milkRed.position.y = 0.28; milkRoot.add(milkRed);
const milkText = new THREE.Mesh(
  new THREE.PlaneGeometry(0.16, 0.04),
  textMat('MILK')
);
milkText.position.set(0, 0.28, 0.127);
milkRoot.add(milkText);
const milkLid = cyl(0.13, 0.13, 0.04, 20, 0xcccccc);
milkLid.position.y = 0.38; milkRoot.add(milkLid);
const milkBottom = cyl(0.125, 0.125, 0.04, 20, 0xcccccc);
milkBottom.position.y = 0.02; milkRoot.add(milkBottom);

// ─── CUPS ─────────────────────────────────────────────────────
function makeCup(color, x, z) {
  const g = new THREE.Group();
  const body = cyl(0.09, 0.07, 0.16, 16, color);
  body.position.y = 0.08; g.add(body);
  const rim2 = cyl(0.095, 0.095, 0.015, 16, 0xffffff);
  rim2.position.y = 0.165; g.add(rim2);
  const base = cyl(0.07, 0.07, 0.015, 16, color);
  base.position.y = 0.008; g.add(base);
  // handle
  const handle = new THREE.Mesh(
    new THREE.TorusGeometry(0.045, 0.012, 8, 12, Math.PI),
    mat(color)
  );
  handle.rotation.y = Math.PI/2;
  handle.rotation.z = Math.PI/2;
  handle.position.set(0.1, 0.09, 0);
  g.add(handle);
  g.position.set(x, 1.6, z);
  return g;
}

tableRoot.add(makeCup(0xf5e6c8, -0.2, 0.25));
tableRoot.add(makeCup(0xffe0b2, 0.2, 0.25));

// ─── SAUCER under each cup ─────────────────────────────────────
function saucer(x, z) {
  const s = cyl(0.13, 0.12, 0.025, 16, 0xf5f5f5);
  s.position.set(x, 1.595, z); return s;
}
tableRoot.add(saucer(-0.2, 0.25));
tableRoot.add(saucer(0.2, 0.25));

// ─── SPOONS ───────────────────────────────────────────────────
function spoon(x, z, angle) {
  const g = new THREE.Group();
  const handle2 = box(0.007, 0.007, 0.18, 0xd4af37);
  handle2.position.z = 0; g.add(handle2);
  const bowl = cyl(0.025, 0.02, 0.015, 12, 0xd4af37);
  bowl.position.z = 0.1; g.add(bowl);
  g.position.set(x, 1.615, z);
  g.rotation.y = angle;
  return g;
}
tableRoot.add(spoon(-0.2, 0.42, 0.2));
tableRoot.add(spoon(0.2, 0.42, -0.2));

// ─── TABLECLOTH DETAIL ────────────────────────────────────────
const clothCanvas = document.createElement('canvas');
clothCanvas.width = 64; clothCanvas.height = 64;
const cCtx = clothCanvas.getContext('2d');
cCtx.fillStyle = '#ffffff'; cCtx.fillRect(0,0,64,64);
cCtx.fillStyle = '#87CEEB'; cCtx.fillRect(0,0,32,32); cCtx.fillRect(32,32,32,32);
const clothTex = new THREE.CanvasTexture(clothCanvas);
clothTex.wrapS = clothTex.wrapT = THREE.RepeatWrapping;
clothTex.repeat.set(6, 4);
const cloth = new THREE.Mesh(new THREE.BoxGeometry(2.24, 0.01, 1.34), new THREE.MeshLambertMaterial({ map: clothTex }));
cloth.castShadow = true; cloth.receiveShadow = true;
cloth.position.y = 1.61; tableRoot.add(cloth);

// ─── FRUIT PLATE ──────────────────────────────────────────────
const fruitGroup = new THREE.Group();
const plate = cyl(0.5, 0.45, 0.05, 24, 0xffffff);
plate.position.y = 0.025; fruitGroup.add(plate);

// Red Apple
const fApple = new THREE.Mesh(new THREE.SphereGeometry(0.11, 12, 12), mat(0xd50000));
fApple.position.set(0.1, 0.13, 0.1); fruitGroup.add(fApple);

// Green Pear
const fPear = new THREE.Mesh(new THREE.SphereGeometry(0.1, 12, 12), mat(0xaeea00));
fPear.scale.set(1, 1.3, 1);
fPear.position.set(-0.05, 0.15, -0.12); fruitGroup.add(fPear);

// Orange
const fOrange = new THREE.Mesh(new THREE.SphereGeometry(0.12, 12, 12), mat(0xff6d00));
fOrange.position.set(-0.15, 0.14, 0.08); fruitGroup.add(fOrange);

// Banana
const fBanana = new THREE.Mesh(new THREE.TorusGeometry(0.18, 0.035, 5, 12, 1.8), mat(0xffd600));
fBanana.rotation.set(Math.PI/2, 0, -0.5);
fBanana.position.set(0.15, 0.06, -0.1); fruitGroup.add(fBanana);

fruitGroup.scale.set(0.55, 0.55, 0.55);
fruitGroup.position.set(0, 1.61, -0.15);
tableRoot.add(fruitGroup);

// ─── CHAIRS ───────────────────────────────────────────────────
function makeChair(x, z, rotY) {
  const g = new THREE.Group();
  const woodColor = 0x966F33; // Lighter wood
  const cushionColor = 0xFFFACD;

  // Round seat
  const seat = cyl(0.42, 0.42, 0.05, 32, woodColor);
  seat.position.y = 1.05; g.add(seat);

  // Round cushion
  const cushion = cyl(0.40, 0.40, 0.08, 32, cushionColor);
  cushion.position.y = 1.09; g.add(cushion);

  // Tapered Legs
  const legPositions = [[0.3,0.3],[-0.3,0.3],[0.3,-0.3],[-0.3,-0.3]];
  legPositions.forEach(([lx, lz]) => {
    const leg = cyl(0.03, 0.04, 1.05, 8, woodColor);
    leg.position.set(lx, 0.525, lz); g.add(leg);
  });

  // Curved backrest
  const backHeight = 0.6;
  const backY = 1.05 + backHeight / 2;
  const backRadius = 0.42;
  for (let i = 0; i < 7; i++) {
    const angle = -Math.PI / 3 + (i / 6) * (Math.PI * 2 / 3); // 120 deg arc
    const slat = cyl(0.02, 0.02, backHeight, 8, woodColor);
    slat.position.set(
      Math.sin(angle) * backRadius,
      backY,
      -Math.cos(angle) * backRadius
    );
    g.add(slat);
  }

  g.position.set(x, 0.5, z);
  g.rotation.y = rotY;
  return g;
}

scene.add(makeChair(1.45, 0.5, -Math.PI/2)); // right
scene.add(makeChair(-1.45, 0.5, Math.PI/2));  // left

// ─── POTTED PLANTS ────────────────────────────────────────────
function plant(x, z) {
  const g = new THREE.Group();
  const pot = cyl(0.2, 0.25, 0.35, 12, 0xc1440e);
  pot.position.y = 0.175; g.add(pot);
  const soil = cyl(0.19, 0.19, 0.04, 12, 0x3b2507);
  soil.position.y = 0.37; g.add(soil);
  for(let i=0; i<5; i++){
    const angle = (i/5)*Math.PI*2;
    const leaf = cyl(0.22, 0.05, 0.55, 6, 0x2e7d32);
    leaf.position.set(Math.cos(angle)*0.12, 0.65, Math.sin(angle)*0.12);
    leaf.rotation.z = 0.6 * Math.cos(angle);
    leaf.rotation.x = 0.6 * Math.sin(angle);
    g.add(leaf);
  }
  const center = cyl(0.08, 0.08, 0.4, 8, 0x1b5e20);
  center.position.y = 0.6; g.add(center);
  g.position.set(x, 0.5, z);
  return g;
}
scene.add(plant(3.2, -0.5));
scene.add(plant(-3.2, -0.5));
scene.add(plant(2.8, 1.2));
scene.add(plant(-2.8, 1.2));

// ─── HANGING LANTERNS ─────────────────────────────────────────
function lantern(x, y, z) {
  const g = new THREE.Group();
  g.userData.isLantern = true;
  const body2 = cyl(0.12, 0.12, 0.28, 8, 0xfff176, { transparent:true, opacity:0.85 });
  body2.position.y = 0; g.add(body2);
  const top2 = cyl(0.1, 0.1, 0.06, 8, 0xf9a825);
  top2.position.y = 0.17; g.add(top2);
  const bot = cyl(0.1, 0.1, 0.06, 8, 0xf9a825);
  bot.position.y = -0.17; g.add(bot);
  const wire = box(0.01, 0.5, 0.01, 0x888888);
  wire.position.y = 0.42; g.add(wire);
  // glow
  const glow = new THREE.PointLight(0xffeeaa, 0.5, 3);
  g.add(glow);
  g.position.set(x,y,z);
  return g;
}
scene.add(lantern(-2, 3.5, 0));
scene.add(lantern(2, 3.5, 0));
scene.add(lantern(0, 3.7, -2.5));

// ─── PATH LIGHTS ────────────────────────────────────────────
function createPathLight(x, z) {
  const g = new THREE.Group();
  const post = cyl(0.04, 0.04, 0.4, 8, 0x4d4d4d);
  post.position.y = 0.2; g.add(post);
  const lightBox = box(0.12, 0.1, 0.12, 0xfff176);
  lightBox.position.y = 0.45; g.add(lightBox);
  
  const light = new THREE.PointLight(0xffd8a8, 0, 4, 2); // intensity=0 initially
  light.position.y = 0.5;
  light.castShadow = true;
  light.shadow.mapSize.set(64, 64); // Low-res shadow for performance
  g.add(light);
  
  g.position.set(x, 0, z);
  scene.add(g);
  return g;
}
// Stagger lights along the path
for(let i=0; i<15; i++){
  const z = 7.0 + i * 3.8;
  createPathLight(i%2===0 ? 2.0 : -2.0, z);
}

// ─── PATHWAY TREES ──────────────────────────────────────────
const bulbGeo = new THREE.SphereGeometry(0.04, 4, 4);
const bulbMat = new THREE.MeshBasicMaterial({ color: 0xffe082 });
const wireMat = new THREE.LineBasicMaterial({ color: 0x333333 });

for(let i=0; i<12; i++){
  const z = 12.0 + i * 5.5; // Start further down and space them out
  const x = (i%2===0 ? 4.5 : -4.5) + (Math.random()-0.5); // Staggered, further from path
  createTree(x, z, 0.5 + Math.random()*0.3); // Small scale
  const s = 0.5 + Math.random()*0.3; // Small scale
  const t = createTree(x, z, s);
  
  // Wired Lights (Spiral)
  const points = [];
  const turns = 4;
  for(let j=0; j<=40; j++) {
    const p = j/40;
    const angle = p * Math.PI * 2 * turns;
    const h = 1.0*s + p * 3.8*s; 
    const r = (1 - p) * 1.6 * s + 0.1;
    const lx = Math.cos(angle)*r; const lz = Math.sin(angle)*r;
    points.push(new THREE.Vector3(lx, h, lz));
    if(j % 3 === 0) { // Bulbs
      add(t, new THREE.Mesh(bulbGeo, bulbMat), lx, h, lz);
    }
  }
  t.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(points), wireMat));
  // Glow
  const glow = new THREE.PointLight(0xffaa00, 0, 5);
  glow.position.y = 2.5*s; t.add(glow);
}

// ─── SMALL FLOWERS ────────────────────────────────────────────
const flowerColors = [0xff4081, 0xffd740, 0xffffff, 0xff80ab];
for(let i=0; i<18; i++){
  const angle = Math.random()*Math.PI*2;
  const r = 7 + Math.random()*18;
  const fc = flowerColors[Math.floor(Math.random()*flowerColors.length)];
  const stem = cyl(0.025, 0.025, 0.25, 6, 0x388e3c);
  stem.position.set(Math.cos(angle)*r, 0.125, Math.sin(angle)*r);
  scene.add(stem);
  const bloom = cyl(0.1, 0.1, 0.04, 8, fc);
  bloom.position.set(Math.cos(angle)*r, 0.28, Math.sin(angle)*r);
  scene.add(bloom);
}

// ─── ROCKS & STONES ───────────────────────────────────────────
function createRock(x, z) {
  const detail = Math.random() > 0.6 ? 1 : 0;
  const geo = new THREE.DodecahedronGeometry(1, detail);
  const scale = 0.2 + Math.random() * 0.6;

  // Deform vertices for a natural look
  const pos = geo.attributes.position;
  for (let i = 0; i < pos.count; i++) {
    const v = new THREE.Vector3().fromBufferAttribute(pos, i);
    v.x += (Math.random() - 0.5) * 0.4;
    v.y += (Math.random() - 0.5) * 0.4;
    v.z += (Math.random() - 0.5) * 0.4;
    pos.setXYZ(i, v.x, v.y, v.z);
  }
  geo.computeVertexNormals(); // Recalculate normals for correct lighting

  const color = new THREE.Color(0x888888);
  color.offsetHSL(0, 0, (Math.random() - 0.5) * 0.2); // Brightness variation
  
  const rock = new THREE.Mesh(geo, mat(color.getHex()));
  rock.scale.set(scale, scale, scale);
  rock.rotation.set(Math.random()*Math.PI, Math.random()*Math.PI, Math.random()*Math.PI);
  rock.position.set(x, scale * 0.4, z); // Lift slightly based on scale
  scene.add(rock);
}
// Place a small cluster of rocks under a tree in the background
const rockClusterX = -18;
const rockClusterZ = -18;
for(let i=0; i<4; i++){
  createRock(rockClusterX + (Math.random() - 0.5) * 3.5, rockClusterZ + (Math.random() - 0.5) * 3.5);
}

// ─── WOODEN FENCE ─────────────────────────────────────────────
function createFence(x, z, angle, len) {
  const g = new THREE.Group();
  const numPosts = Math.floor(len / 4);
  const postGeo = new THREE.BoxGeometry(0.3, 1.4, 0.3);
  const railGeo = new THREE.BoxGeometry(len, 0.15, 0.08);
  const matFence = mat(0x8d6e63); // Wood color

  // Posts
  for (let i = 0; i <= numPosts; i++) {
    const post = new THREE.Mesh(postGeo, matFence);
    post.position.set((i / numPosts) * len - len / 2, 0.7, 0);
    g.add(post);
  }
  // Rails
  const rail1 = new THREE.Mesh(railGeo, matFence);
  rail1.position.y = 1.0; g.add(rail1);
  const rail2 = new THREE.Mesh(railGeo, matFence);
  rail2.position.y = 0.5; g.add(rail2);

  g.rotation.y = angle;
  g.position.set(x, 0, z);
  scene.add(g);
}
// Enclose the garden (Back, Left, Right, Front-Left, Front-Right)
createFence(0, -25, 0, 70);
createFence(-35, 15, Math.PI/2, 80);
createFence(35, 15, Math.PI/2, 80);
createFence(-20, 55, 0, 30);
createFence(20, 55, 0, 30);

// ─── FLOWER ARCHWAY ───────────────────────────────────────────
function createArchway() {
  const g = new THREE.Group();
  const postHeight = 3.5;
  const archWidth = 4.5;

  // Posts & Top Beam
  const postL = box(0.3, postHeight, 0.3, 0x8d6e63);
  postL.position.set(-archWidth/2, postHeight/2, 0); g.add(postL);
  const postR = box(0.3, postHeight, 0.3, 0x8d6e63);
  postR.position.set(archWidth/2, postHeight/2, 0); g.add(postR);
  const topBeam = box(archWidth + 0.3, 0.3, 0.3, 0x8d6e63);
  topBeam.position.y = postHeight; g.add(topBeam);

  // Vines and Flowers
  const leafMat = mat(0x2e7d32);
  const flowerColors = [0xff4081, 0xffd740, 0xffffff];
  for(let i=0; i<60; i++){
    const isFlower = Math.random() > 0.7;
    const geo = new THREE.SphereGeometry(isFlower ? 0.08 : 0.1, 5, 4);
    const m = new THREE.Mesh(geo, isFlower ? mat(flowerColors[Math.floor(Math.random()*flowerColors.length)]) : leafMat);
    if(Math.random() > 0.25) { // On posts
        m.position.set( (Math.random() > 0.5 ? 1 : -1) * (archWidth/2 + (Math.random()-0.5)*0.3), Math.random() * postHeight, (Math.random()-0.5)*0.3 );
    } else { // On top beam
        m.position.set( (Math.random()-0.5) * archWidth, postHeight + (Math.random()-0.5)*0.3, (Math.random()-0.5)*0.3 );
    }
    g.add(m);
  }
  g.position.set(0, 0, 68); // At the start of the long path
  scene.add(g);
}
createArchway();

// ─── PICNIC SETUP ─────────────────────────────────────────────
const picnicGroup = new THREE.Group();
const canvas = document.createElement('canvas');
canvas.width = 64; canvas.height = 64;
const ctx = canvas.getContext('2d');
ctx.fillStyle = '#c62828'; ctx.fillRect(0,0,64,64); // Red
ctx.fillStyle = '#fffde7'; ctx.fillRect(0,0,32,32); ctx.fillRect(32,32,32,32); // Cream
const blanketTex = new THREE.CanvasTexture(canvas);
blanketTex.wrapS = blanketTex.wrapT = THREE.RepeatWrapping;
blanketTex.repeat.set(8, 8);
const blanket = new THREE.Mesh(new THREE.PlaneGeometry(3, 3), new THREE.MeshLambertMaterial({ map: blanketTex }));
blanket.rotation.x = -Math.PI/2; picnicGroup.add(blanket);
const basket = new THREE.Group();
add(basket, cyl(0.4, 0.3, 0.5, 12, 0xab8953), 0, 0.25, 0); // Base
add(basket, new THREE.Mesh(new THREE.TorusGeometry(0.4, 0.05, 8, 16), mat(0x9a7c4b)), 0, 0.5, 0).rotation.x = Math.PI/2; // Rim
add(basket, new THREE.Mesh(new THREE.TorusGeometry(0.3, 0.04, 8, 16, Math.PI), mat(0x9a7c4b)), 0, 0.5, 0); // Handle
add(picnicGroup, basket, 0.8, 0, -0.8);
picnicGroup.position.set(-8, 0.01, 20);
scene.add(picnicGroup);

// ─── BUSHES ───────────────────────────────────────────────────
function createBush(x, z) {
  const g = new THREE.Group();
  const bushMat = mat(0x2e7d32);
  for(let i=0; i<5; i++){
    const p = new THREE.Mesh(new THREE.DodecahedronGeometry(0.5 + Math.random()*0.4, 0), bushMat);
    p.position.set((Math.random()-0.5)*0.8, Math.random()*0.5, (Math.random()-0.5)*0.8);
    g.add(p);
  }
  g.position.set(x, 0.2, z); scene.add(g);
}
createBush(5, 5); createBush(-5, 5); createBush(5, -5); createBush(-5, -5);

// ─── FOUNTAIN ─────────────────────────────────────────────────
const fountainGroup = new THREE.Group();
const fStone = mat(0xeeeeee); // White/Grey marble
const fWater = new THREE.MeshLambertMaterial({ color: 0x4fc3f7, transparent: true, opacity: 0.8 });

// 1. Base Basin
const basin = new THREE.Mesh(new THREE.CylinderGeometry(4, 3.8, 0.6, 24), fStone);
basin.position.y = 0.3; basin.receiveShadow = true; fountainGroup.add(basin);

// Water in base
const pool = new THREE.Mesh(new THREE.CircleGeometry(3.6, 32), fWater);
pool.rotation.x = -Math.PI/2; pool.position.y = 0.61; fountainGroup.add(pool);

// 2. Pillar & Top Tier
const col = new THREE.Mesh(new THREE.CylinderGeometry(0.6, 0.7, 1.5, 12), fStone);
col.position.y = 1; fountainGroup.add(col);

const topBowl = new THREE.Mesh(new THREE.CylinderGeometry(2, 0.2, 0.8, 24), fStone);
topBowl.position.y = 2; fountainGroup.add(topBowl);

// Water in top
const topPool = new THREE.Mesh(new THREE.CircleGeometry(1.8, 32), fWater);
topPool.rotation.x = -Math.PI/2; topPool.position.y = 2.41; fountainGroup.add(topPool);

// 3. Falling Water & Spout
const flowGeo = new THREE.CylinderGeometry(1.9, 1.9, 1.4, 24, 1, true);
const flowMat = new THREE.MeshBasicMaterial({ color: 0x89CFF0, transparent: true, opacity: 0.45, side: THREE.DoubleSide });
const flow = new THREE.Mesh(flowGeo, flowMat);
flow.position.y = 1.6; fountainGroup.add(flow);

const spout = new THREE.Mesh(new THREE.CylinderGeometry(0.2, 0.2, 0.5, 8), fStone);
spout.position.y = 2.4; fountainGroup.add(spout);

const spray = new THREE.Mesh(new THREE.ConeGeometry(0.3, 0.5, 8, 1, true), flowMat);
spray.position.y = 2.9; fountainGroup.add(spray);

// 4. Lily Pads (fewer)
for(let i=0; i<3; i++){
  const pad = new THREE.Mesh(new THREE.CircleGeometry(0.3, 16, 0, 5.5), mat(0x66bb6a, {side:THREE.DoubleSide}));
  pad.rotation.x = -Math.PI/2; pad.rotation.z = Math.random()*6;
  const r = 2.5 + Math.random(); const a = Math.random()*6;
  pad.position.set(Math.cos(a)*r, 0.62, Math.sin(a)*r);
  fountainGroup.add(pad);
}

fountainGroup.position.set(18, 0, 5); // Moved further back
scene.add(fountainGroup);

// ─── FOUNTAIN PARTICLES ───────────────────────────────────────
const fountainParticles = [];
const dropGeo = new THREE.BoxGeometry(0.08, 0.08, 0.08);
const dropMat = new THREE.MeshBasicMaterial({color: 0xccffff});
for(let i=0; i<30; i++) {
  const d = new THREE.Mesh(dropGeo, dropMat);
  // Start at top of fountain relative to world. Fountain is at (18,0,5), spout height ~2.9
  d.userData = { velocity: new THREE.Vector3() };
  scene.add(d);
  fountainParticles.push(d);
  resetDrop(d);
}
function resetDrop(d) {
  d.position.set(18 + (Math.random()-0.5)*0.2, 2.9, 5 + (Math.random()-0.5)*0.2);
  d.userData.velocity.set((Math.random()-0.5)*0.08, Math.random()*0.15, (Math.random()-0.5)*0.08);
}

// ─── FIREFLIES ────────────────────────────────────────────────
const fireflies = [];
const flyGeo = new THREE.SphereGeometry(0.05, 4, 4);
const flyMat = new THREE.MeshBasicMaterial({color: 0xffeb3b});
for(let i=0; i<30; i++){
  const f = new THREE.Mesh(flyGeo, flyMat);
  f.position.set((Math.random()-0.5)*20, 1+Math.random()*3, (Math.random()-0.5)*20);
  f.userData = { 
    offset: Math.random()*100, 
    speed: 0.5 + Math.random()*0.5 
  };
  scene.add(f);
  fireflies.push(f);
}

// ─── CLOUDS ───────────────────────────────────────────────────
const clouds = [];
function createCloud() {
  const g = new THREE.Group();
  const chunks = 3 + Math.floor(Math.random() * 4);
  for(let i=0; i<chunks; i++){
    const puff = new THREE.Mesh(
      new THREE.DodecahedronGeometry(1 + Math.random()),
      new THREE.MeshLambertMaterial({ color: 0xffffff, transparent: true, opacity: 0.85 })
    );
    puff.position.set((Math.random()-0.5)*3, Math.random(), (Math.random()-0.5)*2);
    g.add(puff);
  }
  g.position.set((Math.random()-0.5)*120, 20 + Math.random()*10, (Math.random()-0.5)*80);
  g.userData = { speed: 0.01 + Math.random() * 0.04 };
  scene.add(g);
  clouds.push(g);
}
for(let i=0; i<15; i++) createCloud();

// ─── DECOR: TREES, BUTTERFLIES, PETALS ────────────────────────
// 1. Background Trees
function createTree(x, z, scale=1) {
  const g = new THREE.Group();
  const trunk = cyl(0.4*scale, 0.5*scale, 1.5*scale, 7, 0x8B4513);
  trunk.position.y = 0.75*scale; g.add(trunk);
  // Layered leaves
  const c1 = new THREE.Mesh(new THREE.ConeGeometry(2*scale, 3*scale, 8), mat(0x228b22));
  c1.position.y = 2.5*scale; g.add(c1);
  const c2 = new THREE.Mesh(new THREE.ConeGeometry(1.5*scale, 2.5*scale, 8), mat(0x32cd32));
  c2.position.y = 4*scale; g.add(c2);
  g.position.set(x, 0, z);
  scene.add(g);
  return g;
}
// Arranged Trees: Semi-circle behind + scattered forest
for(let i=0; i<=16; i++){
  const angle = Math.PI + (i/16) * Math.PI; // Arc behind gazebo
  const r = 24 + Math.random() * 5;
  createTree(Math.cos(angle)*r, Math.sin(angle)*r, 1.3 + Math.random()*0.5);
}
for(let i=0; i<30; i++){
  const angle = Math.PI * 0.7 + Math.random() * Math.PI * 1.6; // Wider background
  const r = 35 + Math.random() * 30;
  createTree(Math.cos(angle)*r, Math.sin(angle)*r, 1.8 + Math.random()*1.5);
}

// 2. Butterflies
const butterflies = [];
function createButterfly() {
  const b = new THREE.Group();
  const wingGeo = new THREE.PlaneGeometry(0.1, 0.14);
  const mat = new THREE.MeshBasicMaterial({color: 0xffeb3b, side: THREE.DoubleSide});
  const wL = new THREE.Mesh(wingGeo, mat);
  wL.position.x = -0.05; wL.geometry.translate(0.05,0,0);
  const wR = new THREE.Mesh(wingGeo, mat);
  wR.position.x = 0.05; wR.geometry.translate(-0.05,0,0);
  b.add(wL, wR);
  b.userData = { wL, wR, phase: Math.random()*100, yBase: 1 + Math.random()*3 };
  b.position.set((Math.random()-0.5)*12, b.userData.yBase, (Math.random()-0.5)*12);
  scene.add(b);
  butterflies.push(b);
}
for(let i=0; i<10; i++) createButterfly();

// 3. Falling Petals (Cherry Blossom style)
const petals = [];
const petalGeo = new THREE.PlaneGeometry(0.08, 0.08);
const petalMat = new THREE.MeshBasicMaterial({color: 0xffc0cb, side: THREE.DoubleSide, transparent:true, opacity:0.8});
for(let i=0; i<80; i++){
  const p = new THREE.Mesh(petalGeo, petalMat);
  p.position.set((Math.random()-0.5)*25, Math.random()*12, (Math.random()-0.5)*25);
  p.rotation.set(Math.random(), Math.random(), Math.random());
  p.userData = { speed: 0.01 + Math.random()*0.02, rot: Math.random()*0.05, wobble: Math.random()*10 };
  scene.add(p);
  petals.push(p);
}

// ─── STARS ────────────────────────────────────────────────────
let starMaterial;
function createStars() {
  const starVertices = [];
  for (let i = 0; i < 8000; i++) {
    const x = (Math.random() - 0.5) * 200;
    const y = Math.random() * 100 + 15; // Only in upper hemisphere
    const z = (Math.random() - 0.5) * 200;
    starVertices.push(x, y, z);
  }
  const starGeo = new THREE.BufferGeometry();
  starGeo.setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));
  starMaterial = new THREE.PointsMaterial({
    color: 0xffffff, size: 0.15, transparent: true, opacity: 0
  });
  const stars = new THREE.Points(starGeo, starMaterial);
  scene.add(stars);
}
createStars();

// ─── BIRDS ────────────────────────────────────────────────────
const birds = [];
function createBird() {
  const bird = new THREE.Group();
  // Wings: simple planes pivoted at the edge
  const mat = new THREE.MeshBasicMaterial({ color: 0x222222, side: THREE.DoubleSide });
  const wGeo = new THREE.PlaneGeometry(0.5, 0.2);
  wGeo.translate(0.25, 0, 0); // Move pivot to the edge of the wing

  const wL = new THREE.Mesh(wGeo, mat);
  wL.rotation.y = -Math.PI / 2; // Pointing Z+
  
  const wR = new THREE.Mesh(wGeo, mat);
  wR.rotation.y = Math.PI / 2; // Pointing Z-
  
  bird.add(wL, wR);
  
  bird.userData = { wL, wR, speed: 0.08 + Math.random()*0.1, phase: Math.random()*100 };
  bird.position.set(
    (Math.random()-0.5)*140, 
    18 + Math.random()*15, 
    (Math.random()-0.5)*50 - 20
  );
  scene.add(bird);
  birds.push(bird);
}
for(let i=0; i<15; i++) createBird();

// ─── ORBIT CONTROLS (manual) ──────────────────────────────────
let isDragging = false, prevMouse = {x:0, y:0};
let theta = 0.4, phi = 0.5, radius = 2.5;
const orbitCenter = new THREE.Vector3(0, 2.1, 0.3); // Focus on table items

function updateCamera() {
  camera.position.x = orbitCenter.x + radius * Math.sin(theta) * Math.cos(phi);
  camera.position.y = orbitCenter.y + radius * Math.sin(phi);
  camera.position.z = orbitCenter.z + radius * Math.cos(theta) * Math.cos(phi);
  camera.lookAt(orbitCenter);
}
updateCamera();

renderer.domElement.addEventListener('mousedown', e => { isDragging=true; prevMouse={x:e.clientX,y:e.clientY}; });
renderer.domElement.addEventListener('mouseup', () => isDragging=false);
renderer.domElement.addEventListener('mousemove', e => {
  if(!isDragging) return;
  theta -= (e.clientX - prevMouse.x) * 0.008;
  phi = Math.max(0.05, Math.min(1.4, phi + (e.clientY - prevMouse.y) * 0.006));
  prevMouse = {x:e.clientX, y:e.clientY};
  updateCamera();
});
renderer.domElement.addEventListener('wheel', e => {
  radius = Math.max(1, Math.min(40, radius + e.deltaY * 0.03));
  updateCamera();
});
// Touch
renderer.domElement.addEventListener('touchstart', e => { isDragging=true; prevMouse={x:e.touches[0].clientX,y:e.touches[0].clientY}; });
renderer.domElement.addEventListener('touchend', () => isDragging=false);
renderer.domElement.addEventListener('touchmove', e => {
  if(!isDragging) return;
  theta -= (e.touches[0].clientX - prevMouse.x) * 0.008;
  phi = Math.max(0.05, Math.min(1.4, phi + (e.touches[0].clientY - prevMouse.y) * 0.006));
  prevMouse = {x:e.touches[0].clientX,y:e.touches[0].clientY};
  updateCamera();
});

window.addEventListener('resize', () => {
  camera.aspect = innerWidth/innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(innerWidth, innerHeight);
});

// ─── ANIMATE ──────────────────────────────────────────────────
function animate() {
  requestAnimationFrame(animate);
  
  // Fixed Day Position
  sun.position.set(30, 25, -50);
  celestialGroup.position.copy(sun.position);
  celestialGroup.lookAt(scene.position);
  sunMesh.visible = true;
  moonMesh.visible = false;
  if(starMaterial) starMaterial.opacity = 0;

  // Gentle sway and light control
  const t = Date.now()*0.001;
  scene.children.forEach(c => {
    if (c.isGroup) {
      // Handle light intensity for any group with a pointlight
      const pl = c.children.find(ch => ch.isPointLight);
      if (pl) {
        if (c.userData.isLantern) {
          // Swaying for hanging lanterns
          c.rotation.z = Math.sin(t + c.position.x) * 0.04;
          // Flicker: Base + Sine Wave (pulse) + Random (jitter)
          pl.intensity = 0.8 + Math.sin(t * 20 + c.position.x) * 0.05 + Math.random() * 0.08;
        } else {
          // Path lights flicker
          pl.intensity = 0.6 + Math.sin(t * 15 + c.position.z) * 0.05 + Math.random() * 0.05;
        }
      }
    }
  });
  // Move clouds
  clouds.forEach(c => {
    c.position.x += c.userData.speed;
    if(c.position.x > 70) c.position.x = -70;
  });

  // Animate Butterflies
  butterflies.forEach(b => {
    b.userData.phase += 0.15;
    b.userData.wL.rotation.y = Math.sin(b.userData.phase)*0.6; // Flap
    b.userData.wR.rotation.y = -Math.sin(b.userData.phase)*0.6;
    b.position.y = b.userData.yBase + Math.sin(b.userData.phase*0.1)*0.3; // Bob
    b.position.x += Math.sin(t + b.userData.phase) * 0.015; // Drift
    b.rotation.y += Math.sin(t * 0.5) * 0.02; // Turn slowly
  });

  // Animate Petals
  petals.forEach(p => {
    p.position.y -= p.userData.speed;
    p.rotation.x += p.userData.rot;
    p.rotation.y += p.userData.rot;
    p.position.x += Math.sin(t + p.userData.wobble) * 0.003;
    if(p.position.y < 0) { p.position.y = 10; p.position.x = (Math.random()-0.5)*25; }
  });

  // Animate Birds
  birds.forEach(b => {
    b.position.x += b.userData.speed;
    // Flap wings
    const flap = Math.sin(t * 12 + b.userData.phase) * 0.4;
    b.userData.wL.rotation.z = flap;
    b.userData.wR.rotation.z = -flap;
    
    // Loop around when off-screen
    if(b.position.x > 80) {
      b.position.x = -80;
      b.position.y = 18 + Math.random()*15;
    }
  });

  renderer.render(scene, camera);
}
animate();
</script>
</body>
</html>