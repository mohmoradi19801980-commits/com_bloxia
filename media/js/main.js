import * as THREE from './three/three.module.js';
import { bridge } from './bridge.js';
import { createCharacterRig, updateCharacterAnimation } from './character.js';
import { buildCityPlaza, buildCityInterior } from './city.js';
import { buildHomeInterior, buildRealisticFurniture } from './home.js';
import { UIController } from './ui-panels.js';

class MetaverseApp {
    constructor() {
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(48, window.innerWidth / window.innerHeight, 0.1, 350);
        this.camera.position.set(0, 15, 20);

        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.shadowMap.enabled = true;
        document.getElementById('canvas-container').appendChild(this.renderer.domElement);

        this.ambientLight = new THREE.AmbientLight(0xffffff, 0.72);
        this.dirLight = new THREE.DirectionalLight(0xfffbeb, 1.15);
        this.dirLight.position.set(30, 50, 28);
        this.dirLight.castShadow = true;
        this.scene.add(this.ambientLight, this.dirLight);

        this.worldGroup = new THREE.Group();
        this.remotePlayersGroup = new THREE.Group();
        this.homeFurnitureGroup = new THREE.Group();
        this.scene.add(this.worldGroup, this.remotePlayersGroup, this.homeFurnitureGroup);

        this.targetRing = new THREE.Mesh(
            new THREE.RingGeometry(0.35, 0.55, 24),
            new THREE.MeshBasicMaterial({ color: 0xf59e0b, side: THREE.DoubleSide })
        );
        this.targetRing.rotation.x = -Math.PI / 2;
        this.targetRing.position.y = 0.04;
        this.targetRing.visible = false;
        this.scene.add(this.targetRing);

        this.currentZone = 'plaza';
        this.groundMesh = null;
        this.obstacles = [];
        this.portals = [];
        this.interactables = [];
        this.animatedLandmarks = [];
        this.minimapBuildings = [];
        this.streetLampBulbs = [];
        this.streetLampLights = [];
        this.activePortal = null;
        this.remoteRigs = new Map();
        this.zoneLabels = [];
        this.pendingEmote = null;
        this.pendingChat = null;
        this.lastChatId = 0;

        this.timeModes = ['day', 'sunset', 'night'];
        this.weatherModes = ['sunny', 'cloudy', 'rain', 'snow'];
        this.timeIdx = 0;
        this.weatherIdx = 0;

        this.placingItem = null;
        this.placingPreview = null;
        this.placingRot = 0;

        this.setupWeatherParticles();
        this.ui = new UIController(this);
        this.bindInputs();
    }

    setupWeatherParticles() {
        const count = 1000;
        const geo = new THREE.BufferGeometry();
        const pos = new Float32Array(count * 3);
        for (let i = 0; i < count; i++) {
            pos[i * 3] = (Math.random() - 0.5) * 130;
            pos[i * 3 + 1] = Math.random() * 35;
            pos[i * 3 + 2] = (Math.random() - 0.5) * 130;
        }
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        this.weatherMat = new THREE.PointsMaterial({ color: 0xffffff, size: 0.25, transparent: true, opacity: 0.85 });
        this.weatherPoints = new THREE.Points(geo, this.weatherMat);
        this.weatherPoints.visible = false;
        this.scene.add(this.weatherPoints);
    }

    applyAtmosphere() {
        const t = this.timeModes[this.timeIdx];
        const w = this.weatherModes[this.weatherIdx];
        const outdoor = this.currentZone === 'plaza';
        this.weatherPoints.visible = outdoor && (w === 'rain' || w === 'snow');

        if (!outdoor) {
            this.scene.fog = null;
            this.ambientLight.intensity = 0.8;
            return;
        }

        const isNight = t === 'night';
        const sky = isNight ? 0x0f172a : (t === 'sunset' ? 0xfb923c : (w === 'sunny' ? 0x87ceeb : 0x94a3b8));
        this.ambientLight.intensity = isNight ? 0.3 : 0.72;
        this.dirLight.intensity = isNight ? 0.25 : 1.15;
        this.scene.background = new THREE.Color(sky);
        this.scene.fog = new THREE.Fog(sky, 60, 160);

        this.streetLampBulbs.forEach(m => { m.emissiveIntensity = isNight ? 1.5 : 0.05; });
        this.streetLampLights.forEach(l => { l.intensity = isNight ? 2.8 : 0; });
    }

    setGround(mesh) { this.groundMesh = mesh; }

    clearZoneLabels() {
        if (this.zoneLabels) {
            this.zoneLabels.forEach(l => l.el.remove());
        }
        this.zoneLabels = [];
    }

