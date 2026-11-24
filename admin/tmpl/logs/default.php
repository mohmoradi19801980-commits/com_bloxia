<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

$st = $this->state;
$actionsList = [
    'game_play',
    'host_income',
    'game_submit',
    'game_approve',
    'game_edit',
    'game_reject',
    'admin_wallet_adjust',
    'buy_cart',
    'hair_salon',
    'wallet_tx',
    'visit_home',
    'save_home',
    'cas_save',
];
?>

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=games'); ?>" class="btn btn-outline-primary">🎮 مدیریت بازی‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=users'); ?>" class="btn btn-outline-primary">👥 کاربران و کیف پول BitBlox</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=items'); ?>" class="btn btn-outline-primary">🛍️ فروشگاه و قیمت‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=bank'); ?>" class="btn btn-outline-primary">🏦 بانک و شارژ حساب</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=logs'); ?>" class="btn btn-primary">📜 لاگ فعالیت‌ها</a>
</div>

<!-- کارت‌های خلاصه آمار لاگ -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-secondary">
            <div class="card-body py-2">
                <div class="text-muted small">تعداد کل رویدادها</div>
                <div class="fs-4 fw-bold"><?php echo number_format($this->stats['total_logs']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body py-2">
                <div class="text-muted small">تعداد اجرای بازی‌ها</div>
                <div class="fs-4 fw-bold text-info">▶️ <?php echo number_format($this->stats['total_plays']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body py-2">
                <div class="text-muted small">مجموع BitBlox واریزشده</div>
                <div class="fs-4 fw-bold text-success">+<?php echo number_format($this->stats['total_income']); ?> 💎</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body py-2">
                <div class="text-muted small">مجموع BitBlox خرج‌شده</div>
                <div class="fs-4 fw-bold text-danger">-<?php echo number_format($this->stats['total_spent']); ?> 💎</div>
            </div>
        </div>
    </div>
</div>

<!-- فرم فیلترها -->
<form action="<?php echo Route::_('index.php?option=com_bloxia&view=logs'); ?>" method="post" name="adminForm" id="adminForm" class="mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group">
                <input type="text" name="filter_search" id="filter_search" class="form-control"
                       placeholder="جستجو در جزئیات یا نام..."
                       value="<?php echo htmlspecialchars($st['search'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-search" aria-hidden="true"></span>
                </button>
            </div>

            <select name="filter_user_id" id="filter_user_id" class="form-select w-auto" onchange="this.form.submit()">
                <option value="0">- همه کاربران -</option>
                <?php foreach ($this->userOptions as $uo) : ?>
                    <option value="<?php echo (int) $uo->id; ?>" <?php echo (int) $st['user_id'] === (int) $uo->id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($uo->name . ' (@' . $uo->username . ')', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="filter_action" id="filter_action" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">- همه فعالیت‌ها -</option>
                <?php foreach ($actionsList as $act) : ?>
                    <option value="<?php echo $act; ?>" <?php echo $st['action'] === $act ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(BloxiaHelper::getActionLabel($act), ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value='';document.getElementById('filter_user_id').value='0';document.getElementById('filter_action').value='';this.form.submit();">
                پاک کردن
            </button>
        </div>

        <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ([20, 30, 50, 100, 0] as $lim) : ?>
                <option value="<?php echo $lim; ?>" <?php echo (int) $st['limit'] === $lim ? 'selected' : ''; ?>>
                    <?php echo $lim === 0 ? 'همه' : $lim; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<!-- جدول لاگ‌ها -->
<div class="card">
    <div class="card-body">
        <?php if (empty($this->logs)) : ?>
            <div class="alert alert-info mb-0">هیچ فعالیتی با این فیلترها ثبت نشده است.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کاربر</th>
                            <th>نوع فعالیت</th>
                            <th class="text-center">تغییر BitBlox</th>
                            <th>مرتبط با</th>
                            <th>جزئیات</th>
                            <th>زمان</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->logs as $log) : ?>
                            <tr>
                                <td class="text-muted small"><?php echo (int) $log->id; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log->actor_name ?? ('کاربر #' . $log->user_id), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php if (!empty($log->actor_username)) : ?>
                                        <div class="small text-muted">@<?php echo htmlspecialchars($log->actor_username, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo htmlspecialchars(BloxiaHelper::getActionLabel($log->action_type), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ((int) $log->amount > 0) : ?>
                                        <span class="badge bg-success">+<?php echo (int) $log->amount; ?> 💎</span>
                                    <?php elseif ((int) $log->amount < 0) : ?>
                                        <span class="badge bg-danger"><?php echo (int) $log->amount; ?> 💎</span>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <?php echo !empty($log->target_name) ? htmlspecialchars($log->target_name, ENT_QUOTES, 'UTF-8') : '-'; ?>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($log->details ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="small text-muted" dir="ltr"><?php echo htmlspecialchars($log->created_at, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo $this->pagination->getListFooter(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
