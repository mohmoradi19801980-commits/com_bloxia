import * as THREE from 'three';
import { CATALOG, GAMENET_GAMES } from './bridge.js';

export function createSignTexture(text, bgColor = '#0f172a', borderColor = '#facc15', subText = '') {
    const canvas = document.createElement('canvas');
    canvas.width = 1024;
    canvas.height = 256;
    const ctx = canvas.getContext('2d');

    const grad = ctx.createLinearGradient(0, 0, 0, 256);
    grad.addColorStop(0, bgColor);
    grad.addColorStop(1, '#090d16');
    ctx.fillStyle = grad;
    ctx.beginPath();
    ctx.roundRect(10, 10, 1004, 236, 36);
    ctx.fill();

    ctx.strokeStyle = borderColor;
    ctx.lineWidth = 14;
    ctx.beginPath();
    ctx.roundRect(10, 10, 1004, 236, 36);
    ctx.stroke();

    ctx.shadowColor = 'rgba(0, 0, 0, 0.85)';
    ctx.shadowBlur = 10;
    ctx.shadowOffsetY = 4;
    ctx.fillStyle = '#ffffff';
    ctx.font = subText ? '900 48px "Vazirmatn", Tahoma' : '900 60px "Vazirmatn", Tahoma';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 512, subText ? 95 : 132);

    if (subText) {
        ctx.fillStyle = '#fde047';
        ctx.font = '700 32px "Vazirmatn", Tahoma';
        ctx.fillText(subText, 512, 175);
    }

    const tex = new THREE.CanvasTexture(canvas);
    tex.anisotropy = 8;
    return tex;
}

export function buildDoorstepPlant(scale = 1) {
    const group = new THREE.Group();
    const pot = new THREE.Mesh(new THREE.CylinderGeometry(0.36 * scale, 0.26 * scale, 0.48 * scale, 16), new THREE.MeshStandardMaterial({ color: 0xea580c }));
    pot.position.y = 0.24 * scale;
    const bush = new THREE.Mesh(new THREE.SphereGeometry(0.45 * scale, 14, 12), new THREE.MeshStandardMaterial({ color: 0x22c55e }));
    bush.position.y = 0.66 * scale;
    const flowerColors = [0xf43f5e, 0xfacc15, 0xf472b6, 0x38bdf8];
    for (let i = 0; i < 5; i++) {
        const fl = new THREE.Mesh(new THREE.SphereGeometry(0.1 * scale), new THREE.MeshStandardMaterial({ color: flowerColors[i % 4] }));
        const angle = (i / 5) * Math.PI * 2;
        fl.position.set(Math.cos(angle) * 0.35 * scale, 0.74 * scale, Math.sin(angle) * 0.35 * scale);
        group.add(fl);
    }
    group.add(pot, bush);
    return group;
}