    addZoneLabel(x, y, z, text) {
        const layer = document.getElementById('labels-layer');
        if (!layer) return;
        const el = document.createElement('div');
        el.textContent = text;
        el.style.cssText = 'position:absolute;transform:translate(-50%,-50%);background:rgba(15,23,42,0.88);color:#fde047;border:2px solid #facc15;border-radius:10px;padding:3px 10px;font-size:0.78rem;font-weight:700;white-space:nowrap;pointer-events:none;box-shadow:0 4px 12px rgba(0,0,0,0.35);';
        layer.appendChild(el);
        this.zoneLabels.push({ pos: new THREE.Vector3(x, y, z), el });
    }

    addPortal(x, z, targetZone, promptLabel, color = 0xf59e0b) {
        const pad = new THREE.Mesh(
            new THREE.CylinderGeometry(1.35, 1.35, 0.08, 24),
            new THREE.MeshStandardMaterial({ color, emissive: color, emissiveIntensity: 0.35 })
        );
        pad.position.set(x, 0.04, z);
        this.worldGroup.add(pad);
        this.portals.push({ x, z, targetZone, promptLabel });
    }

    async loadZone(zoneId) {
        const overlay = document.getElementById('fade-overlay');
        overlay.classList.add('active');
        await new Promise(r => setTimeout(r, 260));

        this.worldGroup.clear();
        this.homeFurnitureGroup.clear();
        this.obstacles = [];
        this.portals = [];
        this.interactables = [];
        this.animatedLandmarks = [];
        this.minimapBuildings = [];
        this.streetLampBulbs = [];
        this.streetLampLights = [];
        this.clearZoneLabels();
        this.activePortal = null;
        this.targetRing.visible = false;
        document.getElementById('portal-prompt').classList.remove('visible');

        this.currentZone = zoneId;
        const isHome = zoneId.startsWith('home_');

        document.getElementById('btn-go-plaza').style.display = zoneId === 'plaza' ? 'none' : 'flex';
        document.getElementById('btn-go-my-home').style.display = zoneId === `home_${this.playerState.userId}` ? 'none' : 'flex';
        document.getElementById('btn-toggle-inventory').style.display = 'none';
        document.getElementById('btn-open-wardrobe').style.display = 'none';

        if (zoneId === 'plaza') {
            document.getElementById('zone-title-display').textContent = '🏙️ میدان اصلی شهر';
            const plots = await bridge.getResidentialPlots();
            buildCityPlaza(this, plots);
            this.myRig.mesh.position.set(0, 0, 8);
            this.myRig.targetPos.set(0, 0, 8);
        } else if (isHome) {
            const ownerId = Number(zoneId.replace('home_', ''));
            const homeData = await bridge.getHomeData(ownerId);
            document.getElementById('zone-title-display').textContent = homeData.isMine ? '🏠 خانه من' : `🏠 خانه ${homeData.ownerName}`;
            if (homeData.isMine) {
                document.getElementById('btn-toggle-inventory').style.display = 'flex';
                document.getElementById('btn-open-wardrobe').style.display = 'flex';
            }
            this.scene.background = new THREE.Color(0xe0f2fe);
            buildHomeInterior(homeData, this);
            this.currentHomeData = homeData;
            this.renderHomeFurniture();
            this.myRig.mesh.position.set(0, 0, 6);
            this.myRig.targetPos.set(0, 0, 6);
        } else {
            document.getElementById('zone-title-display').textContent = '🏛️ فضای داخلی';
            buildCityInterior(zoneId, this);
            this.myRig.mesh.position.set(0, 0, 6);
            this.myRig.targetPos.set(0, 0, 6);
        }

        this.applyAtmosphere();
        this.remoteRigs.forEach(r => r.labelEl.remove());
        this.remoteRigs.clear();
        this.remotePlayersGroup.clear();
        await this.syncWorld();

        overlay.classList.remove('active');
    }

    renderHomeFurniture() {
        this.homeFurnitureGroup.clear();
        const layout = this.currentHomeData?.layout || this.playerState.homeLayout;
        const isMine = this.currentHomeData?.isMine;
        layout.forEach(p => {
            const mesh = buildRealisticFurniture(p.itemId, p.color);
            mesh.position.set(p.x, 0, p.z);
            mesh.rotation.y = p.rotY || 0;
            if (p.itemId === 'furn_tv') {
                mesh.userData = { action: 'tv_studio', homeData: this.currentHomeData };
                this.interactables.push(mesh);
            } else if (isMine) {
                mesh.userData = { action: 'edit_furniture', furnitureUid: p.uid, furnitureData: p };
                this.interactables.push(mesh);
            }
            this.homeFurnitureGroup.add(mesh);
        });
    }

