import * as THREE from './three/three.module.js';
import { createSignTexture, buildDoorstepPlant } from './city.js';

function createPatternTexture(kind, baseHex = 0xd8b48b) {
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 256;
    const c = canvas.getContext('2d');
    const col = new THREE.Color(baseHex);
    const hexStr = '#' + col.getHexString();

    c.fillStyle = hexStr;
    c.fillRect(0, 0, 256, 256);

    if (kind.startsWith('floor_parquet')) {
        c.strokeStyle = 'rgba(0,0,0,0.16)';
        c.lineWidth = 2;
        for (let y = 0; y < 256; y += 32) {
            c.beginPath(); c.moveTo(0, y); c.lineTo(256, y); c.stroke();
            const offset = (y / 32) % 2 === 0 ? 0 : 64;
            for (let x = offset; x < 256; x += 128) {
                c.beginPath(); c.moveTo(x, y); c.lineTo(x, y + 32); c.stroke();
            }
        }
    } else if (kind === 'floor_ceramic_checker') {
        for (let y = 0; y < 256; y += 64) {
            for (let x = 0; x < 256; x += 64) {
                if (((x + y) / 64) % 2 === 0) {
                    c.fillStyle = 'rgba(15,23,42,0.22)';
                    c.fillRect(x, y, 64, 64);
                }
            }
        }
    } else if (kind === 'floor_ceramic_marble') {
        c.strokeStyle = 'rgba(217,119,6,0.22)';
        c.lineWidth = 2;
        for (let i = 0; i < 6; i++) {
            c.beginPath();
            c.moveTo(Math.random() * 256, 0);
            c.bezierCurveTo(80, 90, 180, 170, Math.random() * 256, 256);
            c.stroke();
        }
        c.strokeStyle = 'rgba(148,163,184,0.25)';
        c.strokeRect(0, 0, 128, 128);
        c.strokeRect(128, 128, 128, 128);
    } else if (kind === 'floor_ceramic_hex') {
        c.strokeStyle = 'rgba(15,23,42,0.18)';
        c.lineWidth = 2;
        for (let y = 0; y < 256; y += 48) {
            for (let x = 0; x < 256; x += 48) {
                c.strokeRect(x + 4, y + 4, 40, 40);
            }
        }
    } else if (kind === 'wall_wp_striped') {
        c.fillStyle = 'rgba(255,255,255,0.22)';
        for (let x = 0; x < 256; x += 32) {
            c.fillRect(x, 0, 12, 256);
        }
    } else if (kind === 'wall_wp_floral') {
        c.fillStyle = 'rgba(244,63,94,0.22)';
        for (let y = 24; y < 256; y += 64) {
            for (let x = 24; x < 256; x += 64) {
                c.beginPath(); c.arc(x, y, 10, 0, Math.PI * 2); c.fill();
                c.fillStyle = 'rgba(16,185,129,0.22)';
                c.beginPath(); c.arc(x + 12, y + 8, 6, 0, Math.PI * 2); c.fill();
                c.fillStyle = 'rgba(244,63,94,0.22)';
            }
        }
    } else if (kind === 'wall_wp_brick') {
        c.strokeStyle = 'rgba(15,23,42,0.22)';
        c.lineWidth = 3;
        for (let y = 0; y < 256; y += 32) {
            c.beginPath(); c.moveTo(0, y); c.lineTo(256, y); c.stroke();
            const off = (y / 32) % 2 === 0 ? 0 : 32;
            for (let x = off; x < 256; x += 64) {
                c.beginPath(); c.moveTo(x, y); c.lineTo(x, y + 32); c.stroke();
            }
        }
    }

    const tex = new THREE.CanvasTexture(canvas);
    tex.wrapS = THREE.RepeatWrapping;
    tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(4, 4);
    return tex;
}