function createDoorstepSymbolTotem(kind, x, z, accentColor, animatedList) {
    const group = new THREE.Group();
    group.position.set(x, 0, z);
    const pedestal = new THREE.Mesh(new THREE.CylinderGeometry(0.62, 0.72, 0.95, 20), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
    pedestal.position.y = 0.48;
    const ring = new THREE.Mesh(new THREE.TorusGeometry(0.64, 0.06, 10, 24), new THREE.MeshStandardMaterial({ color: accentColor, emissive: accentColor, emissiveIntensity: 0.6 }));
    ring.rotation.x = Math.PI / 2;
    ring.position.y = 0.94;
    group.add(pedestal, ring);

    const iconHolder = new THREE.Group();
    iconHolder.position.y = 1.85;

    if (kind === 'hair_salon') {
        const b1 = new THREE.Mesh(new THREE.BoxGeometry(1.15, 0.12, 0.08), new THREE.MeshStandardMaterial({ color: 0xf8fafc, metalness: 0.8 }));
        b1.rotation.z = 0.55;
        const b2 = b1.clone();
        b2.rotation.z = -0.55;
        iconHolder.add(b1, b2);
    } else if (kind === 'clothes') {
        const shirt = new THREE.Mesh(new THREE.BoxGeometry(0.75, 0.85, 0.25), new THREE.MeshStandardMaterial({ color: 0xec4899 }));
        iconHolder.add(shirt);
    } else if (kind === 'furniture') {
        const sofa = new THREE.Mesh(new THREE.BoxGeometry(1.1, 0.55, 0.55), new THREE.MeshStandardMaterial({ color: 0xf59e0b }));
        iconHolder.add(sofa);
    } else if (kind === 'accessories') {
        const sword = new THREE.Mesh(new THREE.BoxGeometry(0.16, 1.35, 0.08), new THREE.MeshStandardMaterial({ color: 0x38bdf8, emissive: 0x0284c7, emissiveIntensity: 0.6 }));
        sword.rotation.z = -0.45;
        iconHolder.add(sword);
    } else if (kind === 'cinema') {
        const bucket = new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.3, 0.75, 12), new THREE.MeshStandardMaterial({ color: 0xdc2626 }));
        const pop = new THREE.Mesh(new THREE.DodecahedronGeometry(0.42), new THREE.MeshStandardMaterial({ color: 0xfef08a }));
        pop.position.y = 0.45;
        iconHolder.add(bucket, pop);
    } else if (kind === 'gamenet') {
        const pad = new THREE.Mesh(new THREE.BoxGeometry(1.1, 0.55, 0.25), new THREE.MeshStandardMaterial({ color: 0x10b981, emissive: 0x059669, emissiveIntensity: 0.5 }));
        iconHolder.add(pad);
    } else if (kind === 'school') {
        const book = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.22, 0.65), new THREE.MeshStandardMaterial({ color: 0x2563eb }));
        iconHolder.add(book);
    } else if (kind === 'park') {
        const target = new THREE.Mesh(new THREE.CylinderGeometry(0.6, 0.6, 0.15, 20), new THREE.MeshStandardMaterial({ color: 0xef4444 }));
        target.rotation.x = Math.PI / 2;
        iconHolder.add(target);
    }

    group.add(iconHolder);
    animatedList.push({ mesh: iconHolder, type: 'spinAndBob', speed: 2.0, baseY: 1.85 });
    return group;
}

// بیلبورد تبلیغاتی شهری با پایه‌های فلزی و نورافکن
function createCityBillboard(x, z, rotY, titleText, subText, bgColor, ctx) {
    const g = new THREE.Group();
    g.position.set(x, 0, z);
    g.rotation.y = rotY;

    const poleMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.6 });
    [-2.2, 2.2].forEach(px => {
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.22, 6.2, 10), poleMat);
        pole.position.set(px, 3.1, 0);
        g.add(pole);
    });

    const boardFrame = new THREE.Mesh(new THREE.BoxGeometry(6.4, 2.6, 0.35), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
    boardFrame.position.set(0, 5.2, 0);

    const banner = new THREE.Mesh(
        new THREE.PlaneGeometry(6.0, 2.25),
        new THREE.MeshBasicMaterial({ map: createSignTexture(titleText, bgColor, '#facc15', subText) })
    );
    banner.position.set(0, 5.2, 0.19);
    g.add(boardFrame, banner);

    ctx.worldGroup.add(g);
    ctx.obstacles.push({ minX: x - 2.6, maxX: x + 2.6, minZ: z - 0.6, maxZ: z + 0.6 });
}

