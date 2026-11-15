<?php
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
?>

<div class="bloxia-login-container">
    <h2>ورود به دنیای Bloxia</h2>
    
    <div id="step-phone">
        <p>شماره موبایل خود را وارد کنید:</p>
        <input type="text" id="mobile_number" placeholder="09123456789" class="input-large">
        <button id="btn-request-otp" class="btn btn-primary">دریافت کد تایید</button>
        <hr>
        <button class="btn btn-secondary">ورود با اکانت گوگل</button>
    </div>

    <div id="step-otp" style="display: none;">
        <p>کد تایید ارسال شده به موبایل را وارد کنید:</p>
        <!-- چون در حالت تستی هستیم، کد را اینجا نمایش می‌دهیم -->
        <div id="mock-otp-display" style="color: green; font-weight: bold; margin-bottom: 10px;"></div>
        
        <input type="text" id="otp_code" placeholder="کد 4 رقمی" class="input-large">
        <button id="btn-verify-otp" class="btn btn-success">ورود</button>
    </div>

    <!-- توکن امنیتی جوملا برای جلوگیری از حملات CSRF -->
    <input type="hidden" id="csrf_token" name="<?php echo Session::getFormToken(); ?>" value="1">
    <input type="hidden" id="ajax_url" value="<?php echo Route::_('index.php?option=com_bloxia&task=auth.'); ?>">
</div>