    rebuildHomeView() {
        if (!this.currentZone.startsWith('home_')) return;
        this.worldGroup.clear();
        this.obstacles = [];
        this.portals = [];
        this.interactables = [];
        this.clearZoneLabels();
        this.currentHomeData = {
            ...this.currentHomeData,
            homeStyle: this.playerState.homeStyle,
            layout: this.playerState.homeLayout
        };
        buildHomeInterior(this.currentHomeData, this);
        this.renderHomeFurniture();
    }

    startPlacingFurniture(itemDef) {
        this.placingItem = itemDef;
        this.placingRot = 0;
        if (this.placingPreview) this.scene.remove(this.placingPreview);
        this.placingPreview = buildRealisticFurniture(itemDef.id);
        this.scene.add(this.placingPreview);
        document.getElementById('home-edit-banner').style.display = 'flex';
    }

    cancelPlacing() {
        this.placingItem = null;
        if (this.placingPreview) { this.scene.remove(this.placingPreview); this.placingPreview = null; }
        document.getElementById('home-edit-banner').style.display = 'none';
    }

    bindInputs() {
        // دکمه‌های آیکونی شب/روز و آب‌وهوا
        const timeIcons = ['☀️', '🌅', '🌙'];
        document.getElementById('btn-cycle-time').onclick = (e) => {
            this.timeIdx = (this.timeIdx + 1) % 3;
            e.currentTarget.textContent = timeIcons[this.timeIdx];
            this.applyAtmosphere();
        };
        const weatherIcons = ['🌤️', '☁️', '🌧️', '❄️'];
        document.getElementById('btn-cycle-weather').onclick = (e) => {
            this.weatherIdx = (this.weatherIdx + 1) % 4;
            e.currentTarget.textContent = weatherIcons[this.weatherIdx];
            this.applyAtmosphere();
        };

        document.getElementById('portal-prompt').onclick = () => { if (this.activePortal) this.loadZone(this.activePortal.targetZone); };
        document.getElementById('btn-go-my-home').onclick = () => this.loadZone(`home_${this.playerState.userId}`);
        document.getElementById('btn-go-plaza').onclick = () => this.loadZone('plaza');
        document.getElementById('btn-rotate-furniture').onclick = () => { this.placingRot += Math.PI / 2; if (this.placingPreview) this.placingPreview.rotation.y = this.placingRot; };
        document.getElementById('btn-cancel-place').onclick = () => this.cancelPlacing();

        document.querySelectorAll('.coc-emote-btn').forEach(btn => {
            btn.onclick = () => {
                this.myRig.triggerEmote(btn.dataset.emote);
                this.myRig.say(btn.dataset.text);
                this.queueBroadcast(btn.dataset.emote, btn.dataset.text);
            };
        });

        const raycaster = new THREE.Raycaster();
        const pointer = new THREE.Vector2();

        this.renderer.domElement.addEventListener('pointermove', (e) => {
            if (!this.placingItem || !this.placingPreview || !this.groundMesh) return;
            pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
            pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
            raycaster.setFromCamera(pointer, this.camera);
            const hits = raycaster.intersectObject(this.groundMesh);
            if (hits.length > 0) {
                this.placingPreview.position.x = Math.round(hits[0].point.x);
                this.placingPreview.position.z = Math.round(hits[0].point.z);
            }
        });

        this.renderer.domElement.addEventListener('pointerdown', async (e) => {
            if (e.button !== 0 && e.pointerType === 'mouse') return;
            pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
            pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
            raycaster.setFromCamera(pointer, this.camera);

            // 1. کلیک مستقیم روی بازیکن دیگر برای چت یا دعوت به بازی
            const pHits = raycaster.intersectObjects(this.remotePlayersGroup.children, true);
            if (pHits.length > 0) {
                let obj = pHits[0].object;
                while (obj) {
                    if (obj.userData?.citizenData) { this.ui.openDirectPlayerCard(obj.userData.citizenData); return; }
                    obj = obj.parent;
                }
            }

            // 2. کلیک روی اشیاء تعاملی (تلویزیون، کمد لباس، غرفه‌ها)
            const iHits = raycaster.intersectObjects(this.interactables, true);
            if (iHits.length > 0) {
                let obj = iHits[0].object;
                while (obj) {
                    if (obj.userData?.action === 'tv_studio') { this.ui.openTvStudioPanel(obj.userData.homeData); return; }
                    if (obj.userData?.action === 'wardrobe') { this.ui.openWardrobePanel(); return; }
                    if (obj.userData?.action === 'edit_furniture') { this.ui.openFurnitureEditModal(obj.userData.furnitureData); return; }
                    obj = obj.parent;
                }
                this.ui.openZoneActivity(this.currentZone);
                return;
            }

            // 3. حرکت کاراکتر یا قرار دادن مبلمان روی زمین
            if (!this.groundMesh) return;
            const hits = raycaster.intersectObject(this.groundMesh);
            if (hits.length === 0) return;
            const pt = hits[0].point;

            if (this.placingItem && this.placingPreview) {
                const idx = this.playerState.furnitureInventory.indexOf(this.placingItem.id);
                if (idx > -1) this.playerState.furnitureInventory.splice(idx, 1);
                this.playerState.homeLayout.push({ uid: 'f_' + Date.now(), itemId: this.placingItem.id, x: Math.round(pt.x), z: Math.round(pt.z), rotY: this.placingRot });
                this.renderHomeFurniture();
                const saveRes = await bridge.updateHomeAndInventory(this.playerState.homeLayout, this.playerState.furnitureInventory);
                if (saveRes?.state) this.playerState = saveRes.state;
                this.cancelPlacing();
                return;
            }

            this.myRig.targetPos.set(pt.x, 0, pt.z);
            this.targetRing.position.set(pt.x, 0.04, pt.z);
            this.targetRing.visible = true;
        });

        window.addEventListener('resize', () => {
            this.camera.aspect = window.innerWidth / window.innerHeight;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(window.innerWidth, window.innerHeight);
        });
    }

