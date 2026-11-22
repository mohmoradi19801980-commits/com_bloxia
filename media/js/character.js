import * as THREE from 'https://unpkg.com/three@0.160.0/build/three.module.js';

/**
 * ============================================================================
 * سازنده مدل‌های موی سه‌بعدی با جزئیات (چتری، لایه‌های کناری، کش مو و فرها)
 * ============================================================================
 */
export function buildHairMesh(style, hairMat) {
    const group = new THREE.Group();

    // کلاهک اصلی مو (فقط نیم‌کره بالایی سر، بالاتر از چشم‌ها و پیشانی)
    const cap = new THREE.Mesh(
        new THREE.SphereGeometry(0.385, 20, 16, 0, Math.PI * 2, 0, Math.PI * 0.44),
        hairMat
    );
    cap.scale.set(1.03, 1.05, 1.05);
    cap.position.set(0, 0.06, -0.03);
    cap.rotation.x = -0.12; // کمی متمایل به عقب تا پیشانی کاملاً باز بماند
    cap.castShadow = true;
    group.add(cap);

    // چتری ظریف بالای پیشانی (بالاتر از ابروها در y = 0.28)
    const bangLeft = new THREE.Mesh(new THREE.SphereGeometry(0.11, 12, 10), hairMat);
    bangLeft.scale.set(1.3, 0.55, 0.7);
    bangLeft.position.set(-0.13, 0.29, 0.27);

    const bangRight = new THREE.Mesh(new THREE.SphereGeometry(0.12, 12, 10), hairMat);
    bangRight.scale.set(1.35, 0.6, 0.7);
    bangRight.position.set(0.11, 0.28, 0.27);
    group.add(bangLeft, bangRight);

    if (style === 'short') {
        // پوشش پشت سر (عقب‌تر از گوش‌ها تا روی صورت نیاید)
        const back = new THREE.Mesh(new THREE.SphereGeometry(0.36, 16, 14), hairMat);
        back.scale.set(1.0, 1.02, 0.75);
        back.position.set(0, 0.02, -0.14);
        group.add(back);

    } else if (style === 'long') {
        const backLong = new THREE.Mesh(new THREE.CapsuleGeometry(0.31, 0.55, 8, 16), hairMat);
        backLong.scale.set(1.12, 1.0, 0.6);
        backLong.position.set(0, -0.26, -0.24);
        backLong.castShadow = true;

        const strandL = new THREE.Mesh(new THREE.CapsuleGeometry(0.075, 0.42, 6, 10), hairMat);
        strandL.position.set(-0.34, -0.22, 0.06);
        strandL.rotation.z = 0.08;

        const strandR = strandL.clone();
        strandR.position.x = 0.34;
        strandR.rotation.z = -0.08;

        group.add(backLong, strandL, strandR);

    } else if (style === 'ponytail') {
        const tieMat = new THREE.MeshStandardMaterial({ color: 0xf43f5e, roughness: 0.4 });
        const hairTie = new THREE.Mesh(new THREE.TorusGeometry(0.11, 0.035, 8, 16), tieMat);
        hairTie.position.set(0, 0.16, -0.38);
        hairTie.rotation.x = Math.PI / 2.5;

        const tail = new THREE.Mesh(new THREE.CapsuleGeometry(0.13, 0.45, 8, 12), hairMat);
        tail.position.set(0, -0.06, -0.5);
        tail.rotation.x = 0.38;
        tail.castShadow = true;

        group.add(hairTie, tail);

    } else if (style === 'curly') {
        // خوشه‌های فر پفکی فقط روی تاج و پشت سر (بالاتر از خط چشم)
        const puffCoords = [
            [-0.28, 0.28, 0.14], [0.28, 0.28, 0.14], [0, 0.38, 0.1],
            [-0.35, 0.14, -0.08], [0.35, 0.14, -0.08], [-0.26, 0.28, -0.22],
            [0.26, 0.28, -0.22], [0, 0.34, -0.26], [-0.22, 0.02, -0.3],
            [0.22, 0.02, -0.3], [0, 0.04, -0.35]
        ];
        puffCoords.forEach(([px, py, pz]) => {
            const puff = new THREE.Mesh(new THREE.SphereGeometry(0.15, 12, 10), hairMat);
            puff.position.set(px, py, pz);
            puff.castShadow = true;
            group.add(puff);
        });
    }

    return group;
}

