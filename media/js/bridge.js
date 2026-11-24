const BASE_URL = window.BLOXIA_BASE_URL || '/';

export const METAVERSE_CONFIG = {
    mockMode: false,
    syncIntervalMs: 2500,
    endpoints: {
        getCharacter:  `${BASE_URL}index.php?option=com_bloxia&task=metaverse.getCharacter&format=json`,
        saveCharacter: `${BASE_URL}index.php?option=com_bloxia&task=metaverse.saveCharacter&format=json`,
        hairSalonBuy:  `${BASE_URL}index.php?option=com_bloxia&task=metaverse.hairSalon&format=json`,
        submitGame:    `${BASE_URL}index.php?option=com_bloxia&task=metaverse.submitGame&format=json`,
        spendOrReward: `${BASE_URL}index.php?option=com_bloxia&task=metaverse.walletTx&format=json`,
        syncWorld:     `${BASE_URL}index.php?option=com_bloxia&task=metaverse.syncWorld&format=json`,
        buyCart:       `${BASE_URL}index.php?option=com_bloxia&task=metaverse.buyCart&format=json`,
        equipItem:     `${BASE_URL}index.php?option=com_bloxia&task=metaverse.equip&format=json`,
        saveHome:      `${BASE_URL}index.php?option=com_bloxia&task=metaverse.saveHome&format=json`,
        saveHomeStyle: `${BASE_URL}index.php?option=com_bloxia&task=metaverse.saveHomeStyle&format=json`,
        getCatalog:    `${BASE_URL}index.php?option=com_bloxia&task=metaverse.getCatalog&format=json`,
        getHomeData:   `${BASE_URL}index.php?option=com_bloxia&task=metaverse.getHome&format=json`,
        getNeighbors:  `${BASE_URL}index.php?option=com_bloxia&task=metaverse.getNeighbors&format=json`,
        recordGamePlay:`${BASE_URL}index.php?option=com_bloxia&task=metaverse.recordGamePlay&format=json`,
        sendChat:      `${BASE_URL}index.php?option=com_bloxia&task=metaverse.sendChat&format=json`,
        pvpCreate:     `${BASE_URL}index.php?option=com_bloxia&task=metaverse.pvpCreate&format=json`,
        pvpRespond:    `${BASE_URL}index.php?option=com_bloxia&task=metaverse.pvpRespond&format=json`,
        pvpMove:       `${BASE_URL}index.php?option=com_bloxia&task=metaverse.pvpMove&format=json`,
        pvpStatus:     `${BASE_URL}index.php?option=com_bloxia&task=metaverse.pvpStatus&format=json`
    },
    csrfToken: window.BLOXIA_CSRF_TOKEN || (window.Joomla ? Joomla.getOptions('csrf.token') : '')
};

export const SAFE_CHAT_PHRASES = [
    { id: 'greet_how', text: 'سلام خوبی؟ 😊', reply: 'خوبم ممنون :) تو چطوری؟ 🌟' },
    { id: 'pvp_invite',text: 'پایه‌ای سنگ کاغذ قیچی یا منچ بازی کنیم؟ 🎲', reply: 'آره حتماً! بزن بریم بازی 🎮' },
    { id: 'invite_tv', text: 'بیا خونه من بازی جدیدمو روی تلویزیون ببین! 📺', reply: 'حتماً! الان میام خونه‌ت بازی کنیم 🎮' },
    { id: 'go_park',   text: 'بریم پارک و شهربازی دور بزنیم؟ 🎡', reply: 'آره عالیه! بریم 🎉' },
    { id: 'nice_hair', text: 'مدل مو و لباست خیلی قشنگ شده! ✨', reply: 'مرسی دوست خوبم، نظر لطفته 💐' },
    { id: 'bye',       text: 'من دیگه باید برم، خداحافظ! 👋', reply: 'خداحافظ، بازم بیا بازی کنیم 👋' }
];

export const CINEMA_MOVIES = [
    { id: 'mov_space', title: '🚀 سفر شگفت‌انگیز به منظومه شمسی', price: 15, duration: '۱۲ دقیقه', color: '#38bdf8' },
    { id: 'mov_code',  title: '💻 رازهای دنیای الگوریتم و هوش مصنوعی', price: 20, duration: '۱۵ دقیقه', color: '#a855f7' },
    { id: 'mov_nature',title: '🌿 نگهبانان جنگل و طبیعت', price: 10, duration: '۱۰ دقیقه', color: '#10b981' }
];

