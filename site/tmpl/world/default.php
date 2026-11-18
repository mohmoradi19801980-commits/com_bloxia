<?php
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;

$baseUri = Uri::root();
?>

<!-- Import map for Local Three.js -->
<script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js"
        }
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;800;900&display=swap');

    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Vazirmatn', Tahoma, sans-serif; user-select: none; }
    body, html { width: 100%; height: 100%; overflow: hidden; background: #0f172a; }
    #canvas-container { width: 100%; height: 100%; position: relative; }
    #fade-overlay { position: fixed; inset: 0; background: #0f172a; opacity: 0; pointer-events: none; transition: opacity 0.4s ease; z-index: 100; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3rem; font-weight: bold; }
    #fade-overlay.active { opacity: 1; pointer-events: all; }
    .top-hud { position: fixed; top: 12px; right: 12px; left: 12px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px; z-index: 20; pointer-events: none; }
    .hud-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 8px 14px; border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); display: flex; align-items: center; flex-wrap: wrap; gap: 8px; pointer-events: auto; border: 2px solid rgba(255, 255, 255, 0.75); }
    .zone-badge { background: #4f46e5; color: white; padding: 4px 10px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; }
    .bitblox-badge { background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #ffffff; padding: 4px 12px; border-radius: 10px; font-weight: 800; font-size: 0.86rem; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35); }
    .btn-action { background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: transform 0.15s, background 0.2s; font-size: 0.8rem; }
    .btn-action:hover { background: #059669; transform: translateY(-2px); }
    .btn-secondary { background: #6366f1; } .btn-secondary:hover { background: #4f46e5; }
    .btn-amber { background: #f59e0b; } .btn-amber:hover { background: #d97706; }
    .btn-dark { background: #334155; } .btn-dark:hover { background: #1e293b; }
    .btn-pink { background: #ec4899; } .btn-pink:hover { background: #db2777; }
    .btn-danger { background: #ef4444; } .btn-danger:hover { background: #dc2626; }
    .btn-cyan { background: #0284c7; } .btn-cyan:hover { background: #0369a1; }
    .weather-select { padding: 5px 8px; border-radius: 9px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.78rem; font-weight: 600; color: #1e293b; cursor: pointer; }
    #portal-prompt { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) scale(0.9); background: linear-gradient(135deg, #f59e0b, #ea580c); color: white; padding: 11px 24px; border-radius: 999px; font-weight: 800; font-size: 0.95rem; box-shadow: 0 10px 25px rgba(234, 88, 12, 0.45); cursor: pointer; opacity: 0; pointer-events: none; transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); z-index: 30; border: 3px solid #fff; }
    #portal-prompt.visible { opacity: 1; pointer-events: auto; transform: translateX(-50%) scale(1); }
    .bottom-bar { position: fixed; bottom: 14px; left: 50%; transform: translateX(-50%); background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); padding: 8px 14px; border-radius: 22px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25); display: flex; gap: 8px; z-index: 25; max-width: 96vw; overflow-x: auto; }
    .emote-btn { background: #f1f5f9; border: 2px solid #e2e8f0; border-radius: 14px; padding: 6px 12px; font-size: 0.82rem; font-weight: 600; color: #1e293b; cursor: pointer; white-space: nowrap; transition: all 0.15s; }
    .emote-btn:hover { background: #e0e7ff; border-color: #6366f1; transform: translateY(-3px); }
    .floating-label { position: fixed; transform: translate(-50%, -100%); pointer-events: none; text-align: center; z-index: 10; }
    .chat-bubble { background: #ffffff; color: #0f172a; padding: 6px 14px; border-radius: 16px; font-size: 0.82rem; font-weight: 700; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18); border: 2px solid #6366f1; margin-bottom: 6px; position: relative; animation: popIn 0.25s cubic-bezier(0.18, 0.89, 0.32, 1.28); white-space: nowrap; }
    .chat-bubble::after { content: ''; position: absolute; bottom: -7px; left: 50%; transform: translateX(-50%); border-width: 7px 7px 0; border-style: solid; border-color: #6366f1 transparent transparent; }
    .player-tag { background: rgba(15, 23, 42, 0.82); color: #fff; padding: 2px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 600; display: inline-block; }
    .modal-panel { position: fixed; top: 74px; left: 16px; width: 410px; max-width: calc(100vw - 32px); background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border-radius: 20px; padding: 16px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35); z-index: 40; display: none; border: 2px solid #e2e8f0; max-height: calc(100vh - 165px); overflow-y: auto; }
    .modal-panel.open { display: block; animation: popIn 0.2s ease; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
    .item-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .shop-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 9px; text-align: center; }
    .shop-card h4 { font-size: 0.82rem; margin-bottom: 3px; }
    .shop-card .price { font-size: 0.76rem; color: #0284c7; font-weight: 800; margin-bottom: 6px; }
    .chat-history { background: #f1f5f9; border-radius: 12px; padding: 10px; height: 155px; overflow-y: auto; margin-bottom: 10px; display: flex; flex-direction: column; gap: 6px; border: 1px solid #cbd5e1; }
    .chat-msg { background: #fff; padding: 6px 10px; border-radius: 10px; font-size: 0.8rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .chat-msg.mine { background: #e0e7ff; border-right: 3px solid #4f46e5; }
    .chat-msg.reply { background: #dcfce7; border-right: 3px solid #10b981; }
    .minigame-stage { background: #0f172a; color: #fff; border-radius: 14px; padding: 14px; text-align: center; margin-top: 8px; }
    .cas-group { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 9px; margin-bottom: 8px; }
    .cas-group-title { font-size: 0.8rem; font-weight: 700; color: #1e293b; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center; }
    .swatch-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .color-swatch { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1.5px #cbd5e1; cursor: pointer; }
    .color-swatch.selected { box-shadow: 0 0 0 2.5px #4f46e5; transform: scale(1.12); }
    .cart-box { margin-top: 12px; background: #f1f5f9; padding: 10px; border-radius: 12px; border: 2px dashed #cbd5e1; }
    .cart-items-list { max-height: 90px; overflow-y: auto; font-size: 0.78rem; margin: 6px 0; }
    .cart-row { display: flex; justify-content: space-between; align-items: center; padding: 3px 0; border-bottom: 1px solid #e2e8f0; }
    #home-edit-banner { position: fixed; top: 74px; left: 50%; transform: translateX(-50%); background: #1e293b; color: #fff; padding: 8px 16px; border-radius: 12px; font-size: 0.82rem; z-index: 35; display: none; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.3); }
    @keyframes popIn { from { opacity: 0; transform: scale(0.85); } to { opacity: 1; transform: scale(1); } }
</style>

<!-- عناصر UI بازی -->
<div id="canvas-container"></div>
<div id="labels-layer"></div>
<div id="fade-overlay">در حال جابه‌جایی...</div>

<!-- نوار وضعیت بالا -->
<div class="top-hud">
    <div class="hud-card">
        <strong id="player-name-display">کاراکتر من</strong>
        <span class="zone-badge" id="zone-title-display">🏙️ میدان اصلی و محله</span>
        <span class="bitblox-badge">💎 <span id="bitblox-display">500</span> BitBlox</span>

        <select id="time-select" class="weather-select" title="زمان شبانه‌روز">
            <option value="day">☀️ روز روشن</option>
            <option value="sunset">🌅 غروب آفتاب</option>
            <option value="night">🌙 شب و چراغ‌ها</option>
        </select>
        <select id="weather-select" class="weather-select" title="آب‌وهوا و فصل">
            <option value="sunny">🌤️ آفتابی (بهار)</option>
            <option value="cloudy">☁️ ابری (پاییز)</option>
            <option value="rain">🌧️ بارانی</option>
            <option value="snow">❄️ برفی (زمستان)</option>
        </select>
    </div>

    <div class="hud-card">
        <button class="btn-action btn-cyan" id="btn-open-chatroom">💬 چت‌روم امن</button>
        <button class="btn-action btn-dark" id="btn-open-cart">🛒 سبد (<span id="cart-count">0</span>)</button>
        <button class="btn-action btn-secondary" id="btn-neighbors">🏘️ همسایه‌ها</button>
        <button class="btn-action btn-amber" id="btn-go-my-home">🏠 خانه من</button>
        <button class="btn-action" id="btn-go-plaza" style="display:none;">🏙️ میدان شهر</button>
        <button class="btn-action btn-pink" id="btn-open-cas" style="display:none;">👤 ساخت اولیه کاراکتر</button>
        <button class="btn-action btn-secondary" id="btn-open-wardrobe" style="display:none;">👗 کمد لباس</button>
        <button class="btn-action btn-amber" id="btn-toggle-inventory" style="display:none;">🛋️ چیدمان خانه</button>
    </div>
</div>

<button id="portal-prompt">🚪 ورود به ساختمان</button>

<div id="home-edit-banner">
    <span>🛋️ روی کف اتاق کلیک کن تا وسیله قرار بگیرد</span>
    <button class="btn-action" id="btn-rotate-furniture" style="padding:4px 10px;">🔄 چرخش</button>
    <button class="btn-action btn-danger" id="btn-cancel-place" style="padding:4px 10px;">✖ لغو</button>
</div>

<!-- پنل کناری چندمنظوره -->
<div class="modal-panel" id="side-panel">
    <div class="modal-header">
        <h3 id="panel-title">پنل</h3>
        <button onclick="document.getElementById('side-panel').classList.remove('open')" style="border:none;background:none;font-size:1.2rem;cursor:pointer;">✖</button>
    </div>
    <div id="panel-body"></div>

    <div class="cart-box" id="cart-section" style="display:none;">
        <div style="display:flex;justify-content:space-between;font-weight:800;font-size:0.84rem;">
            <span>🛒 سبد خرید شما</span>
            <span>جمع کل: <span id="cart-total" style="color:#0284c7;">0</span> 💎 BitBlox</span>
        </div>
        <div class="cart-items-list" id="cart-items-list">
            <div style="color:#64748b;text-align:center;padding:6px;">سبد خرید خالی است</div>
        </div>
        <button class="btn-action" id="btn-checkout-cart" style="width:100%;margin-top:4px;">💳 پرداخت با BitBlox</button>
    </div>
</div>

<!-- نوار پایین: ایموت‌های سریع -->
<div class="bottom-bar">
    <button class="emote-btn" data-emote="wave" data-text="سلام به همگی! 👋">👋 سلام!</button>
    <button class="emote-btn" data-emote="clap" data-text="آفرین! عالی بود 👏">👏 تشویق</button>
    <button class="emote-btn" data-emote="jump" data-text="هورا! چقدر عالی 🎉">🎉 خوشحالی</button>
    <button class="emote-btn" data-emote="prop_show" data-text="ببینید چی دستمه! ✨">✨ نمایش وسیله</button>
    <button class="emote-btn" data-emote="invite" data-text="بیایید بریم خونه من بازی کنیم! 🎮">🏠 دعوت به خانه</button>
</div>

<script type="module">
    import * as THREE from 'three';

    window.Joomla = window.Joomla || {};
    window.Joomla.getOptions = function(key) {
        if (key === 'csrf.token') {
            return '<?php echo Joomla\CMS\Session\Session::getFormToken(); ?>';
        }
        return '';
    };

        /**
         * ============================================================================
         * 1. METAVERSE BRIDGE (اقتصاد BitBlox + تلویزیون آپلود بازی + چت امن + جوملا)
         * ============================================================================
         */
        const METAVERSE_CONFIG = {
            mockMode: false,
            syncIntervalMs: 2500,
            endpoints: {
                getCharacter:   'index.php?option=com_YOURCOMPONENT&task=metaverse.getCharacter&format=json',
                saveInitialCas: 'index.php?option=com_YOURCOMPONENT&task=metaverse.saveInitialCas&format=json',
                hairSalonBuy:   'index.php?option=com_YOURCOMPONENT&task=metaverse.hairSalon&format=json',
                submitGame:     'index.php?option=com_YOURCOMPONENT&task=metaverse.submitGame&format=json',
                spendOrReward:  'index.php?option=com_YOURCOMPONENT&task=metaverse.walletTx&format=json',
                syncWorld:      'index.php?option=com_YOURCOMPONENT&task=metaverse.sync&format=json',
                buyCart:        'index.php?option=com_YOURCOMPONENT&task=metaverse.buyCart&format=json',
                equipItem:      'index.php?option=com_YOURCOMPONENT&task=metaverse.equip&format=json',
                saveHome:       'index.php?option=com_YOURCOMPONENT&task=metaverse.saveHome&format=json',
                getHomeData:    'index.php?option=com_YOURCOMPONENT&task=metaverse.getHome&format=json'
            },
            csrfToken: window.Joomla ? Joomla.getOptions('csrf.token') : 'dev_token'
        };

        const SAFE_CHAT_PHRASES = [
            { id: 'greet_how', text: 'سلام خوبی؟ 😊', reply: 'خوبم ممنون :) تو چطوری؟ 🌟' },
            { id: 'invite_tv', text: 'بیا خونه من بازی جدیدمو روی تلویزیون ببین! 📺', reply: 'حتماً! الان میام خونه‌ت بازی کنیم 🎮' },
            { id: 'go_park',   text: 'بریم شهربازی دارت و گردونه بازی کنیم؟ 🎡', reply: 'آره عالیه! بریم شهربازی 🎉' },
            { id: 'go_cinema', text: 'بریم سینما فیلم جدید رو ببینیم؟ 🎬', reply: 'منم پایه ام! بریم بلیت بگیریم 🍿' },
            { id: 'nice_hair', text: 'مدل مو و لباست خیلی قشنگ شده! ✨', reply: 'مرسی دوست خوبم، نظر لطفته 💐' },
            { id: 'bye',       text: 'من دیگه باید برم، خداحافظ! 👋', reply: 'خداحافظ، بازم بیا بازی کنیم 👋' }
        ];

        const CINEMA_MOVIES = [
            { id: 'mov_space', title: '🚀 سفر شگفت‌انگیز به منظومه شمسی', price: 15, duration: '۱۲ دقیقه', color: '#38bdf8' },
            { id: 'mov_code',  title: '💻 رازهای دنیای الگوریتم و هوش مصنوعی', price: 20, duration: '۱۵ دقیقه', color: '#a855f7' },
            { id: 'mov_nature',title: '🌿 نگهبانان جنگل و طبیعت', price: 10, duration: '۱۰ دقیقه', color: '#10b981' }
        ];

        const GAMENET_GAMES = [
            { id: 'gn_math_dash', title: '⚡ سرعت عمل ریاضی فضایی', price: 10, rewardMax: 18 },
            { id: 'gn_memory',    title: '🧠 حافظه تصویری قهرمانان', price: 10, rewardMax: 15 },
            { id: 'gn_star_catch',title: '⭐ شکارچی ستاره‌های طلایی', price: 15, rewardMax: 25 }
        ];

        const CATALOG = {
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
                { id: 'furn_tv', name: '📺 تلویزیون استودیو بازی', price: 110, type: 'furniture', color: 0x1e293b },
                { id: 'furn_table', name: '🪑 میز و صندلی ناهارخوری', price: 65, type: 'furniture', color: 0xb45309 },
                { id: 'furn_bed', name: '🛏️ تخت‌خواب دونفره', price: 120, type: 'furniture', color: 0x6366f1 },
                { id: 'furn_lamp', name: '💡 آباژور ایستاده طلایی', price: 45, type: 'furniture', color: 0xf59e0b },
                { id: 'furn_plant', name: '🪴 گلدان برگ‌انجیری', price: 30, type: 'furniture', color: 0x16a34a }
            ]
        };

        const ALL_ITEMS_FLAT = [
            ...CATALOG.shop_clothes,
            ...CATALOG.shop_accessories,
            ...CATALOG.shop_furniture
        ];

        class MetaverseBridge {
            constructor(config) {
                this.config = config;
                this.mockState = JSON.parse(localStorage.getItem('mv_mock_state_v7')) || {
                    userId: 101,
                    name: 'کاراکتر من',
                    bitblox: 500,
                    freeCasUsed: false,
                    appearance: {
                        skinColor: 0xffdbac,
                        hairStyle: 'short',
                        hairColor: 0x451a03,
                        shirtColor: 0x3b82f6,
                        pantsColor: 0x1e293b,
                        shoesColor: 0xf8fafc,
                        hat: null,
                        handProp: null
                    },
                    wardrobe: ['outfit_pink', 'prop_flower'],
                    furnitureInventory: ['furn_lamp'],
                    homeLayout: [
                        { uid: 'f_1', itemId: 'furn_tv', x: 0, z: -8.5, rotY: 0 },
                        { uid: 'f_2', itemId: 'furn_sofa', x: 0, z: -4.2, rotY: Math.PI },
                        { uid: 'f_3', itemId: 'furn_plant', x: -2.5, z: -8.2, rotY: 0 },
                        { uid: 'f_4', itemId: 'furn_table', x: -7.5, z: -3.5, rotY: 0 },
                        { uid: 'f_5', itemId: 'furn_bed', x: 7.5, z: -6.5, rotY: 0 }
                    ],
                    myGames: [
                        { id: 'g_1', title: 'ماجراجویی پسر برگی 🍃', tier: 'staged', reward: 20, status: 'approved', url: 'https://scratch.mit.edu' }
                    ]
                };

                this.mockCitizens = [
                    {
                        id: 202, name: 'سارا (کلاس چهارم)', zone: 'plaza',
                        x: -5, z: 6, targetX: -5, targetZ: 6,
                        houseColor: 0xf9a8d4, roofColor: 0xdb2777,
                        appearance: { skinColor: 0xf1c27d, hairStyle: 'long', hairColor: 0x271c19, shirtColor: 0xec4899, pantsColor: 0x334155, shoesColor: 0xfef08a, hat: 'hat_crown', handProp: 'prop_flower' },
                        homeLayout: [
                            { uid: 's_1', itemId: 'furn_tv', x: 0, z: -8.5, rotY: 0 },
                            { uid: 's_2', itemId: 'furn_sofa', x: 0, z: -4.5, rotY: Math.PI },
                            { uid: 's_3', itemId: 'furn_bed', x: 7.5, z: -6.5, rotY: 0 }
                        ],
                        myGames: [
                            { id: 'sg_1', title: 'پازل ریاضی سارا 🧩', tier: 'pro', reward: 100, status: 'approved' }
                        ],
                        emote: null, chat: ''
                    },
                    {
                        id: 203, name: 'نگار (کلاس پنجم)', zone: 'plaza',
                        x: 6, z: 4, targetX: 6, targetZ: 4,
                        houseColor: 0x6ee7b7, roofColor: 0x059669,
                        appearance: { skinColor: 0xffdbac, hairStyle: 'ponytail', hairColor: 0x78350f, shirtColor: 0x10b981, pantsColor: 0x1e293b, shoesColor: 0xef4444, hat: 'hat_cap', handProp: 'prop_sword' },
                        homeLayout: [
                            { uid: 'n_1', itemId: 'furn_table', x: -7, z: -4, rotY: 0 },
                            { uid: 'n_2', itemId: 'furn_plant', x: -2, z: -8, rotY: 0 }
                        ],
                        myGames: [],
                        emote: null, chat: ''
                    }
                ];
            }

            saveMock() { localStorage.setItem('mv_mock_state_v7', JSON.stringify(this.mockState)); }

            async getCharacter() {
                if (this.config.mockMode) return structuredClone(this.mockState);
                const res = await fetch(this.config.endpoints.getCharacter);
                return await res.json();
            }

            async saveInitialCharacter(newAppearance, newName) {
                if (this.config.mockMode) {
                    if (this.mockState.freeCasUsed) {
                        return { success: false, message: 'ویرایش رایگان اولیه انجام شده! برای تغییر مو به آرایشگاه و برای لباس به فروشگاه برو.' };
                    }
                    this.mockState.appearance = { ...this.mockState.appearance, ...newAppearance };
                    if (newName) this.mockState.name = newName;
                    this.mockState.freeCasUsed = true;
                    this.saveMock();
                    return { success: true, state: structuredClone(this.mockState) };
                }
                const res = await fetch(this.config.endpoints.saveInitialCas, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify({ appearance: newAppearance, name: newName })
                });
                return await res.json();
            }

            async applyHairSalon(hairStyle, hairColor, cost) {
                if (this.config.mockMode) {
                    if (this.mockState.bitblox < cost) return { success: false, message: 'موجودی BitBlox کافی نیست!' };
                    this.mockState.bitblox -= cost;
                    this.mockState.appearance.hairStyle = hairStyle;
                    this.mockState.appearance.hairColor = hairColor;
                    this.saveMock();
                    return { success: true, state: structuredClone(this.mockState) };
                }
                const res = await fetch(this.config.endpoints.hairSalonBuy, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify({ hairStyle, hairColor, cost })
                });
                return await res.json();
            }

            async submitUserGame(title, tier, url) {
                const rewardMap = { simple: 10, staged: 20, pro: 100 };
                const newGame = { id: 'g_' + Date.now(), title, tier, reward: rewardMap[tier] || 10, status: 'pending', url };
                if (this.config.mockMode) {
                    this.mockState.myGames.push(newGame);
                    this.saveMock();
                    return { success: true, state: structuredClone(this.mockState), game: newGame };
                }
                const res = await fetch(this.config.endpoints.submitGame, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify(newGame)
                });
                return await res.json();
            }

            async adminApproveMockGame(gameId) {
                const g = this.mockState.myGames.find(x => x.id === gameId);
                if (g && g.status === 'pending') {
                    g.status = 'approved';
                    this.mockState.bitblox += g.reward;
                    this.saveMock();
                }
                return structuredClone(this.mockState);
            }

            async walletTransaction(deltaAmount, reason) {
                if (this.config.mockMode) {
                    if (deltaAmount < 0 && this.mockState.bitblox < Math.abs(deltaAmount)) {
                        return { success: false, message: 'موجودی BitBlox کافی نیست!' };
                    }
                    this.mockState.bitblox += deltaAmount;
                    this.saveMock();
                    return { success: true, state: structuredClone(this.mockState) };
                }
                const res = await fetch(this.config.endpoints.spendOrReward, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify({ delta: deltaAmount, reason })
                });
                return await res.json();
            }

            async getResidentialPlots() {
                if (this.config.mockMode) {
                    const plots = [{ ownerId: this.mockState.userId, ownerName: this.mockState.name, color: 0x93c5fd, roofColor: 0x2563eb }];
                    this.mockCitizens.forEach(c => plots.push({ ownerId: c.id, ownerName: c.name, color: c.houseColor, roofColor: c.roofColor }));
                    return plots;
                }
                return [];
            }

            async getHomeData(ownerId) {
                if (this.config.mockMode) {
                    if (ownerId === this.mockState.userId) {
                        return { ownerId, ownerName: this.mockState.name, isMine: true, layout: structuredClone(this.mockState.homeLayout), games: structuredClone(this.mockState.myGames) };
                    }
                    const citizen = this.mockCitizens.find(c => c.id === ownerId);
                    return { ownerId, ownerName: citizen ? citizen.name : `شهروند #${ownerId}`, isMine: false, layout: citizen ? structuredClone(citizen.homeLayout) : [], games: citizen ? citizen.myGames : [] };
                }
                const res = await fetch(`${this.config.endpoints.getHomeData}&owner_id=${ownerId}`);
                return await res.json();
            }

            async checkoutCart(cartItems) {
                if (this.config.mockMode) {
                    const totalCost = cartItems.reduce((sum, it) => sum + it.price, 0);
                    if (this.mockState.bitblox < totalCost) return { success: false, message: `موجودی BitBlox کافی نیست! (موجودی: ${this.mockState.bitblox})` };
                    this.mockState.bitblox -= totalCost;
                    cartItems.forEach(item => {
                        if (['hat', 'shirt', 'shoes', 'handProp'].includes(item.type)) {
                            if (!this.mockState.wardrobe.includes(item.id)) this.mockState.wardrobe.push(item.id);
                            if (item.type === 'hat') this.mockState.appearance.hat = item.id;
                            if (item.type === 'shirt') this.mockState.appearance.shirtColor = item.color;
                            if (item.type === 'shoes') this.mockState.appearance.shoesColor = item.color;
                            if (item.type === 'handProp') this.mockState.appearance.handProp = item.id;
                        } else if (item.type === 'furniture') {
                            this.mockState.furnitureInventory.push(item.id);
                        }
                    });
                    this.saveMock();
                    return { success: true, state: structuredClone(this.mockState) };
                }
                const res = await fetch(this.config.endpoints.buyCart, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify({ items: cartItems.map(i => i.id) })
                });
                return await res.json();
            }

            async toggleWardrobeItem(itemDef) {
                if (this.config.mockMode) {
                    if (itemDef.type === 'hat') this.mockState.appearance.hat = (this.mockState.appearance.hat === itemDef.id) ? null : itemDef.id;
                    else if (itemDef.type === 'shirt') this.mockState.appearance.shirtColor = itemDef.color;
                    else if (itemDef.type === 'shoes') this.mockState.appearance.shoesColor = itemDef.color;
                    else if (itemDef.type === 'handProp') this.mockState.appearance.handProp = (this.mockState.appearance.handProp === itemDef.id) ? null : itemDef.id;
                    this.saveMock();
                    return structuredClone(this.mockState);
                }
                const res = await fetch(this.config.endpoints.equipItem, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify({ item_id: itemDef.id })
                });
                return await res.json();
            }

            async updateHomeAndInventory(newLayout, newInventory) {
                if (this.config.mockMode) {
                    this.mockState.homeLayout = newLayout;
                    if (newInventory) this.mockState.furnitureInventory = newInventory;
                    this.saveMock();
                    return structuredClone(this.mockState);
                }
                const res = await fetch(this.config.endpoints.saveHome, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify({ layout: newLayout, inventory: newInventory })
                });
                return await res.json();
            }

            async syncWorld(payload) {
                if (this.config.mockMode) {
                    this.mockCitizens.forEach(bot => {
                        if (bot.zone === 'plaza' && Math.random() < 0.3) {
                            bot.targetX = (Math.random() - 0.5) * 26;
                            bot.targetZ = (Math.random() - 0.5) * 24;
                        }
                        bot.x = bot.targetX;
                        bot.z = bot.targetZ;
                        if (payload.emote === 'wave' && Math.random() < 0.75) {
                            bot.emote = 'wave';
                            bot.chat = 'سلام دوست من! خوش اومدی 👋';
                        } else {
                            bot.emote = null;
                            bot.chat = '';
                        }
                    });
                    return this.mockCitizens.filter(c => c.zone === payload.zone);
                }
                const res = await fetch(this.config.endpoints.syncWorld, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken },
                    body: JSON.stringify(payload)
                });
                return await res.json();
            }
        }

        const bridge = new MetaverseBridge(METAVERSE_CONFIG);

        /**
         * ============================================================================
         * 2. سازنده مدل‌های سه‌بعدی (کاراکتر، مبلمان، گلدان تپل، لوازم دست)
         * ============================================================================
         */
        function buildHairMesh(style, hairMat) {
            const group = new THREE.Group();
            const top = new THREE.Mesh(new THREE.BoxGeometry(0.67, 0.24, 0.67), hairMat);
            top.position.y = 0.25;
            group.add(top);

            if (style === 'short') {
                const back = new THREE.Mesh(new THREE.BoxGeometry(0.66, 0.44, 0.16), hairMat);
                back.position.set(0, 0.06, -0.26);
                group.add(back);
            } else if (style === 'long') {
                const backLong = new THREE.Mesh(new THREE.BoxGeometry(0.68, 0.85, 0.18), hairMat);
                backLong.position.set(0, -0.15, -0.27);
                const sideL = new THREE.Mesh(new THREE.BoxGeometry(0.14, 0.68, 0.25), hairMat);
                sideL.position.set(-0.32, -0.08, 0.05);
                const sideR = sideL.clone();
                sideR.position.x = 0.32;
                group.add(backLong, sideL, sideR);
            } else if (style === 'ponytail') {
                const back = new THREE.Mesh(new THREE.BoxGeometry(0.66, 0.44, 0.16), hairMat);
                back.position.set(0, 0.06, -0.26);
                const tail = new THREE.Mesh(new THREE.CylinderGeometry(0.11, 0.16, 0.62, 10), hairMat);
                tail.position.set(0, 0.05, -0.45);
                tail.rotation.x = 0.35;
                group.add(back, tail);
            } else if (style === 'curly') {
                for (let i = 0; i < 10; i++) {
                    const puff = new THREE.Mesh(new THREE.SphereGeometry(0.18, 10, 10), hairMat);
                    const angle = (i / 10) * Math.PI * 2;
                    puff.position.set(Math.cos(angle) * 0.28, 0.26, Math.sin(angle) * 0.28);
                    group.add(puff);
                }
            }
            return group;
        }

        function buildRealisticFurniture(itemId, customColor) {
            const group = new THREE.Group();
            if (itemId === 'furn_sofa') {
                const mat = new THREE.MeshStandardMaterial({ color: customColor || 0xef4444, roughness: 0.75 });
                const seat = new THREE.Mesh(new THREE.BoxGeometry(2.5, 0.42, 0.95), mat);
                seat.position.y = 0.32;
                seat.castShadow = true;
                const back = new THREE.Mesh(new THREE.BoxGeometry(2.5, 0.65, 0.25), mat);
                back.position.set(0, 0.72, -0.35);
                const armL = new THREE.Mesh(new THREE.BoxGeometry(0.26, 0.55, 0.95), mat);
                armL.position.set(-1.15, 0.52, 0);
                const armR = armL.clone();
                armR.position.x = 1.15;
                const pillowMat = new THREE.MeshStandardMaterial({ color: 0xfef3c7 });
                const p1 = new THREE.Mesh(new THREE.BoxGeometry(0.45, 0.35, 0.15), pillowMat);
                p1.position.set(-0.65, 0.62, -0.15);
                const p2 = p1.clone();
                p2.position.x = 0.65;
                group.add(seat, back, armL, armR, p1, p2);
            } else if (itemId === 'furn_tv') {
                const stand = new THREE.Mesh(new THREE.BoxGeometry(2.4, 0.55, 0.55), new THREE.MeshStandardMaterial({ color: 0x78350f }));
                stand.position.y = 0.28;
                const frame = new THREE.Mesh(new THREE.BoxGeometry(2.2, 1.25, 0.08), new THREE.MeshStandardMaterial({ color: 0x0f172a }));
                frame.position.y = 1.35;
                const screen = new THREE.Mesh(
                    new THREE.PlaneGeometry(2.05, 1.1),
                    new THREE.MeshBasicMaterial({ map: createSignTexture('🎮 استودیو بازی (کلیک کنید)', '#0284c7', '#fde047') })
                );
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
            } else if (itemId === 'furn_bed') {
                const frameMat = new THREE.MeshStandardMaterial({ color: 0x78350f });
                const base = new THREE.Mesh(new THREE.BoxGeometry(2.1, 0.35, 2.5), frameMat);
                base.position.y = 0.22;
                const headboard = new THREE.Mesh(new THREE.BoxGeometry(2.1, 1.1, 0.16), frameMat);
                headboard.position.set(0, 0.55, -1.2);
                const mattress = new THREE.Mesh(new THREE.BoxGeometry(1.96, 0.25, 2.35), new THREE.MeshStandardMaterial({ color: 0xf8fafc }));
                mattress.position.y = 0.48;
                const blanket = new THREE.Mesh(new THREE.BoxGeometry(2.0, 0.27, 1.55), new THREE.MeshStandardMaterial({ color: 0x6366f1 }));
                blanket.position.set(0, 0.49, 0.38);
                group.add(base, headboard, mattress, blanket);
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

        function buildDoorstepPlant(scale = 1) {
            const group = new THREE.Group();
            const pot = new THREE.Mesh(new THREE.CylinderGeometry(0.36 * scale, 0.26 * scale, 0.48 * scale, 16), new THREE.MeshStandardMaterial({ color: 0xea580c, roughness: 0.6 }));
            pot.position.y = 0.24 * scale;
            const bush = new THREE.Mesh(new THREE.SphereGeometry(0.45 * scale, 14, 12), new THREE.MeshStandardMaterial({ color: 0x22c55e, roughness: 0.7 }));
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

        function buildHandPropMesh(propId) {
            const group = new THREE.Group();
            if (propId === 'prop_sword') {
                const hilt = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.28, 0.1), new THREE.MeshStandardMaterial({ color: 0x78350f }));
                const guard = new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.08, 0.14), new THREE.MeshStandardMaterial({ color: 0xfbbf24 }));
                guard.position.y = 0.15;
                const blade = new THREE.Mesh(new THREE.BoxGeometry(0.14, 0.95, 0.05), new THREE.MeshStandardMaterial({ color: 0x38bdf8, emissive: 0x0284c7, emissiveIntensity: 0.35 }));
                blade.position.y = 0.65;
                group.add(hilt, guard, blade);
                group.rotation.x = Math.PI / 2.3;
            } else if (propId === 'prop_axe') {
                const handle = new THREE.Mesh(new THREE.BoxGeometry(0.09, 1.05, 0.09), new THREE.MeshStandardMaterial({ color: 0x92400e }));
                handle.position.y = 0.35;
                const head = new THREE.Mesh(new THREE.BoxGeometry(0.45, 0.32, 0.1), new THREE.MeshStandardMaterial({ color: 0x94a3b8 }));
                head.position.set(0.14, 0.75, 0);
                group.add(handle, head);
                group.rotation.x = Math.PI / 2.3;
            } else if (propId === 'prop_flower') {
                const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.55, 8), new THREE.MeshStandardMaterial({ color: 0x16a34a }));
                stem.position.y = 0.2;
                const blossom = new THREE.Mesh(new THREE.DodecahedronGeometry(0.22), new THREE.MeshStandardMaterial({ color: 0xf43f5e }));
                blossom.position.y = 0.5;
                group.add(stem, blossom);
                group.rotation.x = Math.PI / 3;
            } else if (propId === 'prop_phone') {
                const body = new THREE.Mesh(new THREE.BoxGeometry(0.22, 0.38, 0.04), new THREE.MeshStandardMaterial({ color: 0x0f172a }));
                const screen = new THREE.Mesh(new THREE.PlaneGeometry(0.18, 0.32), new THREE.MeshBasicMaterial({ color: 0x38bdf8 }));
                screen.position.z = 0.022;
                group.add(body, screen);
                group.rotation.x = Math.PI / 3;
            }
            return group;
        }

        function createCharacterRig(name, appearance) {
            const root = new THREE.Group();
            const skinMat = new THREE.MeshStandardMaterial({ color: appearance.skinColor, roughness: 0.6 });
            const hairMat = new THREE.MeshStandardMaterial({ color: appearance.hairColor, roughness: 0.85 });
            const shirtMat = new THREE.MeshStandardMaterial({ color: appearance.shirtColor, roughness: 0.7 });
            const pantsMat = new THREE.MeshStandardMaterial({ color: appearance.pantsColor, roughness: 0.8 });
            const shoesMat = new THREE.MeshStandardMaterial({ color: appearance.shoesColor || 0xf8fafc, roughness: 0.5 });

            const torso = new THREE.Mesh(new THREE.BoxGeometry(0.75, 0.92, 0.42), shirtMat);
            torso.position.y = 1.28;
            torso.castShadow = true;
            root.add(torso);

            const headGroup = new THREE.Group();
            headGroup.position.y = 2.04;
            const head = new THREE.Mesh(new THREE.BoxGeometry(0.62, 0.62, 0.62), skinMat);
            head.castShadow = true;
            headGroup.add(head);

            const hairSlot = new THREE.Group();
            headGroup.add(hairSlot);

            const eyeMat = new THREE.MeshBasicMaterial({ color: 0x111111 });
            const leftEye = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.08, 0.05), eyeMat);
            leftEye.position.set(-0.15, 0.02, 0.31);
            const rightEye = leftEye.clone();
            rightEye.position.x = 0.15;
            headGroup.add(leftEye, rightEye);

            const hatSlot = new THREE.Group();
            hatSlot.position.y = 0.42;
            headGroup.add(hatSlot);
            root.add(headGroup);

            const leftArmPivot = new THREE.Group();
            leftArmPivot.position.set(-0.52, 1.66, 0);
            const leftSleeve = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.48, 0.24), shirtMat);
            leftSleeve.position.y = -0.2;
            const leftHand = new THREE.Mesh(new THREE.BoxGeometry(0.21, 0.3, 0.21), skinMat);
            leftHand.position.y = -0.56;
            leftArmPivot.add(leftSleeve, leftHand);
            root.add(leftArmPivot);

            const rightArmPivot = new THREE.Group();
            rightArmPivot.position.set(0.52, 1.66, 0);
            const rightSleeve = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.48, 0.24), shirtMat);
            rightSleeve.position.y = -0.2;
            const rightHand = new THREE.Mesh(new THREE.BoxGeometry(0.21, 0.3, 0.21), skinMat);
            rightHand.position.y = -0.56;
            rightArmPivot.add(rightSleeve, rightHand);

            const rightHandSlot = new THREE.Group();
            rightHandSlot.position.set(0, -0.68, 0.12);
            rightArmPivot.add(rightHandSlot);
            root.add(rightArmPivot);

            const leftLegPivot = new THREE.Group();
            leftLegPivot.position.set(-0.2, 0.82, 0);
            const leftLeg = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.62, 0.3), pantsMat);
            leftLeg.position.y = -0.31;
            const leftShoe = new THREE.Mesh(new THREE.BoxGeometry(0.31, 0.2, 0.44), shoesMat);
            leftShoe.position.set(0, -0.72, 0.06);
            leftLegPivot.add(leftLeg, leftShoe);
            root.add(leftLegPivot);

            const rightLegPivot = new THREE.Group();
            rightLegPivot.position.set(0.2, 0.82, 0);
            const rightLeg = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.62, 0.3), pantsMat);
            rightLeg.position.y = -0.31;
            const rightShoe = new THREE.Mesh(new THREE.BoxGeometry(0.31, 0.2, 0.44), shoesMat);
            rightShoe.position.set(0, -0.72, 0.06);
            rightLegPivot.add(rightLeg, rightShoe);
            root.add(rightLegPivot);

            const labelEl = document.createElement('div');
            labelEl.className = 'floating-label';
            labelEl.innerHTML = `<div class="chat-bubble" style="display:none;"></div><div class="player-tag">${name}</div>`;
            document.getElementById('labels-layer').appendChild(labelEl);

            const rig = {
                mesh: root,
                parts: { headGroup, leftArmPivot, rightArmPivot, leftLegPivot, rightLegPivot, torso, hatSlot, rightHandSlot, hairSlot },
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
                        const crown = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.22, 0.22, 6), new THREE.MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.6 }));
                        hatSlot.add(crown);
                    } else if (newApp.hat === 'hat_cap') {
                        const cap = new THREE.Mesh(new THREE.BoxGeometry(0.68, 0.18, 0.75), new THREE.MeshStandardMaterial({ color: 0x3b82f6 }));
                        cap.position.z = 0.05;
                        hatSlot.add(cap);
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
         * 3. تابلوهای با کیفیت HD (1024px) + استند نمادهای کنار در + خانه‌های پفکی بامزه
         * ============================================================================
         */
        let animatedLandmarks = [];

        // تابلوهای بسیار خوانا و باکیفیت بالا (1024x256) با سایه متن و حاشیه درخشان
        function createSignTexture(text, bgColor = '#0f172a', borderColor = '#facc15') {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 256;
            const ctx = canvas.getContext('2d');

            // پس‌زمینه گرادینت تیره برای بیشترین کنتراست با متن سفید
            const grad = ctx.createLinearGradient(0, 0, 0, 256);
            grad.addColorStop(0, bgColor);
            grad.addColorStop(1, '#090d16');
            ctx.fillStyle = grad;
            ctx.beginPath();
            ctx.roundRect(10, 10, 1004, 236, 36);
            ctx.fill();

            // کادر حاشیه
            ctx.strokeStyle = borderColor;
            ctx.lineWidth = 14;
            ctx.beginPath();
            ctx.roundRect(10, 10, 1004, 236, 36);
            ctx.stroke();

            // متن درشت و خوانا با سایه
            ctx.shadowColor = 'rgba(0, 0, 0, 0.85)';
            ctx.shadowBlur = 10;
            ctx.shadowOffsetY = 4;
            ctx.fillStyle = '#ffffff';
            ctx.font = '900 62px "Vazirmatn", Tahoma, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(text, 512, 132);

            const tex = new THREE.CanvasTexture(canvas);
            tex.anisotropy = 8;
            return tex;
        }

        // استند نماد سه‌بعدی چرخان کنار در ورودی هر فروشگاه/ساختمان تا کارکردش کاملاً مشخص باشد
        function createDoorstepSymbolTotem(kind, x, z, accentColor = 0xfbbf24) {
            const group = new THREE.Group();
            group.position.set(x, 0, z);

            // پایه استوانه‌ای شیک کنار در
            const pedestal = new THREE.Mesh(
                new THREE.CylinderGeometry(0.62, 0.72, 0.95, 20),
                new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.4 })
            );
            pedestal.position.y = 0.48;
            pedestal.castShadow = true;

            const ring = new THREE.Mesh(
                new THREE.TorusGeometry(0.64, 0.06, 10, 24),
                new THREE.MeshStandardMaterial({ color: accentColor, emissive: accentColor, emissiveIntensity: 0.6 })
            );
            ring.rotation.x = Math.PI / 2;
            ring.position.y = 0.94;
            group.add(pedestal, ring);

            // نماد سه‌بعدی چرخان بالای پایه
            const iconHolder = new THREE.Group();
            iconHolder.position.y = 1.85;

            if (kind === 'hair_salon') {
                // قیچی سه‌بعدی بزرگ + شانه کنار در آرایشگاه
                const b1 = new THREE.Mesh(new THREE.BoxGeometry(1.15, 0.12, 0.08), new THREE.MeshStandardMaterial({ color: 0xf8fafc, metalness: 0.8 }));
                b1.rotation.z = 0.55;
                const b2 = b1.clone();
                b2.rotation.z = -0.55;
                const h1 = new THREE.Mesh(new THREE.TorusGeometry(0.2, 0.05, 8, 16), new THREE.MeshStandardMaterial({ color: 0xe11d48 }));
                h1.position.set(-0.42, -0.35, 0);
                const h2 = h1.clone();
                h2.position.x = 0.42;
                iconHolder.add(b1, b2, h1, h2);

            } else if (kind === 'clothes') {
                // پیراهن و چوب‌لباسی سه‌بعدی کنار در فروشگاه لباس
                const shirtBody = new THREE.Mesh(new THREE.BoxGeometry(0.75, 0.85, 0.25), new THREE.MeshStandardMaterial({ color: 0xec4899 }));
                const sleeveL = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.3, 0.22), new THREE.MeshStandardMaterial({ color: 0xec4899 }));
                sleeveL.position.set(-0.48, 0.22, 0);
                sleeveL.rotation.z = 0.4;
                const sleeveR = sleeveL.clone();
                sleeveR.position.x = 0.48;
                sleeveR.rotation.z = -0.4;
                const hook = new THREE.Mesh(new THREE.TorusGeometry(0.16, 0.04, 8, 16, Math.PI), new THREE.MeshStandardMaterial({ color: 0xfbbf24 }));
                hook.position.y = 0.55;
                iconHolder.add(shirtBody, sleeveL, sleeveR, hook);

            } else if (kind === 'furniture') {
                // مبل مینیاتوری سه‌بعدی کنار در فروشگاه مبلمان
                const miniSofa = buildRealisticFurniture('furn_sofa', 0xf59e0b);
                miniSofa.scale.set(0.45, 0.45, 0.45);
                miniSofa.position.y = -0.25;
                iconHolder.add(miniSofa);

            } else if (kind === 'accessories') {
                // شمشیر آبی درخشان و سپر کنار در فروشگاه لوازم جانبی
                const shield = new THREE.Mesh(new THREE.CylinderGeometry(0.55, 0.55, 0.14, 16), new THREE.MeshStandardMaterial({ color: 0xfbbf24, metalness: 0.7 }));
                shield.rotation.x = Math.PI / 2;
                const sword = new THREE.Mesh(new THREE.BoxGeometry(0.14, 1.35, 0.08), new THREE.MeshStandardMaterial({ color: 0x38bdf8, emissive: 0x0284c7, emissiveIntensity: 0.6 }));
                sword.position.z = 0.12;
                sword.rotation.z = -0.45;
                iconHolder.add(shield, sword);

            } else if (kind === 'cinema') {
                // سطل پاپ‌کورن و حلقه فیلم کنار در سینما
                const bucket = new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.3, 0.75, 12), new THREE.MeshStandardMaterial({ color: 0xdc2626 }));
                const popcorn = new THREE.Mesh(new THREE.DodecahedronGeometry(0.42), new THREE.MeshStandardMaterial({ color: 0xfef08a }));
                popcorn.position.y = 0.45;
                iconHolder.add(bucket, popcorn);

            } else if (kind === 'gamenet') {
                // دسته بازی نئونی سه‌بعدی کنار در گیم‌نت
                const pad = new THREE.Mesh(new THREE.BoxGeometry(1.1, 0.55, 0.25), new THREE.MeshStandardMaterial({ color: 0x10b981, emissive: 0x059669, emissiveIntensity: 0.5 }));
                const btn = new THREE.Mesh(new THREE.SphereGeometry(0.12), new THREE.MeshStandardMaterial({ color: 0xfacc15 }));
                btn.position.set(0.3, 0.08, 0.15);
                iconHolder.add(pad, btn);

            } else if (kind === 'school') {
                // کتاب و کلاه فارغ‌التحصیلی کنار در مدرسه
                const book = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.18, 0.65), new THREE.MeshStandardMaterial({ color: 0x2563eb }));
                const capTop = new THREE.Mesh(new THREE.BoxGeometry(0.75, 0.08, 0.75), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
                capTop.position.y = 0.35;
                capTop.rotation.y = Math.PI / 4;
                iconHolder.add(book, capTop);

            } else if (kind === 'park') {
                // سیبل دارت رنگی کنار در شهربازی
                const target = new THREE.Mesh(new THREE.CylinderGeometry(0.6, 0.6, 0.15, 20), new THREE.MeshStandardMaterial({ color: 0xef4444 }));
                target.rotation.x = Math.PI / 2;
                const bullseye = new THREE.Mesh(new THREE.CylinderGeometry(0.25, 0.25, 0.18, 16), new THREE.MeshStandardMaterial({ color: 0xfacc15 }));
                bullseye.rotation.x = Math.PI / 2;
                iconHolder.add(target, bullseye);
            }

            group.add(iconHolder);
            animatedLandmarks.push({ mesh: iconHolder, type: 'spinAndBob', speed: 2.0, baseY: 1.85 });
            return group;
        }

        function registerBuildingColliderAndPortal(x, z, w, d, rotY, targetZone, promptLabel, padColor) {
            const isRotated90 = Math.abs(Math.sin(rotY)) > 0.7;
            const halfW = (isRotated90 ? d : w) / 2 + 0.5;
            const halfD = (isRotated90 ? w : d) / 2 + 0.5;
            obstacles.push({ minX: x - halfW, maxX: x + halfW, minZ: z - halfD, maxZ: z + halfD });

            if (targetZone) {
                const localDoorOffset = new THREE.Vector3(0, 0, d / 2 + 2.2);
                localDoorOffset.applyAxisAngle(new THREE.Vector3(0, 1, 0), rotY);
                createPortalPad(x + localDoorOffset.x, z + localDoorOffset.z, targetZone, promptLabel, padColor);
            }
        }

        // 1. مدرسه نسیم دانایی + نماد کتاب و کلاه فارغ‌التحصیلی کنار در
        function buildSchoolLandmark(x, z, rotY) {
            const w = 13, h = 7.5, d = 7;
            const g = new THREE.Group();
            g.position.set(x, 0, z);
            g.rotation.y = rotY;

            const steps = new THREE.Mesh(new THREE.BoxGeometry(6, 0.4, 2.2), new THREE.MeshStandardMaterial({ color: 0x94a3b8 }));
            steps.position.set(0, 0.2, d / 2 + 0.8);
            const mainBody = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshStandardMaterial({ color: 0xfef3c7, roughness: 0.6 }));
            mainBody.position.y = h / 2 + 0.3;
            mainBody.castShadow = true;
            const midBand = new THREE.Mesh(new THREE.BoxGeometry(w + 0.3, 0.55, d + 0.3), new THREE.MeshStandardMaterial({ color: 0x2563eb }));
            midBand.position.y = h * 0.52;
            g.add(steps, mainBody, midBand);

            [-2.4, -1.1, 1.1, 2.4].forEach(cx => {
                const col = new THREE.Mesh(new THREE.CylinderGeometry(0.24, 0.28, 6.8, 12), new THREE.MeshStandardMaterial({ color: 0xffffff }));
                col.position.set(cx, 3.7, d / 2 + 0.55);
                g.add(col);
            });

            const clockTower = new THREE.Mesh(new THREE.BoxGeometry(3.2, 3.2, 3.2), new THREE.MeshStandardMaterial({ color: 0xfde68a }));
            clockTower.position.set(0, h + 1.8, 0);
            const clockFace = new THREE.Mesh(new THREE.CylinderGeometry(1.05, 1.05, 0.15, 24), new THREE.MeshBasicMaterial({ color: 0xffffff }));
            clockFace.rotation.x = Math.PI / 2;
            clockFace.position.set(0, h + 2.0, 1.62);
            const towerDome = new THREE.Mesh(new THREE.SphereGeometry(1.6, 16, 12, 0, Math.PI * 2, 0, Math.PI / 2), new THREE.MeshStandardMaterial({ color: 0xf59e0b }));
            towerDome.position.set(0, h + 3.4, 0);
            g.add(clockTower, clockFace, towerDome);

            const door = new THREE.Mesh(new THREE.BoxGeometry(2.0, 2.7, 0.2), new THREE.MeshStandardMaterial({ color: 0x78350f }));
            door.position.set(0, 1.6, d / 2 + 0.05);
            const sign = new THREE.Mesh(new THREE.BoxGeometry(7.2, 1.35, 0.25), new THREE.MeshBasicMaterial({ map: createSignTexture('🎓 سالن اجتماعات مدرسه', '#1e3a8a', '#fbbf24') }));
            sign.position.set(0, 5.2, d / 2 + 0.75);
            g.add(door, sign);

            // استند نماد مدرسه کنار در
            g.add(createDoorstepSymbolTotem('school', 3.6, d / 2 + 1.1, 0x3b82f6));

            worldGroup.add(g);
            registerBuildingColliderAndPortal(x, z, w, d, rotY, 'school_hall', '🎓 ورود به سالن اجتماعات مدرسه', 0x2563eb);
        }

        // 2. سینمای شهر + فرش قرمز + سطل پاپ‌کورن کنار در
        function buildCinemaLandmark(x, z, rotY) {
            const w = 9.5, h = 6.5, d = 7;
            const g = new THREE.Group();
            g.position.set(x, 0, z);
            g.rotation.y = rotY;

            const body = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshStandardMaterial({ color: 0x1e1b4b, roughness: 0.5 }));
            body.position.y = h / 2;
            body.castShadow = true;
            g.add(body);

            const carpet = new THREE.Mesh(new THREE.BoxGeometry(2.4, 0.06, 3.2), new THREE.MeshStandardMaterial({ color: 0xdc2626 }));
            carpet.position.set(0, 0.03, d / 2 + 1.5);
            g.add(carpet);

            const reelGroup = new THREE.Group();
            reelGroup.position.set(0, h + 1.8, 0);
            const reelWheel = new THREE.Mesh(new THREE.CylinderGeometry(1.7, 1.7, 0.45, 24), new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.7 }));
            reelWheel.rotation.x = Math.PI / 2;
            reelGroup.add(reelWheel);
            g.add(reelGroup);
            animatedLandmarks.push({ mesh: reelGroup, type: 'spinZ', speed: 1.2 });

            const sign = new THREE.Mesh(new THREE.BoxGeometry(7.0, 1.35, 0.25), new THREE.MeshBasicMaterial({ map: createSignTexture('🎬 سینمای شهر (CINEMA)', '#7f1d1d', '#fde047') }));
            sign.position.set(0, 4.1, d / 2 + 0.35);
            g.add(sign);

            // استند پاپ‌کورن کنار در سینما
            g.add(createDoorstepSymbolTotem('cinema', 2.8, d / 2 + 1.1, 0xfacc15));

            worldGroup.add(g);
            registerBuildingColliderAndPortal(x, z, w, d, rotY, 'cinema', '🎬 ورود به سینمای شهر', 0x7c3aed);
        }

        // 3. گیم‌نت شهر + دسته بازی نئونی کنار در
        function buildGameNetLandmark(x, z, rotY) {
            const w = 9, h = 5.8, d = 6.5;
            const g = new THREE.Group();
            g.position.set(x, 0, z);
            g.rotation.y = rotY;

            const body = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.4 }));
            body.position.y = h / 2;
            g.add(body);

            const neonMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, emissive: 0x0ea5e9, emissiveIntensity: 1.5 });
            const topNeon = new THREE.Mesh(new THREE.BoxGeometry(w + 0.3, 0.22, d + 0.3), neonMat);
            topNeon.position.y = h;
            g.add(topNeon);

            const sign = new THREE.Mesh(new THREE.BoxGeometry(6.8, 1.35, 0.25), new THREE.MeshBasicMaterial({ map: createSignTexture('🕹️ گیم‌نت GAME NET', '#064e3b', '#34d399') }));
            sign.position.set(0, 3.8, d / 2 + 0.25);
            g.add(sign);

            // استند دسته بازی کنار در گیم‌نت
            g.add(createDoorstepSymbolTotem('gamenet', 2.7, d / 2 + 1.1, 0x10b981));

            worldGroup.add(g);
            registerBuildingColliderAndPortal(x, z, w, d, rotY, 'gamenet', '🕹️ ورود به گیم‌نت شهر', 0x059669);
        }

        // 4. شهربازی + چرخ‌وفلک چرخان + استند هدف دارت کنار در
        function buildAmusementParkLandmark(x, z, rotY) {
            const w = 9.5, h = 4.2, d = 7;
            const g = new THREE.Group();
            g.position.set(x, 0, z);
            g.rotation.y = rotY;

            const base = new THREE.Mesh(new THREE.CylinderGeometry(4.6, 4.8, h, 16), new THREE.MeshStandardMaterial({ color: 0xfef3c7 }));
            base.position.y = h / 2;
            const tentRoof = new THREE.Mesh(new THREE.ConeGeometry(5.2, 3.4, 16), new THREE.MeshStandardMaterial({ color: 0xea580c }));
            tentRoof.position.y = h + 1.7;
            g.add(base, tentRoof);

            const wheelGroup = new THREE.Group();
            wheelGroup.position.set(4.5, 6.0, -0.5);
            const rim = new THREE.Mesh(new THREE.TorusGeometry(3.4, 0.16, 10, 28), new THREE.MeshStandardMaterial({ color: 0xf59e0b, emissive: 0xd97706, emissiveIntensity: 0.4 }));
            wheelGroup.add(rim);
            const cabinColors = [0xef4444, 0x3b82f6, 0x10b981, 0xec4899, 0x8b5cf6, 0xfacc15];
            for (let i = 0; i < 6; i++) {
                const ang = (i / 6) * Math.PI * 2;
                const cabin = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.85, 0.85), new THREE.MeshStandardMaterial({ color: cabinColors[i] }));
                cabin.position.set(Math.cos(ang) * 3.4, Math.sin(ang) * 3.4, 0);
                wheelGroup.add(cabin);
            }
            g.add(wheelGroup);
            animatedLandmarks.push({ mesh: wheelGroup, type: 'spinZ', speed: 0.55 });

            const sign = new THREE.Mesh(new THREE.BoxGeometry(6.6, 1.3, 0.25), new THREE.MeshBasicMaterial({ map: createSignTexture('🎡 شهربازی و جایزه', '#9a3412', '#fde047') }));
            sign.position.set(0, 3.4, 4.75);
            g.add(sign);

            // استند دارت کنار ورودی شهربازی
            g.add(createDoorstepSymbolTotem('park', -2.6, 4.8, 0xf97316));

            worldGroup.add(g);
            registerBuildingColliderAndPortal(x, z, w, d, rotY, 'amusement_park', '🎡 ورود به شهربازی', 0xea580c);
        }

        // 5. فروشگاه‌ها و آرایشگاه (با استند نماد سه‌بعدی کنار در + تابلو بزرگ خوانا)
        function buildSpecialtyShopLandmark({ x, z, rotY, wallColor, roofAccent, labelText, targetZone, promptLabel, padColor, shopKind }) {
            const w = 8.5, h = 5.4, d = 6;
            const g = new THREE.Group();
            g.position.set(x, 0, z);
            g.rotation.y = rotY;

            const body = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshStandardMaterial({ color: wallColor, roughness: 0.6 }));
            body.position.y = h / 2;
            body.castShadow = true;
            g.add(body);

            const awning = new THREE.Mesh(new THREE.BoxGeometry(w - 0.6, 0.45, 1.3), new THREE.MeshStandardMaterial({ color: roofAccent }));
            awning.position.set(0, 3.1, d / 2 + 0.55);
            g.add(awning);

            const door = new THREE.Mesh(new THREE.BoxGeometry(1.6, 2.5, 0.2), new THREE.MeshStandardMaterial({ color: 0x451a03 }));
            door.position.set(0, 1.25, d / 2 + 0.05);
            g.add(door);

            // تابلو بزرگ و بسیار خوانا بالای سر در
            const sign = new THREE.Mesh(new THREE.BoxGeometry(6.8, 1.35, 0.25), new THREE.MeshBasicMaterial({ map: createSignTexture(labelText, '#0f172a', '#fbbf24') }));
            sign.position.set(0, 4.35, d / 2 + 0.25);
            g.add(sign);

            // استند نماد سه‌بعدی چرخان دقیقاً کنار درِ مغازه
            g.add(createDoorstepSymbolTotem(shopKind, 2.45, d / 2 + 1.0, roofAccent));

            worldGroup.add(g);
            registerBuildingColliderAndPortal(x, z, w, d, rotY, targetZone, promptLabel, padColor);
        }

        // 6. خانه‌های ویلایی پفکی و بامزه (Puffy / Chibi Clay-Style Houses)
        function buildResidentialHouseLandmark({ x, z, rotY, wallColor, roofColor, ownerName, ownerId }) {
            const w = 8.2, h = 4.6, d = 6.2;
            const g = new THREE.Group();
            g.position.set(x, 0, z);
            g.rotation.y = rotY;

            // پله سنگی گرد و نرم جلوی خانه
            const base = new THREE.Mesh(new THREE.CylinderGeometry(4.8, 5.1, 0.3, 24), new THREE.MeshStandardMaterial({ color: 0xe2e8f0 }));
            base.position.y = 0.15;
            g.add(base);

            // بدنه اصلی خانه با گوشه‌های پفکی و گرد (Puffy Rounded Corners)
            const wallMat = new THREE.MeshStandardMaterial({ color: wallColor, roughness: 0.55 });
            const coreBody = new THREE.Mesh(new THREE.BoxGeometry(w - 1.2, h, d - 1.2), wallMat);
            coreBody.position.y = h / 2 + 0.2;
            coreBody.castShadow = true;
            g.add(coreBody);

            // ۴ ستون استوانه‌ای تپل در چهار گوشه خانه برای ایجاد حالت پفکی و کارتونی
            const cornerOffsets = [
                [-(w / 2 - 0.6), -(d / 2 - 0.6)],
                [ (w / 2 - 0.6), -(d / 2 - 0.6)],
                [-(w / 2 - 0.6),  (d / 2 - 0.6)],
                [ (w / 2 - 0.6),  (d / 2 - 0.6)]
            ];
            cornerOffsets.forEach(([cx, cz]) => {
                const puffCol = new THREE.Mesh(new THREE.CylinderGeometry(0.95, 1.05, h, 20), wallMat);
                puffCol.position.set(cx, h / 2 + 0.2, cz);
                puffCol.castShadow = true;
                g.add(puffCol);
            });

            // سقف گنبدی-پفکی بامزه (Puffy Dome-Cone Roof) با لبه سفید برفی/خامه‌ای
            const roofMat = new THREE.MeshStandardMaterial({ color: roofColor, roughness: 0.5 });
            const puffyRoof = new THREE.Mesh(new THREE.SphereGeometry(4.7, 24, 16, 0, Math.PI * 2, 0, Math.PI * 0.48), roofMat);
            puffyRoof.scale.set(1.05, 0.72, 0.88);
            puffyRoof.position.y = h + 0.1;
            puffyRoof.castShadow = true;
            g.add(puffyRoof);

            // لبه پفکی سفید دور سقف (مثل خامه روی کیک)
            const roofTrim = new THREE.Mesh(
                new THREE.TorusGeometry(4.35, 0.28, 12, 28),
                new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.5 })
            );
            roofTrim.rotation.x = Math.PI / 2;
            roofTrim.scale.set(1.04, 0.86, 1);
            roofTrim.position.y = h + 0.2;
            g.add(roofTrim);

            // دودکش تپل و گرد روی سقف
            const chimney = new THREE.Mesh(new THREE.CylinderGeometry(0.45, 0.55, 1.5, 16), new THREE.MeshStandardMaterial({ color: 0x9a3412 }));
            chimney.position.set(2.1, h + 1.8, -0.8);
            const chimneyCap = new THREE.Mesh(new THREE.TorusGeometry(0.48, 0.14, 10, 16), new THREE.MeshStandardMaterial({ color: 0xffffff }));
            chimneyCap.rotation.x = Math.PI / 2;
            chimneyCap.position.set(2.1, h + 2.55, -0.8);
            g.add(chimney, chimneyCap);

            // درِ قوسی شکل بامزه با دستگیره طلایی بزرگ
            const doorMat = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.6 });
            const doorRect = new THREE.Mesh(new THREE.BoxGeometry(1.5, 1.8, 0.25), doorMat);
            doorRect.position.set(0, 1.1, d / 2 + 0.35);
            const doorArch = new THREE.Mesh(new THREE.CylinderGeometry(0.75, 0.75, 0.25, 20), doorMat);
            doorArch.rotation.x = Math.PI / 2;
            doorArch.position.set(0, 2.0, d / 2 + 0.35);
            const knob = new THREE.Mesh(new THREE.SphereGeometry(0.13), new THREE.MeshStandardMaterial({ color: 0xfacc15, metalness: 0.7 }));
            knob.position.set(0.48, 1.25, d / 2 + 0.52);
            g.add(doorRect, doorArch, knob);

            // پنجره‌های گرد بامزه (مثل پنجره کلبه کارتونی)
            const winMat = new THREE.MeshStandardMaterial({ color: 0xe0f2fe, emissive: 0x38bdf8, emissiveIntensity: 0.25 });
            houseWindowMaterials.push(winMat);
            [-2.35, 2.35].forEach(wx => {
                const winFrame = new THREE.Mesh(new THREE.TorusGeometry(0.68, 0.12, 10, 20), new THREE.MeshStandardMaterial({ color: 0xffffff }));
                winFrame.position.set(wx, 2.5, d / 2 + 0.36);
                const winGlass = new THREE.Mesh(new THREE.CircleGeometry(0.64, 20), winMat);
                winGlass.position.set(wx, 2.5, d / 2 + 0.38);
                g.add(winFrame, winGlass);
            });

            // دو گلدان تپل و پرگل در دو طرف درِ خانه
            const potL = buildDoorstepPlant(1.05);
            potL.position.set(-1.55, 0.2, d / 2 + 0.9);
            const potR = buildDoorstepPlant(1.05);
            potR.position.set(1.55, 0.2, d / 2 + 0.9);
            g.add(potL, potR);

            // تابلوی بزرگ و بسیار خوانای نام صاحب خانه بالای سر در
            const sign = new THREE.Mesh(
                new THREE.BoxGeometry(6.0, 1.25, 0.25),
                new THREE.MeshBasicMaterial({ map: createSignTexture(`🏠 خانه ${ownerName}`, '#1e293b', '#fde047') })
            );
            sign.position.set(0, h - 0.1, d / 2 + 0.52);
            g.add(sign);

            worldGroup.add(g);
            registerBuildingColliderAndPortal(x, z, w, d, rotY, `home_${ownerId}`, `🚪 ورود به خانه ${ownerName}`, 0x10b981);
        }

        /**
         * ============================================================================
         * 4. موتور صحنه، آسمان شب و روز، آب‌وهوا و لودر مناطق
         * ============================================================================
         */
        const container = document.getElementById('canvas-container');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(48, window.innerWidth / window.innerHeight, 0.1, 300);
        camera.position.set(0, 15, 20);

        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        container.appendChild(renderer.domElement);

        const ambientLight = new THREE.AmbientLight(0xffffff, 0.72);
        scene.add(ambientLight);

        const dirLight = new THREE.DirectionalLight(0xfffbeb, 1.15);
        dirLight.position.set(25, 42, 22);
        dirLight.castShadow = true;
        dirLight.shadow.mapSize.set(2048, 2048);
        dirLight.shadow.camera.near = 1;
        dirLight.shadow.camera.far = 120;
        const d = 55;
        dirLight.shadow.camera.left = -d;
        dirLight.shadow.camera.right = d;
        dirLight.shadow.camera.top = d;
        dirLight.shadow.camera.bottom = -d;
        scene.add(dirLight);

        const worldGroup = new THREE.Group();
        const remotePlayersGroup = new THREE.Group();
        const homeFurnitureGroup = new THREE.Group();
        scene.add(worldGroup, remotePlayersGroup, homeFurnitureGroup);

        let streetLampBulbs = [];
        let streetLampLights = [];
        let houseWindowMaterials = [];
        let currentTimeMode = 'day';
        let currentWeatherMode = 'sunny';

        const PARTICLE_COUNT = 1200;
        const weatherGeo = new THREE.BufferGeometry();
        const weatherPositions = new Float32Array(PARTICLE_COUNT * 3);
        for (let i = 0; i < PARTICLE_COUNT; i++) {
            weatherPositions[i * 3] = (Math.random() - 0.5) * 100;
            weatherPositions[i * 3 + 1] = Math.random() * 35;
            weatherPositions[i * 3 + 2] = (Math.random() - 0.5) * 100;
        }
        weatherGeo.setAttribute('position', new THREE.BufferAttribute(weatherPositions, 3));
        const weatherMat = new THREE.PointsMaterial({ color: 0xffffff, size: 0.25, transparent: true, opacity: 0.85 });
        const weatherParticles = new THREE.Points(weatherGeo, weatherMat);
        weatherParticles.visible = false;
        scene.add(weatherParticles);

        function applyEnvironmentAtmosphere() {
            const isOutdoor = currentZone === 'plaza';
            weatherParticles.visible = isOutdoor && (currentWeatherMode === 'rain' || currentWeatherMode === 'snow');

            if (!isOutdoor) {
                scene.fog = null;
                ambientLight.intensity = 0.78;
                dirLight.intensity = 0.9;
                return;
            }

            const isNight = currentTimeMode === 'night';
            const isSunset = currentTimeMode === 'sunset';

            let skyHex = 0x87ceeb;
            if (isNight) {
                skyHex = 0x0f172a;
                ambientLight.intensity = 0.28;
                dirLight.intensity = 0.25;
                dirLight.color.setHex(0x93c5fd);
            } else if (isSunset) {
                skyHex = 0xfb923c;
                ambientLight.intensity = 0.55;
                dirLight.intensity = 0.85;
                dirLight.color.setHex(0xfdba74);
            } else {
                skyHex = currentWeatherMode === 'cloudy' || currentWeatherMode === 'rain' ? 0x94a3b8 : 0x87ceeb;
                ambientLight.intensity = currentWeatherMode === 'sunny' ? 0.72 : 0.52;
                dirLight.intensity = currentWeatherMode === 'sunny' ? 1.15 : 0.55;
                dirLight.color.setHex(0xfffbeb);
            }

            if (currentWeatherMode === 'rain') {
                weatherMat.color.setHex(0xbae6fd);
                weatherMat.size = 0.18;
            } else if (currentWeatherMode === 'snow') {
                weatherMat.color.setHex(0xffffff);
                weatherMat.size = 0.38;
                if (!isNight) skyHex = 0xcbd5e1;
            }

            scene.background = new THREE.Color(skyHex);
            scene.fog = new THREE.Fog(skyHex, 52, 135);

            streetLampBulbs.forEach(mat => {
                mat.emissiveIntensity = isNight || isSunset ? 1.5 : 0.05;
                mat.color.setHex(isNight || isSunset ? 0xfef08a : 0xe2e8f0);
            });
            streetLampLights.forEach(light => {
                light.intensity = isNight ? 2.8 : (isSunset ? 1.2 : 0);
            });
            houseWindowMaterials.forEach(mat => {
                mat.emissive.setHex(isNight ? 0xfbbf24 : 0x38bdf8);
                mat.emissiveIntensity = isNight ? 0.85 : 0.2;
            });
        }

        document.getElementById('time-select').onchange = (e) => { currentTimeMode = e.target.value; applyEnvironmentAtmosphere(); };
        document.getElementById('weather-select').onchange = (e) => { currentWeatherMode = e.target.value; applyEnvironmentAtmosphere(); };

        function createStreetLamp(x, z) {
            const lamp = new THREE.Group();
            lamp.position.set(x, 0, z);
            const base = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.26, 0.4, 8), new THREE.MeshStandardMaterial({ color: 0x1e293b }));
            base.position.y = 0.2;
            const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.1, 4.2, 8), new THREE.MeshStandardMaterial({ color: 0x334155 }));
            pole.position.y = 2.2;
            const bulbMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, emissive: 0xfef08a, emissiveIntensity: 0.05 });
            const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.32, 12, 12), bulbMat);
            bulb.position.y = 4.4;
            streetLampBulbs.push(bulbMat);

            const pointLight = new THREE.PointLight(0xfef08a, 0, 14);
            pointLight.position.set(0, 4.2, 0);
            streetLampLights.push(pointLight);

            lamp.add(base, pole, bulb, pointLight);
            worldGroup.add(lamp);
            obstacles.push({ minX: x - 0.35, maxX: x + 0.35, minZ: z - 0.35, maxZ: z + 0.35 });
        }

        function createPortalPad(x, z, targetZone, promptLabel, color = 0xf59e0b) {
            const pad = new THREE.Mesh(
                new THREE.CylinderGeometry(1.35, 1.35, 0.08, 24),
                new THREE.MeshStandardMaterial({ color, emissive: color, emissiveIntensity: 0.35 })
            );
            pad.position.set(x, 0.04, z);
            worldGroup.add(pad);
            portals.push({ x, z, targetZone, promptLabel, mesh: pad });
        }

        function createTree(x, z) {
            const tree = new THREE.Group();
            tree.position.set(x, 0, z);
            const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.42, 2.2, 8), new THREE.MeshStandardMaterial({ color: 0x78350f }));
            trunk.position.y = 1.1;
            const leaves = new THREE.Mesh(new THREE.SphereGeometry(1.75, 14, 12), new THREE.MeshStandardMaterial({ color: 0x22c55e }));
            leaves.position.y = 3.1;
            tree.add(trunk, leaves);
            worldGroup.add(tree);
            obstacles.push({ minX: x - 0.7, maxX: x + 0.7, minZ: z - 0.7, maxZ: z + 0.7 });
        }

        const targetRing = new THREE.Mesh(
            new THREE.RingGeometry(0.35, 0.55, 24),
            new THREE.MeshBasicMaterial({ color: 0xf59e0b, side: THREE.DoubleSide })
        );
        targetRing.rotation.x = -Math.PI / 2;
        targetRing.position.y = 0.04;
        targetRing.visible = false;
        scene.add(targetRing);

        let currentZone = 'plaza';
        let groundMesh = null;
        let obstacles = [];
        let portals = [];
        let interactables = [];
        let activePortal = null;
        let playerState = null;
        let myRig = null;
        const remoteRigs = new Map();

        let shoppingCart = [];
        let placingItem = null;
        let placingPreviewMesh = null;
        let placingRotation = 0;

        async function loadZone(zoneId) {
            const overlay = document.getElementById('fade-overlay');
            overlay.classList.add('active');
            await new Promise(r => setTimeout(r, 300));

            worldGroup.clear();
            homeFurnitureGroup.clear();
            obstacles = [];
            portals = [];
            interactables = [];
            animatedLandmarks = [];
            streetLampBulbs = [];
            streetLampLights = [];
            houseWindowMaterials = [];
            activePortal = null;
            targetRing.visible = false;
            document.getElementById('portal-prompt').classList.remove('visible');
            document.getElementById('side-panel').classList.remove('open');

            currentZone = zoneId;
            const isHome = zoneId.startsWith('home_');

            document.getElementById('btn-go-plaza').style.display = zoneId === 'plaza' ? 'none' : 'inline-block';
            document.getElementById('btn-go-my-home').style.display = zoneId === `home_${playerState.userId}` ? 'none' : 'inline-block';
            document.getElementById('btn-toggle-inventory').style.display = 'none';
            document.getElementById('btn-open-wardrobe').style.display = 'none';
            document.getElementById('btn-open-cas').style.display = 'none';

            if (zoneId === 'plaza') {
                document.getElementById('zone-title-display').textContent = '🏙️ میدان اصلی و محله مسکونی';

                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(115, 115), new THREE.MeshStandardMaterial({ color: 0x86efac, roughness: 0.9 }));
                groundMesh.rotation.x = -Math.PI / 2;
                groundMesh.receiveShadow = true;
                worldGroup.add(groundMesh);

                const plazaStone = new THREE.Mesh(new THREE.CircleGeometry(17.5, 36), new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.8 }));
                plazaStone.rotation.x = -Math.PI / 2;
                plazaStone.position.y = 0.01;
                plazaStone.receiveShadow = true;
                worldGroup.add(plazaStone);

                const roadMat = new THREE.MeshStandardMaterial({ color: 0x475569, roughness: 0.85 });
                const southAvenue = new THREE.Mesh(new THREE.PlaneGeometry(8, 36), roadMat);
                southAvenue.rotation.x = -Math.PI / 2;
                southAvenue.position.set(0, 0.015, 31);
                const crossStreet = new THREE.Mesh(new THREE.PlaneGeometry(84, 7.5), roadMat);
                crossStreet.rotation.x = -Math.PI / 2;
                crossStreet.position.set(0, 0.016, 29);
                worldGroup.add(southAvenue, crossStreet);

                [
                    [-14, 13], [14, 13], [-14, -11], [14, -11],
                    [-28, 24.8], [-15, 24.8], [15, 24.8], [28, 24.8],
                    [-28, 33.2], [-15, 33.2], [15, 33.2], [28, 33.2]
                ].forEach(([lx, lz]) => createStreetLamp(lx, lz));

                const fountainBase = new THREE.Mesh(new THREE.CylinderGeometry(3.2, 3.5, 0.8, 24), new THREE.MeshStandardMaterial({ color: 0x94a3b8 }));
                fountainBase.position.y = 0.4;
                const fountainWater = new THREE.Mesh(new THREE.CylinderGeometry(2.9, 2.9, 0.85, 24), new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.2 }));
                fountainWater.position.y = 0.42;
                worldGroup.add(fountainBase, fountainWater);
                obstacles.push({ minX: -3.5, maxX: 3.5, minZ: -3.5, maxZ: 3.5 });

                // --- ۸ بنای متمایز با استند نماد سه‌بعدی کنار در و تابلوهای HD ---
                buildSchoolLandmark(0, -23.5, 0);

                buildSpecialtyShopLandmark({
                    x: 14.5, z: -20.5, rotY: -Math.PI / 8,
                    wallColor: 0xffe4e6, roofAccent: 0xe11d48,
                    labelText: '💇 آرایشگاه شهر', targetZone: 'hair_salon',
                    promptLabel: '💇 ورود به آرایشگاه شهر', padColor: 0xe11d48, shopKind: 'hair_salon'
                });

                buildSpecialtyShopLandmark({
                    x: 23.5, z: -11, rotY: -Math.PI / 3,
                    wallColor: 0xfce7f3, roofAccent: 0xdb2777,
                    labelText: '👗 فروشگاه لباس و کفش', targetZone: 'shop_clothes',
                    promptLabel: '👗 ورود به فروشگاه لباس و کفش', padColor: 0xdb2777, shopKind: 'clothes'
                });

                buildSpecialtyShopLandmark({
                    x: 25, z: 2, rotY: -Math.PI / 2,
                    wallColor: 0xfef3c7, roofAccent: 0xd97706,
                    labelText: '🛋️ فروشگاه مبلمان', targetZone: 'shop_furniture',
                    promptLabel: '🛋️ ورود به فروشگاه مبلمان', padColor: 0xd97706, shopKind: 'furniture'
                });

                buildSpecialtyShopLandmark({
                    x: -14.5, z: -20.5, rotY: Math.PI / 8,
                    wallColor: 0xe0f2fe, roofAccent: 0x0284c7,
                    labelText: '⚔️ فروشگاه لوازم جانبی', targetZone: 'shop_accessories',
                    promptLabel: '⚔️ ورود به فروشگاه لوازم جانبی', padColor: 0x0284c7, shopKind: 'accessories'
                });

                buildGameNetLandmark(-23.5, -11, Math.PI / 3);
                buildCinemaLandmark(-25, 2, Math.PI / 2);
                buildAmusementParkLandmark(18.5, 13, -Math.PI * 0.75);

                // --- خانه‌های ویلایی پفکی و بامزه شهروندان ---
                const plots = await bridge.getResidentialPlots();
                const houseCoords = [
                    { x: -24, z: 19, rotY: 0 },
                    { x: -11, z: 19, rotY: 0 },
                    { x: 11,  z: 39, rotY: Math.PI },
                    { x: 24,  z: 39, rotY: Math.PI },
                    { x: -24, z: 39, rotY: Math.PI },
                    { x: -11, z: 39, rotY: Math.PI }
                ];

                plots.forEach((plot, idx) => {
                    if (idx >= houseCoords.length) return;
                    const pos = houseCoords[idx];
                    buildResidentialHouseLandmark({
                        x: pos.x, z: pos.z, rotY: pos.rotY,
                        wallColor: plot.color || 0x93c5fd,
                        roofColor: plot.roofColor || 0xe11d48,
                        ownerName: plot.ownerName,
                        ownerId: plot.ownerId
                    });
                });

                [[-9, 9], [9, 9], [-35, 12], [35, 12], [-35, 29], [35, 29]].forEach(([tx, tz]) => createTree(tx, tz));

                myRig.mesh.position.set(0, 0, 8);
                myRig.targetPos.set(0, 0, 8);

            } else if (zoneId === 'hair_salon') {
                document.getElementById('zone-title-display').textContent = '💇 آرایشگاه شهر (تغییر مدل و رنگ مو)';
                scene.background = new THREE.Color(0xffe4e6);
                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(22, 20), new THREE.MeshStandardMaterial({ color: 0xfecdd3 }));
                groundMesh.rotation.x = -Math.PI / 2;
                worldGroup.add(groundMesh);

                const chair = new THREE.Mesh(new THREE.CylinderGeometry(1.2, 1.4, 0.6, 20), new THREE.MeshStandardMaterial({ color: 0xe11d48 }));
                chair.position.set(0, 0.3, -3);
                const mirror = new THREE.Mesh(new THREE.BoxGeometry(8, 3.6, 0.2), new THREE.MeshBasicMaterial({ map: createSignTexture('💇 آرایشگاه تخصصی نسیم', '#be123c', '#fde047') }));
                mirror.position.set(0, 2.4, -7);
                worldGroup.add(chair, mirror);
                interactables.push(chair, mirror);

                createPortalPad(0, 7.5, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
                myRig.mesh.position.set(0, 0, 4);
                myRig.targetPos.set(0, 0, 4);
                openHairSalonPanel();

            } else if (zoneId === 'cinema') {
                document.getElementById('zone-title-display').textContent = '🎬 سینمای شهر';
                scene.background = new THREE.Color(0x0f172a);
                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(24, 24), new THREE.MeshStandardMaterial({ color: 0x7f1d1d }));
                groundMesh.rotation.x = -Math.PI / 2;
                worldGroup.add(groundMesh);

                const screen = new THREE.Mesh(new THREE.BoxGeometry(15, 6.5, 0.2), new THREE.MeshBasicMaterial({ map: createSignTexture('🎬 روی پرده کلیک کنید تا فیلم انتخاب شود', '#1e1b4b', '#a855f7') }));
                screen.position.set(0, 4, -10.5);
                worldGroup.add(screen);
                interactables.push(screen);

                for (let row = 0; row < 3; row++) {
                    for (let col = -3; col <= 3; col++) {
                        const seat = new THREE.Mesh(new THREE.BoxGeometry(1.1, 0.9, 1), new THREE.MeshStandardMaterial({ color: 0xdc2626 }));
                        seat.position.set(col * 1.8, 0.45, -3 + row * 2.5);
                        worldGroup.add(seat);
                    }
                }

                createPortalPad(0, 9.5, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
                myRig.mesh.position.set(0, 0, 7);
                myRig.targetPos.set(0, 0, 7);
                openCinemaPanel();

            } else if (zoneId === 'gamenet') {
                document.getElementById('zone-title-display').textContent = '🕹️ GAME NET - سالن بازی‌های آرکید';
                scene.background = new THREE.Color(0x064e3b);
                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(24, 22), new THREE.MeshStandardMaterial({ color: 0x0f172a }));
                groundMesh.rotation.x = -Math.PI / 2;
                worldGroup.add(groundMesh);

                GAMENET_GAMES.forEach((g, idx) => {
                    const xPos = -5 + idx * 5;
                    const cabinet = new THREE.Mesh(new THREE.BoxGeometry(1.8, 3.2, 1.5), new THREE.MeshStandardMaterial({ color: 0x10b981 }));
                    cabinet.position.set(xPos, 1.6, -5);
                    const screen = new THREE.Mesh(new THREE.PlaneGeometry(1.4, 1.1), new THREE.MeshBasicMaterial({ color: 0x38bdf8 }));
                    screen.position.set(xPos, 2.2, -4.23);
                    worldGroup.add(cabinet, screen);
                    interactables.push(cabinet, screen);
                });

                createPortalPad(0, 8, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
                myRig.mesh.position.set(0, 0, 5.5);
                myRig.targetPos.set(0, 0, 5.5);
                openGameNetPanel();

            } else if (zoneId === 'amusement_park') {
                document.getElementById('zone-title-display').textContent = '🎡 شهربازی و غرفه‌های جایزه‌دار';
                scene.background = new THREE.Color(0xffedd5);
                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(28, 24), new THREE.MeshStandardMaterial({ color: 0xfef3c7 }));
                groundMesh.rotation.x = -Math.PI / 2;
                worldGroup.add(groundMesh);

                const booths = [
                    { x: -7, label: '🎯 پرتاب دارت و توپ', color: 0xef4444 },
                    { x: 0,  label: '🥤 غرفه انداختن لیوان', color: 0x3b82f6 },
                    { x: 7,  label: '🎰 گردونه شانس BitBlox', color: 0xf59e0b }
                ];
                booths.forEach(b => {
                    const stall = new THREE.Mesh(new THREE.BoxGeometry(4.2, 3.4, 2.5), new THREE.MeshStandardMaterial({ color: b.color }));
                    stall.position.set(b.x, 1.7, -6);
                    const sign = new THREE.Mesh(new THREE.BoxGeometry(4.4, 0.95, 0.2), new THREE.MeshBasicMaterial({ map: createSignTexture(b.label) }));
                    sign.position.set(b.x, 3.85, -4.7);
                    worldGroup.add(stall, sign);
                    interactables.push(stall, sign);
                });

                createPortalPad(0, 8.5, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
                myRig.mesh.position.set(0, 0, 5.5);
                myRig.targetPos.set(0, 0, 5.5);
                openAmusementParkPanel();

            } else if (zoneId === 'shop_clothes' || zoneId === 'shop_furniture' || zoneId === 'shop_accessories') {
                const titles = {
                    shop_clothes: '👗 فروشگاه لباس و کفش',
                    shop_furniture: '🛋️ فروشگاه مبلمان خانه',
                    shop_accessories: '⚔️ فروشگاه لوازم جانبی'
                };
                const bgColors = { shop_clothes: 0xfce7f3, shop_furniture: 0xffedd5, shop_accessories: 0xe0f2fe };

                document.getElementById('zone-title-display').textContent = titles[zoneId];
                scene.background = new THREE.Color(bgColors[zoneId]);

                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(26, 22), new THREE.MeshStandardMaterial({ color: bgColors[zoneId] }));
                groundMesh.rotation.x = -Math.PI / 2;
                worldGroup.add(groundMesh);

                const items = CATALOG[zoneId];
                items.forEach((item, idx) => {
                    const xPos = -8 + idx * 3.2;
                    const stand = new THREE.Mesh(new THREE.CylinderGeometry(1.0, 1.1, 0.5, 20), new THREE.MeshStandardMaterial({ color: 0x475569 }));
                    stand.position.set(xPos, 0.25, -4);
                    const box = new THREE.Mesh(new THREE.BoxGeometry(0.9, 0.9, 0.9), new THREE.MeshStandardMaterial({ color: item.color }));
                    box.position.set(xPos, 1.1, -4);
                    worldGroup.add(stand, box);
                    interactables.push(stand, box);
                });

                createPortalPad(0, 8, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
                myRig.mesh.position.set(0, 0, 5.5);
                myRig.targetPos.set(0, 0, 5.5);
                openShopPanel(zoneId);

            } else if (zoneId === 'school_hall') {
                document.getElementById('zone-title-display').textContent = '🎓 سالن اجتماعات مدرسه';
                scene.background = new THREE.Color(0xeff6ff);
                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(28, 26), new THREE.MeshStandardMaterial({ color: 0xf1f5f9 }));
                groundMesh.rotation.x = -Math.PI / 2;
                worldGroup.add(groundMesh);

                const stage = new THREE.Mesh(new THREE.BoxGeometry(14, 0.7, 5), new THREE.MeshStandardMaterial({ color: 0x92400e }));
                stage.position.set(0, 0.35, -9);
                const board = new THREE.Mesh(new THREE.BoxGeometry(10, 4.2, 0.2), new THREE.MeshBasicMaterial({ map: createSignTexture('سالن اجتماعات مدرسه نسیم دانایی', '#065f46', '#34d399') }));
                board.position.set(0, 3.2, -11.3);
                worldGroup.add(stage, board);

                createPortalPad(0, 10, 'plaza', '🏙️ خروج به میدان شهر', 0x10b981);
                myRig.mesh.position.set(0, 0, 7);
                myRig.targetPos.set(0, 0, 7);

            } else if (isHome) {
                const ownerId = Number(zoneId.replace('home_', ''));
                const homeData = await bridge.getHomeData(ownerId);

                document.getElementById('zone-title-display').textContent = homeData.isMine
                    ? '🏠 خانه شخصی من'
                    : `🏠 مهمانی در خانه ${homeData.ownerName}`;

                if (homeData.isMine) {
                    document.getElementById('btn-toggle-inventory').style.display = 'inline-block';
                    document.getElementById('btn-open-wardrobe').style.display = 'inline-block';
                    document.getElementById('btn-open-cas').style.display = playerState.freeCasUsed ? 'none' : 'inline-block';
                }

                scene.background = new THREE.Color(0xe0f2fe);
                groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(24, 22), new THREE.MeshStandardMaterial({ color: 0xd97706, roughness: 0.8 }));
                groundMesh.rotation.x = -Math.PI / 2;
                groundMesh.receiveShadow = true;
                worldGroup.add(groundMesh);

                if (homeData.isMine) {
                    const gridHelper = new THREE.GridHelper(24, 24, 0x78350f, 0xb45309);
                    gridHelper.position.y = 0.02;
                    worldGroup.add(gridHelper);
                }

                const wallMat = new THREE.MeshStandardMaterial({ color: 0xfef9c3 });
                const backWall = new THREE.Mesh(new THREE.BoxGeometry(24, 4.5, 0.4), wallMat);
                backWall.position.set(0, 2.25, -11);
                const leftWall = new THREE.Mesh(new THREE.BoxGeometry(0.4, 4.5, 22), wallMat);
                leftWall.position.set(-12, 2.25, 0);
                const rightWall = new THREE.Mesh(new THREE.BoxGeometry(0.4, 4.5, 22), wallMat);
                rightWall.position.set(12, 2.25, 0);
                worldGroup.add(backWall, leftWall, rightWall);

                const dividerMat = new THREE.MeshStandardMaterial({ color: 0xfde68a });
                const divLeft = new THREE.Mesh(new THREE.BoxGeometry(0.3, 3.2, 6.5), dividerMat);
                divLeft.position.set(-4.5, 1.6, -7.6);
                obstacles.push({ minX: -4.8, maxX: -4.2, minZ: -11, maxZ: -4.2 });

                const divRight = new THREE.Mesh(new THREE.BoxGeometry(0.3, 3.2, 6.5), dividerMat);
                divRight.position.set(4.5, 1.6, -7.6);
                obstacles.push({ minX: 4.2, maxX: 4.8, minZ: -11, maxZ: -4.2 });
                worldGroup.add(divLeft, divRight);

                const counter = new THREE.Mesh(new THREE.BoxGeometry(6.5, 1.0, 1.1), new THREE.MeshStandardMaterial({ color: 0xf8fafc }));
                counter.position.set(-8.2, 0.5, -10.1);
                const fridge = new THREE.Mesh(new THREE.BoxGeometry(1.2, 2.5, 1.1), new THREE.MeshStandardMaterial({ color: 0xe2e8f0 }));
                fridge.position.set(-11, 1.25, -8.8);
                worldGroup.add(counter, fridge);
                obstacles.push({ minX: -11.8, maxX: -4.8, minZ: -10.9, maxZ: -8.2 });

                const rug = new THREE.Mesh(new THREE.PlaneGeometry(6.5, 4.5), new THREE.MeshStandardMaterial({ color: 0x991b1b }));
                rug.rotation.x = -Math.PI / 2;
                rug.position.set(0, 0.025, -5.5);
                worldGroup.add(rug);

                const wardrobeGroup = new THREE.Group();
                wardrobeGroup.position.set(10.5, 0, -9.8);
                const wBody = new THREE.Mesh(new THREE.BoxGeometry(2.2, 2.8, 0.95), new THREE.MeshStandardMaterial({ color: 0x78350f }));
                wBody.position.y = 1.4;
                const wSign = new THREE.Mesh(new THREE.BoxGeometry(2.6, 0.65, 0.15), new THREE.MeshBasicMaterial({ map: createSignTexture('👗 کمد لباس', '#4f46e5', '#fff') }));
                wSign.position.set(0, 3.15, 0.1);
                wardrobeGroup.add(wBody, wSign);
                worldGroup.add(wardrobeGroup);
                if (homeData.isMine) {
                    wardrobeGroup.userData = { action: 'wardrobe' };
                    interactables.push(wardrobeGroup);
                }

                createPortalPad(0, 9.2, 'plaza', '🏙️ خروج به خیابان و میدان شهر', 0x10b981);

                renderHomeFurniture(homeData.layout, homeData);

                myRig.mesh.position.set(0, 0, 6);
                myRig.targetPos.set(0, 0, 6);
            }

            applyEnvironmentAtmosphere();

            remoteRigs.forEach(r => r.labelEl.remove());
            remoteRigs.clear();
            remotePlayersGroup.clear();
            await syncWithServer();

            overlay.classList.remove('active');
        }

        function renderHomeFurniture(layout, homeDataContext = null) {
            homeFurnitureGroup.clear();
            (layout || []).forEach(placed => {
                const mesh = buildRealisticFurniture(placed.itemId);
                mesh.position.set(placed.x, 0, placed.z);
                mesh.rotation.y = placed.rotY || 0;
                if (placed.itemId === 'furn_tv') {
                    mesh.userData = { action: 'tv_studio', homeData: homeDataContext };
                    interactables.push(mesh);
                }
                homeFurnitureGroup.add(mesh);
            });
        }

        /**
         * ============================================================================
         * 5. پنل‌های تعاملی (تلویزیون، چت‌روم ثابت، آرایشگاه، سینما، گیم‌نت، شهربازی)
         * ============================================================================
         */
        function refreshBitBloxUI() { document.getElementById('bitblox-display').textContent = playerState.bitblox; }

        function openTvStudioPanel(homeData) {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';

            const isMine = !homeData || homeData.isMine;
            const gamesList = isMine ? playerState.myGames : (homeData.games || []);
            title.textContent = isMine ? '📺 تلویزیون و استودیو بازی‌سازی من' : `📺 بازی‌های ساخته شده توسط ${homeData.ownerName}`;

            let html = `<h4 style="font-size:0.84rem;margin-bottom:6px;color:#1e293b;">🎮 بازی‌های قابل اجرا روی تلویزیون:</h4>`;
            if (gamesList.length === 0) {
                html += `<div style="font-size:0.8rem;color:#64748b;margin-bottom:10px;">هنوز بازی تایید شده‌ای روی این تلویزیون نیست.</div>`;
            } else {
                gamesList.forEach(g => {
                    const isApproved = g.status === 'approved';
                    html += `
                        <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:8px;margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <div style="font-weight:700;font-size:0.82rem;">${g.title}</div>
                                <div style="font-size:0.74rem;color:${isApproved ? '#059669' : '#d97706'};">
                                    ${isApproved ? `✅ تایید شده (پاداش: ${g.reward} BitBlox)` : `⏳ در انتظار بررسی ادمین (${g.reward} BitBlox)`}
                                </div>
                            </div>
                            ${isApproved
                                ? `<button class="btn-action btn-cyan btn-play-tv-game" data-title="${g.title}" style="padding:5px 10px;">▶ اجرا</button>`
                                : (isMine ? `<button class="btn-action btn-amber btn-demo-approve" data-id="${g.id}" style="padding:4px 8px;font-size:0.72rem;">تست تایید ادمین</button>` : '')
                            }
                        </div>
                    `;
                });
            }

            html += `<div id="tv-screen-player"></div>`;

            if (isMine) {
                html += `
                    <div style="margin-top:12px;padding-top:10px;border-top:2px dashed #cbd5e1;">
                        <h4 style="font-size:0.84rem;margin-bottom:6px;color:#4f46e5;">📤 آپلود بازی جدید برای دریافت BitBlox:</h4>
                        <input type="text" id="new-game-title" placeholder="نام بازی (مثلاً: قلعه جادویی)" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;font-size:0.8rem;margin-bottom:6px;">
                        <select id="new-game-tier" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;font-size:0.8rem;margin-bottom:6px;">
                            <option value="simple">سطح ۱: بازی ساده (پاداش: 10 BitBlox)</option>
                            <option value="staged">سطح ۲: بازی مرحله‌ای (پاداش: 20 BitBlox)</option>
                            <option value="pro">سطح ۳: بازی حرفه‌ای و خلاقانه (پاداش: 100 BitBlox)</option>
                        </select>
                        <input type="text" id="new-game-url" placeholder="لینک فایل HTML5 یا Scratch بازی" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;font-size:0.8rem;margin-bottom:8px;">
                        <button class="btn-action" id="btn-submit-user-game" style="width:100%;">🚀 ارسال بازی برای تایید ادمین</button>
                    </div>
                `;
            }

            body.innerHTML = html;

            body.querySelectorAll('.btn-play-tv-game').forEach(btn => {
                btn.onclick = () => {
                    document.getElementById('tv-screen-player').innerHTML = `
                        <div class="minigame-stage">
                            <div style="font-weight:800;color:#38bdf8;margin-bottom:6px;">📺 در حال پخش روی تلویزیون: ${btn.dataset.title}</div>
                            <p style="font-size:0.78rem;color:#cbd5e1;">بازی دانش‌آموز با موفقیت روی تلویزیون اتاق اجرا شد! دوستانت هم می‌توانند تماشا کنند.</p>
                        </div>
                    `;
                };
            });

            body.querySelectorAll('.btn-demo-approve').forEach(btn => {
                btn.onclick = async () => {
                    playerState = await bridge.adminApproveMockGame(btn.dataset.id);
                    refreshBitBloxUI();
                    myRig.say('هورا! ادمین بازیم رو تایید کرد و BitBlox گرفتم 💎🎉');
                    myRig.triggerEmote('jump');
                    openTvStudioPanel(homeData);
                };
            });

            const submitBtn = document.getElementById('btn-submit-user-game');
            if (submitBtn) {
                submitBtn.onclick = async () => {
                    const gTitle = document.getElementById('new-game-title').value.trim();
                    const gTier = document.getElementById('new-game-tier').value;
                    const gUrl = document.getElementById('new-game-url').value.trim() || '#';
                    if (!gTitle) return alert('لطفاً نام بازی را وارد کن!');
                    const res = await bridge.submitUserGame(gTitle, gTier, gUrl);
                    playerState = res.state;
                    openTvStudioPanel(homeData);
                };
            }

            panel.classList.add('open');
        }

        const chatLog = [
            { sender: 'سیستم امن', text: 'به چت‌روم امن خوش آمدید! جملات آماده را انتخاب کنید.', type: 'reply' }
        ];

        function openSafeChatRoom() {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '💬 چت‌روم کنترل‌شده (جملات ثابت)';

            const logHtml = chatLog.map(m => `<div class="chat-msg ${m.type}"><strong>${m.sender}:</strong> ${m.text}</div>`).join('');
            const phrasesHtml = SAFE_CHAT_PHRASES.map(p => `
                <button class="btn-action btn-secondary btn-send-safe-chat" data-id="${p.id}" style="width:100%;text-align:right;margin-bottom:5px;padding:7px 10px;">
                    ${p.text}
                </button>
            `).join('');

            body.innerHTML = `
                <div style="margin-bottom:8px;font-size:0.8rem;">
                    <label>ارسال به:</label>
                    <select id="chat-target-select" style="padding:4px 8px;border-radius:8px;border:1px solid #cbd5e1;">
                        <option value="all">همه حاضران در منطقه</option>
                        <option value="203">نگار (کلاس پنجم)</option>
                        <option value="202">سارا (کلاس چهارم)</option>
                    </select>
                </div>
                <div class="chat-history" id="chat-history-box">${logHtml}</div>
                <div style="font-size:0.78rem;font-weight:700;margin-bottom:6px;color:#334155;">انتخاب جمله از پیش تعریف‌شده:</div>
                <div>${phrasesHtml}</div>
            `;

            const box = document.getElementById('chat-history-box');
            box.scrollTop = box.scrollHeight;

            body.querySelectorAll('.btn-send-safe-chat').forEach(btn => {
                btn.onclick = () => {
                    const phrase = SAFE_CHAT_PHRASES.find(x => x.id === btn.dataset.id);
                    const targetSel = document.getElementById('chat-target-select');
                    const targetName = targetSel.options[targetSel.selectedIndex].text;
                    if (!phrase) return;

                    chatLog.push({ sender: `${playerState.name} (به ${targetName})`, text: phrase.text, type: 'mine' });
                    myRig.say(phrase.text);
                    myRig.triggerEmote('wave');

                    const responderId = targetSel.value === '202' ? 202 : 203;
                    const responderName = responderId === 202 ? 'سارا' : 'نگار';
                    setTimeout(() => {
                        chatLog.push({ sender: responderName, text: phrase.reply, type: 'reply' });
                        const botRig = remoteRigs.get(responderId);
                        if (botRig) {
                            botRig.say(phrase.reply);
                            botRig.triggerEmote('wave');
                        }
                        if (panel.classList.contains('open') && title.textContent.includes('چت‌روم')) {
                            openSafeChatRoom();
                        }
                    }, 700);

                    openSafeChatRoom();
                };
            });

            panel.classList.add('open');
        }

        document.getElementById('btn-open-chatroom').onclick = openSafeChatRoom;

        function openHairSalonPanel() {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '💇 آرایشگاه شهر (مدل و رنگ مو)';

            const styles = [
                { id: 'short', name: 'مدل کوتاه کلاسیک', price: 25 },
                { id: 'long', name: 'مدل بلند ابریشمی', price: 35 },
                { id: 'ponytail', name: 'مدل دم‌اسبی اسپرت', price: 30 },
                { id: 'curly', name: 'مدل فرفری مجلسی', price: 40 }
            ];
            const hairColors = [0x1c1917, 0x451a03, 0x78350f, 0xd97706, 0xfacc15, 0xb91c1c, 0x7c3aed, 0xec4899];

            let selectedStyle = playerState.appearance.hairStyle || 'short';
            let selectedColor = playerState.appearance.hairColor || 0x451a03;

            function renderSalon() {
                const stObj = styles.find(s => s.id === selectedStyle) || styles[0];
                body.innerHTML = `
                    <div style="font-size:0.8rem;color:#475569;margin-bottom:8px;">مدل موی جدید و رنگ دلخواهت رو انتخاب کن:</div>
                    <div class="item-grid" style="margin-bottom:10px;">
                        ${styles.map(s => `
                            <div class="shop-card" style="border-color:${selectedStyle === s.id ? '#e11d48' : '#e2e8f0'};cursor:pointer;" data-style="${s.id}">
                                <h4>${s.name}</h4>
                                <div class="price">💎 ${s.price} BitBlox</div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="cas-group">
                        <div class="cas-group-title"><span>🎨 انتخاب رنگ مو:</span></div>
                        <div class="swatch-row">
                            ${hairColors.map(c => `<div class="color-swatch ${selectedColor === c ? 'selected' : ''}" data-hex="${c}" style="background:#${c.toString(16).padStart(6, '0')};"></div>`).join('')}
                        </div>
                    </div>
                    <button class="btn-action btn-pink" id="btn-confirm-hair" style="width:100%;padding:9px;">
                        ✂️ تایید و پرداخت (${stObj.price} 💎 BitBlox)
                    </button>
                `;

                body.querySelectorAll('.shop-card').forEach(card => {
                    card.onclick = () => {
                        selectedStyle = card.dataset.style;
                        myRig.applyAppearance({ ...playerState.appearance, hairStyle: selectedStyle, hairColor: selectedColor });
                        renderSalon();
                    };
                });

                body.querySelectorAll('.color-swatch').forEach(sw => {
                    sw.onclick = () => {
                        selectedColor = Number(sw.dataset.hex);
                        myRig.applyAppearance({ ...playerState.appearance, hairStyle: selectedStyle, hairColor: selectedColor });
                        renderSalon();
                    };
                });

                document.getElementById('btn-confirm-hair').onclick = async () => {
                    const res = await bridge.applyHairSalon(selectedStyle, selectedColor, stObj.price);
                    if (!res.success) {
                        myRig.applyAppearance(playerState.appearance);
                        return alert(res.message);
                    }
                    playerState = res.state;
                    refreshBitBloxUI();
                    myRig.say('مدل موی جدیدم چطور شد؟ 💇✨');
                    myRig.triggerEmote('jump');
                    panel.classList.remove('open');
                };
            }

            renderSalon();
            panel.classList.add('open');
        }

        function openCinemaPanel() {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '🎬 گیشه بلیت سینمای شهر';

            body.innerHTML = `
                <div style="font-size:0.8rem;color:#475569;margin-bottom:8px;">فیلم مورد نظرت رو انتخاب کن و با پرداخت BitBlox روی پرده بزرگ سینما تماشا کن:</div>
                ${CINEMA_MOVIES.map(m => `
                    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:10px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:700;font-size:0.82rem;">${m.title}</div>
                            <div style="font-size:0.75rem;color:#64748b;">مدت: ${m.duration} | بلیت: <strong>${m.price} 💎 BitBlox</strong></div>
                        </div>
                        <button class="btn-action btn-secondary btn-buy-ticket" data-id="${m.id}">🎟️ خرید بلیت</button>
                    </div>
                `).join('')}
                <div id="cinema-player-box"></div>
            `;

            body.querySelectorAll('.btn-buy-ticket').forEach(btn => {
                btn.onclick = async () => {
                    const movie = CINEMA_MOVIES.find(x => x.id === btn.dataset.id);
                    const res = await bridge.walletTransaction(-movie.price, `cinema_${movie.id}`);
                    if (!res.success) return alert(res.message);
                    playerState = res.state;
                    refreshBitBloxUI();
                    myRig.say(`بلیت ${movie.title} رو گرفتم! 🍿🎬`);
                    document.getElementById('cinema-player-box').innerHTML = `
                        <div class="minigame-stage" style="border:2px solid ${movie.color};">
                            <div style="font-size:1rem;font-weight:800;color:${movie.color};margin-bottom:6px;">🎬 در حال اکران: ${movie.title}</div>
                            <p style="font-size:0.78rem;">پخش انیمیشن روی پرده اصلی سالن سینما آغاز شد! از تماشا لذت ببر.</p>
                        </div>
                    `;
                };
            });

            panel.classList.add('open');
        }

        function openGameNetPanel() {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '🕹️ گیم‌نت شهر (GAME NET)';

            body.innerHTML = `
                <div style="font-size:0.8rem;color:#475569;margin-bottom:8px;">دستگاه بازی مورد نظرت رو روشن کن (ورودی با BitBlox + جایزه برد):</div>
                ${GAMENET_GAMES.map(g => `
                    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:10px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:700;font-size:0.82rem;">${g.title}</div>
                            <div style="font-size:0.74rem;color:#059669;">ورودی: ${g.price} 💎 | جایزه برد: تا ${g.rewardMax} 💎</div>
                        </div>
                        <button class="btn-action btn-start-gn" data-id="${g.id}">🕹️ شروع بازی</button>
                    </div>
                `).join('')}
                <div id="gamenet-stage-box"></div>
            `;

            body.querySelectorAll('.btn-start-gn').forEach(btn => {
                btn.onclick = async () => {
                    const game = GAMENET_GAMES.find(x => x.id === btn.dataset.id);
                    const res = await bridge.walletTransaction(-game.price, `gamenet_${game.id}`);
                    if (!res.success) return alert(res.message);
                    playerState = res.state;
                    refreshBitBloxUI();

                    const a = Math.floor(Math.random() * 12) + 3;
                    const b = Math.floor(Math.random() * 12) + 4;
                    const ans = a + b;
                    const stage = document.getElementById('gamenet-stage-box');
                    stage.innerHTML = `
                        <div class="minigame-stage">
                            <div style="font-weight:800;color:#34d399;margin-bottom:6px;">${game.title}</div>
                            <div style="font-size:1.1rem;margin-bottom:8px;">حاصل عبارت چقدر است؟ <strong>${a} + ${b} = ?</strong></div>
                            <div style="display:flex;gap:8px;justify-content:center;">
                                <button class="btn-action btn-gn-ans" data-ok="1">${ans}</button>
                                <button class="btn-action btn-gn-ans" data-ok="0">${ans + 2}</button>
                                <button class="btn-action btn-gn-ans" data-ok="0">${ans - 1}</button>
                            </div>
                        </div>
                    `;
                    stage.querySelectorAll('.btn-gn-ans').forEach(ansBtn => {
                        ansBtn.onclick = async () => {
                            if (ansBtn.dataset.ok === '1') {
                                const rewardRes = await bridge.walletTransaction(game.rewardMax, `win_${game.id}`);
                                playerState = rewardRes.state;
                                refreshBitBloxUI();
                                stage.innerHTML = `<div class="minigame-stage" style="color:#4ade80;">🎉 آفرین! برنده شدی و <strong>${game.rewardMax} BitBlox</strong> جایزه گرفتی!</div>`;
                                myRig.triggerEmote('jump');
                            } else {
                                stage.innerHTML = `<div class="minigame-stage" style="color:#f87171;">😅 اشتباه بود! دوباره تلاش کن.</div>`;
                            }
                        };
                    });
                };
            });

            panel.classList.add('open');
        }

        function openAmusementParkPanel() {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '🎡 شهربازی و غرفه‌های جایزه‌دار';

            body.innerHTML = `
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div class="shop-card" style="text-align:right;">
                        <h4>🎯 غرفه پرتاب دارت و توپ در هدف</h4>
                        <div class="price">هزینه بازی: 10 💎 BitBlox | جایزه مرکز هدف: 25 💎</div>
                        <button class="btn-action" id="btn-game-darts" style="width:100%;">پرتاب دارت (10 💎)</button>
                    </div>
                    <div class="shop-card" style="text-align:right;">
                        <h4>🥤 غرفه انداختن برج لیوان‌ها</h4>
                        <div class="price">هزینه بازی: 10 💎 BitBlox | جایزه: 22 💎</div>
                        <button class="btn-action btn-secondary" id="btn-game-cups" style="width:100%;">پرتاب توپ به لیوان‌ها (10 💎)</button>
                    </div>
                    <div class="shop-card" style="text-align:right;">
                        <h4>🎰 گردونه شانس رنگارنگ</h4>
                        <div class="price">هزینه چرخش: 15 💎 BitBlox | جوایز: 0 تا 50 💎</div>
                        <button class="btn-action btn-amber" id="btn-game-wheel" style="width:100%;">چرخاندن گردونه شانس (15 💎)</button>
                    </div>
                </div>
                <div id="park-game-area"></div>
            `;

            const area = document.getElementById('park-game-area');

            document.getElementById('btn-game-darts').onclick = async () => {
                const res = await bridge.walletTransaction(-10, 'park_darts');
                if (!res.success) return alert(res.message);
                playerState = res.state;
                refreshBitBloxUI();

                area.innerHTML = `
                    <div class="minigame-stage">
                        <div style="font-weight:700;margin-bottom:6px;">🎯 وقتی نشانگر روی منطقه سبز وسط قرار گرفت دکمه پرتاب را بزن!</div>
                        <div style="width:100%;height:24px;background:#334155;border-radius:12px;position:relative;overflow:hidden;margin:10px 0;">
                            <div style="position:absolute;left:40%;width:20%;height:100%;background:#10b981;"></div>
                            <div id="dart-cursor" style="position:absolute;left:0%;width:10px;height:100%;background:#facc15;"></div>
                        </div>
                        <button class="btn-action btn-danger" id="btn-throw-dart-now">🎯 پرتاب کن!</button>
                    </div>
                `;
                let pos = 0, dir = 2.8;
                const timer = setInterval(() => {
                    pos += dir;
                    if (pos >= 96 || pos <= 0) dir *= -1;
                    const cur = document.getElementById('dart-cursor');
                    if (cur) cur.style.left = pos + '%';
                    else clearInterval(timer);
                }, 20);

                document.getElementById('btn-throw-dart-now').onclick = async () => {
                    clearInterval(timer);
                    if (pos >= 38 && pos <= 60) {
                        const win = await bridge.walletTransaction(25, 'win_darts');
                        playerState = win.state;
                        refreshBitBloxUI();
                        area.innerHTML = `<div class="minigame-stage" style="color:#4ade80;">🎯 زد به هدف! <strong>25 BitBlox</strong> برنده شدی!</div>`;
                        myRig.say('زدم به مرکز هدف! 🎯💎');
                        myRig.triggerEmote('jump');
                    } else {
                        area.innerHTML = `<div class="minigame-stage" style="color:#f87171;">نزدیک بود! دوباره امتحان کن.</div>`;
                    }
                };
            };

            document.getElementById('btn-game-cups').onclick = async () => {
                const res = await bridge.walletTransaction(-10, 'park_cups');
                if (!res.success) return alert(res.message);
                playerState = res.state;
                refreshBitBloxUI();

                const knocked = Math.floor(Math.random() * 4) + 3;
                if (knocked >= 5) {
                    const win = await bridge.walletTransaction(22, 'win_cups');
                    playerState = win.state;
                    refreshBitBloxUI();
                    area.innerHTML = `<div class="minigame-stage" style="color:#4ade80;">🥤 عالی! ${knocked} لیوان رو انداختی و <strong>22 BitBlox</strong> برنده شدی!</div>`;
                    myRig.triggerEmote('clap');
                } else {
                    area.innerHTML = `<div class="minigame-stage" style="color:#fbbf24;">🥤 ${knocked} لیوان افتاد (برای جایزه حداقل ۵ لیوان باید بیفته).</div>`;
                }
            };

            document.getElementById('btn-game-wheel').onclick = async () => {
                const res = await bridge.walletTransaction(-15, 'park_wheel');
                if (!res.success) return alert(res.message);
                playerState = res.state;
                refreshBitBloxUI();

                const prizes = [0, 10, 15, 25, 40, 50];
                const won = prizes[Math.floor(Math.random() * prizes.length)];
                if (won > 0) {
                    const win = await bridge.walletTransaction(won, 'win_wheel');
                    playerState = win.state;
                    refreshBitBloxUI();
                    area.innerHTML = `<div class="minigame-stage" style="color:#38bdf8;">🎰 گردونه روی <strong>${won} BitBlox</strong> ایستاد! مبارکت باشه!</div>`;
                    myRig.say(`تو گردونه شانس ${won} BitBlox بردم! 🎰💎`);
                    myRig.triggerEmote('jump');
                } else {
                    area.innerHTML = `<div class="minigame-stage" style="color:#cbd5e1;">🎰 این بار پوچ شد! شانس بعدی رو امتحان کن.</div>`;
                }
            };

            panel.classList.add('open');
        }

        const COLOR_PRESETS = {
            skin:  [0xffdbac, 0xf1c27d, 0xe0ac69, 0xc68642, 0x8d5524],
            hair:  [0x1c1917, 0x451a03, 0x78350f, 0xd97706, 0xfacc15, 0xb91c1c, 0x7c3aed],
            shirt: [0x3b82f6, 0xec4899, 0x10b981, 0xf59e0b, 0x8b5cf6, 0xef4444, 0xf8fafc, 0x1e293b],
            pants: [0x1e293b, 0x334155, 0x1d4ed8, 0x78350f, 0x4b5563, 0x0f172a],
            shoes: [0xf8fafc, 0x0f172a, 0xef4444, 0xf59e0b, 0x3b82f6, 0xec4899]
        };

        function hexNumToCss(num) { return '#' + num.toString(16).padStart(6, '0'); }

        function openInitialCharacterCreator() {
            if (playerState.freeCasUsed) {
                return alert('ساخت اولیه کاراکتر قبلاً انجام شده! برای تغییر مو به آرایشگاه و برای لباس و کفش جدید به فروشگاه شهر برو.');
            }
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '👤 ساخت اولیه کاراکتر (۱ بار رایگان)';

            const sections = [
                { key: 'skinColor',  label: '🧑 رنگ پوست',  swatches: COLOR_PRESETS.skin },
                { key: 'hairColor',  label: '💇 رنگ مو',    swatches: COLOR_PRESETS.hair },
                { key: 'shirtColor', label: '👕 رنگ لباس',  swatches: COLOR_PRESETS.shirt },
                { key: 'pantsColor', label: '👖 رنگ شلوار', swatches: COLOR_PRESETS.pants },
                { key: 'shoesColor', label: '👟 رنگ کفش',   swatches: COLOR_PRESETS.shoes }
            ];

            let html = `
                <div style="font-size:0.78rem;color:#b45309;background:#fef3c7;padding:6px 10px;border-radius:8px;margin-bottom:8px;">
                    ⚠️ توجه: این تنظیم اولیه رایگان است. پس از تایید، برای تغییرات بعدی باید به آرایشگاه و فروشگاه‌های شهر بروی.
                </div>
                <div class="cas-group">
                    <div class="cas-group-title"><span>🏷️ نام کاراکتر:</span></div>
                    <input type="text" id="cas-name-input" value="${playerState.name}" style="width:100%;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;font-size:0.84rem;">
                </div>
            `;

            sections.forEach(sec => {
                const currentHex = playerState.appearance[sec.key] || 0xffffff;
                const swatchesHtml = sec.swatches.map(hex => {
                    const css = hexNumToCss(hex);
                    return `<div class="color-swatch ${hex === currentHex ? 'selected' : ''}" data-key="${sec.key}" data-hex="${hex}" style="background:${css};"></div>`;
                }).join('');
                html += `
                    <div class="cas-group">
                        <div class="cas-group-title"><span>${sec.label}</span></div>
                        <div class="swatch-row">${swatchesHtml}</div>
                    </div>
                `;
            });

            html += `<button class="btn-action" id="btn-save-initial-cas" style="width:100%;padding:9px;margin-top:4px;">✅ تایید و قفل کردن کاراکتر پایه</button>`;
            body.innerHTML = html;

            body.querySelectorAll('.color-swatch').forEach(el => {
                el.onclick = () => {
                    playerState.appearance[el.dataset.key] = Number(el.dataset.hex);
                    myRig.applyAppearance(playerState.appearance);
                    openInitialCharacterCreator();
                };
            });

            document.getElementById('btn-save-initial-cas').onclick = async () => {
                const newName = document.getElementById('cas-name-input').value.trim() || playerState.name;
                const res = await bridge.saveInitialCharacter(playerState.appearance, newName);
                if (!res.success) return alert(res.message);
                playerState = res.state;
                document.getElementById('player-name-display').textContent = playerState.name;
                document.getElementById('btn-open-cas').style.display = 'none';
                myRig.setName(playerState.name);
                myRig.say('کاراکتر من ساخته شد! 🎉');
                panel.classList.remove('open');
            };

            panel.classList.add('open');
        }

        document.getElementById('btn-open-cas').onclick = openInitialCharacterCreator;

        async function returnFurnitureToInventory(placedIndex) {
            const removed = playerState.homeLayout.splice(placedIndex, 1)[0];
            if (!removed) return;
            playerState.furnitureInventory.push(removed.itemId);
            renderHomeFurniture(playerState.homeLayout);
            playerState = await bridge.updateHomeAndInventory(playerState.homeLayout, playerState.furnitureInventory);
        }

        function openHomeInventoryAndRemovePanel() {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '🛋️ چیدمان و مدیریت وسایل خانه';

            let invHtml = `<h4 style="font-size:0.84rem;margin-bottom:6px;color:#334155;">📦 وسایل موجود در انبار:</h4>`;
            if (playerState.furnitureInventory.length === 0) {
                invHtml += `<div style="font-size:0.8rem;color:#64748b;margin-bottom:12px;">انبار خالی است! از فروشگاه مبلمان بخرید یا وسایل اتاق را به انبار برگردانید.</div>`;
            } else {
                invHtml += `<div class="item-grid" style="margin-bottom:14px;">`;
                playerState.furnitureInventory.forEach(itemId => {
                    const def = CATALOG.shop_furniture.find(f => f.id === itemId);
                    if (!def) return;
                    invHtml += `
                        <div class="shop-card">
                            <h4>${def.name}</h4>
                            <button class="btn-action btn-secondary btn-place-item" data-id="${def.id}" style="width:100%;padding:5px;margin-top:6px;">قرار دادن در اتاق</button>
                        </div>
                    `;
                });
                invHtml += `</div>`;
            }

            invHtml += `<h4 style="font-size:0.84rem;margin-bottom:6px;color:#b91c1c;border-top:1px solid #e2e8f0;padding-top:10px;">🗑️ وسایل چیده‌شده در اتاق:</h4>`;
            if (playerState.homeLayout.length === 0) {
                invHtml += `<div style="font-size:0.8rem;color:#64748b;">هنوز وسیله‌ای چیده نشده است.</div>`;
            } else {
                invHtml += `<div style="display:flex;flex-direction:column;gap:6px;">`;
                playerState.homeLayout.forEach((placed, idx) => {
                    const def = CATALOG.shop_furniture.find(f => f.id === placed.itemId);
                    invHtml += `
                        <div style="display:flex;justify-content:space-between;align-items:center;background:#fef2f2;border:1px solid #fecaca;padding:6px 10px;border-radius:10px;font-size:0.8rem;">
                            <span>${def ? def.name : placed.itemId}</span>
                            <button class="btn-action btn-danger btn-remove-placed" data-idx="${idx}" style="padding:4px 9px;font-size:0.75rem;">حذف به انبار</button>
                        </div>
                    `;
                });
                invHtml += `</div>`;
            }

            body.innerHTML = invHtml;

            body.querySelectorAll('.btn-place-item').forEach(btn => {
                btn.onclick = () => {
                    const def = CATALOG.shop_furniture.find(f => f.id === btn.dataset.id);
                    if (def) {
                        startPlacingFurniture(def);
                        panel.classList.remove('open');
                    }
                };
            });

            body.querySelectorAll('.btn-remove-placed').forEach(btn => {
                btn.onclick = async () => {
                    await returnFurnitureToInventory(Number(btn.dataset.idx));
                    openHomeInventoryAndRemovePanel();
                };
            });

            panel.classList.add('open');
        }

        document.getElementById('btn-toggle-inventory').onclick = openHomeInventoryAndRemovePanel;

        function updateCartUI() {
            document.getElementById('cart-count').textContent = shoppingCart.length;
            const listEl = document.getElementById('cart-items-list');
            const totalEl = document.getElementById('cart-total');

            if (shoppingCart.length === 0) {
                listEl.innerHTML = '<div style="color:#64748b;text-align:center;padding:6px;">سبد خرید خالی است</div>';
                totalEl.textContent = '0';
                return;
            }

            let total = 0;
            listEl.innerHTML = '';
            shoppingCart.forEach((item, index) => {
                total += item.price;
                const row = document.createElement('div');
                row.className = 'cart-row';
                row.innerHTML = `
                    <span>${item.name} (${item.price} 💎)</span>
                    <button style="border:none;background:#fee2e2;color:#dc2626;border-radius:6px;padding:1px 6px;cursor:pointer;">حذف</button>
                `;
                row.querySelector('button').onclick = () => {
                    shoppingCart.splice(index, 1);
                    updateCartUI();
                };
                listEl.appendChild(row);
            });
            totalEl.textContent = total;
        }

        document.getElementById('btn-checkout-cart').onclick = async () => {
            if (shoppingCart.length === 0) return;
            const res = await bridge.checkoutCart(shoppingCart);
            if (!res.success) return alert(res.message);
            const count = shoppingCart.length;
            shoppingCart = [];
            updateCartUI();

            playerState = res.state;
            refreshBitBloxUI();
            myRig.applyAppearance(playerState.appearance);
            myRig.say(`ایول! ${count} آیتم با BitBlox خریدم 🎉`);
            myRig.triggerEmote('jump');
        };

        function openShopPanel(shopZone) {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'block';

            const titles = {
                shop_clothes: '👗 فروشگاه لباس و کفش',
                shop_furniture: '🛋️ فروشگاه مبلمان خانه',
                shop_accessories: '⚔️ لوازم جانبی دست'
            };
            title.textContent = titles[shopZone] || 'فروشگاه';

            let gridHtml = `<div class="item-grid">`;
            CATALOG[shopZone].forEach(item => {
                gridHtml += `
                    <div class="shop-card">
                        <h4>${item.name}</h4>
                        <div class="price">💎 ${item.price} BitBlox</div>
                        <button class="btn-action btn-secondary btn-add-cart" data-id="${item.id}" style="width:100%;padding:5px;">➕ افزودن به سبد</button>
                    </div>
                `;
            });
            gridHtml += `</div>`;
            body.innerHTML = gridHtml;

            body.querySelectorAll('.btn-add-cart').forEach(btn => {
                btn.onclick = () => {
                    const item = CATALOG[shopZone].find(i => i.id === btn.dataset.id);
                    if (item) {
                        shoppingCart.push(item);
                        updateCartUI();
                    }
                };
            });

            updateCartUI();
            panel.classList.add('open');
        }

        document.getElementById('btn-open-cart').onclick = () => {
            const panel = document.getElementById('side-panel');
            document.getElementById('panel-title').textContent = '🛒 سبد خرید BitBlox';
            document.getElementById('panel-body').innerHTML = '<div style="font-size:0.8rem;color:#475569;">آیتم‌های انتخابی از فروشگاه‌های شهر در سبد شما ذخیره می‌شوند.</div>';
            document.getElementById('cart-section').style.display = 'block';
            updateCartUI();
            panel.classList.add('open');
        };

        function openWardrobePanel() {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '👗 کمد لباس، کفش و وسایل من';

            let html = `<div class="item-grid">`;
            playerState.wardrobe.forEach(itemId => {
                const def = ALL_ITEMS_FLAT.find(i => i.id === itemId);
                if (!def) return;
                const isEquipped =
                    (def.type === 'hat' && playerState.appearance.hat === def.id) ||
                    (def.type === 'handProp' && playerState.appearance.handProp === def.id) ||
                    (def.type === 'shirt' && playerState.appearance.shirtColor === def.color) ||
                    (def.type === 'shoes' && playerState.appearance.shoesColor === def.color);

                html += `
                    <div class="shop-card">
                        <h4>${def.name}</h4>
                        <button class="btn-action ${isEquipped ? 'btn-amber' : 'btn-secondary'} btn-equip-wardrobe" data-id="${def.id}" style="width:100%;padding:5px;margin-top:6px;">
                            ${isEquipped ? '✔ در حال استفاده' : 'پوشیدن'}
                        </button>
                    </div>
                `;
            });
            html += `</div>`;
            body.innerHTML = html;

            body.querySelectorAll('.btn-equip-wardrobe').forEach(btn => {
                btn.onclick = async () => {
                    const def = ALL_ITEMS_FLAT.find(i => i.id === btn.dataset.id);
                    if (def) {
                        playerState = await bridge.toggleWardrobeItem(def);
                        myRig.applyAppearance(playerState.appearance);
                        openWardrobePanel();
                    }
                };
            });
            panel.classList.add('open');
        }

        document.getElementById('btn-open-wardrobe').onclick = openWardrobePanel;

        document.getElementById('btn-neighbors').onclick = async () => {
            const panel = document.getElementById('side-panel');
            const title = document.getElementById('panel-title');
            const body = document.getElementById('panel-body');
            document.getElementById('cart-section').style.display = 'none';
            title.textContent = '🏘️ خانه‌های شهروندان';

            const plots = await bridge.getResidentialPlots();
            let html = `<div class="item-grid">`;
            plots.forEach(plot => {
                html += `
                    <div class="shop-card">
                        <h4>🏠 ${plot.ownerName}</h4>
                        <button class="btn-action btn-secondary btn-visit-house" data-owner="${plot.ownerId}" style="width:100%;padding:6px;margin-top:8px;">ورود به خانه</button>
                    </div>
                `;
            });
            html += `</div>`;
            body.innerHTML = html;

            body.querySelectorAll('.btn-visit-house').forEach(btn => {
                btn.onclick = () => {
                    panel.classList.remove('open');
                    loadZone(`home_${btn.dataset.owner}`);
                };
            });
            panel.classList.add('open');
        };

        function startPlacingFurniture(itemDef) {
            placingItem = itemDef;
            placingRotation = 0;
            if (placingPreviewMesh) scene.remove(placingPreviewMesh);
            placingPreviewMesh = buildRealisticFurniture(itemDef.id);
            placingPreviewMesh.traverse(child => {
                if (child.isMesh) {
                    child.material = child.material.clone();
                    child.material.transparent = true;
                    child.material.opacity = 0.65;
                }
            });
            scene.add(placingPreviewMesh);
            document.getElementById('home-edit-banner').style.display = 'flex';
        }

        function cancelPlacingFurniture() {
            placingItem = null;
            if (placingPreviewMesh) {
                scene.remove(placingPreviewMesh);
                placingPreviewMesh = null;
            }
            document.getElementById('home-edit-banner').style.display = 'none';
        }

        document.getElementById('btn-rotate-furniture').onclick = () => {
            placingRotation += Math.PI / 2;
            if (placingPreviewMesh) placingPreviewMesh.rotation.y = placingRotation;
        };
        document.getElementById('btn-cancel-place').onclick = cancelPlacingFurniture;

        /**
         * ============================================================================
         * 6. کنترل حرکت و حلقه انیمیشن
         * ============================================================================
         */
        const raycaster = new THREE.Raycaster();
        const pointer = new THREE.Vector2();

        function handleInteractableClick(hitObj) {
            let obj = hitObj;
            while (obj) {
                if (obj.userData && obj.userData.action === 'tv_studio') {
                    openTvStudioPanel(obj.userData.homeData);
                    return true;
                }
                if (obj.userData && obj.userData.action === 'wardrobe') {
                    openWardrobePanel();
                    return true;
                }
                obj = obj.parent;
            }
            if (currentZone === 'hair_salon') { openHairSalonPanel(); return true; }
            if (currentZone === 'cinema') { openCinemaPanel(); return true; }
            if (currentZone === 'gamenet') { openGameNetPanel(); return true; }
            if (currentZone === 'amusement_park') { openAmusementParkPanel(); return true; }
            if (currentZone.startsWith('shop_')) { openShopPanel(currentZone); return true; }
            return false;
        }

        function getGroundIntersection(clientX, clientY) {
            pointer.x = (clientX / window.innerWidth) * 2 - 1;
            pointer.y = -(clientY / window.innerHeight) * 2 + 1;
            raycaster.setFromCamera(pointer, camera);

            if (interactables.length > 0) {
                const hits = raycaster.intersectObjects(interactables, true);
                if (hits.length > 0 && handleInteractableClick(hits[0].object)) {
                    return null;
                }
            }

            if (!groundMesh) return null;
            const hits = raycaster.intersectObject(groundMesh);
            return hits.length > 0 ? hits[0].point : null;
        }

        renderer.domElement.addEventListener('pointermove', (e) => {
            if (!placingItem || !placingPreviewMesh) return;
            const pt = getGroundIntersection(e.clientX, e.clientY);
            if (pt) {
                placingPreviewMesh.position.x = Math.round(pt.x);
                placingPreviewMesh.position.z = Math.round(pt.z);
            }
        });

        renderer.domElement.addEventListener('pointerdown', async (e) => {
            if (e.button !== 0 && e.pointerType === 'mouse') return;
            const pt = getGroundIntersection(e.clientX, e.clientY);
            if (!pt) return;

            if (placingItem && placingPreviewMesh) {
                const snapX = Math.round(pt.x);
                const snapZ = Math.round(pt.z);
                const invIdx = playerState.furnitureInventory.indexOf(placingItem.id);
                if (invIdx > -1) playerState.furnitureInventory.splice(invIdx, 1);

                playerState.homeLayout.push({
                    uid: 'f_' + Date.now(),
                    itemId: placingItem.id,
                    x: snapX,
                    z: snapZ,
                    rotY: placingRotation
                });
                renderHomeFurniture(playerState.homeLayout, { isMine: true });
                playerState = await bridge.updateHomeAndInventory(playerState.homeLayout, playerState.furnitureInventory);
                cancelPlacingFurniture();
                return;
            }

            myRig.targetPos.set(pt.x, 0, pt.z);
            targetRing.position.set(pt.x, 0.04, pt.z);
            targetRing.visible = true;
        });

        let pendingEmote = null;
        let pendingChat = '';

        document.querySelectorAll('.emote-btn').forEach(btn => {
            btn.onclick = () => {
                const emote = btn.dataset.emote;
                const text = btn.dataset.text;
                myRig.triggerEmote(emote);
                myRig.say(text);
                pendingEmote = emote;
                pendingChat = text;
                syncWithServer();
            };
        });

        document.getElementById('portal-prompt').onclick = () => {
            if (activePortal) loadZone(activePortal.targetZone);
        };
        document.getElementById('btn-go-my-home').onclick = () => loadZone(`home_${playerState.userId}`);
        document.getElementById('btn-go-plaza').onclick = () => loadZone('plaza');

        async function syncWithServer() {
            if (!myRig) return;
            const payload = {
                zone: currentZone,
                x: Number(myRig.mesh.position.x.toFixed(2)),
                z: Number(myRig.mesh.position.z.toFixed(2)),
                emote: pendingEmote,
                chat: pendingChat
            };
            pendingEmote = null;
            pendingChat = '';

            const others = await bridge.syncWorld(payload);
            const activeIds = new Set();

            others.forEach(p => {
                activeIds.add(p.id);
                let rig = remoteRigs.get(p.id);
                if (!rig) {
                    rig = createCharacterRig(p.name, p.appearance);
                    rig.mesh.position.set(p.x, 0, p.z);
                    rig.targetPos.set(p.x, 0, p.z);
                    remotePlayersGroup.add(rig.mesh);
                    remoteRigs.set(p.id, rig);
                } else {
                    rig.targetPos.set(p.x, 0, p.z);
                }
                if (p.emote) rig.triggerEmote(p.emote);
                if (p.chat) rig.say(p.chat);
            });

            for (const [id, rig] of remoteRigs.entries()) {
                if (!activeIds.has(id)) {
                    remotePlayersGroup.remove(rig.mesh);
                    rig.labelEl.remove();
                    remoteRigs.delete(id);
                }
            }
        }

        function checkObstacleCollision(nextX, nextZ) {
            for (const box of obstacles) {
                if (nextX > box.minX && nextX < box.maxX && nextZ > box.minZ && nextZ < box.maxZ) {
                    return true;
                }
            }
            return false;
        }

        function updateCharacterAnimation(rig, dt, time, isLocal) {
            const pos = rig.mesh.position;
            const dist = pos.distanceTo(rig.targetPos);

            if (dist > 0.12) {
                const dir = new THREE.Vector3().subVectors(rig.targetPos, pos).normalize();
                const speed = 5.5 * dt;
                const nextX = pos.x + dir.x * speed;
                const nextZ = pos.z + dir.z * speed;

                if (!isLocal || !checkObstacleCollision(nextX, nextZ)) {
                    pos.x = nextX;
                    pos.z = nextZ;
                } else {
                    rig.targetPos.copy(pos);
                    if (isLocal) targetRing.visible = false;
                }

                const targetAngle = Math.atan2(dir.x, dir.z);
                const targetQuat = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 1, 0), targetAngle);
                rig.mesh.quaternion.slerp(targetQuat, 10 * dt);

                const swing = Math.sin(time * 11) * 0.65;
                rig.parts.leftLegPivot.rotation.x = swing;
                rig.parts.rightLegPivot.rotation.x = -swing;
                rig.parts.leftArmPivot.rotation.x = -swing * 0.8;
                if (!rig.emote) rig.parts.rightArmPivot.rotation.x = swing * 0.8;
            } else {
                if (isLocal && targetRing.visible) targetRing.visible = false;
                rig.parts.leftLegPivot.rotation.x *= 0.8;
                rig.parts.rightLegPivot.rotation.x *= 0.8;
                if (!rig.emote) {
                    rig.parts.leftArmPivot.rotation.x *= 0.8;
                    rig.parts.rightArmPivot.rotation.x *= 0.8;
                }
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

            const headWorldPos = new THREE.Vector3(pos.x, pos.y + 2.65, pos.z);
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

        const clock = new THREE.Clock();

        function animate() {
            requestAnimationFrame(animate);
            const dt = Math.min(clock.getDelta(), 0.1);
            const elapsed = clock.getElapsedTime();

            // انیمیشن چرخش چرخ‌وفلک و نمادهای کنار در مغازه‌ها
            animatedLandmarks.forEach(item => {
                if (item.type === 'spinZ') item.mesh.rotation.z += item.speed * dt;
                if (item.type === 'spinY') item.mesh.rotation.y += item.speed * dt;
                if (item.type === 'spinAndBob') {
                    item.mesh.rotation.y += item.speed * dt;
                    item.mesh.position.y = item.baseY + Math.sin(elapsed * 3) * 0.12;
                }
            });

            if (weatherParticles.visible) {
                const posArr = weatherParticles.geometry.attributes.position.array;
                const fallSpeed = currentWeatherMode === 'rain' ? 28 * dt : 6 * dt;
                for (let i = 0; i < PARTICLE_COUNT; i++) {
                    posArr[i * 3 + 1] -= fallSpeed;
                    if (currentWeatherMode === 'snow') posArr[i * 3] += Math.sin(elapsed + i) * 0.02;
                    if (posArr[i * 3 + 1] < 0) posArr[i * 3 + 1] = 35;
                }
                weatherParticles.geometry.attributes.position.needsUpdate = true;
            }

            if (myRig) {
                updateCharacterAnimation(myRig, dt, elapsed, true);

                const desiredCamPos = new THREE.Vector3(
                    myRig.mesh.position.x,
                    myRig.mesh.position.y + 13.5,
                    myRig.mesh.position.z + 16.5
                );
                camera.position.lerp(desiredCamPos, 4 * dt);
                camera.lookAt(myRig.mesh.position.x, myRig.mesh.position.y + 1.2, myRig.mesh.position.z);

                let foundPortal = null;
                for (const p of portals) {
                    const d = Math.hypot(myRig.mesh.position.x - p.x, myRig.mesh.position.z - p.z);
                    if (d < 2.2) {
                        foundPortal = p;
                        break;
                    }
                }
                const promptBtn = document.getElementById('portal-prompt');
                if (foundPortal && foundPortal !== activePortal) {
                    activePortal = foundPortal;
                    promptBtn.textContent = foundPortal.promptLabel;
                    promptBtn.classList.add('visible');
                } else if (!foundPortal && activePortal) {
                    activePortal = null;
                    promptBtn.classList.remove('visible');
                }
            }

            remoteRigs.forEach(rig => updateCharacterAnimation(rig, dt, elapsed, false));
            renderer.render(scene, camera);
        }

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        (async function init() {
            playerState = await bridge.getCharacter();
            document.getElementById('player-name-display').textContent = playerState.name;
            refreshBitBloxUI();

            myRig = createCharacterRig(playerState.name, playerState.appearance);
            scene.add(myRig.mesh);

            await loadZone('plaza');
            animate();

            setInterval(syncWithServer, METAVERSE_CONFIG.syncIntervalMs);
        })();
    </script>