function createArtTexture(kind) {
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 192;
    const c = canvas.getContext('2d');
    if (kind === 'furn_frame_nature') {
        const g = c.createLinearGradient(0, 0, 0, 192);
        g.addColorStop(0, '#7dd3fc');
        g.addColorStop(1, '#bae6fd');
        c.fillStyle = g; c.fillRect(0, 0, 256, 192);
        c.fillStyle = '#065f46';
        c.beginPath(); c.moveTo(0, 192); c.lineTo(75, 80); c.lineTo(155, 192); c.fill();
        c.fillStyle = '#047857';
        c.beginPath(); c.moveTo(100, 192); c.lineTo(185, 65); c.lineTo(256, 192); c.fill();
        c.fillStyle = '#fde047';
        c.beginPath(); c.arc(205, 42, 18, 0, Math.PI * 2); c.fill();
    } else if (kind === 'furn_frame_art') {
        c.fillStyle = '#fef3c7'; c.fillRect(0, 0, 256, 192);
        c.fillStyle = '#ef4444'; c.beginPath(); c.arc(90, 95, 45, 0, Math.PI * 2); c.fill();
        c.fillStyle = '#3b82f6'; c.fillRect(125, 55, 75, 85);
        c.fillStyle = '#f59e0b'; c.beginPath(); c.arc(170, 120, 28, 0, Math.PI * 2); c.fill();
    } else {
        c.fillStyle = '#0f172a'; c.fillRect(0, 0, 256, 192);
        c.fillStyle = '#334155';
        [20, 65, 115, 165, 205].forEach((x, idx) => {
            const h = 70 + (idx % 3) * 30;
            c.fillRect(x, 192 - h, 36, h);
            c.fillStyle = '#facc15';
            for (let wy = 192 - h + 10; wy < 180; wy += 16) {
                c.fillRect(x + 8, wy, 7, 8);
                c.fillRect(x + 21, wy, 7, 8);
            }
            c.fillStyle = '#334155';
        });
        c.fillStyle = '#f8fafc'; c.beginPath(); c.arc(45, 36, 14, 0, Math.PI * 2); c.fill();
    }
    return new THREE.CanvasTexture(canvas);
}