export function buildCityPlaza(ctx, residentialPlots) {
    // 1. زمین چمن شهر
    const ground = new THREE.Mesh(new THREE.PlaneGeometry(160, 160), new THREE.MeshStandardMaterial({ color: 0x86efac, roughness: 0.9 }));
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    ctx.worldGroup.add(ground);
    ctx.setGround(ground);

    // 2. شبکه خیابان‌ها، بلوارها و کوچه‌های شهر
    const roadMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.85 });
    const plazaRing = new THREE.Mesh(new THREE.RingGeometry(15.5, 22.5, 48), roadMat);
    plazaRing.rotation.x = -Math.PI / 2;
    plazaRing.position.y = 0.012;

    const plazaCenter = new THREE.Mesh(new THREE.CircleGeometry(15.5, 48), new THREE.MeshStandardMaterial({ color: 0xe2e8f0 }));
    plazaCenter.rotation.x = -Math.PI / 2;
    plazaCenter.position.y = 0.015;

    const nsBoulevard = new THREE.Mesh(new THREE.PlaneGeometry(8.5, 110), roadMat);
    nsBoulevard.rotation.x = -Math.PI / 2;
    nsBoulevard.position.set(0, 0.011, 15);

    const ewAvenue = new THREE.Mesh(new THREE.PlaneGeometry(115, 8.5), roadMat);
    ewAvenue.rotation.x = -Math.PI / 2;
    ewAvenue.position.set(0, 0.011, 2);

    const baharAlley = new THREE.Mesh(new THREE.PlaneGeometry(96, 7.2), roadMat);
    baharAlley.rotation.x = -Math.PI / 2;
    baharAlley.position.set(0, 0.012, 31);

    const yasAlley = new THREE.Mesh(new THREE.PlaneGeometry(96, 7.2), roadMat);
    yasAlley.rotation.x = -Math.PI / 2;
    yasAlley.position.set(0, 0.012, 54);
    ctx.worldGroup.add(plazaRing, plazaCenter, nsBoulevard, ewAvenue, baharAlley, yasAlley);

    // آب‌نمای مرکزی میدان
    const fountainBase = new THREE.Mesh(new THREE.CylinderGeometry(3.2, 3.5, 0.8, 24), new THREE.MeshStandardMaterial({ color: 0x94a3b8 }));
    fountainBase.position.y = 0.4;
    const fountainWater = new THREE.Mesh(new THREE.CylinderGeometry(2.9, 2.9, 0.85, 24), new THREE.MeshStandardMaterial({ color: 0x38bdf8 }));
    fountainWater.position.y = 0.42;
    ctx.worldGroup.add(fountainBase, fountainWater);
    ctx.obstacles.push({ minX: -3.5, maxX: 3.5, minZ: -3.5, maxZ: 3.5 });

    // بیلبوردهای تبلیغاتی شهری
    createCityBillboard(-10, -14, Math.PI / 6, '🎓 مدرسه نسیم دانایی', 'جشنواره بازی‌سازی و دریافت 100 BitBlox!', '#1e3a8a', ctx);
    createCityBillboard(11, 13, -Math.PI * 0.75, '🎬 اکران ویژه سینمای شهر', 'سفر به منظومه شمسی - بلیت 15 BitBlox', '#7f1d1d', ctx);

    // چراغ‌های خیابان
    [[-15, 14], [15, 14], [-15, -12], [15, -12], [-32, 26.8], [-16, 26.8], [16, 26.8], [32, 26.8]].forEach(([lx, lz]) => {
        const lamp = new THREE.Group();
        lamp.position.set(lx, 0, lz);
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.1, 4.2, 8), new THREE.MeshStandardMaterial({ color: 0x334155 }));
        pole.position.y = 2.1;
        const bulbMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, emissive: 0xfef08a, emissiveIntensity: 0.05 });
        const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.32, 12, 12), bulbMat);
        bulb.position.y = 4.3;
        ctx.streetLampBulbs.push(bulbMat);
        const light = new THREE.PointLight(0xfef08a, 0, 14);
        light.position.y = 4.1;
        ctx.streetLampLights.push(light);
        lamp.add(pole, bulb, light);
        ctx.worldGroup.add(lamp);
        ctx.obstacles.push({ minX: lx - 0.35, maxX: lx + 0.35, minZ: lz - 0.35, maxZ: lz + 0.35 });
    });

    // نیمکت‌های پارک و درختان
    [[-10, 9, Math.PI], [10, 9, Math.PI], [-36, 15, Math.PI / 2], [36, 20, -Math.PI / 2]].forEach(([bx, bz, ry]) => {
        const bench = new THREE.Group();
        bench.position.set(bx, 0, bz);
        bench.rotation.y = ry;
        const seat = new THREE.Mesh(new THREE.BoxGeometry(2.2, 0.12, 0.65), new THREE.MeshStandardMaterial({ color: 0x92400e }));
        seat.position.y = 0.52;
        bench.add(seat);
        ctx.worldGroup.add(bench);
        ctx.obstacles.push({ minX: bx - 1.1, maxX: bx + 1.1, minZ: bz - 0.5, maxZ: bz + 0.5 });
    });

    [[-11, 12], [11, 12], [-38, 12], [38, 12], [-38, 31], [38, 31], [-38, 54], [38, 54]].forEach(([tx, tz]) => {
        const tree = new THREE.Group();
        tree.position.set(tx, 0, tz);
        const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.42, 2.2, 8), new THREE.MeshStandardMaterial({ color: 0x78350f }));
        trunk.position.y = 1.1;
        const leaves = new THREE.Mesh(new THREE.SphereGeometry(1.75, 14, 12), new THREE.MeshStandardMaterial({ color: 0x22c55e }));
        leaves.position.y = 3.1;
        tree.add(trunk, leaves);
        ctx.worldGroup.add(tree);
        ctx.obstacles.push({ minX: tx - 0.7, maxX: tx + 0.7, minZ: tz - 0.7, maxZ: tz + 0.7 });
    });

    // ساخت ساختمان‌های شهر با معماری اختصاصی و استند کنار در
    const placeBuilding = (opts) => {
        const { x, z, w, h, d, rotY, color, label, zone, prompt, padColor, kind, mapLabel, mapColor } = opts;
        const g = new THREE.Group();
        g.position.set(x, 0, z);
        g.rotation.y = rotY;

        const body = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshStandardMaterial({ color, roughness: 0.6 }));
        body.position.y = h / 2;
        body.castShadow = true;
        g.add(body);

        const roof = new THREE.Mesh(new THREE.BoxGeometry(w + 0.5, 0.55, d + 0.5), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
        roof.position.y = h + 0.25;
        g.add(roof);

        const door = new THREE.Mesh(new THREE.BoxGeometry(1.7, 2.6, 0.2), new THREE.MeshStandardMaterial({ color: 0x451a03 }));
        door.position.set(0, 1.3, d / 2 + 0.05);
        g.add(door);

        const sign = new THREE.Mesh(new THREE.BoxGeometry(Math.min(w * 0.92, 7.0), 1.35, 0.25), new THREE.MeshBasicMaterial({ map: createSignTexture(label) }));
        sign.position.set(0, h - 1.1, d / 2 + 0.25);
        g.add(sign);

        g.add(createDoorstepSymbolTotem(kind, w / 2 - 1.4, d / 2 + 1.05, padColor, ctx.animatedLandmarks));
        ctx.worldGroup.add(g);

        const isRot90 = Math.abs(Math.sin(rotY)) > 0.7;
        const hw = (isRot90 ? d : w) / 2 + 0.5;
        const hd = (isRot90 ? w : d) / 2 + 0.5;
        ctx.obstacles.push({ minX: x - hw, maxX: x + hw, minZ: z - hd, maxZ: z + hd });
        ctx.minimapBuildings.push({ x, z, w: hw * 2, d: hd * 2, label: mapLabel, color: mapColor });

        const doorVec = new THREE.Vector3(0, 0, d / 2 + 2.2).applyAxisAngle(new THREE.Vector3(0, 1, 0), rotY);
        ctx.addPortal(x + doorVec.x, z + doorVec.z, zone, prompt, padColor);
    };

    placeBuilding({ x: 0, z: -27, w: 13, h: 7.5, d: 7, rotY: 0, color: 0xfef3c7, label: '🎓 سالن اجتماعات مدرسه', zone: 'school_hall', prompt: '🎓 ورود به مدرسه', padColor: 0x2563eb, kind: 'school', mapLabel: 'مدرسه', mapColor: '#3b82f6' });
    placeBuilding({ x: 16, z: -22, w: 8.5, h: 5.5, d: 6, rotY: -Math.PI / 8, color: 0xffe4e6, label: '💇 آرایشگاه شهر', zone: 'hair_salon', prompt: '💇 ورود به آرایشگاه', padColor: 0xe11d48, kind: 'hair_salon', mapLabel: 'آرایشگاه', mapColor: '#f43f5e' });
    placeBuilding({ x: 27, z: -11, w: 8.5, h: 5.5, d: 6, rotY: -Math.PI / 3, color: 0xfce7f3, label: '👗 فروشگاه لباس و کفش', zone: 'shop_clothes', prompt: '👗 ورود به فروشگاه لباس', padColor: 0xdb2777, kind: 'clothes', mapLabel: 'لباس', mapColor: '#ec4899' });
    placeBuilding({ x: 29, z: 9, w: 8.5, h: 5.5, d: 6, rotY: -Math.PI / 2, color: 0xfef3c7, label: '🛋️ فروشگاه لوازم خانه', zone: 'shop_furniture', prompt: '🛋️ ورود به فروشگاه مبلمان', padColor: 0xd97706, kind: 'furniture', mapLabel: 'مبلمان', mapColor: '#f59e0b' });
    placeBuilding({ x: -16, z: -22, w: 8.5, h: 5.5, d: 6, rotY: Math.PI / 8, color: 0xe0f2fe, label: '⚔️ فروشگاه لوازم جانبی', zone: 'shop_accessories', prompt: '⚔️ ورود به فروشگاه لوازم', padColor: 0x0284c7, kind: 'accessories', mapLabel: 'لوازم', mapColor: '#0ea5e9' });
    placeBuilding({ x: -27, z: -11, w: 9, h: 5.8, d: 6.5, rotY: Math.PI / 3, color: 0x0f172a, label: '🕹️ گیم‌نت GAME NET', zone: 'gamenet', prompt: '🕹️ ورود به گیم‌نت', padColor: 0x059669, kind: 'gamenet', mapLabel: 'گیم‌نت', mapColor: '#10b981' });
    placeBuilding({ x: -29, z: 9, w: 9.5, h: 6.2, d: 7, rotY: Math.PI / 2, color: 0x1e1b4b, label: '🎬 سینمای شهر', zone: 'cinema', prompt: '🎬 ورود به سینما', padColor: 0x7c3aed, kind: 'cinema', mapLabel: 'سینما', mapColor: '#8b5cf6' });
    placeBuilding({ x: 38, z: -22, w: 9.5, h: 5.2, d: 7, rotY: -Math.PI * 0.75, color: 0xffedd5, label: '🎡 شهربازی و جایزه', zone: 'amusement_park', prompt: '🎡 ورود به شهربازی', padColor: 0xea580c, kind: 'park', mapLabel: 'شهربازی', mapColor: '#f97316' });

    // خانه‌های پفکی و بامزه شهروندان در کوچه‌ها
    const houseCoords = [
        { x: -25, z: 20, rotY: 0 }, { x: -12, z: 20, rotY: 0 },
        { x: 12,  z: 20, rotY: 0 }, { x: 25,  z: 20, rotY: 0 }
    ];
    residentialPlots.forEach((plot, idx) => {
        if (idx >= houseCoords.length) return;
        const pos = houseCoords[idx];
        const w = 8.2, h = 4.6, d = 6.2;
        const g = new THREE.Group();
        g.position.set(pos.x, 0, pos.z);
        const wallMat = new THREE.MeshStandardMaterial({ color: plot.color || 0x93c5fd, roughness: 0.55 });
        const core = new THREE.Mesh(new THREE.BoxGeometry(w - 1.2, h, d - 1.2), wallMat);
        core.position.y = h / 2 + 0.2;
        g.add(core);

        [[-(w/2-0.6), -(d/2-0.6)], [(w/2-0.6), -(d/2-0.6)], [-(w/2-0.6), (d/2-0.6)], [(w/2-0.6), (d/2-0.6)]].forEach(([cx, cz]) => {
            const col = new THREE.Mesh(new THREE.CylinderGeometry(0.95, 1.05, h, 20), wallMat);
            col.position.set(cx, h / 2 + 0.2, cz);
            g.add(col);
        });

        const roof = new THREE.Mesh(new THREE.SphereGeometry(4.7, 24, 16, 0, Math.PI * 2, 0, Math.PI * 0.48), new THREE.MeshStandardMaterial({ color: plot.roofColor || 0xe11d48 }));
        roof.scale.set(1.05, 0.72, 0.88);
        roof.position.y = h + 0.1;
        g.add(roof);

        const potL = buildDoorstepPlant(1.05); potL.position.set(-1.55, 0.2, d / 2 + 0.9);
        const potR = buildDoorstepPlant(1.05); potR.position.set(1.55, 0.2, d / 2 + 0.9);
        const sign = new THREE.Mesh(new THREE.BoxGeometry(6.0, 1.25, 0.25), new THREE.MeshBasicMaterial({ map: createSignTexture(`🏠 خانه ${plot.ownerName}`) }));
        sign.position.set(0, h - 0.1, d / 2 + 0.52);
        g.add(potL, potR, sign);

        ctx.worldGroup.add(g);
        ctx.obstacles.push({ minX: pos.x - 4.5, maxX: pos.x + 4.5, minZ: pos.z - 3.6, maxZ: pos.z + 3.6 });
        ctx.minimapBuildings.push({ x: pos.x, z: pos.z, w: 8, d: 6, label: `خانه ${plot.ownerName.split(' ')[0]}`, color: '#38bdf8' });
        ctx.addPortal(pos.x, pos.z + d / 2 + 2.2, `home_${plot.ownerId}`, `🚪 ورود به خانه ${plot.ownerName}`, 0x10b981);
    });
}

