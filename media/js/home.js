import * as THREE from 'three';
import { createSignTexture, buildDoorstepPlant } from './city.js';

export function buildRealisticFurniture(itemId, customColor) {
    const group = new THREE.Group();
    if (itemId === 'furn_sofa') {
        const mat = new THREE.MeshStandardMaterial({ color: customColor || 0xef4444, roughness: 0.75 });
        const seat = new THREE.Mesh(new THREE.BoxGeometry(2.5, 0.42, 0.95), mat);
        seat.position.y = 0.32;
        const back = new THREE.Mesh(new THREE.BoxGeometry(2.5, 0.65, 0.25), mat);
        back.position.set(0, 0.72, -0.35);
        group.add(seat, back);
    } else if (itemId === 'furn_tv') {
        const stand = new THREE.Mesh(new THREE.BoxGeometry(2.4, 0.55, 0.55), new THREE.MeshStandardMaterial({ color: 0x78350f }));
        stand.position.y = 0.28;
        const frame = new THREE.Mesh(new THREE.BoxGeometry(2.2, 1.25, 0.08), new THREE.MeshStandardMaterial({ color: 0x0f172a }));
        frame.position.y = 1.35;
        const screen = new THREE.Mesh(new THREE.PlaneGeometry(2.05, 1.1), new THREE.MeshBasicMaterial({ map: createSignTexture('🎮 استودیو بازی تی‌وی روم', '#0284c7', '#fde047') }));
        screen.position.set(0, 1.35, 0.045);
        group.add(stand, frame, screen);
    } else if (itemId === 'furn_table') {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0xb45309 });
        const top = new THREE.Mesh(new THREE.BoxGeometry(1.8, 0.12, 1.2), woodMat);
        top.position.y = 0.78;
        [[-0.78, -0.48], [0.78, -0.48], [-0.78, 0.48], [0.78, 0.48]].forEach(([lx, lz]) => {
            const leg = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.75, 0.1), woodMat);
            leg.position.set(lx, 0.37, lz);
            group.add(leg);
        });
        group.add(top);
    } else if (itemId === 'furn_arcade') {
        const cab = new THREE.Mesh(new THREE.BoxGeometry(1.3, 2.2, 1.1), new THREE.MeshStandardMaterial({ color: 0x10b981 }));
        cab.position.y = 1.1;
        const scr = new THREE.Mesh(new THREE.PlaneGeometry(1.0, 0.8), new THREE.MeshBasicMaterial({ color: 0x38bdf8 }));
        scr.position.set(0, 1.5, 0.56);
        group.add(cab, scr);
    } else if (itemId === 'furn_lamp') {
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.18, 1.55, 12), new THREE.MeshStandardMaterial({ color: 0x334155 }));
        pole.position.y = 0.78;
        const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.24, 0.42, 0.45, 16), new THREE.MeshStandardMaterial({ color: 0xfef08a, emissive: 0xf59e0b, emissiveIntensity: 0.65 }));
        shade.position.y = 1.65;
        group.add(pole, shade);
    } else if (itemId === 'furn_plant') {
        return buildDoorstepPlant(1.15);
    }
    return group;
}