export function buildRealisticFurniture(itemId, customColor) {
    const group = new THREE.Group();
    const col = customColor || null;

    if (itemId === 'furn_sofa') {
        const mat = new THREE.MeshStandardMaterial({ color: col || 0xef4444, roughness: 0.72 });
        const seat = new THREE.Mesh(new THREE.BoxGeometry(2.6, 0.44, 0.95), mat);
        seat.position.y = 0.34;
        const back = new THREE.Mesh(new THREE.BoxGeometry(2.6, 0.7, 0.25), mat);
        back.position.set(0, 0.74, -0.35);
        const armL = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.62, 0.95), mat);
        armL.position.set(-1.22, 0.45, 0);
        const armR = armL.clone();
        armR.position.x = 1.22;
        group.add(seat, back, armL, armR);
    } else if (itemId === 'furn_armchair') {
        const fabric = new THREE.MeshStandardMaterial({ color: col || 0xf1f5f9, roughness: 0.75 });
        const wood = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.6 });
        const seat = new THREE.Mesh(new THREE.BoxGeometry(1.15, 0.28, 1.0), fabric);
        seat.position.y = 0.42;
        const back = new THREE.Mesh(new THREE.BoxGeometry(1.15, 0.65, 0.24), fabric);
        back.position.set(0, 0.76, -0.38);
        const armL = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.42, 0.96), fabric);
        armL.position.set(-0.52, 0.56, 0);
        const armR = armL.clone(); armR.position.x = 0.52;
        [[-0.48, -0.4], [0.48, -0.4], [-0.48, 0.4], [0.48, 0.4]].forEach(([lx, lz]) => {
            const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.03, 0.34, 8), wood);
            leg.position.set(lx, 0.17, lz);
            group.add(leg);
        });
        group.add(seat, back, armL, armR);
    } else if (itemId === 'furn_tv') {
        const stand = new THREE.Mesh(new THREE.BoxGeometry(2.6, 0.55, 0.6), new THREE.MeshStandardMaterial({ color: col || 0x451a03 }));
        stand.position.y = 0.28;
        const frame = new THREE.Mesh(new THREE.BoxGeometry(2.4, 1.35, 0.1), new THREE.MeshStandardMaterial({ color: 0x0f172a }));
        frame.position.y = 1.42;
        const screen = new THREE.Mesh(
            new THREE.PlaneGeometry(2.22, 1.18),
            new THREE.MeshBasicMaterial({ map: createSignTexture('📺 استودیو بازی تی‌وی روم', '#0284c7', '#fde047') })
        );
        screen.position.set(0, 1.42, 0.06);
        group.add(stand, frame, screen);
    } else if (itemId === 'furn_table') {
        const woodMat = new THREE.MeshStandardMaterial({ color: col || 0xb45309 });
        const top = new THREE.Mesh(new THREE.BoxGeometry(1.8, 0.12, 1.2), woodMat);
        top.position.y = 0.78;
        [[-0.78, -0.48], [0.78, -0.48], [-0.78, 0.48], [0.78, 0.48]].forEach(([lx, lz]) => {
            const leg = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.75, 0.1), woodMat);
            leg.position.set(lx, 0.37, lz);
            group.add(leg);
        });
        group.add(top);
    } else if (itemId === 'furn_arcade') {
        const cab = new THREE.Mesh(new THREE.BoxGeometry(1.3, 2.2, 1.1), new THREE.MeshStandardMaterial({ color: col || 0x10b981 }));
        cab.position.y = 1.1;
        const scr = new THREE.Mesh(new THREE.PlaneGeometry(1.0, 0.8), new THREE.MeshBasicMaterial({ color: 0x38bdf8 }));
        scr.position.set(0, 1.5, 0.56);
        group.add(cab, scr);
    } else if (itemId === 'furn_lamp') {
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.18, 1.65, 12), new THREE.MeshStandardMaterial({ color: 0x334155 }));
        pole.position.y = 0.82;
        const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.24, 0.44, 0.48, 16), new THREE.MeshStandardMaterial({ color: col || 0xfef08a, emissive: col || 0xf59e0b, emissiveIntensity: 0.65 }));
        shade.position.y = 1.75;
        const light = new THREE.PointLight(0xffe4b5, 1.1, 9, 2);
        light.position.set(0, 1.65, 0);
        group.add(pole, shade, light);
    } else if (itemId === 'furn_table_lamp') {
        const tbl = new THREE.Mesh(new THREE.CylinderGeometry(0.38, 0.32, 0.72, 16), new THREE.MeshStandardMaterial({ color: 0x0f172a }));
        tbl.position.y = 0.36;
        const base = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.14, 0.35, 12), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
        base.position.y = 0.9;
        const shade = new THREE.Mesh(new THREE.ConeGeometry(0.32, 0.36, 16, 1, true), new THREE.MeshStandardMaterial({ color: col || 0xfbbf24, emissive: col || 0xf59e0b, emissiveIntensity: 0.55, side: THREE.DoubleSide }));
        shade.position.y = 1.18;
        const light = new THREE.PointLight(0xffe4b5, 0.95, 8, 2);
        light.position.set(0, 1.12, 0);
        group.add(tbl, base, shade, light);
    } else if (itemId === 'furn_wall_sconce') {
        const mount = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.45, 0.12), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
        mount.position.y = 1.85;
        const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.18, 14, 14), new THREE.MeshStandardMaterial({ color: col || 0xfef08a, emissive: col || 0xfde047, emissiveIntensity: 0.85 }));
        bulb.position.set(0, 1.95, 0.14);
        const light = new THREE.PointLight(0xfffbeb, 1.0, 8.5, 2);
        light.position.set(0, 1.95, 0.3);
        group.add(mount, bulb, light);
    } else if (itemId === 'furn_plant') {
        return buildDoorstepPlant(1.15);
    } else if (itemId === 'furn_radiator') {
        const body = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.72, 0.22), new THREE.MeshStandardMaterial({ color: col || 0xef4444, roughness: 0.4 }));
        body.position.y = 0.42;
        group.add(body);
    } else if (itemId === 'furn_rug') {
        const rug = new THREE.Mesh(new THREE.BoxGeometry(3.4, 0.03, 2.4), new THREE.MeshStandardMaterial({ color: col || 0xeab308, roughness: 0.9 }));
        rug.position.y = 0.025;
        group.add(rug);
    } else if (itemId === 'furn_statue') {
        const ped = new THREE.Mesh(new THREE.BoxGeometry(0.75, 0.8, 0.75), new THREE.MeshStandardMaterial({ color: 0xe2e8f0 }));
        ped.position.y = 0.4;
        const art = new THREE.Mesh(new THREE.OctahedronGeometry(0.42, 1), new THREE.MeshStandardMaterial({ color: col || 0xfbbf24, metalness: 0.65, roughness: 0.25 }));
        art.position.y = 1.25;
        group.add(ped, art);
    } else if (itemId.startsWith('furn_frame_')) {
        const frame = new THREE.Mesh(new THREE.BoxGeometry(1.5, 1.15, 0.1), new THREE.MeshStandardMaterial({ color: col || 0x1e293b }));
        frame.position.y = 1.9;
        const art = new THREE.Mesh(new THREE.PlaneGeometry(1.3, 0.95), new THREE.MeshBasicMaterial({ map: createArtTexture(itemId) }));
        art.position.set(0, 1.9, 0.06);
        group.add(frame, art);
    } else if (itemId === 'furn_fridge') {
        const body = new THREE.Mesh(new THREE.BoxGeometry(1.15, 2.35, 0.95), new THREE.MeshStandardMaterial({ color: col || 0xe2e8f0, metalness: 0.35, roughness: 0.35 }));
        body.position.y = 1.18;
        const line = new THREE.Mesh(new THREE.BoxGeometry(1.17, 0.04, 0.05), new THREE.MeshStandardMaterial({ color: 0x475569 }));
        line.position.set(0, 1.45, 0.46);
        group.add(body, line);
    } else if (itemId === 'furn_stove') {
        const body = new THREE.Mesh(new THREE.BoxGeometry(1.15, 0.95, 0.9), new THREE.MeshStandardMaterial({ color: col || 0x334155 }));
        body.position.y = 0.48;
        const glass = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.48, 0.05), new THREE.MeshStandardMaterial({ color: 0x0f172a }));
        glass.position.set(0, 0.45, 0.45);
        group.add(body, glass);
    } else if (itemId === 'furn_kitchen_island') {
        const base = new THREE.Mesh(new THREE.BoxGeometry(2.0, 0.92, 0.95), new THREE.MeshStandardMaterial({ color: col || 0xf8fafc }));
        base.position.y = 0.46;
        const top = new THREE.Mesh(new THREE.BoxGeometry(2.1, 0.08, 1.05), new THREE.MeshStandardMaterial({ color: 0xd97706 }));
        top.position.y = 0.95;
        group.add(base, top);
    } else if (itemId === 'furn_bed_single' || itemId === 'furn_bed_double' || itemId === 'furn_bed' || itemId === 'furn_bed_bunk') {
        const w = itemId === 'furn_bed_double' ? 2.4 : 1.5;
        const frame = new THREE.Mesh(new THREE.BoxGeometry(w, 0.38, 2.3), new THREE.MeshStandardMaterial({ color: 0x78350f }));
        frame.position.y = 0.22;
        const head = new THREE.Mesh(new THREE.BoxGeometry(w, 1.1, 0.16), new THREE.MeshStandardMaterial({ color: 0x451a03 }));
        head.position.set(0, 0.55, -1.08);
        const blanket = new THREE.Mesh(new THREE.BoxGeometry(w - 0.12, 0.24, 1.65), new THREE.MeshStandardMaterial({ color: col || 0x6366f1 }));
        blanket.position.set(0, 0.46, 0.25);
        const pillow = new THREE.Mesh(new THREE.BoxGeometry(w - 0.35, 0.16, 0.42), new THREE.MeshStandardMaterial({ color: 0xffffff }));
        pillow.position.set(0, 0.5, -0.78);
        group.add(frame, head, blanket, pillow);
        if (itemId === 'furn_bed_bunk') {
            const topBlanket = blanket.clone();
            topBlanket.position.y = 1.55;
            const topFrame = frame.clone();
            topFrame.position.y = 1.32;
            group.add(topFrame, topBlanket);
        }
    }
    return group;
}

