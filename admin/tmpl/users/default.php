<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$st = $this->state;
?>

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=games'); ?>" class="btn btn-outline-primary">🎮 مدیریت بازی‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=users'); ?>" class="btn btn-primary">👥 کاربران و کیف پول BitBlox</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=items'); ?>" class="btn btn-outline-primary">🛍️ فروشگاه و قیمت‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=bank'); ?>" class="btn btn-outline-primary">🏦 بانک و شارژ حساب</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=logs'); ?>" class="btn btn-outline-secondary">📜 لاگ فعالیت‌ها</a>
</div>

<!-- کارت‌های خلاصه آمار -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body py-2">
                <div class="text-muted small">کل شهروندان</div>
                <div class="fs-4 fw-bold text-primary"><?php echo (int) $this->summary['total_users']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body py-2">
                <div class="text-muted small">بازیکنان آنلاین اکنون</div>
                <div class="fs-4 fw-bold text-success">🟢 <?php echo (int) $this->summary['online_users']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body py-2">
                <div class="text-muted small">مجموع BitBlox در گردش</div>
                <div class="fs-4 fw-bold text-warning">💎 <?php echo number_format((int) $this->summary['total_bitblox']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body py-2">
                <div class="text-muted small">مجموع اجرای بازی‌ها</div>
                <div class="fs-4 fw-bold text-info">▶️ <?php echo number_format((int) $this->summary['total_plays']); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- فرم فیلتر و جستجو -->
<form action="<?php echo Route::_('index.php?option=com_bloxia&view=users'); ?>" method="post" name="adminForm" id="adminForm" class="mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group">
                <input type="text" name="filter_search" id="filter_search" class="form-control"
                       placeholder="جستجو نام یا نام کاربری..."
                       value="<?php echo htmlspecialchars($st['search'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-search" aria-hidden="true"></span>
                </button>
            </div>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value='';this.form.submit();">پاک کردن</button>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <select name="list_fullordering" class="form-select w-auto" onchange="this.form.submit()">
                <option value="points_desc" <?php echo $st['ordering'] === 'points_desc' ? 'selected' : ''; ?>>بیشترین BitBlox</option>
                <option value="points_asc"  <?php echo $st['ordering'] === 'points_asc' ? 'selected' : ''; ?>>کمترین BitBlox</option>
                <option value="plays_desc"  <?php echo $st['ordering'] === 'plays_desc' ? 'selected' : ''; ?>>بیشترین اجرای بازی</option>
                <option value="name_asc"    <?php echo $st['ordering'] === 'name_asc' ? 'selected' : ''; ?>>نام (الف تا ی)</option>
            </select>

            <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                <?php foreach ([10, 20, 50, 100, 0] as $lim) : ?>
                    <option value="<?php echo $lim; ?>" <?php echo (int) $st['limit'] === $lim ? 'selected' : ''; ?>>
                        <?php echo $lim === 0 ? 'همه' : $lim; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<!-- جدول کاربران و تغییر دستی کیف پول -->
<div class="card">
    <div class="card-body">
        <?php if (empty($this->users)) : ?>
            <div class="alert alert-info mb-0">هیچ کاربری یافت نشد.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>کاربر</th>
                            <th class="text-center">وضعیت</th>
                            <th class="text-center">موجودی BitBlox</th>
                            <th class="text-center">بازی‌ها / تعداد اجرا</th>
                            <th>تغییر دستی BitBlox</th>
                            <th class="text-center">گزارش</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->users as $u) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($u->character_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <div class="small text-muted">
                                        @<?php echo htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8'); ?> (#<?php echo $u->user_id; ?>)
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($u->is_online) : ?>
                                        <span class="badge bg-success">🟢 آنلاین</span>
                                        <div class="small text-muted"><?php echo htmlspecialchars($u->current_zone, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">⚪ آفلاین</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark fs-6">💎 <?php echo number_format($u->bitblox); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="small">
                                        🎮 <?php echo $u->games_approved; ?> تاییدشده از <?php echo $u->games_total; ?>
                                    </div>
                                    <div class="small text-info fw-bold">
                                        ▶️ <?php echo $u->hosted_plays; ?> اجرای بازی‌هایش | 🕹️ <?php echo $u->played_count; ?> بازی کرده
                                    </div>
                                </td>
                                <td>
                                    <form action="<?php echo Route::_('index.php?option=com_bloxia&task=users.adjustWallet'); ?>" method="post" class="d-flex flex-wrap align-items-center gap-1">
                                        <input type="hidden" name="user_id" value="<?php echo $u->user_id; ?>">
                                        <select name="mode" class="form-select form-select-sm w-auto">
                                            <option value="add">➕ افزایش</option>
                                            <option value="sub">➖ کاهش</option>
                                            <option value="set">🟰 تنظیم دقیق</option>
                                        </select>
                                        <input type="number" name="amount" class="form-control form-control-sm" style="width:85px;" min="0" required placeholder="مبلغ">
                                        <input type="text" name="reason" class="form-control form-control-sm" style="width:140px;" placeholder="علت (اختیاری)">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('تغییر موجودی BitBlox اعمال شود؟');">اعمال</button>
                                        <?php echo HTMLHelper::_('form.token'); ?>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=logs&filter_user_id=' . $u->user_id); ?>" class="btn btn-sm btn-outline-secondary">
                                        📜 ریز فعالیت‌ها
                                    </a>
                                </td>
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