    queueBroadcast(emote = null, chat = null) {
        if (emote) this.pendingEmote = emote;
        if (chat) this.pendingChat = chat;
        this.syncWorld();
    }

    async syncWorld() {
        if (!this.myRig) return;
        const emoteToSend = this.pendingEmote;
        const chatToSend = this.pendingChat;
        this.pendingEmote = null;
        this.pendingChat = null;

        const syncData = await bridge.syncWorld({
            zone: this.currentZone,
            x: this.myRig.mesh.position.x,
            z: this.myRig.mesh.position.z,
            emote: emoteToSend,
            chat: chatToSend,
            lastChatId: this.lastChatId
        });
        const others = syncData.players || [];
        if (syncData.chats && syncData.chats.length > 0) {
            this.lastChatId = syncData.chats[syncData.chats.length - 1].id;
            this.ui.receiveIncomingChats(syncData.chats);
        }
        if (syncData.pvp) {
            this.ui.handlePvpSync(syncData.pvp);
        }
        const ids = new Set();
        others.forEach(p => {
            ids.add(p.id);
            let rig = this.remoteRigs.get(p.id);
            if (!rig) {
                rig = createCharacterRig(p.name, p.appearance, p);
                rig.mesh.position.set(p.x, 0, p.z);
                rig.targetPos.set(p.x, 0, p.z);
                rig.lastEmoteAt = null;
                rig.lastChatAt = null;
                this.remotePlayersGroup.add(rig.mesh);
                this.remoteRigs.set(p.id, rig);
            } else {
                rig.targetPos.set(p.x, 0, p.z);
                rig.mesh.userData.citizenData = p;
            }
            if (p.emote && p.emoteAt && p.emoteAt !== rig.lastEmoteAt) {
                rig.lastEmoteAt = p.emoteAt;
                rig.triggerEmote(p.emote);
            }
            if (p.chat && p.chatAt && p.chatAt !== rig.lastChatAt) {
                rig.lastChatAt = p.chatAt;
                rig.say(p.chat);
            }
        });
        for (const [id, rig] of this.remoteRigs.entries()) {
            if (!ids.has(id)) { this.remotePlayersGroup.remove(rig.mesh); rig.labelEl.remove(); this.remoteRigs.delete(id); }
        }
    }

