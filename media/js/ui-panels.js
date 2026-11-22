import { bridge, CATALOG, ALL_ITEMS_FLAT, SAFE_CHAT_PHRASES, CINEMA_MOVIES, GAMENET_GAMES } from './bridge.js';

export class UIController {
    constructor(app) {
        this.app = app;
        this.shoppingCart = [];
        this.chatLog = [{ sender: 'سیستم امن', text: 'به چت‌روم امن خوش آمدید! جملات آماده را انتخاب کنید.', type: 'reply' }];
        this.bindGlobalButtons();
    }

    bindGlobalButtons() {
        document.getElementById('btn-close-panel').onclick = () => document.getElementById('side-panel').classList.remove('open');
        document.getElementById('btn-close-interact').onclick = () => document.getElementById('player-interact-card').classList.remove('open');

        document.getElementById('btn-open-chatroom').onclick = () => this.openSafeChatRoom('all');
        document.getElementById('btn-pvp-challenge').onclick = () => this.openPvPChallengeCenter(null);
        document.getElementById('btn-neighbors').onclick = () => this.openNeighborsPanel();
        document.getElementById('btn-open-cas').onclick = () => this.openCharacterEditor();
        document.getElementById('btn-open-wardrobe').onclick = () => this.openWardrobePanel();
        document.getElementById('btn-toggle-inventory').onclick = () => this.openHomeInventoryPanel();
        document.getElementById('btn-city-directory').onclick = () => this.openCityDirectoryPanel();
        document.getElementById('btn-open-cart').onclick = () => this.openCartOnlyPanel();
        document.getElementById('btn-checkout-cart').onclick = () => this.checkoutCart();
    }

    refreshBitBlox() {
        document.getElementById('bitblox-display').textContent = this.app.playerState.bitblox;
    }

    // منوی آیکونی جابه‌جایی سریع بین مکان‌های شهر (دکمه بزرگ گوشه پایین-چپ)
    openCityDirectoryPanel() {
        const panel = document.getElementById('side-panel');
        document.getElementById('panel-title').textContent = '🗺️ مکان‌های شهر متاورسی';
        document.getElementById('cart-section').style.display = 'none';

        const places = [
            { zone: 'plaza', icon: '🏙️', name: 'میدان اصلی شهر' },
            { zone: 'gamenet', icon: '🕹️', name: 'گیم‌نت (GAME NET)' },
            { zone: 'hair_salon', icon: '💇', name: 'آرایشگاه شهر' },
            { zone: 'cinema', icon: '🎬', name: 'سینمای شهر' },
            { zone: 'shop_accessories', icon: '⚔️', name: 'فروشگاه لوازم جانبی' },
            { zone: 'shop_furniture', icon: '🛋️', name: 'فروشگاه لوازم خانه' },
            { zone: 'shop_clothes', icon: '👗', name: 'فروشگاه لباس و کفش' },
            { zone: 'school_hall', icon: '🎓', name: 'سالن اجتماعات مدرسه' },
            { zone: 'amusement_park', icon: '🎡', name: 'شهربازی و جایزه' }
        ];

        const body = document.getElementById('panel-body');
        body.innerHTML = `<div class="item-grid">${places.map(p => `
            <div class="shop-card" style="cursor:pointer;" data-zone="${p.zone}">
                <div style="font-size:1.6rem;">${p.icon}</div>
                <h4>${p.name}</h4>
            </div>
        `).join('')}</div>`;

        body.querySelectorAll('.shop-card').forEach(card => {
            card.onclick = () => {
                panel.classList.remove('open');
                this.app.loadZone(card.dataset.zone);
            };
        });
        panel.classList.add('open');
    }