/**
 * ============================================================================
 * سازنده لوازم جانبی دست با جزئیات (شمشیر، تبر، دسته گل، موبایل)
 * ============================================================================
 */
export function buildHandPropMesh(propId) {
    const group = new THREE.Group();
    if (propId === 'prop_sword') {
        const hilt = new THREE.Mesh(new THREE.CylinderGeometry(0.045, 0.05, 0.28, 10), new THREE.MeshStandardMaterial({ color: 0x78350f }));
        const pommel = new THREE.Mesh(new THREE.SphereGeometry(0.07), new THREE.MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.7 }));
        pommel.position.y = -0.15;
        const guard = new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.08, 0.14), new THREE.MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.7 }));
        guard.position.y = 0.15;
        const blade = new THREE.Mesh(new THREE.BoxGeometry(0.13, 0.92, 0.045), new THREE.MeshStandardMaterial({ color: 0x38bdf8, emissive: 0x0284c7, emissiveIntensity: 0.4, metalness: 0.6 }));
        blade.position.y = 0.64;
        group.add(hilt, pommel, guard, blade);
        group.rotation.x = Math.PI / 2.3;
    } else if (propId === 'prop_axe') {
        const handle = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.045, 1.05, 10), new THREE.MeshStandardMaterial({ color: 0x92400e }));
        handle.position.y = 0.35;
        const head = new THREE.Mesh(new THREE.BoxGeometry(0.45, 0.32, 0.09), new THREE.MeshStandardMaterial({ color: 0xcbd5e1, metalness: 0.8 }));
        head.position.set(0.14, 0.75, 0);
        group.add(handle, head);
        group.rotation.x = Math.PI / 2.3;
    } else if (propId === 'prop_flower') {
        const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.55, 8), new THREE.MeshStandardMaterial({ color: 0x16a34a }));
        stem.position.y = 0.2;
        const wrap = new THREE.Mesh(new THREE.ConeGeometry(0.16, 0.28, 12, 1, true), new THREE.MeshStandardMaterial({ color: 0xfef3c7, side: THREE.DoubleSide }));
        wrap.position.y = 0.34;
        wrap.rotation.x = Math.PI;
        const colors = [0xf43f5e, 0xfacc15, 0xa855f7];
        colors.forEach((c, i) => {
            const fl = new THREE.Mesh(new THREE.SphereGeometry(0.11, 10, 10), new THREE.MeshStandardMaterial({ color: c }));
            const a = (i / 3) * Math.PI * 2;
            fl.position.set(Math.cos(a) * 0.09, 0.5, Math.sin(a) * 0.09);
            group.add(fl);
        });
        group.add(stem, wrap);
        group.rotation.x = Math.PI / 3;
    } else if (propId === 'prop_phone') {
        const body = new THREE.Mesh(new THREE.BoxGeometry(0.22, 0.4, 0.04), new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 }));
        const screen = new THREE.Mesh(new THREE.PlaneGeometry(0.18, 0.34), new THREE.MeshBasicMaterial({ color: 0x38bdf8 }));
        screen.position.z = 0.022;
        group.add(body, screen);
        group.rotation.x = Math.PI / 3;
    }
    return group;
}

/**
 * ============================================================================
 * سازنده کاراکتر سه‌بعدی پرجزئیات (صورت زنده، چشم‌های درخشان، لباس یقه‌دار، کفش کتانی)
 * ============================================================================
 */