export const GAMENET_GAMES = [
    { id: 'gn_math_dash', title: '⚡ سرعت عمل ریاضی فضایی', price: 10, rewardMax: 18 },
    { id: 'gn_memory',    title: '🧠 حافظه تصویری قهرمانان', price: 10, rewardMax: 15 },
    { id: 'gn_star_catch',title: '⭐ شکارچی ستاره‌های طلایی', price: 15, rewardMax: 25 }
];

export const CATALOG = {
    shop_clothes: [
        { id: 'hat_crown', name: '👑 تاج طلایی سلطنتی', price: 80, type: 'hat', color: 0xfbbf24 },
        { id: 'hat_cap', name: '🧢 کلاه اسپرت آبی', price: 40, type: 'hat', color: 0x3b82f6 },
        { id: 'outfit_pink', name: '👚 لباس مجلسی صورتی', price: 60, type: 'shirt', color: 0xec4899 },
        { id: 'outfit_purple', name: '👕 لباس کهکشانی بنفش', price: 70, type: 'shirt', color: 0x8b5cf6 },
        { id: 'shoes_red', name: '👟 کتانی اسپرت قرمز', price: 45, type: 'shoes', color: 0xef4444 },
        { id: 'shoes_gold', name: '👞 کفش طلایی درخشان', price: 65, type: 'shoes', color: 0xf59e0b }
    ],
    shop_accessories: [
        { id: 'prop_sword', name: '⚔️ شمشیر شوالیه', price: 90, type: 'handProp', color: 0x38bdf8 },
        { id: 'prop_axe', name: '🪓 تبر چوب‌بری', price: 85, type: 'handProp', color: 0x94a3b8 },
        { id: 'prop_flower', name: '💐 دسته گل رنگارنگ', price: 35, type: 'handProp', color: 0xf43f5e },
        { id: 'prop_phone', name: '📱 موبایل هوشمند', price: 65, type: 'handProp', color: 0x1e293b }
    ],
    shop_furniture: [
        // ۱. دیوار و کاغذ دیواری
        { id: 'wall_paint_classic', name: '🎨 رنگ دیوار کلاسیک با قرنیز', price: 40, type: 'wall_style', color: 0x94a3b8 },
        { id: 'wall_wp_striped',    name: '📜 کاغذ دیواری راه‌راه مدرن',   price: 55, type: 'wall_style', color: 0xcbd5e1 },
        { id: 'wall_wp_floral',     name: '🌸 کاغذ دیواری گلدار کلاسیک',  price: 65, type: 'wall_style', color: 0xfce7f3 },
        { id: 'wall_wp_brick',      name: '🧱 دیوارپوش آجری و هندسی',      price: 75, type: 'wall_style', color: 0xe2e8f0 },
        // ۲. کفپوش پارکت و سرامیک
        { id: 'floor_parquet_oak',     name: '🪵 پارکت بلوط روشن',        price: 50, type: 'floor_style', color: 0xd8b48b },
        { id: 'floor_parquet_walnut',  name: '🪵 پارکت گردویی تیره',      price: 60, type: 'floor_style', color: 0x78350f },
        { id: 'floor_parquet_white',   name: '🪵 پارکت سفید اسکاندیناوی',  price: 65, type: 'floor_style', color: 0xf1f5f9 },
        { id: 'floor_ceramic_marble',  name: '⬜ سرامیک مرمر سفید طلایی',   price: 75, type: 'floor_style', color: 0xf8fafc },
        { id: 'floor_ceramic_checker', name: '🏁 سرامیک شطرنجی کلاسیک',    price: 65, type: 'floor_style', color: 0xcbd5e1 },
        { id: 'floor_ceramic_hex',     name: '🔷 سرامیک شش‌ضلعی فیروزه‌ای', price: 70, type: 'floor_style', color: 0x99f6e4 },
        // ۳. قاب عکس
        { id: 'furn_frame_nature', name: '🖼️ قاب عکس منظره طبیعت', price: 35, type: 'frame', color: 0x1e293b },
        { id: 'furn_frame_art',    name: '🎨 قاب عکس نقاشی مدرن',  price: 45, type: 'frame', color: 0x78350f },
        { id: 'furn_frame_city',   name: '🌃 قاب عکس شب‌های شهر',   price: 50, type: 'frame', color: 0x0f172a },
        // ۴. لوازم آشپزخانه
        { id: 'furn_fridge',         name: '🧊 یخچال دوقلو مدرن',        price: 110, type: 'kitchen', color: 0xe2e8f0 },
        { id: 'furn_stove',          name: '🍳 اجاق گاز و فر آشپزخانه',   price: 95,  type: 'kitchen', color: 0x334155 },
        { id: 'furn_kitchen_island', name: '☕ جزیره و قهوه‌ساز آشپزخانه', price: 85,  type: 'kitchen', color: 0xf8fafc },
        // ۵. نورپردازی
        { id: 'furn_lamp',        name: '💡 آباژور ایستاده طلایی', price: 45, type: 'lighting', color: 0xf59e0b },
        { id: 'furn_table_lamp',  name: '🪔 آباژور رومیزی کلاسیک', price: 40, type: 'lighting', color: 0xfbbf24 },
        { id: 'furn_wall_sconce', name: '🔦 چراغ دیواری مدرن',     price: 50, type: 'lighting', color: 0xfef08a },
        // ۶. مبلمان و دکوراسیون
        { id: 'furn_sofa',     name: '🛋️ مبل راحتی سه‌نفره',     price: 75,  type: 'decor', color: 0xef4444 },
        { id: 'furn_armchair', name: '🪑 مبل تک‌نفره مدرن',       price: 55,  type: 'decor', color: 0xf1f5f9 },
        { id: 'furn_table',    name: '🪑 میز پذیرایی و عسلی',     price: 65,  type: 'decor', color: 0xb45309 },
        { id: 'furn_statue',   name: '🗿 مجسمه دکوراتیو هنری',    price: 70,  type: 'decor', color: 0xfbbf24 },
        { id: 'furn_rug',      name: '🧶 فرش طرح‌دار نشیمن',      price: 50,  type: 'decor', color: 0xeab308 },
        { id: 'furn_plant',    name: '🪴 گلدان برگ‌انجیری',       price: 30,  type: 'decor', color: 0x16a34a },
        { id: 'furn_radiator', name: '🔥 شوفاژ دکوراتیو رنگی',    price: 40,  type: 'decor', color: 0xef4444 },
        { id: 'furn_tv',       name: '📺 تلویزیون هوشمند اضافه',   price: 110, type: 'decor', color: 0x1e293b },
        { id: 'furn_arcade',   name: '🕹️ میز بازی آرکید',         price: 95,  type: 'decor', color: 0x10b981 },
        // ۷. تخت خواب
        { id: 'furn_bed_single', name: '🛏️ تخت تک‌نفره مدرن',  price: 90,  type: 'bedroom', color: 0x6366f1 },
        { id: 'furn_bed_double', name: '🛏️ تخت دونفره لوکس',   price: 130, type: 'bedroom', color: 0xec4899 },
        { id: 'furn_bed_bunk',   name: '🛏️ تخت دوطبقه فانتزی', price: 115, type: 'bedroom', color: 0x0ea5e9 }
    ]
};