    // اصلاح کاراکتر من (رنگ پوست، مو، لباس، شلوار، کفش)
    openCharacterEditor() {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '👤 اصلاح کاراکتر من';
        document.getElementById('cart-section').style.display = 'none';

        const presets = {
            skinColor:  { label: '🧑 رنگ پوست', colors: [0xffdbac, 0xf1c27d, 0xe0ac69, 0xc68642, 0x8d5524] },
            hairColor:  { label: '💇 رنگ مو', colors: [0x1c1917, 0x451a03, 0x78350f, 0xd97706, 0xfacc15, 0xb91c1c, 0x7c3aed] },
            shirtColor: { label: '👕 رنگ لباس', colors: [0x3b82f6, 0xec4899, 0x10b981, 0xf59e0b, 0x8b5cf6, 0xef4444, 0xf8fafc] },
            pantsColor: { label: '👖 رنگ شلوار', colors: [0x1e293b, 0x334155, 0x1d4ed8, 0x78350f, 0x4b5563] },
            shoesColor: { label: '👟 رنگ کفش', colors: [0xf8fafc, 0x0f172a, 0xef4444, 0xf59e0b, 0x3b82f6, 0xec4899] }
        };

        let html = `
            <div class="cas-group">
                <div class="cas-group-title">🏷️ نام کاراکتر:</div>
                <input type="text" id="cas-name-input" value="${this.app.playerState.name}" style="width:100%;padding:6px;border-radius:8px;border:1px solid #cbd5e1;">
            </div>
        `;

        Object.entries(presets).forEach(([key, cfg]) => {
            const cur = this.app.playerState.appearance[key];
            html += `
                <div class="cas-group">
                    <div class="cas-group-title">${cfg.label}</div>
                    <div class="swatch-row">
                        ${cfg.colors.map(hex => `<div class="color-swatch ${cur === hex ? 'selected' : ''}" data-key="${key}" data-hex="${hex}" style="background:#${hex.toString(16).padStart(6, '0')};"></div>`).join('')}
                    </div>
                </div>
            `;
        });

        html += `<button class="coc-action-btn green" id="btn-save-cas" style="width:100%;">💾 ذخیره ظاهر کاراکتر</button>`;
        body.innerHTML = html;

        body.querySelectorAll('.color-swatch').forEach(sw => {
            sw.onclick = () => {
                this.app.playerState.appearance[sw.dataset.key] = Number(sw.dataset.hex);
                this.app.myRig.applyAppearance(this.app.playerState.appearance);
                this.openCharacterEditor();
            };
        });

        document.getElementById('btn-save-cas').onclick = async () => {
            const newName = document.getElementById('cas-name-input').value.trim() || this.app.playerState.name;
            const res = await bridge.saveCharacterAppearance(this.app.playerState.appearance, newName);
            this.app.playerState = res.state;
            document.getElementById('player-name-display').textContent = this.app.playerState.name;
            this.app.myRig.setName(this.app.playerState.name);
            this.app.myRig.say('ظاهر جدیدم آماده شد! ✨');
            panel.classList.remove('open');
        };

        panel.classList.add('open');
    }

    // چت‌روم امن با جملات ثابت
    openSafeChatRoom(targetId = 'all') {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '💬 چت‌روم کنترل‌شده';
        document.getElementById('cart-section').style.display = 'none';

        const logHtml = this.chatLog.map(m => `<div class="chat-msg ${m.type}"><strong>${m.sender}:</strong> ${m.text}</div>`).join('');
        body.innerHTML = `
            <select id="chat-target-select" style="width:100%;padding:5px;border-radius:8px;margin-bottom:8px;">
                <option value="all">همه حاضران</option>
                ${bridge.mockCitizens.map(c => `<option value="${c.id}" ${Number(targetId) === c.id ? 'selected' : ''}>${c.name}</option>`).join('')}
            </select>
            <div class="chat-history" id="chat-box">${logHtml}</div>
            ${SAFE_CHAT_PHRASES.map(p => `<button class="coc-action-btn" data-id="${p.id}" style="width:100%;text-align:right;margin-bottom:4px;">${p.text}</button>`).join('')}
        `;

        body.querySelectorAll('.coc-action-btn').forEach(btn => {
            btn.onclick = () => {
                const phrase = SAFE_CHAT_PHRASES.find(x => x.id === btn.dataset.id);
                const sel = document.getElementById('chat-target-select');
                const tVal = sel.value;
                this.chatLog.push({ sender: this.app.playerState.name, text: phrase.text, type: 'mine' });
                this.app.myRig.say(phrase.text);
                this.app.myRig.triggerEmote('wave');

                const botObj = bridge.mockCitizens.find(c => c.id === (tVal === 'all' ? 203 : Number(tVal))) || bridge.mockCitizens[0];
                setTimeout(() => {
                    this.chatLog.push({ sender: botObj.name.split(' ')[0], text: phrase.reply, type: 'reply' });
                    const botRig = this.app.remoteRigs.get(botObj.id);
                    if (botRig) { botRig.say(phrase.reply); botRig.triggerEmote('wave'); }
                    if (panel.classList.contains('open')) this.openSafeChatRoom(tVal);
                }, 650);
                this.openSafeChatRoom(tVal);
            };
        });
        panel.classList.add('open');
    }