export function createCharacterRig(name, appearance, citizenData = null) {
    const root = new THREE.Group();
    if (citizenData) root.userData = { citizenData };

    const skinMat = new THREE.MeshStandardMaterial({ color: appearance.skinColor, roughness: 0.55 });
    const hairMat = new THREE.MeshStandardMaterial({ color: appearance.hairColor, roughness: 0.78 });
    const shirtMat = new THREE.MeshStandardMaterial({ color: appearance.shirtColor, roughness: 0.65 });
    const pantsMat = new THREE.MeshStandardMaterial({ color: appearance.pantsColor, roughness: 0.75 });
    const shoesMat = new THREE.MeshStandardMaterial({ color: appearance.shoesColor || 0xf8fafc, roughness: 0.45 });
    const whiteTrimMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.5 });

    // حلقه نشانگر زیر پای بازیکنان آنلاین برای تشخیص کلیک‌پذیری
    if (citizenData) {
        const ring = new THREE.Mesh(
            new THREE.RingGeometry(0.58, 0.76, 28),
            new THREE.MeshBasicMaterial({ color: 0x38bdf8, side: THREE.DoubleSide, transparent: true, opacity: 0.8 })
        );
        ring.rotation.x = -Math.PI / 2;
        ring.position.y = 0.03;
        root.add(ring);
    }

    // 1. تنه (Torso) با فرم کپسولی نرم + یقه و دکمه لباس
    const bodyGroup = new THREE.Group();
    bodyGroup.position.y = 1.26;

    const torso = new THREE.Mesh(new THREE.CapsuleGeometry(0.34, 0.44, 10, 18), shirtMat);
    torso.scale.set(1.12, 1.0, 0.78);
    torso.castShadow = true;
    bodyGroup.add(torso);

    // یقه سفید لباس
    const collar = new THREE.Mesh(new THREE.TorusGeometry(0.21, 0.045, 8, 18), whiteTrimMat);
    collar.rotation.x = Math.PI / 2.2;
    collar.position.set(0, 0.42, 0.03);
    bodyGroup.add(collar);

    // کمربند و سگک طلایی
    const belt = new THREE.Mesh(new THREE.CylinderGeometry(0.35, 0.36, 0.1, 18), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
    belt.scale.set(1.12, 1.0, 0.8);
    belt.position.y = -0.34;
    const buckle = new THREE.Mesh(new THREE.BoxGeometry(0.14, 0.11, 0.06), new THREE.MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.7 }));
    buckle.position.set(0, -0.34, 0.28);
    bodyGroup.add(belt, buckle);

    // دکمه‌های روی پیراهن
    [0.2, 0.02, -0.15].forEach(by => {
        const btn = new THREE.Mesh(new THREE.SphereGeometry(0.032, 8, 8), whiteTrimMat);
        btn.position.set(0, by, 0.27);
        bodyGroup.add(btn);
    });

    root.add(bodyGroup);

    // 2. سر و اجزای چهره زنده (چشم‌های براق، ابرو، گونه گل‌انداخته، بینی، لبخند، گوش)
    const headGroup = new THREE.Group();
    headGroup.position.y = 2.08;

    const head = new THREE.Mesh(new THREE.SphereGeometry(0.37, 22, 18), skinMat);
    head.scale.set(1.04, 1.02, 0.98);
    head.castShadow = true;
    headGroup.add(head);

    // گوش‌های گرد چپ و راست
    [-0.36, 0.36].forEach(ex => {
        const ear = new THREE.Mesh(new THREE.SphereGeometry(0.085, 10, 10), skinMat);
        ear.position.set(ex, -0.02, -0.02);
        headGroup.add(ear);
    });

    // چشم‌های درشت با نقطه درخشش سفید (Eye Catchlights)
    const eyeWhiteMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
    const pupilMat = new THREE.MeshBasicMaterial({ color: 0x0f172a });

    [-0.13, 0.13].forEach(ex => {
        const eyeBg = new THREE.Mesh(new THREE.SphereGeometry(0.068, 12, 12), eyeWhiteMat);
        eyeBg.scale.set(0.9, 1.15, 0.5);
        eyeBg.position.set(ex, 0.03, 0.33);

        const pupil = new THREE.Mesh(new THREE.SphereGeometry(0.046, 12, 12), pupilMat);
        pupil.scale.set(0.9, 1.15, 0.5);
        pupil.position.set(ex, 0.03, 0.352);

        // نقطه درخشش داخل چشم برای زنده شدن نگاه کاراکتر
        const shine = new THREE.Mesh(new THREE.SphereGeometry(0.018, 8, 8), eyeWhiteMat);
        shine.position.set(ex + 0.016, 0.052, 0.372);

        // ابرو هماهنگ با رنگ مو
        const brow = new THREE.Mesh(new THREE.BoxGeometry(0.11, 0.026, 0.03), hairMat);
        brow.position.set(ex, 0.13, 0.34);
        brow.rotation.z = ex < 0 ? 0.08 : -0.08;

        headGroup.add(eyeBg, pupil, shine, brow);
    });

    // لپ‌های گل‌انداخته (Blush)
    const blushMat = new THREE.MeshBasicMaterial({ color: 0xfb7185, transparent: true, opacity: 0.55 });
    [-0.2, 0.2].forEach(bx => {
        const blush = new THREE.Mesh(new THREE.CircleGeometry(0.055, 12), blushMat);
        blush.position.set(bx, -0.05, 0.335);
        blush.rotation.y = bx < 0 ? -0.45 : 0.45;
        headGroup.add(blush);
    });

    // بینی کوچک و لبخند مهربان
    const nose = new THREE.Mesh(new THREE.SphereGeometry(0.032, 10, 10), skinMat);
    nose.position.set(0, -0.02, 0.365);

    const smile = new THREE.Mesh(
        new THREE.TorusGeometry(0.065, 0.016, 8, 16, Math.PI),
        new THREE.MeshBasicMaterial({ color: 0x9f1239 })
    );
    smile.rotation.x = Math.PI;
    smile.position.set(0, -0.09, 0.35);
    headGroup.add(nose, smile);

    // اسلات مو و کلاه
    const hairSlot = new THREE.Group();
    headGroup.add(hairSlot);

    const hatSlot = new THREE.Group();
    hatSlot.position.y = 0.42;
    headGroup.add(hatSlot);
    root.add(headGroup);

    // 3. بازوها با آستین پفکی و دست‌های گرد
    const leftArmPivot = new THREE.Group();
    leftArmPivot.position.set(-0.46, 1.62, 0);
    const leftSleeve = new THREE.Mesh(new THREE.SphereGeometry(0.14, 12, 12), shirtMat);
    leftSleeve.position.y = -0.06;
    const leftArm = new THREE.Mesh(new THREE.CapsuleGeometry(0.085, 0.36, 8, 12), skinMat);
    leftArm.position.y = -0.32;
    const leftHand = new THREE.Mesh(new THREE.SphereGeometry(0.105, 12, 12), skinMat);
    leftHand.position.y = -0.56;
    leftArmPivot.add(leftSleeve, leftArm, leftHand);
    root.add(leftArmPivot);

    const rightArmPivot = new THREE.Group();
    rightArmPivot.position.set(0.46, 1.62, 0);
    const rightSleeve = new THREE.Mesh(new THREE.SphereGeometry(0.14, 12, 12), shirtMat);
    rightSleeve.position.y = -0.06;
    const rightArm = new THREE.Mesh(new THREE.CapsuleGeometry(0.085, 0.36, 8, 12), skinMat);
    rightArm.position.y = -0.32;
    const rightHand = new THREE.Mesh(new THREE.SphereGeometry(0.105, 12, 12), skinMat);
    rightHand.position.y = -0.56;
    rightArmPivot.add(rightSleeve, rightArm, rightHand);

    const rightHandSlot = new THREE.Group();
    rightHandSlot.position.set(0, -0.62, 0.1);
    rightArmPivot.add(rightHandSlot);
    root.add(rightArmPivot);

    // 4. پاها + کفش‌های کتانی با کفی سفید و بند
    function buildLegWithSneaker(xOffset) {
        const legPivot = new THREE.Group();
        legPivot.position.set(xOffset, 0.84, 0);

        const leg = new THREE.Mesh(new THREE.CapsuleGeometry(0.125, 0.42, 8, 14), pantsMat);
        leg.position.y = -0.28;
        leg.castShadow = true;

        // بدنه کفش کتانی
        const shoeBody = new THREE.Mesh(new THREE.CapsuleGeometry(0.135, 0.18, 8, 14), shoesMat);
        shoeBody.rotation.x = Math.PI / 2;
        shoeBody.position.set(0, -0.66, 0.06);
        shoeBody.castShadow = true;

        // کفی سفید زیر کفش (Sneaker Sole)
        const sole = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.07, 0.44), whiteTrimMat);
        sole.position.set(0, -0.78, 0.06);

        // بند سفید روی کفش
        const lace = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.03, 0.14), whiteTrimMat);
        lace.position.set(0, -0.54, 0.1);

        legPivot.add(leg, shoeBody, sole, lace);
        return legPivot;
    }

    const leftLegPivot = buildLegWithSneaker(-0.18);
    const rightLegPivot = buildLegWithSneaker(0.18);
    root.add(leftLegPivot, rightLegPivot);

    // 5. برچسب نام و حباب گفتگو
    const labelEl = document.createElement('div');
    labelEl.className = 'floating-label';
    labelEl.innerHTML = `<div class="chat-bubble" style="display:none;"></div><div class="player-tag">${name}</div>`;
    document.getElementById('labels-layer').appendChild(labelEl);

    const rig = {
        mesh: root,
        parts: { bodyGroup, headGroup, leftArmPivot, rightArmPivot, leftLegPivot, rightLegPivot, torso, hatSlot, rightHandSlot, hairSlot },
        targetPos: new THREE.Vector3(),
        emote: null,
        emoteTimer: 0,
        labelEl,
        chatBubbleEl: labelEl.querySelector('.chat-bubble'),
        tagEl: labelEl.querySelector('.player-tag'),
        chatTimeout: null,
        setName(newName) { this.tagEl.textContent = newName; },
        applyAppearance(newApp) {
            if (newApp.skinColor !== undefined) skinMat.color.setHex(newApp.skinColor);
            if (newApp.hairColor !== undefined) hairMat.color.setHex(newApp.hairColor);
            if (newApp.shirtColor !== undefined) shirtMat.color.setHex(newApp.shirtColor);
            if (newApp.pantsColor !== undefined) pantsMat.color.setHex(newApp.pantsColor);
            if (newApp.shoesColor !== undefined) shoesMat.color.setHex(newApp.shoesColor);

            hairSlot.clear();
            hairSlot.add(buildHairMesh(newApp.hairStyle || 'short', hairMat));

            hatSlot.clear();
            if (newApp.hat === 'hat_crown') {
                const crown = new THREE.Mesh(
                    new THREE.CylinderGeometry(0.28, 0.21, 0.22, 8),
                    new THREE.MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.7, roughness: 0.2 })
                );
                hatSlot.add(crown);
            } else if (newApp.hat === 'hat_cap') {
                const capGroup = new THREE.Group();
                const dome = new THREE.Mesh(new THREE.SphereGeometry(0.38, 16, 12, 0, Math.PI * 2, 0, Math.PI / 2), new THREE.MeshStandardMaterial({ color: 0x3b82f6 }));
                const visor = new THREE.Mesh(new THREE.CylinderGeometry(0.36, 0.36, 0.04, 16, 1, false, -Math.PI / 3, Math.PI * 0.66), new THREE.MeshStandardMaterial({ color: 0x1d4ed8 }));
                visor.position.set(0, 0.02, 0.14);
                capGroup.add(dome, visor);
                capGroup.position.y = -0.15;
                hatSlot.add(capGroup);
            }
            rightHandSlot.clear();
            if (newApp.handProp) rightHandSlot.add(buildHandPropMesh(newApp.handProp));
        },
        say(text) {
            if (!text) return;
            this.chatBubbleEl.textContent = text;
            this.chatBubbleEl.style.display = 'block';
            clearTimeout(this.chatTimeout);
            this.chatTimeout = setTimeout(() => { this.chatBubbleEl.style.display = 'none'; }, 5000);
        },
        triggerEmote(emoteName) {
            this.emote = emoteName;
            this.emoteTimer = 2.5;
        }
    };

    rig.applyAppearance(appearance);
    return rig;
}