export function buildCityInterior(zoneId, ctx) {
    const bgMap = { hair_salon: 0xffe4e6, cinema: 0x0f172a, gamenet: 0x064e3b, amusement_park: 0xffedd5, shop_clothes: 0xfce7f3, shop_furniture: 0xffedd5, shop_accessories: 0xe0f2fe, school_hall: 0xeff6ff };
    const ground = new THREE.Mesh(new THREE.PlaneGeometry(26, 24), new THREE.MeshStandardMaterial({ color: bgMap[zoneId] || 0xf1f5f9 }));
    ground.rotation.x = -Math.PI / 2;
    ctx.worldGroup.add(ground);
    ctx.setGround(ground);

    if (zoneId.startsWith('shop_')) {
        (CATALOG[zoneId] || []).forEach((item, idx) => {
            const xPos = -8 + idx * 3.2;
            const stand = new THREE.Mesh(new THREE.CylinderGeometry(1.0, 1.1, 0.5, 20), new THREE.MeshStandardMaterial({ color: 0x475569 }));
            stand.position.set(xPos, 0.25, -4);
            const box = new THREE.Mesh(new THREE.BoxGeometry(0.9, 0.9, 0.9), new THREE.MeshStandardMaterial({ color: item.color }));
            box.position.set(xPos, 1.1, -4);
            ctx.worldGroup.add(stand, box);
            ctx.interactables.push(stand, box);
        });
    } else if (zoneId === 'gamenet') {
        GAMENET_GAMES.forEach((g, idx) => {
            const xPos = -5 + idx * 5;
            const cab = new THREE.Mesh(new THREE.BoxGeometry(1.8, 3.2, 1.5), new THREE.MeshStandardMaterial({ color: 0x10b981 }));
            cab.position.set(xPos, 1.6, -5);
            ctx.worldGroup.add(cab);
            ctx.interactables.push(cab);
        });
    } else {
        const board = new THREE.Mesh(new THREE.BoxGeometry(12, 4.5, 0.3), new THREE.MeshBasicMaterial({ map: createSignTexture('کلیک کنید برای شروع فعالیت یا خرید بلیت', '#1e293b', '#38bdf8') }));
        board.position.set(0, 3.2, -9);
        ctx.worldGroup.add(board);
        ctx.interactables.push(board);
    }

    ctx.addPortal(0, 9.2, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
}