    // چالش بازی‌های دونفره (سنگ کاغذ قیچی، دوز، دارت، منچ، گل یا پوچ)
    openPvPChallengeCenter(oppId = null) {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '⚔️ چالش بازی با دوستان';
        document.getElementById('cart-section').style.display = 'none';

        const opp = bridge.mockCitizens.find(c => c.id === oppId) || bridge.mockCitizens[0];
        body.innerHTML = `
            <select id="pvp-opp" style="width:100%;padding:6px;border-radius:8px;margin-bottom:8px;">
                ${bridge.mockCitizens.map(c => `<option value="${c.id}" ${c.id === opp.id ? 'selected' : ''}>👤 ${c.name}</option>`).join('')}
            </select>
            <div class="item-grid">
                <button class="coc-action-btn" data-g="rps">✊✌️✋ سنگ کاغذ قیچی</button>
                <button class="coc-action-btn cyan" data-g="ttt">⭕❌ بازی دوز</button>
                <button class="coc-action-btn amber" data-g="darts">🎯 دارت دونفره</button>
                <button class="coc-action-btn pink" data-g="ludo">🎲 منچ سرعتی</button>
                <button class="coc-action-btn green" data-g="gol" style="grid-column:1/-1;">✊✨ گل یا پوچ</button>
            </div>
            <div id="pvp-arena"></div>
        `;

        body.querySelectorAll('[data-g]').forEach(btn => {
            btn.onclick = () => {
                const curOpp = bridge.mockCitizens.find(c => c.id === Number(document.getElementById('pvp-opp').value));
                this.runPvPMiniGame(btn.dataset.g, curOpp);
            };
        });
        panel.classList.add('open');
    }

    async winPvP(oppName, label) {
        const res = await bridge.walletTransaction(15, `pvp_${label}`);
        this.app.playerState = res.state;
        this.refreshBitBlox();
        this.app.myRig.say(`بردم! +15 BitBlox از بازی ${label} 🏆💎`);
        this.app.myRig.triggerEmote('jump');
    }