/**
 * ============================================================================
 * حلقه انیمیشن کاراکتر (راه رفتن، نفس کشیدن ملایم در حالت ایستاده، ایموت‌ها)
 * ============================================================================
 */
export function updateCharacterAnimation(rig, dt, time, camera, checkCollisionFn, targetRing) {
    const pos = rig.mesh.position;
    const dist = pos.distanceTo(rig.targetPos);

    if (dist > 0.12) {
        const dir = new THREE.Vector3().subVectors(rig.targetPos, pos).normalize();
        const speed = 5.8 * dt;
        const nextX = pos.x + dir.x * speed;
        const nextZ = pos.z + dir.z * speed;

        if (!checkCollisionFn || !checkCollisionFn(nextX, nextZ)) {
            pos.x = nextX;
            pos.z = nextZ;
        } else {
            rig.targetPos.copy(pos);
            if (targetRing) targetRing.visible = false;
        }

        const targetAngle = Math.atan2(dir.x, dir.z);
        const targetQuat = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 1, 0), targetAngle);
        rig.mesh.quaternion.slerp(targetQuat, 10 * dt);

        // انیمیشن راه رفتن پویا با بالا و پایین رفتن ملایم سر و بدن
        const swing = Math.sin(time * 11) * 0.65;
        rig.parts.leftLegPivot.rotation.x = swing;
        rig.parts.rightLegPivot.rotation.x = -swing;
        rig.parts.leftArmPivot.rotation.x = -swing * 0.8;
        if (!rig.emote) rig.parts.rightArmPivot.rotation.x = swing * 0.8;

        rig.parts.bodyGroup.position.y = 1.26 + Math.abs(Math.sin(time * 11)) * 0.05;
        rig.parts.headGroup.position.y = 2.08 + Math.abs(Math.sin(time * 11)) * 0.06;
    } else {
        if (targetRing && targetRing.visible) targetRing.visible = false;
        rig.parts.leftLegPivot.rotation.x *= 0.8;
        rig.parts.rightLegPivot.rotation.x *= 0.8;
        if (!rig.emote) {
            rig.parts.leftArmPivot.rotation.x *= 0.8;
            rig.parts.rightArmPivot.rotation.x *= 0.8;
        }

        // انیمیشن نفس کشیدن ملایم هنگام ایستادن (Idle Breathing)
        const breath = Math.sin(time * 3) * 0.02;
        rig.parts.bodyGroup.position.y = 1.26 + breath;
        rig.parts.headGroup.position.y = 2.08 + breath * 1.2;
        rig.parts.headGroup.rotation.z = Math.sin(time * 1.5) * 0.04;
    }

    if (rig.emote && rig.emoteTimer > 0) {
        rig.emoteTimer -= dt;
        if (rig.emote === 'wave') {
            rig.parts.rightArmPivot.rotation.z = -2.6;
            rig.parts.rightArmPivot.rotation.x = Math.sin(time * 14) * 0.35;
        } else if (rig.emote === 'clap') {
            rig.parts.leftArmPivot.rotation.x = -1.2;
            rig.parts.rightArmPivot.rotation.x = -1.2;
            rig.parts.leftArmPivot.rotation.z = 0.3 + Math.abs(Math.sin(time * 16)) * 0.25;
            rig.parts.rightArmPivot.rotation.z = -0.3 - Math.abs(Math.sin(time * 16)) * 0.25;
        } else if (rig.emote === 'prop_show') {
            rig.parts.rightArmPivot.rotation.x = -2.2;
            rig.parts.rightArmPivot.rotation.z = -0.3 + Math.sin(time * 10) * 0.15;
        } else if (rig.emote === 'jump' || rig.emote === 'invite') {
            rig.mesh.position.y = Math.abs(Math.sin(time * 9)) * 0.65;
            rig.parts.leftArmPivot.rotation.z = 2.5;
            rig.parts.rightArmPivot.rotation.z = -2.5;
        }
        if (rig.emoteTimer <= 0) {
            rig.emote = null;
            rig.mesh.position.y = 0;
            rig.parts.leftArmPivot.rotation.set(0, 0, 0);
            rig.parts.rightArmPivot.rotation.set(0, 0, 0);
        }
    }

    const headWorldPos = new THREE.Vector3(pos.x, pos.y + 2.75, pos.z);
    headWorldPos.project(camera);
    if (headWorldPos.z < 1) {
        const screenX = (headWorldPos.x * 0.5 + 0.5) * window.innerWidth;
        const screenY = (-headWorldPos.y * 0.5 + 0.5) * window.innerHeight;
        rig.labelEl.style.left = `${screenX}px`;
        rig.labelEl.style.top = `${screenY}px`;
        rig.labelEl.style.display = 'block';
    } else {
        rig.labelEl.style.display = 'none';
    }
}