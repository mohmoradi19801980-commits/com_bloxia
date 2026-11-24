<?php
defined('_JEXEC') or die;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
?>

<div class="bloxia-login-page">
    <div class="bloxia-login-container">
        <div class="bloxia-login-logo">💎</div>
        <h2>ورود به دنیای Bloxia</h2>
        <p class="bloxia-login-subtitle">شهر متاورسی ساخت بازی، دوستی و خلاقیت</p>

        <div id="step-phone">
            <p class="bloxia-login-label">📱 شماره موبایل خود را وارد کنید:</p>
            <input type="tel" id="mobile_number" placeholder="09123456789" class="input-large" autocomplete="tel" maxlength="11">
            <button type="button" id="btn-request-otp" class="bloxia-btn bloxia-btn-primary">📲 دریافت کد تایید</button>

            <div class="bloxia-divider">یا</div>

            <button type="button" id="btn-google-login" class="bloxia-btn bloxia-btn-google">🔐 ورود با اکانت گوگل</button>
        </div>

        <div id="step-otp" style="display: none;">
            <p class="bloxia-login-label">🔑 کد تایید ارسال شده به موبایل را وارد کنید:</p>
            <div id="mock-otp-display" class="bloxia-otp-badge"></div>

            <input type="text" id="otp_code" placeholder="کد ۴ رقمی" class="input-large" inputmode="numeric" maxlength="4">
            <button type="button" id="btn-verify-otp" class="bloxia-btn bloxia-btn-success">🚀 ورود به بازی</button>
            <button type="button" id="btn-back-phone" class="bloxia-btn-link">✏️ تغییر شماره موبایل</button>
        </div>

        <!-- توکن امنیتی جوملا برای جلوگیری از حملات CSRF -->
        <input type="hidden" id="csrf_token" name="<?php echo Session::getFormToken(); ?>" value="1">
        <!-- استفاده از فرمت خام و json برای جلوگیری از تداخل با SEF جوملا -->
        <input type="hidden" id="ajax_url" value="<?php echo Uri::root() . 'index.php?option=com_bloxia&format=json&task=auth.'; ?>">
    </div>
</div>