    runPvPMiniGame(type, opp) {
        const arena = document.getElementById('pvp-arena');
        const name = opp.name.split(' ')[0];

        if (type === 'rps') {
            const opts = [{ id: 'r', l: '✊ سنگ' }, { id: 'p', l: '✋ کاغذ' }, { id: 's', l: '✌️ قیچی' }];
            arena.innerHTML = `<div class="pvp-board"><h4>سنگ کاغذ قیچی با ${name}</h4><div style="display:flex;gap:6px;justify-content:center;margin-top:8px;">${opts.map(o => `<button class="coc-action-btn" data-p="${o.id}">${o.l}</button>`).join('')}</div><div id="rps-out" style="margin-top:8px;"></div></div>`;
            arena.querySelectorAll('[data-p]').forEach(b => {
                b.onclick = async () => {
                    const my = b.dataset.p;
                    const bot = opts[Math.floor(Math.random() * 3)];
                    const out = document.getElementById('rps-out');
                    if (my === bot.id) out.textContent = `مساوی! (${bot.l})`;
                    else if ((my === 'r' && bot.id === 's') || (my === 'p' && bot.id === 'r') || (my === 's' && bot.id === 'p')) {
                        out.innerHTML = `🎉 بردی! (${bot.l}) +15 💎`;
                        await this.winPvP(name, 'سنگ کاغذ قیچی');
                    } else out.textContent = `😅 ${name} برد! (${bot.l})`;
                };
            });
        } else if (type === 'ttt') {
            let cells = Array(9).fill('');
            const render = (msg = `نوبت تو (❌) با ${name}`) => {
                arena.innerHTML = `<div class="pvp-board"><div>${msg}</div><div class="ttt-grid">${cells.map((v, i) => `<button class="ttt-cell" data-i="${i}">${v}</button>`).join('')}</div></div>`;
                arena.querySelectorAll('.ttt-cell').forEach(c => {
                    c.onclick = async () => {
                        const i = Number(c.dataset.i);
                        if (cells[i]) return;
                        cells[i] = '❌';
                        const empty = cells.map((v, idx) => v === '' ? idx : -1).filter(v => v !== -1);
                        if (empty.length > 0) cells[empty[Math.floor(Math.random() * empty.length)]] = '⭕';
                        const wins = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
                        const wonX = wins.some(([a,b,d]) => cells[a]==='❌' && cells[b]==='❌' && cells[d]==='❌');
                        if (wonX) { render('🎉 دوز کردی! +15 💎'); await this.winPvP(name, 'دوز'); }
                        else render();
                    };
                });
            };
            render();
        } else if (type === 'gol') {
            arena.innerHTML = `<div class="pvp-board"><h4>✊✨ گل یا پوچ با ${name}</h4><div style="display:flex;gap:10px;justify-content:center;margin-top:8px;"><button class="coc-action-btn" data-h="0">✊ دست راست</button><button class="coc-action-btn" data-h="1">✊ دست چپ</button></div><div id="gol-out" style="margin-top:8px;"></div></div>`;
            arena.querySelectorAll('[data-h]').forEach(b => {
                b.onclick = async () => {
                    const winHand = Math.floor(Math.random() * 2);
                    if (Number(b.dataset.h) === winHand) {
                        document.getElementById('gol-out').innerHTML = '🌺 گل بود! +15 💎 BitBlox';
                        await this.winPvP(name, 'گل یا پوچ');
                    } else document.getElementById('gol-out').textContent = '💨 پوچ بود! دوباره حدس بزن.';
                };
            });
        } else {
            arena.innerHTML = `<div class="pvp-board"><h4>🎲 مسابقه با ${name}</h4><button class="coc-action-btn amber" id="btn-quick-roll" style="margin-top:8px;">🎲 تاس / پرتاب</button><div id="quick-out" style="margin-top:8px;"></div></div>`;
            document.getElementById('btn-quick-roll').onclick = async () => {
                const s1 = Math.floor(Math.random() * 6) + 1;
                const s2 = Math.floor(Math.random() * 6) + 1;
                if (s1 >= s2) {
                    document.getElementById('quick-out').innerHTML = `امتیاز تو: ${s1} | ${name}: ${s2} -> 🎉 بردی! +15 💎`;
                    await this.winPvP(name, 'مسابقه');
                } else document.getElementById('quick-out').textContent = `امتیاز تو: ${s1} | ${name}: ${s2} -> 😅 ${name} برد!`;
            };
        }
    }

    openShopPanel(shopZone) {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '🛍️ فروشگاه';
        document.getElementById('cart-section').style.display = 'block';

        body.innerHTML = `<div class="item-grid">${(CATALOG[shopZone] || []).map(it => `
            <div class="shop-card">
                <h4>${it.name}</h4>
                <div class="price">💎 ${it.price} BitBlox</div>
                <button class="coc-action-btn" data-id="${it.id}" style="width:100%;">➕ سبد خرید</button>
            </div>
        `).join('')}</div>`;

        body.querySelectorAll('[data-id]').forEach(btn => {
            btn.onclick = () => {
                const it = CATALOG[shopZone].find(x => x.id === btn.dataset.id);
                if (it) { this.shoppingCart.push(it); this.updateCartUI(); }
            };
        });
        this.updateCartUI();
        panel.classList.add('open');
    }

    openCartOnlyPanel() {
        document.getElementById('panel-title').textContent = '🛒 سبد خرید BitBlox';
        document.getElementById('panel-body').innerHTML = '';
        document.getElementById('cart-section').style.display = 'block';
        this.updateCartUI();
        document.getElementById('side-panel').classList.add('open');
    }

    updateCartUI() {
        document.getElementById('cart-count').textContent = this.shoppingCart.length;
        const list = document.getElementById('cart-items-list');
        const totalEl = document.getElementById('cart-total');
        if (this.shoppingCart.length === 0) {
            list.innerHTML = '<div style="text-align:center;color:#64748b;">سبد خالی است</div>';
            totalEl.textContent = '0';
            return;
        }
        let sum = 0;
        list.innerHTML = this.shoppingCart.map((it, idx) => {
            sum += it.price;
            return `<div class="cart-row"><span>${it.name} (${it.price} 💎)</span><button data-rm="${idx}" style="border:none;color:red;cursor:pointer;">✖</button></div>`;
        }).join('');
        totalEl.textContent = sum;
        list.querySelectorAll('[data-rm]').forEach(b => {
            b.onclick = () => { this.shoppingCart.splice(Number(b.dataset.rm), 1); this.updateCartUI(); };
        });
    }

