<?php
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
$mediaBase = Uri::root() . 'media/com_bloxia/';
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Bloxia</title>
    <link rel="stylesheet" href="css/game-hud.css">
    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.160.0/build/three.module.js"
            }
        }
    </script>
    <link rel="stylesheet" href="<?php echo $mediaBase; ?>css/game-hud.css">
</head>
<body>
    <div id="canvas-container"></div>
    <div id="labels-layer"></div>
    <div id="fade-overlay">در حال ورود...</div>

    <!-- بالا-چپ: پروفایل بازیکن + کنترل آیکونی شب/روز و آب‌وهوا -->
    <div class="hud-top-left">
        <div class="coc-badge-box profile-box">
            <div class="level-star">⭐</div>
            <div class="profile-info">
                <strong id="player-name-display">کاراکتر من</strong>
                <span id="zone-title-display">🏙️ میدان اصلی شهر</span>
            </div>
        </div>
        <div class="env-icon-group">
            <button class="coc-mini-btn" id="btn-cycle-time" title="تغییر شب و روز">☀️</button>
            <button class="coc-mini-btn" id="btn-cycle-weather" title="تغییر آب‌وهوا و فصل">🌤️</button>
        </div>
    </div>

    <!-- بالا-راست: نوار منابع BitBlox + سبد خرید -->
    <div class="hud-top-right">
        <div class="coc-resource-bar">
            <span class="res-value" id="bitblox-display">500</span>
            <span class="res-label">BitBlox</span>
            <div class="res-icon gem-bg">💎</div>
        </div>
        <button class="coc-icon-btn sm-btn" id="btn-open-cart" title="سبد خرید">
            <span class="ico">🛒</span>
            <span class="coc-notif" id="cart-count">0</span>
        </button>
    </div>

    <!-- ستون آیکونی سمت چپ: چت امن، چالش بازی دونفره، همسایه‌ها -->
    <div class="hud-side-left">
        <button class="coc-icon-btn" id="btn-open-chatroom" title="چت‌روم امن">
            <span class="ico">💬</span>
            <span class="lbl">چت</span>
        </button>
        <button class="coc-icon-btn battle-style" id="btn-pvp-challenge" title="چالش بازی دونفره">
            <span class="ico">⚔️</span>
            <span class="lbl">بازی</span>
        </button>
        <button class="coc-icon-btn" id="btn-neighbors" title="محله و همسایه‌ها">
            <span class="ico">🏘️</span>
            <span class="lbl">محله</span>
        </button>
    </div>

    <!-- ستون آیکونی سمت راست: خانه من، کاراکتر من، کمد لباس، چیدمان -->
    <div class="hud-side-right">
        <button class="coc-icon-btn home-style" id="btn-go-my-home" title="خانه من">
            <span class="ico">🏠</span>
            <span class="lbl">خانه من</span>
        </button>
        <button class="coc-icon-btn plaza-style" id="btn-go-plaza" style="display:none;" title="بازگشت به شهر">
            <span class="ico">🏙️</span>
            <span class="lbl">شهر</span>
        </button>
        <button class="coc-icon-btn char-style" id="btn-open-cas" title="اصلاح کاراکتر من">
            <span class="ico">👤</span>
            <span class="lbl">کاراکتر</span>
        </button>
        <button class="coc-icon-btn" id="btn-open-wardrobe" style="display:none;" title="کمد لباس">
            <span class="ico">👗</span>
            <span class="lbl">کمد</span>
        </button>
        <button class="coc-icon-btn" id="btn-toggle-inventory" style="display:none;" title="چیدمان خانه">
            <span class="ico">🛋️</span>
            <span class="lbl">چیدمان</span>
        </button>
    </div>

    <!-- پایین-چپ: دکمه بزرگ آیکونی جابه‌جایی سریع بین مکان‌های شهر -->
    <button class="coc-big-corner-btn bottom-left" id="btn-city-directory">
        <span class="big-ico">🗺️</span>
        <span class="big-lbl">مکان‌های شهر</span>
    </button>

    <!-- پایین-وسط: نوار ایموت‌های آیکونی سریع -->
    <div class="hud-bottom-center">
        <button class="coc-emote-btn" data-emote="wave" data-text="سلام به همگی! 👋">👋</button>
        <button class="coc-emote-btn" data-emote="clap" data-text="آفرین! عالی بود 👏">👏</button>
        <button class="coc-emote-btn" data-emote="jump" data-text="هورا! چقدر عالی 🎉">🎉</button>
        <button class="coc-emote-btn" data-emote="prop_show" data-text="ببینید چی دستمه! ✨">✨</button>
        <button class="coc-emote-btn" data-emote="invite" data-text="بیایید بازی کنیم! 🎲">🎲</button>
    </div>

    <!-- پایین-راست: مینی‌مپ راداری شیشه‌ای -->
    <div id="minimap-container">
        <div class="minimap-title">🗺️ نقشه</div>
        <canvas id="minimap-canvas" width="210" height="210"></canvas>
    </div>

    <!-- دکمه ورود هنگام نزدیک شدن به در ساختمان -->
    <button id="portal-prompt">🚪 ورود</button>

    <!-- بنر حالت چیدمان مبلمان در خانه -->
    <div id="home-edit-banner">
        <span>🛋️ روی کف اتاق کلیک کن</span>
        <button class="coc-mini-btn" id="btn-rotate-furniture">🔄</button>
        <button class="coc-mini-btn danger" id="btn-cancel-place">✖</button>
    </div>

    <!-- کارت تعامل سریع هنگام کلیک روی بازیکن دیگر -->
    <div id="player-interact-card" class="coc-modal">
        <div class="modal-header">
            <h3 id="interact-player-name">بازیکن</h3>
            <button class="close-btn" id="btn-close-interact">✖</button>
        </div>
        <div class="direct-actions-grid">
            <button class="coc-action-btn cyan" id="btn-direct-chat">💬 چت امن</button>
            <button class="coc-action-btn pink" id="btn-direct-pvp">⚔️ دعوت به بازی</button>
            <button class="coc-action-btn amber" id="btn-direct-home">🏠 بازدید از خانه</button>
        </div>
    </div>

    <!-- پنجره مودال مرکزی/کناری برای پنل‌ها -->
    <div class="coc-modal main-panel" id="side-panel">
        <div class="modal-header">
            <h3 id="panel-title">پنل</h3>
            <button class="close-btn" id="btn-close-panel">✖</button>
        </div>
        <div id="panel-body"></div>
        <div class="cart-box" id="cart-section" style="display:none;">
            <div class="cart-summary">
                <span>🛒 سبد خرید</span>
                <span>جمع: <strong id="cart-total">0</strong> 💎</span>
            </div>
            <div class="cart-items-list" id="cart-items-list"></div>
            <button class="coc-action-btn green" id="btn-checkout-cart" style="width:100%;">💳 پرداخت با BitBlox</button>
        </div>
    </div>

    <script type="module" src="js/main.js"></script>
    <script>window.BLOXIA_CSRF_TOKEN = '<?php echo Session::getFormToken(); ?>';</script>
    <script type="module" src="<?php echo $mediaBase; ?>js/main.js"></script>
</body>
</html>