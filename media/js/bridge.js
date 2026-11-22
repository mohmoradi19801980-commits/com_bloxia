export const METAVERSE_CONFIG = {
    mockMode: false,
    syncIntervalMs: 2500,
    endpoints: {
        getCharacter:  '/index.php?option=com_bloxia&task=metaverse.getCharacter&format=json',
        saveCharacter: '/index.php?option=com_bloxia&task=metaverse.saveCharacter&format=json',
        hairSalonBuy:  '/index.php?option=com_bloxia&task=metaverse.hairSalon&format=json',
        submitGame:    '/index.php?option=com_bloxia&task=metaverse.submitGame&format=json',
        spendOrReward: '/index.php?option=com_bloxia&task=metaverse.walletTx&format=json',
        syncWorld:     '/index.php?option=com_bloxia&task=metaverse.syncWorld&format=json',
        buyCart:       '/index.php?option=com_bloxia&task=metaverse.buyCart&format=json',
        equipItem:     '/index.php?option=com_bloxia&task=metaverse.equip&format=json',
        saveHome:      '/index.php?option=com_bloxia&task=metaverse.saveHome&format=json',
        getHomeData:   '/index.php?option=com_bloxia&task=metaverse.getHome&format=json'
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
        { id: 'furn_sofa', name: '🛋️ مبل راحتی سه‌نفره', price: 75, type: 'furniture', color: 0xef4444 },
        { id: 'furn_tv', name: '📺 تلویزیون تی‌وی روم', price: 110, type: 'furniture', color: 0x1e293b },
        { id: 'furn_table', name: '🪑 میز ناهارخوری آشپزخانه', price: 65, type: 'furniture', color: 0xb45309 },
        { id: 'furn_arcade', name: '🕹️ میز بازی اتاق بازی', price: 95, type: 'furniture', color: 0x10b981 },
        { id: 'furn_lamp', name: '💡 آباژور ایستاده طلایی', price: 45, type: 'furniture', color: 0xf59e0b },
        { id: 'furn_plant', name: '🪴 گلدان برگ‌انجیری', price: 30, type: 'furniture', color: 0x16a34a }
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
        // در فاز ۳ این بخش از دیتابیس همسایه‌های واقعی خوانده می‌شود
        return [];
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

    async syncWorld(payload) {
        try {
            const res = await fetch(this.config.endpoints.syncWorld, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                body: JSON.stringify(payload)
            });
            if (!res.ok) return [];
            const data = await res.json();
            return Array.isArray(data) ? data : [];
        } catch (e) {
            return [];
        }
    }
}

export const bridge = new MetaverseBridge(METAVERSE_CONFIG);