export const ALL_ITEMS_FLAT = [
    ...CATALOG.shop_clothes,
    ...CATALOG.shop_accessories,
    ...CATALOG.shop_furniture
];

class MetaverseBridge {
    constructor(config) {
        this.config = config;
        this.mockCitizens = [
            {
                id: 202, name: 'سارا (کلاس چهارم)', zone: 'plaza',
                x: -6, z: 7, targetX: -6, targetZ: 7,
                houseColor: 0xf9a8d4, roofColor: 0xdb2777,
                appearance: { skinColor: 0xf1c27d, hairStyle: 'long', hairColor: 0x271c19, shirtColor: 0xec4899, pantsColor: 0x334155, shoesColor: 0xfef08a, hat: 'hat_crown', handProp: 'prop_flower' },
                homeLayout: [], myGames: [], emote: null, chat: ''
            }
        ];
    }

    async postJson(url, bodyObj) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
            body: JSON.stringify(bodyObj)
        });
        return await res.json();
    }

    async getCharacter() {
        const res = await fetch(this.config.endpoints.getCharacter);
        return await res.json();
    }

    async saveCharacterAppearance(newAppearance, newName) {
        return await this.postJson(this.config.endpoints.saveCharacter, { appearance: newAppearance, name: newName });
    }

    async applyHairSalon(hairStyle, hairColor, cost) {
        return await this.postJson(this.config.endpoints.hairSalonBuy, { hairStyle, hairColor, cost });
    }

    async submitUserGame(title, tier, fileObj) {
        const formData = new FormData();
        formData.append('title', title);
        formData.append('tier', tier);
        formData.append('game_file', fileObj);
        const res = await fetch(this.config.endpoints.submitGame, {
            method: 'POST',
            headers: { 'X-CSRF-Token': this.config.csrfToken },
            body: formData
        });
        return await res.json();
    }

    async walletTransaction(deltaAmount, reason) {
        return await this.postJson(this.config.endpoints.spendOrReward, { delta: deltaAmount, reason });
    }

    async getResidentialPlots() {
        try {
            const res = await fetch(this.config.endpoints.getNeighbors);
            if (!res.ok) return [];
            const data = await res.json();
            return Array.isArray(data) ? data : [];
        } catch (e) {
            return [];
        }
    }

    async recordGamePlay(ownerId, gameId) {
        try {
            return await this.postJson(this.config.endpoints.recordGamePlay, { owner_id: ownerId, game_id: gameId });
        } catch (e) {
            return { success: false };
        }
    }

    async getHomeData(ownerId) {
        const res = await fetch(`${this.config.endpoints.getHomeData}&owner_id=${ownerId}`);
        return await res.json();
    }

    async checkoutCart(cartItems) {
        const itemIds = cartItems.map(it => it.id);
        return await this.postJson(this.config.endpoints.buyCart, { items: itemIds });
    }

    async toggleWardrobeItem(itemDef) {
        return await this.postJson(this.config.endpoints.equipItem, { item_id: itemDef.id });
    }

    async updateHomeAndInventory(newLayout, newInventory) {
        return await this.postJson(this.config.endpoints.saveHome, { layout: newLayout, inventory: newInventory });
    }

    async saveHomeStyle(homeStyle) {
        return await this.postJson(this.config.endpoints.saveHomeStyle, homeStyle);
    }

    async loadCatalogFromServer() {
        try {
            const res = await fetch(this.config.endpoints.getCatalog);
            if (!res.ok) return;
            const data = await res.json();
            if (Array.isArray(data.shop_clothes)) CATALOG.shop_clothes = data.shop_clothes;
            if (Array.isArray(data.shop_accessories)) CATALOG.shop_accessories = data.shop_accessories;
            if (Array.isArray(data.shop_furniture)) CATALOG.shop_furniture = data.shop_furniture;
            ALL_ITEMS_FLAT.length = 0;
            ALL_ITEMS_FLAT.push(...CATALOG.shop_clothes, ...CATALOG.shop_accessories, ...CATALOG.shop_furniture);
        } catch (e) {
        }
    }

    async sendChat(zone, targetId, message) {
        try {
            return await this.postJson(this.config.endpoints.sendChat, { zone, target_id: Number(targetId) || 0, message });
        } catch (e) {
            return { success: false };
        }
    }

    async pvpCreate(opponentId, gameType) {
        return await this.postJson(this.config.endpoints.pvpCreate, { opponent_id: opponentId, game_type: gameType });
    }

    async pvpRespond(matchId, action) {
        return await this.postJson(this.config.endpoints.pvpRespond, { match_id: matchId, action });
    }

    async pvpMove(matchId, move) {
        return await this.postJson(this.config.endpoints.pvpMove, { match_id: matchId, move });
    }

    async pvpStatus(matchId) {
        try {
            const res = await fetch(`${this.config.endpoints.pvpStatus}&match_id=${matchId}`);
            return await res.json();
        } catch (e) {
            return { success: false };
        }
    }

    async syncWorld(payload) {
        try {
            const res = await fetch(this.config.endpoints.syncWorld, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                body: JSON.stringify(payload)
            });
            if (!res.ok) return { players: [], chats: [], pvp: null };
            const data = await res.json();
            if (Array.isArray(data)) {
                return { players: data, chats: [], pvp: null };
            }
            return {
                players: Array.isArray(data.players) ? data.players : [],
                chats: Array.isArray(data.chats) ? data.chats : [],
                pvp: data.pvp || null
            };
        } catch (e) {
            return { players: [], chats: [], pvp: null };
        }
    }
}

export const bridge = new MetaverseBridge(METAVERSE_CONFIG);