    async checkoutCart() {
        if (this.shoppingCart.length === 0) return;
        const res = await bridge.checkoutCart(this.shoppingCart);
        if (!res.success) return alert(res.message);
        this.shoppingCart = [];
        this.updateCartUI();
        this.app.playerState = res.state;
        this.refreshBitBlox();
        this.app.myRig.applyAppearance(this.app.playerState.appearance);
        this.app.myRig.say('خرید با BitBlox انجام شد 🎉');
    }

    openWardrobePanel() {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '👗 کمد لباس من';
        document.getElementById('cart-section').style.display = 'none';

        body.innerHTML = `<div class="item-grid">${this.app.playerState.wardrobe.map(id => {
            const def = ALL_ITEMS_FLAT.find(x => x.id === id);
            if (!def) return '';
            return `<div class="shop-card"><h4>${def.name}</h4><button class="coc-action-btn cyan" data-eq="${def.id}" style="width:100%;margin-top:6px;">پوشیدن</button></div>`;
        }).join('')}</div>`;

        body.querySelectorAll('[data-eq]').forEach(btn => {
            btn.onclick = async () => {
                const def = ALL_ITEMS_FLAT.find(x => x.id === btn.dataset.eq);
                this.app.playerState = await bridge.toggleWardrobeItem(def);
                this.app.myRig.applyAppearance(this.app.playerState.appearance);
            };
        });
        panel.classList.add('open');
    }

    openHomeInventoryPanel() {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '🛋️ چیدمان و حذف مبلمان';
        document.getElementById('cart-section').style.display = 'none';

        let html = `<div class="item-grid" style="margin-bottom:10px;">${this.app.playerState.furnitureInventory.map(id => {
            const def = CATALOG.shop_furniture.find(f => f.id === id);
            return def ? `<div class="shop-card"><h4>${def.name}</h4><button class="coc-action-btn green" data-pl="${def.id}" style="width:100%;margin-top:5px;">قرار دادن</button></div>` : '';
        }).join('')}</div>`;

        html += `<h4>🗑️ وسایل داخل خانه (بازگشت به انبار):</h4>`;
        this.app.playerState.homeLayout.forEach((p, idx) => {
            const def = CATALOG.shop_furniture.find(f => f.id === p.itemId);
            html += `<div class="cart-row"><span>${def ? def.name : p.itemId}</span><button class="coc-action-btn danger" data-del="${idx}" style="padding:3px 8px;">حذف</button></div>`;
        });

        body.innerHTML = html;
        body.querySelectorAll('[data-pl]').forEach(b => {
            b.onclick = () => {
                const def = CATALOG.shop_furniture.find(f => f.id === b.dataset.pl);
                if (def) { this.app.startPlacingFurniture(def); panel.classList.remove('open'); }
            };
        });
        body.querySelectorAll('[data-del]').forEach(b => {
            b.onclick = async () => {
                const removed = this.app.playerState.homeLayout.splice(Number(b.dataset.del), 1)[0];
                this.app.playerState.furnitureInventory.push(removed.itemId);
                this.app.renderHomeFurniture();
                this.app.playerState = await bridge.updateHomeAndInventory(this.app.playerState.homeLayout, this.app.playerState.furnitureInventory);
                this.openHomeInventoryPanel();
            };
        });
        panel.classList.add('open');
    }

