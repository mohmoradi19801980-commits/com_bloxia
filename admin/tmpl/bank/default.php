<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;

$token = Session::getFormToken();
$s = $this->settings;
?>

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=games'); ?>" class="btn btn-outline-primary">🎮 مدیریت بازی‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=users'); ?>" class="btn btn-outline-primary">👥 کاربران و کیف پول BitBlox</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=items'); ?>" class="btn btn-outline-primary">🛍️ فروشگاه و قیمت‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=bank'); ?>" class="btn btn-primary">🏦 بانک و شارژ حساب</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=logs'); ?>" class="btn btn-outline-secondary">📜 لاگ فعالیت‌ها</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header fw-bold">💳 تنظیمات کارت به کارت و SMS Forwarder</div>
            <div class="card-body">
                <form action="<?php echo Route::_('index.php?option=com_bloxia&task=bank.saveSettings'); ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">شماره کارت بانکی (۱۶ رقمی):</label>
                        <input type="text" name="card_number" class="form-control" dir="ltr" placeholder="6037-9971-...."
                               value="<?php echo htmlspecialchars($s->card_number, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نام صاحب کارت:</label>
                        <input type="text" name="card_holder" class="form-control" placeholder="مثلاً: محسن سجادی"
                               value="<?php echo htmlspecialchars($s->card_holder, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">کلید امنیتی SMS Forwarder:</label>
                        <input type="text" name="sms_secret" class="form-control" dir="ltr"
                               value="<?php echo htmlspecialchars($s->sms_secret, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">آدرس Webhook برای برنامه SMS Forwarder گوشی:</label>
                        <div class="input-group">
                            <input type="text" id="bloxia-webhook-url" class="form-control form-control-sm" dir="ltr" readonly
                                   value="<?php echo htmlspecialchars($this->webhookUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="navigator.clipboard.writeText(document.getElementById('bloxia-webhook-url').value);alert('آدرس Webhook کپی شد!');">
                                📋 کپی
                            </button>
                        </div>
                        <div class="form-text">در اپلیکیشن SMS Forwarder روی گوشی، پیامک‌های بانک را به این آدرس (POST JSON با فیلد <code>text</code> یا <code>message</code>) ارسال کنید.</div>
                    </div>

                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold">🎥 لینک ویدئو آموزشی کارت به کارت (MP4 / لینک مستقیم):</label>
                        <input type="url" name="tutorial_video_url" class="form-control" dir="ltr" placeholder="https://.../tutorial.mp4"
                               value="<?php echo htmlspecialchars($s->tutorial_video_url, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">📄 لینک فایل PDF آموزشی کارت به کارت:</label>
                        <input type="url" name="tutorial_pdf_url" class="form-control" dir="ltr" placeholder="https://.../guide.pdf"
                               value="<?php echo htmlspecialchars($s->tutorial_pdf_url, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <button type="submit" class="btn btn-success w-100">💾 ذخیره تنظیمات بانک</button>
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header fw-bold">📋 آخرین سفارش‌های شارژ حساب (کارت به کارت)</div>
            <div class="card-body">
                <?php if (empty($this->orders)) : ?>
                    <div class="alert alert-info mb-0">هنوز سفارشی ثبت نشده است.</div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>کاربر</th>
                                    <th>مقدار BitBlox</th>
                                    <th>مبلغ واریزی (تومان)</th>
                                    <th>وضعیت</th>
                                    <th>زمان / انقضا</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->orders as $o) : ?>
                                    <tr>
                                        <td><?php echo (int) $o->id; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($o->user_name ?? ('#' . $o->user_id), ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <div class="small text-muted">@<?php echo htmlspecialchars($o->username ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td><span class="badge bg-info text-dark">💎 <?php echo (int) $o->bitblox_amount; ?></span></td>
                                        <td>
                                            <strong><?php echo number_format((int) $o->total_toman); ?></strong>
                                            <div class="small text-muted">کد رندوم: +<?php echo (int) $o->random_code; ?></div>
                                        </td>
                                        <td>
                                            <?php if ($o->status === 'paid') : ?>
                                                <span class="badge bg-success">✅ پرداخت شد</span>
                                            <?php elseif ($o->status === 'pending') : ?>
                                                <span class="badge bg-warning text-dark">⏳ در انتظار واریز</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">⌛ منقضی شده</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small" dir="ltr">
                                            <div>ایجاد: <?php echo htmlspecialchars($o->created_at, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-muted">انقضا: <?php echo htmlspecialchars($o->expires_at, ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td>
                                            <?php if ($o->status !== 'paid') : ?>
                                                <a href="<?php echo Route::_('index.php?option=com_bloxia&task=bank.approveOrder&order_id=' . (int) $o->id . '&' . $token . '=1'); ?>"
                                                   class="btn btn-sm btn-success"
                                                   onclick="return confirm('سفارش به صورت دستی تایید و BitBlox واریز شود؟');">
                                                    ✔️ تایید دستی
                                                </a>
                                            <?php else : ?>
                                                <span class="small text-success">واریز شده</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
