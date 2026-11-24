<?php
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

$baseUrl = Uri::root();
$worldUrl = Route::_('index.php?option=com_bloxia&view=world');
$csrfToken = Session::getFormToken();
$s = $this->settings;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بانک Bloxia | شارژ حساب BitBlox</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        * { box-sizing: border-box; font-family: 'Vazirmatn', Tahoma, sans-serif; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 15% 15%, rgba(56,189,248,0.22), transparent 45%),
                        radial-gradient(circle at 85% 85%, rgba(245,158,11,0.18), transparent 45%),
                        linear-gradient(145deg, #0f172a 0%, #1e293b 55%, #0c4a6e 100%);
            color: #0f172a;
            padding: 20px 14px 40px;
        }
        .bank-wrap {
            max-width: 920px;
            margin: 0 auto;
        }
        .bank-topbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            background: rgba(15, 23, 42, 0.85);
            border: 2px solid #facc15;
            border-radius: 20px;
            padding: 14px 20px;
            color: #fff;
            margin-bottom: 20px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
        }
        .bank-brand { display: flex; align-items: center; gap: 10px; font-size: 1.25rem; font-weight: 900; }
        .bank-balance {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(250,204,21,0.5);
            padding: 6px 14px;
            border-radius: 14px;
            font-weight: 800;
            color: #fde047;
        }
        .btn-game {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 14px;
            border: none;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            transition: transform 0.1s, filter 0.15s;
        }
        .btn-game:active { transform: translateY(2px); }
        .btn-game:hover { filter: brightness(1.06); }
        .btn-cyan { background: linear-gradient(180deg, #38bdf8, #0284c7); border-bottom: 4px solid #0369a1; }
        .btn-green { background: linear-gradient(180deg, #4ade80, #16a34a); border-bottom: 4px solid #15803d; }
        .btn-amber { background: linear-gradient(180deg, #fbbf24, #d97706); border-bottom: 4px solid #b45309; color: #0f172a; }
        .btn-slate { background: linear-gradient(180deg, #64748b, #334155); border-bottom: 4px solid #1e293b; }

        .bank-card {
            background: rgba(255,255,255,0.97);
            border: 3px solid #facc15;
            border-radius: 24px;
            padding: 22px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.45);
            margin-bottom: 20px;
        }
        .pkg-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px;
            margin-top: 14px;
        }
        .pkg-box {
            background: #f8fafc;
            border: 2px solid #cbd5e1;
            border-radius: 18px;
            padding: 18px 14px;
            text-align: center;
            position: relative;
            transition: border-color 0.2s, transform 0.15s;
        }
        .pkg-box:hover { border-color: #0284c7; transform: translateY(-2px); }
        .pkg-badge {
            position: absolute;
            top: -10px;
            left: 14px;
            background: #ef4444;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 99px;
        }
        .pkg-title { font-size: 1.35rem; font-weight: 900; color: #0284c7; margin: 4px 0 8px; }
        .pkg-old { text-decoration: line-through; color: #94a3b8; font-size: 0.85rem; }
        .pkg-price { font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 4px 0 14px; }

        .invoice-box {
            display: none;
            background: #f0f9ff;
            border: 2px solid #0284c7;
            border-radius: 18px;
            padding: 18px;
            margin-top: 18px;
        }
        .invoice-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 10px;
        }
        .timer-pill {
            display: inline-block;
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
            font-weight: 900;
            padding: 5px 14px;
            border-radius: 12px;
            font-size: 1rem;
        }
        .success-box {
            display: none;
            background: #ecfdf5;
            border: 2px solid #10b981;
            color: #065f46;
            border-radius: 18px;
            padding: 20px;
            text-align: center;
            margin-top: 16px;
        }
        .tutorial-panel {
            display: none;
            margin-top: 16px;
            background: #f8fafc;
            border: 2px solid #cbd5e1;
            border-radius: 16px;
            padding: 16px;
        }
        @media (max-width: 600px) {
            .bank-card { padding: 16px 14px; }
            .bank-topbar { padding: 12px 14px; }
        }
    </style>
</head>
<body>
<div class="bank-wrap">
    <div class="bank-topbar">
        <div class="bank-brand">🏦 بانک Bloxia <span style="font-size:0.85rem;color:#94a3b8;font-weight:600;">(<?php echo htmlspecialchars($this->playerName, ENT_QUOTES, 'UTF-8'); ?>)</span></div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <div class="bank-balance">موجودی فعلی: <span id="bank-balance-val"><?php echo number_format($this->bitblox); ?></span> 💎 BitBlox</div>
            <a href="<?php echo $worldUrl; ?>" class="btn-game btn-slate">🏙️ بازگشت به بازی</a>
        </div>
    </div>

    <div class="bank-card">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;">
            <div>
                <h2 style="margin:0 0 4px;font-weight:900;">💳 شارژ حساب با کارت به کارت هوشمند</h2>
                <p style="margin:0;font-size:0.86rem;color:#475569;">پکیج مورد نظر را انتخاب کنید تا شماره کارت و مبلغ دقیق (همراه با کد رندوم اختصاصی ۱۰ دقیقه‌ای) نمایش داده شود.</p>
            </div>
            <button type="button" id="btn-toggle-tutorial" class="btn-game btn-amber">🎓 آموزش کارت به کارت (ویدئو و PDF)</button>
        </div>

        <!-- بخش آموزش ویدئویی و PDF -->
        <div id="bank-tutorial-panel" class="tutorial-panel">
            <h3 style="margin:0 0 10px;font-weight:900;">🎓 راهنمای شارژ حساب کارت به کارت آنلاین</h3>
            <ol style="margin:0 0 14px;padding-right:20px;line-height:1.8;font-size:0.88rem;color:#334155;">
                <li>یکی از بسته‌های ۱۰۰، ۵۰۰ یا ۲۰۰۰ BitBlox را انتخاب کنید.</li>
                <li>سیستم یک <strong>عدد رندوم ۲ یا ۳ رقمی</strong> به انتهای مبلغ اضافه می‌کند (مثلاً ۵۰۰,۳۴۷ تومان) که فقط تا <strong>۱۰ دقیقه</strong> معتبر است.</li>
                <li>دقیقاً همان مبلغ (تا آخرین ریال/تومان) را به شماره کارت نمایش‌داده‌شده کارت به کارت کنید.</li>
                <li>به محض دریافت پیامک بانکی، حساب شما به صورت خودکار شارژ می‌شود!</li>
            </ol>

            <?php if (!empty($s->tutorial_video_url)) : ?>
                <div style="margin-bottom:14px;">
                    <div style="font-weight:800;margin-bottom:6px;">🎥 ویدئو آموزشی:</div>
                    <video controls style="width:100%;max-height:360px;border-radius:14px;background:#000;">
                        <source src="<?php echo htmlspecialchars($s->tutorial_video_url, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                        مرورگر شما از پخش ویدئو پشتیبانی نمی‌کند.
                    </video>
                </div>
            <?php endif; ?>

            <?php if (!empty($s->tutorial_pdf_url)) : ?>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <a href="<?php echo htmlspecialchars($s->tutorial_pdf_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn-game btn-cyan">
                        📄 مشاهده و دانلود فایل PDF آموزشی
                    </a>
                </div>
            <?php endif; ?>

            <?php if (empty($s->tutorial_video_url) && empty($s->tutorial_pdf_url)) : ?>
                <div style="font-size:0.82rem;color:#64748b;">فایل ویدئو یا PDF آموزشی پس از بارگذاری در پنل ادمین در این بخش نمایش داده می‌شود.</div>
            <?php endif; ?>
        </div>

        <!-- ۳ پکیج شارژ -->
        <div class="pkg-grid">
            <?php foreach ($this->packages as $pkg) : ?>
                <div class="pkg-box">
                    <?php if ($pkg['discount_pct'] > 0) : ?>
                        <div class="pkg-badge"><?php echo (int) $pkg['discount_pct']; ?>٪ تخفیف</div>
                    <?php endif; ?>
                    <div class="pkg-title">💎 <?php echo number_format($pkg['bitblox']); ?> BitBlox</div>
                    <?php if ($pkg['discount_pct'] > 0) : ?>
                        <div class="pkg-old"><?php echo number_format($pkg['orig_toman']); ?> تومان</div>
                    <?php endif; ?>
                    <div class="pkg-price"><?php echo number_format($pkg['base_toman']); ?> تومان</div>
                    <button type="button" class="btn-game btn-cyan btn-select-pkg" data-pkg="<?php echo htmlspecialchars($pkg['key'], ENT_QUOTES, 'UTF-8'); ?>" style="width:100%;">
                        💳 انتخاب و دریافت شماره کارت
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- فاکتور کارت به کارت -->
        <div id="bank-invoice-box" class="invoice-box">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;">
                <strong style="font-size:1.05rem;color:#0369a1;">🧾 اطلاعات واریز کارت به کارت</strong>
                <div class="timer-pill">⏳ اعتبار کد رندوم: <span id="inv-timer">10:00</span></div>
            </div>

            <div class="invoice-row">
                <div>
                    <div style="font-size:0.8rem;color:#64748b;">شماره کارت مقصد:</div>
                    <strong id="inv-card-num" dir="ltr" style="font-size:1.15rem;letter-spacing:1.5px;"></strong>
                    <div id="inv-card-holder" style="font-size:0.82rem;color:#334155;"></div>
                </div>
                <button type="button" class="btn-game btn-slate" id="btn-copy-card" style="padding:8px 14px;font-size:0.82rem;">📋 کپی کارت</button>
            </div>

            <div class="invoice-row" style="border-color:#f59e0b;background:#fffbeb;">
                <div>
                    <div style="font-size:0.8rem;color:#92400e;">مبلغ دقیق واریزی (حتماً با کد رندوم انتها واریز شود):</div>
                    <strong id="inv-amount-toman" style="font-size:1.25rem;color:#b45309;"></strong>
                    <div id="inv-amount-rial" style="font-size:0.82rem;color:#78350f;"></div>
                </div>
                <button type="button" class="btn-game btn-amber" id="btn-copy-amount" style="padding:8px 14px;font-size:0.82rem;">📋 کپی مبلغ (تومان)</button>
            </div>

            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;font-size:0.82rem;color:#0369a1;">
                <span>🔄 در حال بررسی خودکار دریافت پیامک واریز...</span>
                <button type="button" id="btn-refresh-code" class="btn-game btn-slate" style="padding:6px 12px;font-size:0.78rem;">🔄 تولید عدد رندوم جدید</button>
            </div>
        </div>

        <!-- پیام موفقیت پرداخت -->
        <div id="bank-success-box" class="success-box">
            <div style="font-size:2.2rem;">🎉💎</div>
            <h3 style="margin:6px 0;">شارژ حساب با موفقیت انجام شد!</h3>
            <p id="bank-success-msg" style="margin:0 0 12px;"></p>
            <a href="<?php echo $worldUrl; ?>" class="btn-game btn-green">🚀 ورود به دنیای Bloxia</a>
        </div>
    </div>
</div>

<script>
(function() {
    const baseUrl = '<?php echo $baseUrl; ?>index.php?option=com_bloxia&format=json&task=bank.';
    const csrfToken = '<?php echo $csrfToken; ?>';
    let activeOrder = null;
    let countdownTimer = null;
    let pollTimer = null;
    let remainingSec = 0;

    document.getElementById('btn-toggle-tutorial').onclick = () => {
        const p = document.getElementById('bank-tutorial-panel');
        p.style.display = (p.style.display === 'block') ? 'none' : 'block';
    };

    function formatTime(sec) {
        const m = String(Math.floor(sec / 60)).padStart(2, '0');
        const s = String(sec % 60).padStart(2, '0');
        return m + ':' + s;
    }

    function stopTimers() {
        if (countdownTimer) clearInterval(countdownTimer);
        if (pollTimer) clearInterval(pollTimer);
        countdownTimer = null;
        pollTimer = null;
    }

    async function createOrder(pkgKey, forceNew = false) {
        stopTimers();
        document.getElementById('bank-success-box').style.display = 'none';

        const res = await fetch(baseUrl + 'createOrder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ package_key: pkgKey, force_new: forceNew })
        });
        const data = await res.json();
        if (!data.success) {
            alert(data.message || 'خطا در ایجاد فاکتور');
            return;
        }

        activeOrder = data.order;
        remainingSec = activeOrder.remaining_seconds;

        document.getElementById('inv-card-num').textContent = activeOrder.card_number;
        document.getElementById('inv-card-holder').textContent = activeOrder.card_holder ? ('به نام: ' + activeOrder.card_holder) : '';
        document.getElementById('inv-amount-toman').innerHTML =
            activeOrder.total_toman.toLocaleString('en-US') +
            ' تومان <span style="font-size:0.82rem;background:#fde68a;padding:2px 6px;border-radius:6px;">(کد رندوم: +' + activeOrder.random_code + ')</span>';
        document.getElementById('inv-amount-rial').textContent = 'معادل ' + activeOrder.total_rial.toLocaleString('en-US') + ' ریال';
        document.getElementById('inv-timer').textContent = formatTime(remainingSec);
        document.getElementById('bank-invoice-box').style.display = 'block';

        countdownTimer = setInterval(() => {
            remainingSec--;
            if (remainingSec <= 0) {
                document.getElementById('inv-timer').textContent = '00:00';
                createOrder(activeOrder.package_key, true); // پس از ۱۰ دقیقه تغییر خودکار عدد رندوم
                return;
            }
            document.getElementById('inv-timer').textContent = formatTime(remainingSec);
        }, 1000);

        pollTimer = setInterval(async () => {
            if (!activeOrder) return;
            const r = await fetch(baseUrl + 'checkOrder&order_id=' + activeOrder.id);
            const st = await r.json();
            if (!st.success) return;
            if (st.status === 'paid') {
                stopTimers();
                document.getElementById('bank-invoice-box').style.display = 'none';
                document.getElementById('bank-balance-val').textContent = Number(st.new_balance).toLocaleString('en-US');
                document.getElementById('bank-success-msg').textContent =
                    st.bitblox_amount + ' BitBlox با موفقیت به حساب شما اضافه شد!';
                document.getElementById('bank-success-box').style.display = 'block';
            } else if (st.status === 'expired') {
                createOrder(activeOrder.package_key, true);
            }
        }, 4000);
    }

    document.querySelectorAll('.btn-select-pkg').forEach(btn => {
        btn.onclick = () => createOrder(btn.dataset.pkg, false);
    });

    document.getElementById('btn-refresh-code').onclick = () => {
        if (activeOrder) createOrder(activeOrder.package_key, true);
    };

    document.getElementById('btn-copy-card').onclick = () => {
        if (activeOrder) {
            navigator.clipboard.writeText(activeOrder.card_number.replace(/-/g, ''));
            alert('شماره کارت کپی شد!');
        }
    };

    document.getElementById('btn-copy-amount').onclick = () => {
        if (activeOrder) {
            navigator.clipboard.writeText(String(activeOrder.total_toman));
            alert('مبلغ دقیق (' + activeOrder.total_toman + ' تومان) کپی شد!');
        }
    };
})();
</script>
</body>
</html>