    renderMiniMap() {
        const canvas = document.getElementById('minimap-canvas');
        const ctx = canvas.getContext('2d');
        const w = canvas.width, h = canvas.height;
        ctx.clearRect(0, 0, w, h);

        if (this.currentZone !== 'plaza') return;
        const scale = w / 120;
        const tx = (x) => w / 2 + x * scale;
        const ty = (z) => h / 2 - 10 + z * scale;

        ctx.fillStyle = 'rgba(71, 85, 105, 0.5)';
        ctx.fillRect(tx(-4), 0, 8 * scale, h);
        ctx.fillRect(0, ty(-2), w, 8 * scale);

        this.minimapBuildings.forEach(b => {
            ctx.fillStyle = b.color + 'cc';
            ctx.fillRect(tx(b.x - b.w / 2), ty(b.z - b.d / 2), b.w * scale, b.d * scale);
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 11px "Vazirmatn", Tahoma';
            ctx.textAlign = 'center';
            ctx.fillText(b.label, tx(b.x), ty(b.z) + 3);
        });

        this.remoteRigs.forEach(r => {
            ctx.fillStyle = '#4ade80';
            ctx.beginPath();
            ctx.arc(tx(r.mesh.position.x), ty(r.mesh.position.z), 4.5, 0, Math.PI * 2);
            ctx.fill();
        });

        if (this.myRig) {
            ctx.fillStyle = '#38bdf8';
            ctx.beginPath();
            ctx.arc(tx(this.myRig.mesh.position.x), ty(this.myRig.mesh.position.z), 5.5, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    checkCollision(nx, nz) {
        return this.obstacles.some(b => nx > b.minX && nx < b.maxX && nz > b.minZ && nz < b.maxZ);
    }

    async start() {
        try {
            if (document.fonts && document.fonts.ready) {
                await Promise.race([document.fonts.ready, new Promise(r => setTimeout(r, 1200))]);
            }
            await bridge.loadCatalogFromServer();
            this.playerState = await bridge.getCharacter();
            if (!this.playerState || this.playerState.error || !this.playerState.appearance) {
                console.error('Invalid playerState:', this.playerState);
                document.getElementById('zone-title-display').textContent = '⚠️ خطا در بارگذاری اطلاعات کاربر';
                return;
            }
            document.getElementById('player-name-display').textContent = this.playerState.name;
            this.ui.refreshBitBlox();

            this.myRig = createCharacterRig(this.playerState.name, this.playerState.appearance, null);
            this.scene.add(this.myRig.mesh);

            await this.loadZone('plaza');
        } catch (err) {
            console.error('Bloxia start error:', err);
            document.getElementById('zone-title-display').textContent = '⚠️ خطا در اجرای دنیای سه‌بعدی';
            return;
        }

        const clock = new THREE.Clock();
        const loop = () => {
            requestAnimationFrame(loop);
            const dt = Math.min(clock.getDelta(), 0.1);
            const t = clock.getElapsedTime();

            this.animatedLandmarks.forEach(it => {
                if (it.type === 'spinAndBob') {
                    it.mesh.rotation.y += it.speed * dt;
                    it.mesh.position.y = it.baseY + Math.sin(t * 3) * 0.12;
                }
            });

            if (this.weatherPoints.visible) {
                const arr = this.weatherPoints.geometry.attributes.position.array;
                const spd = this.weatherModes[this.weatherIdx] === 'rain' ? 28 * dt : 6 * dt;
                for (let i = 0; i < arr.length; i += 3) {
                    arr[i + 1] -= spd;
                    if (arr[i + 1] < 0) arr[i + 1] = 35;
                }
                this.weatherPoints.geometry.attributes.position.needsUpdate = true;
            }

            if (this.myRig) {
                updateCharacterAnimation(this.myRig, dt, t, this.camera, (x, z) => this.checkCollision(x, z), this.targetRing);
                const camTarget = new THREE.Vector3(this.myRig.mesh.position.x, this.myRig.mesh.position.y + 13.5, this.myRig.mesh.position.z + 16.5);
                this.camera.position.lerp(camTarget, 4 * dt);
                this.camera.lookAt(this.myRig.mesh.position.x, this.myRig.mesh.position.y + 1.2, this.myRig.mesh.position.z);

                const found = this.portals.find(p => Math.hypot(this.myRig.mesh.position.x - p.x, this.myRig.mesh.position.z - p.z) < 2.2);
                const btn = document.getElementById('portal-prompt');
                if (found && found !== this.activePortal) {
                    this.activePortal = found;
                    btn.textContent = found.promptLabel;
                    btn.classList.add('visible');
                } else if (!found && this.activePortal) {
                    this.activePortal = null;
                    btn.classList.remove('visible');
                }
            }

            this.remoteRigs.forEach(r => updateCharacterAnimation(r, dt, t, this.camera, null, null));
            this.zoneLabels.forEach(l => {
                const p = l.pos.clone().project(this.camera);
                if (p.z < 1) {
                    l.el.style.left = `${(p.x * 0.5 + 0.5) * window.innerWidth}px`;
                    l.el.style.top = `${(-p.y * 0.5 + 0.5) * window.innerHeight}px`;
                    l.el.style.display = 'block';
                } else {
                    l.el.style.display = 'none';
                }
            });
            this.renderMiniMap();
            this.renderer.render(this.scene, this.camera);
        };
        loop();
        setInterval(() => this.syncWorld(), 2500);
    }
}

new MetaverseApp().start();