import { bridge, CATALOG, ALL_ITEMS_FLAT, SAFE_CHAT_PHRASES, CINEMA_MOVIES, GAMENET_GAMES } from './bridge.js';

export class UIController {
    constructor(app) {
        this.app = app;
        this.shoppingCart = [];
        this.chatLog = [{ sender: 'سیستم امن', text: 'به چت‌روم امن خوش آمدید! جملات آماده را انتخاب کنید.', type: 'reply' }];
        this.seenChatIds = new Set();
        this.activePvpMatch = null;
        this.pvpPollTimer = null;
        this.dismissedInviteIds = new Set();
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
        const bankBtn = document.getElementById('btn-open-bank-menu');
        if (bankBtn) {
            bankBtn.onclick = () => this.openBankChargePrompt();
        }
    }

    openBankChargePrompt() {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '🏦 شارژ حساب BitBlox';
        document.getElementById('cart-section').style.display = 'none';
        const bankUrl = (window.BLOXIA_BASE_URL || '/') + 'index.php?option=com_bloxia&view=bank';
        body.innerHTML = `
            <div style="text-align:center;padding:8px;">
                <div style="font-size:2.4rem;margin-bottom:6px;">🏦💎</div>
                <p style="font-size:0.85rem;color:#334155;line-height:1.7;margin-bottom:12px;">
                    برای خرید بسته‌های <strong>۱۰۰، ۵۰۰ و ۲۰۰۰ BitBlox</strong> به صورت کارت به کارت هوشمند وارد صفحه اختصاصی <strong>بانک</strong> شوید.
                </p>
                <a href="${bankUrl}" class="coc-action-btn green" style="display:inline-flex;width:100%;justify-content:center;text-decoration:none;padding:12px;font-size:0.95rem;">
                    💳 شارژ حساب (ورود به صفحه بانک)
                </a>
            </div>
        `;
        panel.classList.add('open');
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

    hexNumToCss(num) {
        return '#' + (Number(num) || 0).toString(16).padStart(6, '0');
    }

    cssToHexNum(css) {
        return parseInt(String(css).replace('#', ''), 16) || 0;
    }

    renderColorPaletteHtml(inputId, dataKey, currentNum, label, quickHexes = []) {
        const cssHex = this.hexNumToCss(currentNum);
        const defaults = quickHexes.length ? quickHexes : [0xffffff, 0xf8fafc, 0x94a3b8, 0x1e293b, 0xef4444, 0xf59e0b, 0x10b981, 0x3b82f6, 0x8b5cf6, 0xec4899];
        return `
            <div class="cas-group">
                <div class="cas-group-title">${label}</div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:5px;background:#f1f5f9;padding:4px 8px;border-radius:8px;border:1px solid #cbd5e1;cursor:pointer;font-size:0.75rem;font-weight:700;">
                        🎨 پالت کامل:
                        <input type="color" id="${inputId}" class="bloxia-full-color-input" data-key="${dataKey}" value="${cssHex}"
                               style="width:30px;height:24px;border:none;background:none;cursor:pointer;padding:0;">
                    </label>
                    <div class="swatch-row" style="margin:0;">
                        ${defaults.map(hex => `<div class="color-swatch ${Number(currentNum) === hex ? 'selected' : ''}" data-key="${dataKey}" data-hex="${hex}" style="background:${this.hexNumToCss(hex)};"></div>`).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    // اصلاح کاراکتر من (رنگ پوست، مو، لباس، شلوار، کفش با پالت رنگ کامل - تغییر رنگ نیازمند پرداخت BitBlox)
    openCharacterEditor(keepOrig = false) {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '👤 اصلاح کاراکتر من';
        document.getElementById('cart-section').style.display = 'none';

        const app = this.app.playerState.appearance;
        if (!keepOrig || !this._casOrigColors) {
            this._casOrigColors = {
                skinColor: app.skinColor,
                hairColor: app.hairColor,
                shirtColor: app.shirtColor,
                pantsColor: app.pantsColor,
                shoesColor: app.shoesColor
            };
        }
        const calcCost = () => {
            let changed = 0;
            Object.entries(this._casOrigColors).forEach(([k, v]) => {
                if (Number(app[k]) !== Number(v)) changed++;
            });
            return changed * 10;
        };

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
            <div style="font-size:0.74rem;color:#b45309;background:#fef3c7;padding:6px 8px;border-radius:8px;margin-bottom:8px;">
                💎 تغییر هر رنگ هزینه <strong>۱۰ BitBlox</strong> دارد.
            </div>
        `;

        Object.entries(presets).forEach(([key, cfg]) => {
            html += this.renderColorPaletteHtml(`cas-cp-${key}`, key, app[key], cfg.label, cfg.colors);
        });

        const initCost = calcCost();
        html += `<button class="coc-action-btn green" id="btn-save-cas" style="width:100%;">${initCost > 0 ? `💳 ذخیره و پرداخت ${initCost} BitBlox` : '💾 ذخیره ظاهر (رایگان)'}</button>`;
        body.innerHTML = html;

        const updateBtn = () => {
            const c = calcCost();
            const b = document.getElementById('btn-save-cas');
            if (b) b.textContent = c > 0 ? `💳 ذخیره و پرداخت ${c} BitBlox` : '💾 ذخیره ظاهر (رایگان)';
        };

        body.querySelectorAll('.color-swatch').forEach(sw => {
            sw.onclick = () => {
                app[sw.dataset.key] = Number(sw.dataset.hex);
                this.app.myRig.applyAppearance(app);
                this.openCharacterEditor(true);
            };
        });

        body.querySelectorAll('.bloxia-full-color-input').forEach(inp => {
            inp.oninput = (e) => {
                app[inp.dataset.key] = this.cssToHexNum(e.target.value);
                this.app.myRig.applyAppearance(app);
                updateBtn();
            };
        });

        document.getElementById('btn-save-cas').onclick = async () => {
            const newName = document.getElementById('cas-name-input').value.trim() || this.app.playerState.name;
            const res = await bridge.saveCharacterAppearance(app, newName);
            if (!res?.success) {
                Object.assign(app, this._casOrigColors);
                this.app.myRig.applyAppearance(app);
                return alert(res?.message || 'خطا در ذخیره ظاهر کاراکتر');
            }
            this._casOrigColors = null;
            this.app.playerState = res.state;
            this.refreshBitBlox();
            document.getElementById('player-name-display').textContent = this.app.playerState.name;
            this.app.myRig.setName(this.app.playerState.name);
            this.app.myRig.say(res.cost > 0 ? `ظاهر جدیدم آماده شد! (-${res.cost} 💎)` : 'ظاهر جدیدم آماده شد! ✨');
            panel.classList.remove('open');
        };

        panel.classList.add('open');
    }

    getOnlineZonePlayers() {
        const list = [];
        if (this.app && this.app.remoteRigs) {
            this.app.remoteRigs.forEach(rig => {
                if (rig.mesh?.userData?.citizenData) {
                    list.push(rig.mesh.userData.citizenData);
                }
            });
        }
        return list;
    }

    receiveIncomingChats(chats) {
        const myId = Number(this.app.playerState?.userId || 0);
        let added = false;
        chats.forEach(c => {
            if (this.seenChatIds.has(c.id)) return;
            this.seenChatIds.add(c.id);
            const isMine = Number(c.senderId) === myId;
            this.chatLog.push({
                sender: c.senderName,
                text: c.message,
                type: isMine ? 'mine' : 'reply'
            });
            added = true;
        });

        if (added) {
            const box = document.getElementById('chat-box');
            if (box) {
                box.innerHTML = this.chatLog.map(m => `<div class="chat-msg ${m.type}"><strong>${m.sender}:</strong> ${m.text}</div>`).join('');
                box.scrollTop = box.scrollHeight;
            }
        }
    }

    // چت‌روم امن با جملات ثابت (دوطرفه و زنده بین بازیکنان آنلاین)
    openSafeChatRoom(targetId = 'all') {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '💬 چت‌روم کنترل‌شده';
        document.getElementById('cart-section').style.display = 'none';

        const onlinePlayers = this.getOnlineZonePlayers();
        const logHtml = this.chatLog.map(m => `<div class="chat-msg ${m.type}"><strong>${m.sender}:</strong> ${m.text}</div>`).join('');
        body.innerHTML = `
            <select id="chat-target-select" style="width:100%;padding:5px;border-radius:8px;margin-bottom:8px;">
                <option value="all">همه حاضران (${onlinePlayers.length} آنلاین)</option>
                ${onlinePlayers.map(c => `<option value="${c.id}" ${Number(targetId) === c.id ? 'selected' : ''}>🟢 ${c.name}</option>`).join('')}
            </select>
            <div class="chat-history" id="chat-box">${logHtml}</div>
            ${SAFE_CHAT_PHRASES.map(p => `<button class="coc-action-btn" data-id="${p.id}" style="width:100%;text-align:right;margin-bottom:4px;">${p.text}</button>`).join('')}
        `;
        const box = document.getElementById('chat-box');
        if (box) box.scrollTop = box.scrollHeight;

        body.querySelectorAll('.coc-action-btn').forEach(btn => {
            btn.onclick = async () => {
                const phrase = SAFE_CHAT_PHRASES.find(x => x.id === btn.dataset.id);
                const sel = document.getElementById('chat-target-select');
                const tVal = sel.value;
                const targetNum = tVal === 'all' ? 0 : Number(tVal);

                this.app.myRig.say(phrase.text);
                this.app.myRig.triggerEmote('wave');

                const res = await bridge.sendChat(this.app.currentZone, targetNum, phrase.text);
                if (res?.success && res.chat) {
                    this.receiveIncomingChats([res.chat]);
                } else {
                    this.chatLog.push({ sender: this.app.playerState.name, text: phrase.text, type: 'mine' });
                    this.openSafeChatRoom(tVal);
                }
            };
        });
        panel.classList.add('open');
    }

    getPvpGameTitle(type) {
        const map = {
            rps: '✊✌️✋ سنگ کاغذ قیچی',
            ttt: '⭕❌ بازی دوز',
            darts: '🎯 دارت دونفره',
            ludo: '🎲 منچ سرعتی',
            gol: '✊✨ گل یا پوچ'
        };
        return map[type] || type;
    }

    handlePvpSync(pvp) {
        const invites = pvp?.incomingInvites || [];
        const validInvite = invites.find(inv => !this.dismissedInviteIds.has(inv.id));
        if (validInvite && (!this.activePvpMatch || this.activePvpMatch.status === 'finished')) {
            this.showPvpInviteToast(validInvite);
        } else if (!validInvite) {
            const toast = document.getElementById('bloxia-pvp-invite-toast');
            if (toast) toast.remove();
        }

        if (pvp?.activeMatch) {
            const m = pvp.activeMatch;
            const prevStatus = this.activePvpMatch?.status;
            this.activePvpMatch = m;
            if (prevStatus === 'pending' && m.status === 'active') {
                this.openLivePvpPanel(m);
            }
        }
    }

    showPvpInviteToast(invite) {
        let toast = document.getElementById('bloxia-pvp-invite-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'bloxia-pvp-invite-toast';
            toast.style.cssText = 'position:fixed;top:76px;left:50%;transform:translateX(-50%);z-index:9999;background:linear-gradient(135deg,#1e293b,#0f172a);color:#fff;padding:12px 16px;border-radius:16px;border:2px solid #f59e0b;box-shadow:0 12px 30px rgba(0,0,0,0.45);display:flex;align-items:center;gap:12px;font-family:inherit;max-width:92vw;';
            document.body.appendChild(toast);
        }
        if (Number(toast.dataset.matchId) === invite.id) return;
        toast.dataset.matchId = invite.id;

        toast.innerHTML = `
            <div style="font-size:0.85rem;line-height:1.5;">
                <strong>⚔️ دعوت به بازی از ${invite.challengerName}</strong><br>
                <span style="color:#fde047;">${this.getPvpGameTitle(invite.gameType)}</span>
            </div>
            <div style="display:flex;gap:6px;">
                <button id="btn-accept-pvp-toast" class="coc-action-btn green" style="padding:6px 12px;">✅ قبول</button>
                <button id="btn-decline-pvp-toast" class="coc-action-btn danger" style="padding:6px 10px;">❌ رد</button>
            </div>
        `;

        document.getElementById('btn-accept-pvp-toast').onclick = async () => {
            toast.remove();
            const res = await bridge.pvpRespond(invite.id, 'accept');
            if (res?.success && res.match) {
                this.activePvpMatch = res.match;
                this.openLivePvpPanel(res.match);
            }
        };
        document.getElementById('btn-decline-pvp-toast').onclick = async () => {
            this.dismissedInviteIds.add(invite.id);
            toast.remove();
            await bridge.pvpRespond(invite.id, 'decline');
        };
    }

    startPvpPolling(matchId) {
        this.stopPvpPolling();
        this.pvpPollTimer = setInterval(async () => {
            const arena = document.getElementById('pvp-arena');
            const panelOpen = document.getElementById('side-panel')?.classList.contains('open');
            if (!arena || !panelOpen) {
                this.stopPvpPolling();
                return;
            }
            const res = await bridge.pvpStatus(matchId);
            if (!res?.success || !res.match) return;
            const prevJson = JSON.stringify(this.activePvpMatch);
            const nextJson = JSON.stringify(res.match);
            if (prevJson !== nextJson) {
                this.activePvpMatch = res.match;
                this.renderLivePvpArena(res.match);
                if (res.match.status === 'finished' || res.match.status === 'declined' || res.match.status === 'cancelled') {
                    this.stopPvpPolling();
                }
            }
        }, 1200);
    }

    stopPvpPolling() {
        if (this.pvpPollTimer) {
            clearInterval(this.pvpPollTimer);
            this.pvpPollTimer = null;
        }
    }

    openLivePvpPanel(match) {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = `⚔️ ${this.getPvpGameTitle(match.gameType)}`;
        document.getElementById('cart-section').style.display = 'none';
        body.innerHTML = `<div id="pvp-arena"></div>`;
        panel.classList.add('open');
        this.renderLivePvpArena(match);
        if (match.status === 'pending' || match.status === 'active') {
            this.startPvpPolling(match.id);
        }
    }

    // چالش بازی‌های دونفره واقعی بین بازیکنان آنلاین
    openPvPChallengeCenter(oppId = null) {
        if (this.activePvpMatch && (this.activePvpMatch.status === 'pending' || this.activePvpMatch.status === 'active')) {
            this.openLivePvpPanel(this.activePvpMatch);
            return;
        }

        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '⚔️ چالش بازی دوطرفه با دوستان';
        document.getElementById('cart-section').style.display = 'none';

        const onlinePlayers = this.getOnlineZonePlayers();
        if (onlinePlayers.length === 0) {
            body.innerHTML = `
                <div style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:12px;border-radius:12px;font-size:0.83rem;line-height:1.6;margin-bottom:10px;">
                    ⚠️ در حال حاضر بازیکن آنلاین دیگری در این منطقه حضور ندارد.<br>
                    وقتی دوستت وارد همین منطقه (یا خانه) شود، می‌توانی دعوت چالش دوطرفه برایش بفرستی!
                </div>
                <div id="pvp-arena"></div>
            `;
            panel.classList.add('open');
            return;
        }

        const opp = onlinePlayers.find(c => c.id === oppId) || onlinePlayers[0];
        body.innerHTML = `
            <div style="font-size:0.78rem;color:#0f172a;margin-bottom:4px;font-weight:700;">حریف آنلاین را انتخاب کن:</div>
            <select id="pvp-opp" style="width:100%;padding:6px;border-radius:8px;margin-bottom:8px;">
                ${onlinePlayers.map(c => `<option value="${c.id}" ${c.id === opp.id ? 'selected' : ''}>🟢 ${c.name}</option>`).join('')}
            </select>
            <div class="item-grid">
                <button class="coc-action-btn" data-g="rps">✊✌️✋ سنگ کاغذ قیچی</button>
                <button class="coc-action-btn cyan" data-g="ttt">⭕❌ بازی دوز</button>
                <button class="coc-action-btn amber" data-g="darts">🎯 دارت دونفره</button>
                <button class="coc-action-btn pink" data-g="ludo">🎲 منچ سرعتی</button>
                <button class="coc-action-btn green" data-g="gol" style="grid-column:1/-1;">✊✨ گل یا پوچ</button>
            </div>
            <div id="pvp-arena" style="margin-top:10px;"></div>
        `;

        body.querySelectorAll('[data-g]').forEach(btn => {
            btn.onclick = async () => {
                const targetId = Number(document.getElementById('pvp-opp').value);
                const arena = document.getElementById('pvp-arena');
                arena.innerHTML = '<div style="text-align:center;padding:12px;color:#64748b;">⏳ در حال ارسال دعوت...</div>';
                const res = await bridge.pvpCreate(targetId, btn.dataset.g);
                if (!res?.success || !res.match) {
                    arena.innerHTML = `<div style="color:#ef4444;text-align:center;">${res?.message || 'خطا در ارسال دعوت'}</div>`;
                    return;
                }
                this.activePvpMatch = res.match;
                this.renderLivePvpArena(res.match);
                this.startPvpPolling(res.match.id);
            };
        });
        panel.classList.add('open');
    }

    async renderLivePvpArena(match) {
        const arena = document.getElementById('pvp-arena');
        if (!arena) return;

        const myId = Number(this.app.playerState.userId);
        const isChallenger = myId === match.challengerId;
        const oppName = isChallenger ? match.opponentName : match.challengerName;
        const oppId = isChallenger ? match.opponentId : match.challengerId;

        if (match.status === 'pending') {
            arena.innerHTML = `
                <div class="pvp-board" style="text-align:center;padding:14px;">
                    <div style="font-size:1.5rem;">⏳</div>
                    <div style="font-weight:800;margin:6px 0;">در انتظار قبول چالش توسط ${oppName}...</div>
                    <div style="font-size:0.76rem;color:#64748b;margin-bottom:10px;">دعوت برای بازیکن ارسال شد.</div>
                    <button class="coc-action-btn danger" id="btn-cancel-pvp">✖ لغو دعوت</button>
                </div>
            `;
            document.getElementById('btn-cancel-pvp').onclick = async () => {
                await bridge.pvpRespond(match.id, 'cancel');
                this.stopPvpPolling();
                this.activePvpMatch = null;
                this.openPvPChallengeCenter(oppId);
            };
            return;
        }

        if (match.status === 'declined' || match.status === 'cancelled') {
            this.stopPvpPolling();
            arena.innerHTML = `
                <div class="pvp-board" style="text-align:center;padding:14px;">
                    <div style="font-weight:800;color:#ef4444;margin-bottom:8px;">
                        ${match.status === 'declined' ? `😅 ${oppName} دعوت را رد کرد.` : '✖ مسابقه لغو شد.'}
                    </div>
                    <button class="coc-action-btn" id="btn-pvp-back">بازگشت به لیست بازی‌ها</button>
                </div>
            `;
            document.getElementById('btn-pvp-back').onclick = () => {
                this.activePvpMatch = null;
                this.openPvPChallengeCenter(oppId);
            };
            return;
        }

        if (match.status === 'finished') {
            this.stopPvpPolling();
            let resultBanner = '';
            if (match.winnerId === 0) {
                resultBanner = `<div style="font-weight:800;color:#f59e0b;font-size:1rem;">🤝 مساوی شد!</div>`;
            } else if (match.winnerId === myId) {
                resultBanner = `<div style="font-weight:800;color:#10b981;font-size:1rem;">🏆 تو برنده شدی! +15 BitBlox 💎</div>`;
                const fresh = await bridge.getCharacter();
                if (fresh && fresh.bitblox !== undefined) {
                    this.app.playerState = fresh;
                    this.refreshBitBlox();
                }
                this.app.myRig.say(`بردم! +15 💎 مقابل ${oppName}`);
                this.app.myRig.triggerEmote('jump');
            } else {
                resultBanner = `<div style="font-weight:800;color:#ef4444;font-size:1rem;">😅 ${oppName} برنده شد!</div>`;
            }

            let detailHtml = '';
            if (match.gameType === 'rps') {
                const lbl = { r: '✊ سنگ', p: '✋ کاغذ', s: '✌️ قیچی' };
                const myC = match.state.moves?.[String(myId)];
                const opC = match.state.moves?.[String(oppId)];
                detailHtml = `<div style="margin:8px 0;font-size:0.85rem;">انتخاب تو: ${lbl[myC] || '-'} | ${oppName}: ${lbl[opC] || '-'}</div>`;
            } else if (match.gameType === 'darts' || match.gameType === 'ludo') {
                const myS = match.state.scores?.[String(myId)];
                const opS = match.state.scores?.[String(oppId)];
                detailHtml = `<div style="margin:8px 0;font-size:0.85rem;">امتیاز تو: <strong>${myS}</strong> | ${oppName}: <strong>${opS}</strong></div>`;
            } else if (match.gameType === 'gol') {
                const hLbl = (h) => h === 0 ? '✊ دست راست' : '✊ دست چپ';
                detailHtml = `<div style="margin:8px 0;font-size:0.85rem;">گل در ${hLbl(match.state.hiddenHand)} بود | حدس: ${hLbl(match.state.guessHand)}</div>`;
            }

            arena.innerHTML = `
                <div class="pvp-board" style="text-align:center;padding:12px;">
                    ${resultBanner}
                    ${detailHtml}
                    <button class="coc-action-btn cyan" id="btn-pvp-rematch" style="margin-top:8px;">🔄 چالش جدید</button>
                </div>
            `;
            document.getElementById('btn-pvp-rematch').onclick = () => {
                this.activePvpMatch = null;
                this.openPvPChallengeCenter(oppId);
            };
            return;
        }

        // وضعیت active (در حال بازی دوطرفه)
        const st = match.state || {};
        const sendMove = async (moveObj) => {
            const res = await bridge.pvpMove(match.id, moveObj);
            if (!res?.success) {
                if (res?.message) alert(res.message);
                return;
            }
            this.activePvpMatch = res.match;
            this.renderLivePvpArena(res.match);
        };

        if (match.gameType === 'rps') {
            const myMove = st.moves?.[String(myId)];
            const oppMoved = st.moves?.[String(oppId)] !== null;
            const opts = [{ id: 'r', l: '✊ سنگ' }, { id: 'p', l: '✋ کاغذ' }, { id: 's', l: '✌️ قیچی' }];
            arena.innerHTML = `
                <div class="pvp-board" style="text-align:center;">
                    <h4>سنگ کاغذ قیچی با ${oppName}</h4>
                    <div style="font-size:0.78rem;color:#64748b;margin:4px 0;">
                        ${oppMoved ? `✅ ${oppName} انتخابش را ثبت کرده!` : `⏳ ${oppName} هنوز انتخاب نکرده...`}
                    </div>
                    ${myMove
                        ? `<div style="margin-top:8px;font-weight:700;color:#0284c7;">انتخاب تو ثبت شد! منتظر ${oppName}...</div>`
                        : `<div style="display:flex;gap:6px;justify-content:center;margin-top:8px;">${opts.map(o => `<button class="coc-action-btn" data-p="${o.id}">${o.l}</button>`).join('')}</div>`}
                </div>
            `;
            arena.querySelectorAll('[data-p]').forEach(b => {
                b.onclick = () => sendMove({ choice: b.dataset.p });
            });
        } else if (match.gameType === 'ttt') {
            const myMark = st.marks?.[String(myId)] || '❌';
            const isMyTurn = match.turnUserId === myId;
            const board = st.board || Array(9).fill('');
            arena.innerHTML = `
                <div class="pvp-board">
                    <div style="text-align:center;font-weight:800;margin-bottom:6px;color:${isMyTurn ? '#10b981' : '#f59e0b'};">
                        ${isMyTurn ? `🟢 نوبت تو (${myMark})` : `⏳ نوبت ${oppName}...`}
                    </div>
                    <div class="ttt-grid">${board.map((v, i) => `<button class="ttt-cell" data-i="${i}" ${(!isMyTurn || v) ? 'disabled' : ''}>${v}</button>`).join('')}</div>
                </div>
            `;
            arena.querySelectorAll('.ttt-cell').forEach(c => {
                c.onclick = () => sendMove({ cell: Number(c.dataset.i) });
            });
        } else if (match.gameType === 'darts' || match.gameType === 'ludo') {
            const myScore = st.scores?.[String(myId)];
            const oppScore = st.scores?.[String(oppId)];
            arena.innerHTML = `
                <div class="pvp-board" style="text-align:center;">
                    <h4>${this.getPvpGameTitle(match.gameType)} با ${oppName}</h4>
                    <div style="font-size:0.8rem;margin:6px 0;">
                        امتیاز تو: <strong>${myScore !== null && myScore !== undefined ? myScore : '؟'}</strong> |
                        ${oppName}: <strong>${oppScore !== null && oppScore !== undefined ? 'ثبت شد ✅' : 'منتظر...'}</strong>
                    </div>
                    ${myScore === null || myScore === undefined
                        ? `<button class="coc-action-btn amber" id="btn-pvp-roll">🎲 پرتاب / انداختن تاس</button>`
                        : `<div style="color:#0284c7;font-weight:700;">امتیازت ثبت شد! منتظر پرتاب ${oppName}...</div>`}
                </div>
            `;
            const rollBtn = document.getElementById('btn-pvp-roll');
            if (rollBtn) rollBtn.onclick = () => sendMove({ roll: true });
        } else if (match.gameType === 'gol') {
            const isMyTurn = match.turnUserId === myId;
            if (isChallenger) {
                arena.innerHTML = `
                    <div class="pvp-board" style="text-align:center;">
                        <h4>✊✨ گل یا پوچ با ${oppName}</h4>
                        ${st.hiddenHand === null
                            ? `<div style="margin:6px 0;">گل را در کدام دست پنهان می‌کنی؟</div>
                               <div style="display:flex;gap:8px;justify-content:center;">
                                   <button class="coc-action-btn" data-h="0">✊ دست راست</button>
                                   <button class="coc-action-btn" data-h="1">✊ دست چپ</button>
                               </div>`
                            : `<div style="color:#0284c7;font-weight:700;">گل پنهان شد! منتظر حدس ${oppName}...</div>`}
                    </div>
                `;
            } else {
                arena.innerHTML = `
                    <div class="pvp-board" style="text-align:center;">
                        <h4>✊✨ گل یا پوچ با ${oppName}</h4>
                        ${st.hiddenHand === null
                            ? `<div style="color:#f59e0b;font-weight:700;">⏳ ${oppName} در حال پنهان کردن گل است...</div>`
                            : `<div style="margin:6px 0;">حدس بزن گل در کدام دست ${oppName} است؟</div>
                               <div style="display:flex;gap:8px;justify-content:center;">
                                   <button class="coc-action-btn green" data-h="0" ${!isMyTurn ? 'disabled' : ''}>✊ دست راست</button>
                                   <button class="coc-action-btn green" data-h="1" ${!isMyTurn ? 'disabled' : ''}>✊ دست چپ</button>
                               </div>`}
                    </div>
                `;
            }
            arena.querySelectorAll('[data-h]').forEach(b => {
                b.onclick = () => sendMove({ hand: Number(b.dataset.h) });
            });
        }
    }

    openShopPanel(shopZone, subFilter = 'all') {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = shopZone === 'shop_furniture' ? '🛋️ فروشگاه لوازم خانگی و دکوراسیون' : '🛍️ فروشگاه';
        document.getElementById('cart-section').style.display = 'block';

        const allItems = CATALOG[shopZone] || [];
        const cats = [
            { id: 'all', label: 'همه' },
            { id: 'wall_style', label: '🎨 دیوار' },
            { id: 'floor_style', label: '🪵 کفپوش' },
            { id: 'frame', label: '🖼️ قاب عکس' },
            { id: 'kitchen', label: '🍳 آشپزخانه' },
            { id: 'lighting', label: '💡 نور' },
            { id: 'decor', label: '🛋️ دکور' },
            { id: 'bedroom', label: '🛏️ تخت' }
        ];

        const filtered = (shopZone === 'shop_furniture' && subFilter !== 'all')
            ? allItems.filter(it => it.type === subFilter)
            : allItems;

        let tabsHtml = '';
        if (shopZone === 'shop_furniture') {
            tabsHtml = `<div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;">
                ${cats.map(c => `<button class="coc-action-btn ${subFilter === c.id ? 'cyan' : ''}" data-sub="${c.id}" style="padding:4px 8px;font-size:0.72rem;">${c.label}</button>`).join('')}
            </div>`;
        }

        body.innerHTML = `${tabsHtml}<div class="item-grid">${filtered.map(it => `
            <div class="shop-card">
                <h4>${it.name}</h4>
                <div class="price">💎 ${it.price} BitBlox</div>
                <button class="coc-action-btn" data-id="${it.id}" style="width:100%;">➕ سبد خرید</button>
            </div>
        `).join('')}</div>`;

        body.querySelectorAll('[data-sub]').forEach(b => {
            b.onclick = () => this.openShopPanel(shopZone, b.dataset.sub);
        });
        body.querySelectorAll('[data-id]').forEach(btn => {
            btn.onclick = () => {
                const it = allItems.find(x => x.id === btn.dataset.id);
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
        if (this.app.currentZone === `home_${this.app.playerState.userId}` && this.app.rebuildHomeView) {
            this.app.rebuildHomeView();
        }
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

    openFurnitureEditModal(furnItem, keepOrig = false) {
        if (!furnItem) return;
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        const def = ALL_ITEMS_FLAT.find(x => x.id === furnItem.itemId);
        document.getElementById('panel-title').textContent = `🎨 ویرایش ${def ? def.name : furnItem.itemId}`;
        document.getElementById('cart-section').style.display = 'none';

        const targetObj = this.app.playerState.homeLayout.find(x => x.uid === furnItem.uid);
        if (!keepOrig || this._furnOrigUid !== furnItem.uid) {
            this._furnOrigUid = furnItem.uid;
            this._furnOrigColor = furnItem.color ?? null;
        }
        const isColorChanged = () => {
            const c = targetObj ? (targetObj.color ?? null) : (furnItem.color ?? null);
            return c !== null && Number(c) !== Number(this._furnOrigColor);
        };

        const curColor = furnItem.color ?? def?.color ?? 0x3b82f6;
        const initCost = isColorChanged() ? 10 : 0;
        body.innerHTML = `
            <div style="font-size:0.74rem;color:#b45309;background:#fef3c7;padding:6px 8px;border-radius:8px;margin-bottom:8px;">
                💎 تغییر رنگ: <strong>۱۰ BitBlox</strong> | 🔄 چرخش ۹۰°: <strong>رایگان</strong>
            </div>
            ${this.renderColorPaletteHtml('furn-color-picker', 'furnColor', curColor, '🎨 تغییر رنگ وسیله (پالت کامل):')}
            <div style="display:flex;gap:8px;margin-top:10px;">
                <button class="coc-action-btn cyan" id="btn-furn-rotate" style="flex:1;">🔄 چرخش ۹۰° (رایگان)</button>
                <button class="coc-action-btn danger" id="btn-furn-remove" style="flex:1;">🗑️ بازگشت به انبار</button>
            </div>
            <button class="coc-action-btn green" id="btn-furn-save" style="width:100%;margin-top:10px;">
                ${initCost > 0 ? '💳 ذخیره رنگ و پرداخت 10 BitBlox' : '💾 ذخیره چرخش (رایگان)'}
            </button>
        `;

        const updateBtn = () => {
            const b = document.getElementById('btn-furn-save');
            if (b) b.textContent = isColorChanged() ? '💳 ذخیره رنگ و پرداخت 10 BitBlox' : '💾 ذخیره چرخش (رایگان)';
        };
        const applyLive = () => {
            this.app.renderHomeFurniture();
        };

        body.querySelectorAll('.color-swatch').forEach(sw => {
            sw.onclick = () => {
                if (targetObj) targetObj.color = Number(sw.dataset.hex);
                applyLive();
                this.openFurnitureEditModal(targetObj || furnItem, true);
            };
        });
        const picker = document.getElementById('furn-color-picker');
        if (picker) {
            picker.oninput = (e) => {
                if (targetObj) targetObj.color = this.cssToHexNum(e.target.value);
                applyLive();
                updateBtn();
            };
        }

        document.getElementById('btn-furn-rotate').onclick = () => {
            if (targetObj) targetObj.rotY = (targetObj.rotY || 0) + Math.PI / 2;
            applyLive();
        };
        document.getElementById('btn-furn-remove').onclick = async () => {
            const idx = this.app.playerState.homeLayout.findIndex(x => x.uid === furnItem.uid);
            if (idx > -1) {
                const removed = this.app.playerState.homeLayout.splice(idx, 1)[0];
                this.app.playerState.furnitureInventory.push(removed.itemId);
                this.app.renderHomeFurniture();
                const res = await bridge.updateHomeAndInventory(this.app.playerState.homeLayout, this.app.playerState.furnitureInventory);
                if (res?.state) this.app.playerState = res.state;
                this._furnOrigUid = null;
                panel.classList.remove('open');
            }
        };
        document.getElementById('btn-furn-save').onclick = async () => {
            const res = await bridge.updateHomeAndInventory(this.app.playerState.homeLayout, this.app.playerState.furnitureInventory);
            if (res && res.success === false) {
                if (targetObj) targetObj.color = this._furnOrigColor;
                applyLive();
                return alert(res.message || 'موجودی BitBlox کافی نیست!');
            }
            if (res?.state) this.app.playerState = res.state;
            this._furnOrigUid = null;
            this.refreshBitBlox();
            this.app.myRig.say(res?.cost > 0 ? `رنگ جدید ذخیره شد (-${res.cost} 💎)` : 'چرخش ذخیره شد ✨');
            panel.classList.remove('open');
        };
        panel.classList.add('open');
    }

    openHomeInventoryPanel(tab = 'layout', subFilter = 'all') {
        const panel = document.getElementById('side-panel');
        const body = document.getElementById('panel-body');
        document.getElementById('panel-title').textContent = '🛋️ چیدمان و دکوراسیون خانه';
        document.getElementById('cart-section').style.display = tab === 'shop' ? 'block' : 'none';

        let html = `
            <div style="display:flex;gap:5px;margin-bottom:10px;">
                <button id="tab-home-layout" class="coc-action-btn ${tab === 'layout' ? 'cyan' : ''}" style="flex:1;font-size:0.74rem;padding:6px 4px;">🛋️ انبار من</button>
                <button id="tab-home-style" class="coc-action-btn ${tab === 'style' ? 'cyan' : ''}" style="flex:1;font-size:0.74rem;padding:6px 4px;">🎨 دیوار و کف</button>
                <button id="tab-home-shop" class="coc-action-btn ${tab === 'shop' ? 'green' : ''}" style="flex:1;font-size:0.74rem;padding:6px 4px;">🛍️ خرید لوازم</button>
            </div>
        `;

        if (tab === 'layout') {
            html += `<div style="font-size:0.75rem;color:#64748b;margin-bottom:8px;">💡 نکته: روی هر وسیله داخل خانه کلیک کنید تا رنگش را تغییر دهید!</div>`;
            html += `<div class="item-grid" style="margin-bottom:10px;">${(this.app.playerState.furnitureInventory || []).map(id => {
                const def = ALL_ITEMS_FLAT.find(f => f.id === id);
                return def ? `<div class="shop-card"><h4>${def.name}</h4><button class="coc-action-btn green" data-pl="${def.id}" style="width:100%;margin-top:5px;">قرار دادن</button></div>` : '';
            }).join('')}</div>`;

            html += `<h4>🗑️ وسایل داخل خانه (کلیک یا بازگشت به انبار):</h4>`;
            (this.app.playerState.homeLayout || []).forEach((p, idx) => {
                const def = ALL_ITEMS_FLAT.find(f => f.id === p.itemId);
                html += `<div class="cart-row"><span>${def ? def.name : p.itemId}</span><div style="display:flex;gap:4px;"><button class="coc-action-btn cyan" data-ed="${idx}" style="padding:3px 8px;">🎨</button><button class="coc-action-btn danger" data-del="${idx}" style="padding:3px 8px;">حذف</button></div></div>`;
            });

            body.innerHTML = html;
            body.querySelectorAll('[data-pl]').forEach(b => {
                b.onclick = () => {
                    const def = ALL_ITEMS_FLAT.find(f => f.id === b.dataset.pl);
                    if (def) { this.app.startPlacingFurniture(def); panel.classList.remove('open'); }
                };
            });
            body.querySelectorAll('[data-ed]').forEach(b => {
                b.onclick = () => this.openFurnitureEditModal(this.app.playerState.homeLayout[Number(b.dataset.ed)]);
            });
            body.querySelectorAll('[data-del]').forEach(b => {
                b.onclick = async () => {
                    const removed = this.app.playerState.homeLayout.splice(Number(b.dataset.del), 1)[0];
                    this.app.playerState.furnitureInventory.push(removed.itemId);
                    this.app.renderHomeFurniture();
                    const res = await bridge.updateHomeAndInventory(this.app.playerState.homeLayout, this.app.playerState.furnitureInventory);
                    if (res?.state) this.app.playerState = res.state;
                    this.openHomeInventoryPanel('layout');
                };
            });
        } else if (tab === 'style') {
            const hs = this.app.playerState.homeStyle || {};
            if (subFilter !== '_keep' || !this._styleOrigColors) {
                this._styleOrigColors = {
                    wallColor: hs.wallColor ?? 0x94a3b8,
                    floorColor: hs.floorColor ?? 0xd8b48b,
                    kitchenCabinetColor: hs.kitchenCabinetColor ?? 0xf8fafc,
                    kitchenCeramicColor: hs.kitchenCeramicColor ?? 0xe2e8f0
                };
            }
            const calcStyleCost = () => {
                let changed = 0;
                Object.entries(this._styleOrigColors).forEach(([k, v]) => {
                    if (Number(hs[k] ?? v) !== Number(v)) changed++;
                });
                return changed * 10;
            };

            const unlocked = this.app.playerState.unlockedStyles || ['wall_paint_classic', 'floor_parquet_oak'];
            const wallStyles = ALL_ITEMS_FLAT.filter(x => x.type === 'wall_style' && unlocked.includes(x.id));
            const floorStyles = ALL_ITEMS_FLAT.filter(x => x.type === 'floor_style' && unlocked.includes(x.id));
            const initCost = calcStyleCost();

            html += `
                <div style="font-size:0.74rem;color:#b45309;background:#fef3c7;padding:6px 8px;border-radius:8px;margin-bottom:8px;">
                    💎 تغییر رنگ دیوار، پارکت، کابینت یا سرامیک: <strong>۱۰ BitBlox</strong> برای هر بخش.
                </div>
                <div class="cas-group">
                    <div class="cas-group-title">🧱 طرح دیوار / کاغذ دیواری (موارد خریداری‌شده):</div>
                    <select id="hs-wall-style" style="width:100%;padding:6px;border-radius:8px;">
                        ${wallStyles.map(w => `<option value="${w.id}" ${hs.wallStyle === w.id ? 'selected' : ''}>${w.name}</option>`).join('')}
                    </select>
                </div>
                ${this.renderColorPaletteHtml('hs-wall-color', 'wallColor', hs.wallColor ?? 0x94a3b8, '🎨 رنگ دیوار:')}

                <div class="cas-group">
                    <div class="cas-group-title">🪵 طرح کفپوش (پارکت / سرامیک خریداری‌شده):</div>
                    <select id="hs-floor-style" style="width:100%;padding:6px;border-radius:8px;">
                        ${floorStyles.map(f => `<option value="${f.id}" ${hs.floorStyle === f.id ? 'selected' : ''}>${f.name}</option>`).join('')}
                    </select>
                </div>
                ${this.renderColorPaletteHtml('hs-floor-color', 'floorColor', hs.floorColor ?? 0xd8b48b, '🎨 رنگ کفپوش / پارکت:')}
                ${this.renderColorPaletteHtml('hs-cab-color', 'kitchenCabinetColor', hs.kitchenCabinetColor ?? 0xf8fafc, '🍳 رنگ کابینت آشپزخانه:')}
                ${this.renderColorPaletteHtml('hs-cer-color', 'kitchenCeramicColor', hs.kitchenCeramicColor ?? 0xe2e8f0, '⬜ رنگ سرامیک آشپزخانه:')}

                <button class="coc-action-btn green" id="btn-save-home-style" style="width:100%;">
                    ${initCost > 0 ? `💳 ذخیره دکور و پرداخت ${initCost} BitBlox` : '💾 ذخیره دکور (رایگان)'}
                </button>
            `;
            body.innerHTML = html;

            if (!this.app.playerState.homeStyle) this.app.playerState.homeStyle = {};
            const styleObj = this.app.playerState.homeStyle;
            const updateStyleBtn = () => {
                const c = calcStyleCost();
                const b = document.getElementById('btn-save-home-style');
                if (b) b.textContent = c > 0 ? `💳 ذخیره دکور و پرداخت ${c} BitBlox` : '💾 ذخیره دکور (رایگان)';
            };

            document.getElementById('hs-wall-style').onchange = (e) => { styleObj.wallStyle = e.target.value; this.app.rebuildHomeView(); };
            document.getElementById('hs-floor-style').onchange = (e) => { styleObj.floorStyle = e.target.value; this.app.rebuildHomeView(); };

            body.querySelectorAll('.color-swatch').forEach(sw => {
                sw.onclick = () => {
                    styleObj[sw.dataset.key] = Number(sw.dataset.hex);
                    this.app.rebuildHomeView();
                    this.openHomeInventoryPanel('style', '_keep');
                };
            });
            body.querySelectorAll('.bloxia-full-color-input').forEach(inp => {
                inp.oninput = (e) => {
                    styleObj[inp.dataset.key] = this.cssToHexNum(e.target.value);
                    this.app.rebuildHomeView();
                    updateStyleBtn();
                };
            });
            document.getElementById('btn-save-home-style').onclick = async () => {
                const res = await bridge.saveHomeStyle(styleObj);
                if (!res?.success) {
                    Object.assign(styleObj, this._styleOrigColors);
                    this.app.rebuildHomeView();
                    return alert(res?.message || 'موجودی BitBlox کافی نیست!');
                }
                this._styleOrigColors = null;
                if (res?.state) this.app.playerState = res.state;
                this.refreshBitBlox();
                this.app.rebuildHomeView();
                this.app.myRig.say(res.cost > 0 ? `دکور جدید ذخیره شد (-${res.cost} 💎)` : 'دکور جدید خانه ذخیره شد! 🏠✨');
                panel.classList.remove('open');
            };
        } else if (tab === 'shop') {
            const allFurn = CATALOG.shop_furniture || [];
            const cats = [
                { id: 'all', label: 'همه' },
                { id: 'wall_style', label: '🎨 دیوار' },
                { id: 'floor_style', label: '🪵 کفپوش' },
                { id: 'frame', label: '🖼️ قاب عکس' },
                { id: 'kitchen', label: '🍳 آشپزخانه' },
                { id: 'lighting', label: '💡 نور' },
                { id: 'decor', label: '🛋️ دکور' },
                { id: 'bedroom', label: '🛏️ تخت' }
            ];
            const filtered = subFilter === 'all' ? allFurn : allFurn.filter(it => it.type === subFilter);

            html += `<div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px;">
                ${cats.map(c => `<button class="coc-action-btn ${subFilter === c.id ? 'cyan' : ''}" data-hsub="${c.id}" style="padding:4px 7px;font-size:0.7rem;">${c.label}</button>`).join('')}
            </div>`;
            html += `<div class="item-grid">${filtered.map(it => `
                <div class="shop-card">
                    <h4>${it.name}</h4>
                    <div class="price">💎 ${it.price} BitBlox</div>
                    <div style="display:flex;gap:4px;">
                        <button class="coc-action-btn green" data-buynow="${it.id}" style="flex:1;font-size:0.72rem;padding:5px;">💳 خرید</button>
                        <button class="coc-action-btn" data-addcart="${it.id}" style="font-size:0.72rem;padding:5px 8px;" title="افزودن به سبد">➕</button>
                    </div>
                </div>
            `).join('')}</div>`;
            body.innerHTML = html;

            body.querySelectorAll('[data-hsub]').forEach(b => {
                b.onclick = () => this.openHomeInventoryPanel('shop', b.dataset.hsub);
            });
            body.querySelectorAll('[data-addcart]').forEach(b => {
                b.onclick = () => {
                    const it = allFurn.find(x => x.id === b.dataset.addcart);
                    if (it) { this.shoppingCart.push(it); this.updateCartUI(); }
                };
            });
            body.querySelectorAll('[data-buynow]').forEach(b => {
                b.onclick = async () => {
                    const it = allFurn.find(x => x.id === b.dataset.buynow);
                    if (!it) return;
                    const res = await bridge.checkoutCart([it]);
                    if (!res?.success) return alert(res?.message || 'خطا در خرید');
                    this.app.playerState = res.state;
                    this.refreshBitBlox();
                    this.app.rebuildHomeView();
                    this.app.myRig.say(`${it.name} خریداری شد! 🎉`);
                    this.openHomeInventoryPanel(it.type === 'wall_style' || it.type === 'floor_style' ? 'style' : 'layout');
                };
            });
            this.updateCartUI();
        }

        document.getElementById('tab-home-layout').onclick = () => this.openHomeInventoryPanel('layout');
        document.getElementById('tab-home-style').onclick = () => this.openHomeInventoryPanel('style');
        document.getElementById('tab-home-shop').onclick = () => this.openHomeInventoryPanel('shop', 'all');
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
                            ? `<button class="coc-action-btn cyan btn-play-user-game" data-id="${g.id}" data-url="${g.url}" data-title="${g.title}">▶️ اجرا</button>`
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
            btn.onclick = async () => {
                const ownerId = homeData?.ownerId || this.app.playerState.userId;
                const playRes = await bridge.recordGamePlay(ownerId, btn.dataset.id);
                if (playRes?.hostReward > 0) {
                    const msg = `بازی در خانه ${playRes.ownerName}! +${playRes.hostReward} 💎 پاداش برای میزبان`;
                    this.app.myRig.say(msg);
                    if (this.app.queueBroadcast) this.app.queueBroadcast('clap', msg);
                }

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
        body.innerHTML = '<div style="text-align:center;padding:14px;color:#64748b;">⏳ در حال دریافت لیست همسایه‌ها...</div>';
        panel.classList.add('open');

        const plots = await bridge.getResidentialPlots();
        if (!plots || plots.length === 0) {
            body.innerHTML = '<div style="text-align:center;padding:18px;color:#64748b;font-size:0.85rem;">هنوز همسایه دیگری در شهر ثبت نشده است.</div>';
            return;
        }

        body.innerHTML = `<div class="item-grid">${plots.map(p => `
            <div class="shop-card">
                <h4>🏠 ${p.ownerName}</h4>
                <div style="font-size:0.72rem;color:${p.isOnline ? '#10b981' : '#64748b'};margin:4px 0;">
                    ${p.isOnline ? '🟢 آنلاین' : '⚪ آفلاین'} • 🎮 ${p.gamesCount || 0} بازی
                </div>
                <button class="coc-action-btn" data-h="${p.ownerId}" style="width:100%;margin-top:6px;">🚪 ورود به خانه</button>
            </div>
        `).join('')}</div>`;
        body.querySelectorAll('[data-h]').forEach(b => {
            b.onclick = () => { panel.classList.remove('open'); this.app.loadZone(`home_${b.dataset.h}`); };
        });
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