export function buildHomeInterior(homeData, ctx) {
    const ground = new THREE.Mesh(new THREE.PlaneGeometry(26, 22), new THREE.MeshStandardMaterial({ color: 0xd97706, roughness: 0.8 }));
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    ctx.worldGroup.add(ground);
    ctx.setGround(ground);

    if (homeData.isMine) {
        const grid = new THREE.GridHelper(26, 26, 0x78350f, 0xb45309);
        grid.position.y = 0.02;
        ctx.worldGroup.add(grid);
    }

    // دیوارهای بیرونی
    const wallMat = new THREE.MeshStandardMaterial({ color: 0xfef9c3 });
    const backWall = new THREE.Mesh(new THREE.BoxGeometry(26, 4.5, 0.4), wallMat);
    backWall.position.set(0, 2.25, -11);
    const leftWall = new THREE.Mesh(new THREE.BoxGeometry(0.4, 4.5, 22), wallMat);
    leftWall.position.set(-13, 2.25, 0);
    const rightWall = new THREE.Mesh(new THREE.BoxGeometry(0.4, 4.5, 22), wallMat);
    rightWall.position.set(13, 2.25, 0);
    ctx.worldGroup.add(backWall, leftWall, rightWall);

    // پارتیشن‌های جداکننده آشپزخانه (چپ)، هال و تی‌وی روم (وسط) و اتاق بازی (راست)
    const divMat = new THREE.MeshStandardMaterial({ color: 0xfde68a });
    const divLeft = new THREE.Mesh(new THREE.BoxGeometry(0.3, 3.2, 6.5), divMat);
    divLeft.position.set(-4.5, 1.6, -7.6);
    ctx.obstacles.push({ minX: -4.8, maxX: -4.2, minZ: -11, maxZ: -4.2 });

    const divRight = new THREE.Mesh(new THREE.BoxGeometry(0.3, 3.2, 6.5), divMat);
    divRight.position.set(4.5, 1.6, -7.6);
    ctx.obstacles.push({ minX: 4.2, maxX: 4.8, minZ: -11, maxZ: -4.2 });
    ctx.worldGroup.add(divLeft, divRight);

    // ۱. بخش آشپزخانه (شمال‌غرب)
    const counter = new THREE.Mesh(new THREE.BoxGeometry(6.8, 1.0, 1.1), new THREE.MeshStandardMaterial({ color: 0xf8fafc }));
    counter.position.set(-8.6, 0.5, -10.1);
    const kitchenSign = new THREE.Mesh(new THREE.BoxGeometry(3.2, 0.7, 0.15), new THREE.MeshBasicMaterial({ map: createSignTexture('🍳 آشپزخانه', '#0f172a', '#38bdf8') }));
    kitchenSign.position.set(-8.6, 3.5, -10.7);
    ctx.worldGroup.add(counter, kitchenSign);
    ctx.obstacles.push({ minX: -12.2, maxX: -5.0, minZ: -10.9, maxZ: -9.2 });

    // ۲. بخش هال و تی‌وی روم (مرکز)
    const hallRug = new THREE.Mesh(new THREE.PlaneGeometry(6.8, 4.8), new THREE.MeshStandardMaterial({ color: 0x991b1b }));
    hallRug.rotation.x = -Math.PI / 2;
    hallRug.position.set(0, 0.025, -5.5);
    const tvRoomSign = new THREE.Mesh(new THREE.BoxGeometry(3.8, 0.7, 0.15), new THREE.MeshBasicMaterial({ map: createSignTexture('📺 هال و تی‌وی روم', '#0f172a', '#facc15') }));
    tvRoomSign.position.set(0, 3.5, -10.7);
    ctx.worldGroup.add(hallRug, tvRoomSign);

    // ۳. بخش اتاق بازی (شمال‌شرق) + کمد لباس
    const playRug = new THREE.Mesh(new THREE.PlaneGeometry(6.8, 5.2), new THREE.MeshStandardMaterial({ color: 0x2563eb }));
    playRug.rotation.x = -Math.PI / 2;
    playRug.position.set(8.6, 0.025, -6.5);
    const playRoomSign = new THREE.Mesh(new THREE.BoxGeometry(3.6, 0.7, 0.15), new THREE.MeshBasicMaterial({ map: createSignTexture('🎮 اتاق بازی (Playroom)', '#065f46', '#34d399') }));
    playRoomSign.position.set(8.6, 3.5, -10.7);
    ctx.worldGroup.add(playRug, playRoomSign);

    // کمد لباس تعاملی
    const wardrobe = new THREE.Group();
    wardrobe.position.set(11.4, 0, -9.8);
    const wBody = new THREE.Mesh(new THREE.BoxGeometry(2.0, 2.8, 0.95), new THREE.MeshStandardMaterial({ color: 0x78350f }));
    wBody.position.y = 1.4;
    const wSign = new THREE.Mesh(new THREE.BoxGeometry(2.4, 0.65, 0.15), new THREE.MeshBasicMaterial({ map: createSignTexture('👗 کمد لباس', '#4f46e5', '#fff') }));
    wSign.position.set(0, 3.15, 0.1);
    wardrobe.add(wBody, wSign);
    ctx.worldGroup.add(wardrobe);

    if (homeData.isMine) {
        wardrobe.userData = { action: 'wardrobe' };
        ctx.interactables.push(wardrobe);
    }

    ctx.addPortal(0, 9.2, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
}