    openTvStudioPanel(homeData) {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('cart-section').style.display = 'none';

        const isMine = homeData?.isMine ?? true;
        const gamesList = isMine ? (this.app.playerState.myGames || []) : (homeData.games || []);

        document.getElementById('panel-title').textContent = isMine
            ? '📺 تلویزیون و استودیو بازی من'
            : `📺 بازی‌های خانه ${homeData.ownerName}`;

        let html = '';
        if (isMine) {
            html += `
                <div style="display:flex;gap:6px;margin-bottom:10px;">
                    <button id="tab-tv-play" class="coc-action-btn cyan" style="flex:1;">🎮 بازی کردن (${gamesList.length})</button>
                    <button id="tab-tv-upload" class="coc-action-btn" style="flex:1;opacity:0.75;">📤 آپلود بازی جدید</button>
                </div>
            `;
        }

        html += `<div id="tv-play-section">`;
        const visibleGames = isMine ? gamesList : gamesList.filter(g => g.status === 'approved');
        if (visibleGames.length === 0) {
            html += `<div style="text-align:center;color:#64748b;padding:16px;font-size:0.82rem;">
                ${isMine ? 'هنوز بازی‌ای نساختی! از تب «آپلود بازی جدید» شروع کن.' : 'این همسایه هنوز بازی تایید شده‌ای ندارد.'}
            </div>`;
        } else {
            html += `<div style="display:flex;flex-direction:column;gap:8px;max-height:300px;overflow-y:auto;">`;
            visibleGames.forEach(g => {
                const isApproved = g.status === 'approved';
                html += `
                    <div class="cart-row" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:8px;align-items:center;">
                        <div>
                            <div style="font-weight:800;font-size:0.82rem;">${g.title}</div>
                            <div style="font-size:0.72rem;color:${isApproved ? '#10b981' : '#f59e0b'};font-weight:700;">
                                ${isApproved ? '✅ تایید شده' : '⏳ در انتظار تایید مدیر'}
                            </div>
                        </div>
                        ${isApproved
                            ? `<button class="coc-action-btn cyan btn-play-user-game" data-url="${g.url}" data-title="${g.title}">▶️ اجرا</button>`
                            : `<span style="font-size:0.7rem;background:#fef3c7;color:#b45309;padding:3px 8px;border-radius:8px;">در صف بررسی</span>`}
                    </div>
                `;
            });
            html += `</div>`;
        }
        html += `</div>`;

        if (isMine) {
            html += `
                <div id="tv-upload-section" style="display:none;background:#f8fafc;padding:10px;border-radius:12px;border:1px solid #e2e8f0;">
                    <input type="text" id="tv-g-title" placeholder="نام بازی جدید" style="width:100%;padding:6px;border-radius:8px;border:1px solid #cbd5e1;margin-bottom:6px;">
                    <select id="tv-g-tier" style="width:100%;padding:6px;border-radius:8px;border:1px solid #cbd5e1;margin-bottom:6px;background:#fff;">
                        <option value="simple">بازی ساده (10 BitBlox)</option>
                        <option value="staged">بازی مرحله‌ای (20 BitBlox)</option>
                        <option value="pro">بازی حرفه‌ای (100 BitBlox)</option>
                    </select>
                    <input type="file" id="tv-g-file" accept=".zip" style="width:100%;padding:6px;border-radius:8px;border:1px solid #cbd5e1;margin-bottom:8px;background:#fff;">
                    <button class="coc-action-btn green" id="btn-tv-upload" style="width:100%;">🚀 ارسال بازی برای تایید ادمین</button>
                </div>
            `;
        }

        body.innerHTML = html;

        if (isMine) {
            const tabPlay = document.getElementById('tab-tv-play');
            const tabUpload = document.getElementById('tab-tv-upload');
            const secPlay = document.getElementById('tv-play-section');
            const secUpload = document.getElementById('tv-upload-section');

            tabPlay.onclick = () => { secPlay.style.display = 'block'; secUpload.style.display = 'none'; tabPlay.style.opacity = '1'; tabUpload.style.opacity = '0.75'; };
            tabUpload.onclick = () => { secPlay.style.display = 'none'; secUpload.style.display = 'block'; tabUpload.style.opacity = '1'; tabPlay.style.opacity = '0.75'; };

            const submitBtn = document.getElementById('btn-tv-upload');
            submitBtn.onclick = async () => {
                const title = document.getElementById('tv-g-title').value.trim();
                const tier = document.getElementById('tv-g-tier').value;
                const file = document.getElementById('tv-g-file').files[0];
                if (!title) return alert('لطفاً نام بازی را وارد کن!');
                if (!file) return alert('لطفاً فایل ZIP بازی را انتخاب کن!');

                submitBtn.disabled = true;
                submitBtn.textContent = '⏳ در حال آپلود...';
                try {
                    const res = await bridge.submitUserGame(title, tier, file);
                    if (!res.success) alert(res.message || 'خطا در آپلود بازی');
                    else {
                        this.app.playerState = res.state;
                        alert('بازی با موفقیت آپلود شد و پس از تایید مدیر فعال می‌شود!');
                        this.openTvStudioPanel(homeData);
                    }
                } catch { alert('خطا در ارتباط با سرور.'); }
                finally { submitBtn.disabled = false; submitBtn.textContent = '🚀 ارسال بازی برای تایید ادمین'; }
            };
        }

        body.querySelectorAll('.btn-play-user-game').forEach(btn => {
            btn.onclick = () => {
                body.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:6px;">
                        <strong style="font-size:0.82rem;">🎮 ${btn.dataset.title}</strong>
                        <div style="display:flex;gap:6px;">
                            <button class="coc-action-btn cyan" id="btn-fullscreen-tv" style="padding:4px 10px;">⛶ تمام‌صفحه</button>
                            <button class="coc-action-btn danger" id="btn-back-tv-list" style="padding:4px 10px;">⬅️ بازگشت</button>
                        </div>
                    </div>
                    <iframe id="bloxia-game-iframe" style="width:100%;height:340px;border:2px solid #334155;border-radius:12px;background:#000;" sandbox="allow-scripts" allowfullscreen></iframe>
                `;
                const iframe = document.getElementById('bloxia-game-iframe');
                iframe.src = btn.dataset.url;
                document.getElementById('btn-back-tv-list').onclick = () => this.openTvStudioPanel(homeData);
                document.getElementById('btn-fullscreen-tv').onclick = () => {
                    if (iframe.requestFullscreen) iframe.requestFullscreen();
                    else if (iframe.webkitRequestFullscreen) iframe.webkitRequestFullscreen();
                };
            };
        });

        panel.classList.add('open');
    }
	
	
	
    async openNeighborsPanel() {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '🏘️ خانه‌های شهروندان';
        document.getElementById('cart-section').style.display = 'none';
        const plots = await bridge.getResidentialPlots();
        body.innerHTML = `<div class="item-grid">${plots.map(p => `
            <div class="shop-card"><h4>🏠 ${p.ownerName}</h4><button class="coc-action-btn" data-h="${p.ownerId}" style="width:100%;margin-top:6px;">ورود</button></div>
        `).join('')}</div>`;
        body.querySelectorAll('[data-h]').forEach(b => {
            b.onclick = () => { panel.classList.remove('open'); this.app.loadZone(`home_${b.dataset.h}`); };
        });
        panel.classList.add('open');
    }

    openDirectPlayerCard(citizen) {
        document.getElementById('interact-player-name').textContent = `👤 ${citizen.name}`;
        document.getElementById('player-interact-card').classList.add('open');
        document.getElementById('btn-direct-chat').onclick = () => { document.getElementById('player-interact-card').classList.remove('open'); this.openSafeChatRoom(citizen.id); };
        document.getElementById('btn-direct-pvp').onclick = () => { document.getElementById('player-interact-card').classList.remove('open'); this.openPvPChallengeCenter(citizen.id); };
        document.getElementById('btn-direct-home').onclick = () => { document.getElementById('player-interact-card').classList.remove('open'); this.app.loadZone(`home_${citizen.id}`); };
    }

    openZoneActivity(zoneId) {
        if (zoneId.startsWith('shop_')) return this.openShopPanel(zoneId);
        if (zoneId === 'hair_salon') {
            bridge.applyHairSalon('curly', 0xec4899, 30).then(res => {
                if (res.success) { this.app.playerState = res.state; this.refreshBitBlox(); this.app.myRig.applyAppearance(this.app.playerState.appearance); this.app.myRig.say('مدل موی جدید! 💇'); }
            });
            return;
        }
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('cart-section').style.display = 'none';
        document.getElementById('panel-title').textContent = '🎯 فعالیت و بازی';
        body.innerHTML = `<button class="coc-action-btn green" id="btn-zone-play" style="width:100%;padding:12px;">💎 پرداخت 10 BitBlox و دریافت جایزه (20 BitBlox)</button>`;
        document.getElementById('btn-zone-play').onclick = async () => {
            const res = await bridge.walletTransaction(10, zoneId);
            if (res.success) { this.app.playerState = res.state; this.refreshBitBlox(); this.app.myRig.triggerEmote('jump'); panel.classList.remove('open'); }
        };
        panel.classList.add('open');
    }

}