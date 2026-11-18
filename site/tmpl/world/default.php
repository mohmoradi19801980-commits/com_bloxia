<?php
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;

$baseUri = Uri::root();
?>

<!-- فراخوانی Three.js از هاست خودت (برای کارکرد آفلاین و بدون قطعی) -->
<script type="importmap">
    {
        "imports": {
            "three": "<?php echo $baseUri; ?>media/com_bloxia/js/three/three.module.js"
        }
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;800;900&display=swap');

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Vazirmatn', Tahoma, sans-serif;
        user-select: none;
    }

    body, html {
        width: 100%;
        height: 100%;
        overflow: hidden;
        background: #0f172a;
    }

    /* --- بقیه کدهای CSS فایلت را دقیقاً از اینجا به بعد پیست کن --- */
    #canvas-container { width: 100%; height: 100%; position: relative; }
    #fade-overlay { position: fixed; inset: 0; background: #0f172a; opacity: 0; pointer-events: none; transition: opacity 0.4s ease; z-index: 100; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3rem; font-weight: bold; }
    #fade-overlay.active { opacity: 1; pointer-events: all; }
    
    /* برای جلوگیری از شلوغی این پیام، مابقی CSS آخرین فایلت را تا قبل از </style> اینجا قرار بده */
    
    @keyframes popIn {
        from { opacity: 0; transform: scale(0.85); }
        to { opacity: 1; transform: scale(1); }
    }
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

    // تزریق امنیتی جوملا برای جلوگیری از هک در ارتباطات AJAX
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
    // مسیرهای ارتباطی با جوملا
    const METAVERSE_CONFIG = {
        mockMode: true, // فعلا روی True می‌ماند تا ظاهر جدید را تست کنی
        syncIntervalMs: 2500,
        endpoints: {
            getCharacter:   'index.php?option=com_bloxia&task=metaverse.getCharacter&format=json',
            saveInitialCas: 'index.php?option=com_bloxia&task=metaverse.saveInitialCas&format=json',
            hairSalonBuy:   'index.php?option=com_bloxia&task=metaverse.hairSalon&format=json',
            submitGame:     'index.php?option=com_bloxia&task=metaverse.submitGame&format=json',
            spendOrReward:  'index.php?option=com_bloxia&task=metaverse.walletTx&format=json',
            syncWorld:      'index.php?option=com_bloxia&task=metaverse.sync&format=json',
            buyCart:        'index.php?option=com_bloxia&task=metaverse.buyCart&format=json',
            equipItem:      'index.php?option=com_bloxia&task=metaverse.equip&format=json',
            saveHome:       'index.php?option=com_bloxia&task=metaverse.saveHome&format=json',
            getHomeData:    'index.php?option=com_bloxia&task=metaverse.getHome&format=json'
        },
        csrfToken: window.Joomla.getOptions('csrf.token')
    };

     <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.160.0/build/three.module.js"
            }
        }
    </script>

    <script type="module">

</script>