function buildStyledWall(w, h, d, upperMat, lowerColor = 0x0f172a, trimColor = 0x0891b2) {
    const grp = new THREE.Group();
    const upper = new THREE.Mesh(new THREE.BoxGeometry(w, h * 0.62, d), upperMat);
    upper.position.y = h * 0.69;
    const lower = new THREE.Mesh(new THREE.BoxGeometry(w, h * 0.34, d + 0.03), new THREE.MeshStandardMaterial({ color: lowerColor, roughness: 0.65 }));
    lower.position.y = h * 0.21;
    const base = new THREE.Mesh(new THREE.BoxGeometry(w, h * 0.08, d + 0.06), new THREE.MeshStandardMaterial({ color: trimColor }));
    base.position.y = h * 0.04;
    grp.add(upper, lower, base);
    return grp;
}

export function buildHomeInterior(homeData, ctx) {
    const hs = homeData.homeStyle || {};
    const wallColor = hs.wallColor ?? 0x94a3b8;
    const wallStyle = hs.wallStyle || 'wall_paint_classic';
    const floorColor = hs.floorColor ?? 0xd8b48b;
    const floorStyle = hs.floorStyle || 'floor_parquet_oak';
    const cabColor = hs.kitchenCabinetColor ?? 0xf8fafc;
    const ceramicColor = hs.kitchenCeramicColor ?? 0xe2e8f0;

    // کف اصلی یکپارچه برای تمام فضاها (از باغچه چپ تا بالکن راست)
    const floorTex = createPatternTexture(floorStyle, floorColor);
    const ground = new THREE.Mesh(
        new THREE.PlaneGeometry(46, 24),
        new THREE.MeshStandardMaterial({ map: floorTex, roughness: 0.72 })
    );
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    ctx.worldGroup.add(ground);
    ctx.setGround(ground);

    if (homeData.isMine) {
        const grid = new THREE.GridHelper(44, 44, 0x94a3b8, 0xcbd5e1);
        grid.position.y = 0.015;
        ctx.worldGroup.add(grid);
    }

    const wallMat = (wallStyle === 'wall_paint_classic')
        ? new THREE.MeshStandardMaterial({ color: wallColor, roughness: 0.7 })
        : new THREE.MeshStandardMaterial({ map: createPatternTexture(wallStyle, wallColor), roughness: 0.75 });

    // دیوارهای بیرونی خانه اصلی (x: -15 تا +15, z: -12 تا +10)
    const backWall = buildStyledWall(30, 4.4, 0.35, wallMat);
    backWall.position.set(0, 0, -11.8);
    const leftWall = buildStyledWall(0.35, 4.4, 21.8, wallMat);
    leftWall.position.set(-15, 0, -0.9);
    const rightWall = buildStyledWall(0.35, 4.4, 21.8, wallMat);
    rightWall.position.set(15, 0, -0.9);
    ctx.worldGroup.add(backWall, leftWall, rightWall);

    // پنجره‌های بلند با پرده (الهام از room-design-perfect.webp)
    [-9.5, 0, 9.5].forEach(wx => {
        const winFrame = new THREE.Mesh(new THREE.BoxGeometry(3.2, 2.5, 0.14), new THREE.MeshStandardMaterial({ color: 0xffffff }));
        winFrame.position.set(wx, 2.3, -11.6);
        const glass = new THREE.Mesh(new THREE.PlaneGeometry(2.8, 2.1), new THREE.MeshBasicMaterial({ color: 0xe0f2fe }));
        glass.position.set(wx, 2.3, -11.52);
        const curL = new THREE.Mesh(new THREE.BoxGeometry(0.42, 2.8, 0.18), new THREE.MeshStandardMaterial({ color: 0x475569 }));
        curL.position.set(wx - 1.6, 2.35, -11.5);
        const curR = curL.clone(); curR.position.x = wx + 1.6;
        ctx.worldGroup.add(winFrame, glass, curL, curR);
    });

    // پارتیشن‌های جداکننده آشپزخانه، اتاق TV و اتاق خواب (همراه با درگاه عبور)
    const divLeft = buildStyledWall(0.3, 4.0, 7.5, wallMat);
    divLeft.position.set(-5, 0, -8.0);
    ctx.obstacles.push({ minX: -5.3, maxX: -4.7, minZ: -11.8, maxZ: -4.2 });

    const divRight = buildStyledWall(0.3, 4.0, 7.5, wallMat);
    divRight.position.set(5, 0, -8.0);
    ctx.obstacles.push({ minX: 4.7, maxX: 5.3, minZ: -11.8, maxZ: -4.2 });
    ctx.worldGroup.add(divLeft, divRight);

    // ۱. آشپزخانه (شمال‌غرب: x -15..-5, z -12..-2)
    const kitchenFloor = new THREE.Mesh(
        new THREE.PlaneGeometry(9.6, 9.6),
        new THREE.MeshStandardMaterial({ map: createPatternTexture('floor_ceramic_checker', ceramicColor), roughness: 0.5 })
    );
    kitchenFloor.rotation.x = -Math.PI / 2;
    kitchenFloor.position.set(-10, 0.02, -7.0);
    const counter = new THREE.Mesh(new THREE.BoxGeometry(7.6, 1.0, 1.15), new THREE.MeshStandardMaterial({ color: cabColor }));
    counter.position.set(-10, 0.5, -10.9);
    const counterTop = new THREE.Mesh(new THREE.BoxGeometry(7.7, 0.1, 1.25), new THREE.MeshStandardMaterial({ color: 0x334155 }));
    counterTop.position.set(-10, 1.03, -10.9);
    ctx.worldGroup.add(kitchenFloor, counter, counterTop);
    ctx.addZoneLabel(-10, 3.8, -11.2, '🍳 آشپزخانه');
    ctx.obstacles.push({ minX: -14.0, maxX: -6.0, minZ: -11.6, maxZ: -10.1 });

    // ۲. اتاق TV (شمال‌مرکز: x -5..5, z -12..-2) + تلویزیون پیش‌فرض همیشه فعال!
    const tvRug = new THREE.Mesh(new THREE.PlaneGeometry(7.2, 5.2), new THREE.MeshStandardMaterial({ color: 0x991b1b }));
    tvRug.rotation.x = -Math.PI / 2;
    tvRug.position.set(0, 0.025, -6.5);

    // تلویزیون پیش‌فرض در تمام خانه‌های کاربران (محل آپلود و اجرای بازی‌ها)
    const defaultTv = buildRealisticFurniture('furn_tv');
    defaultTv.position.set(0, 0, -10.7);
    defaultTv.userData = { action: 'tv_studio', homeData };
    ctx.interactables.push(defaultTv);
    ctx.worldGroup.add(tvRug, defaultTv);
    ctx.addZoneLabel(0, 3.8, -11.2, '📺 اتاق TV و بازی');

    // ۳. اتاق خواب (شمال‌شرق: x 5..15, z -12..-2)
    const bedRug = new THREE.Mesh(new THREE.PlaneGeometry(7.5, 5.5), new THREE.MeshStandardMaterial({ color: 0x312e81 }));
    bedRug.rotation.x = -Math.PI / 2;
    bedRug.position.set(10, 0.025, -6.5);
    ctx.worldGroup.add(bedRug);
    ctx.addZoneLabel(10, 3.8, -11.2, '🛏️ اتاق خواب');

    const wardrobe = new THREE.Group();
    wardrobe.position.set(13.4, 0, -10.7);
    const wBody = new THREE.Mesh(new THREE.BoxGeometry(2.0, 2.8, 0.95), new THREE.MeshStandardMaterial({ color: 0x78350f }));
    wBody.position.y = 1.4;
    wardrobe.add(wBody);
    ctx.worldGroup.add(wardrobe);
    ctx.addZoneLabel(13.4, 3.2, -10.6, '👗 کمد لباس');
    if (homeData.isMine) {
        wardrobe.userData = { action: 'wardrobe' };
        ctx.interactables.push(wardrobe);
    }

    // ۴. نشیمن برای دوستان (جنوب‌مرکز و راست: x -12..14, z 0..8)
    const loungeRug = new THREE.Mesh(new THREE.PlaneGeometry(9.5, 5.4), new THREE.MeshStandardMaterial({ color: 0xeab308 }));
    loungeRug.rotation.x = -Math.PI / 2;
    loungeRug.position.set(2, 0.025, 3.5);
    ctx.worldGroup.add(loungeRug);
    ctx.addZoneLabel(2, 1.4, 3.5, '🛋️ نشیمن دوستان');

    // ۵. باغچه (سمت چپ خانه: x -23..-15, z -11..9)
    const lawn = new THREE.Mesh(new THREE.PlaneGeometry(7.6, 20), new THREE.MeshStandardMaterial({ color: 0x22c55e, roughness: 0.9 }));
    lawn.rotation.x = -Math.PI / 2;
    lawn.position.set(-19, 0.025, -1);
    const fenceMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc });
    const gFence = new THREE.Mesh(new THREE.BoxGeometry(0.2, 1.0, 20), fenceMat);
    gFence.position.set(-22.8, 0.5, -1);
    ctx.worldGroup.add(lawn, gFence);
    ctx.addZoneLabel(-19, 2.4, -10.2, '🌿 باغچه');
    [-8, -3, 3, 7].forEach(gz => {
        const bush = buildDoorstepPlant(1.1);
        bush.position.set(-21.2, 0, gz);
        ctx.worldGroup.add(bush);
    });

    // ۶. بالکن (سمت راست خانه: x 15..22, z -11..9)
    const balcFloor = new THREE.Mesh(new THREE.PlaneGeometry(6.6, 20), new THREE.MeshStandardMaterial({ color: 0x92400e, roughness: 0.8 }));
    balcFloor.rotation.x = -Math.PI / 2;
    balcFloor.position.set(18.5, 0.025, -1);
    const balcRail = new THREE.Mesh(new THREE.BoxGeometry(0.16, 1.15, 20), new THREE.MeshStandardMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.55 }));
    balcRail.position.set(21.8, 0.58, -1);
    ctx.worldGroup.add(balcFloor, balcRail);
    ctx.addZoneLabel(18.5, 2.4, -10.2, '🌅 بالکن');

    ctx.addPortal(0, 10.2, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
}
