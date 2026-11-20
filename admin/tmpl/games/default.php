<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;

$token = Session::getFormToken();
$st = $this->state;
$hasFilters = ($st['status'] !== '' || $st['tier'] !== '');
?>

<form action="<?php echo Route::_('index.php?option=com_bloxia&view=games'); ?>" method="post" name="adminForm" id="adminForm">

    <!-- نوار استاندارد Search Tools جوملا -->
    <div class="js-stools mb-3">
        <div class="js-stools-container-bar d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="btn-toolbar d-flex flex-wrap gap-2">
                <div class="input-group">
                    <input type="text" name="filter_search" id="filter_search" class="form-control"
                           placeholder="جستجو در نام بازی یا سازنده..."
                           value="<?php echo htmlspecialchars($st['search'], ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-primary" title="جستجو">
                        <span class="icon-search" aria-hidden="true"></span>
                    </button>
                </div>

                <button type="button" class="btn btn-primary" onclick="document.getElementById('bloxia-filter-box').classList.toggle('d-none');">
                    فیلترها <span class="icon-chevron-down" aria-hidden="true"></span>
                </button>

                <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value='';document.getElementById('filter_status').value='';document.getElementById('filter_tier').value='';this.form.submit();">
                    پاک کردن
                </button>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <select name="list_fullordering" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="status_asc"  <?php echo $st['ordering'] === 'status_asc' ? 'selected' : ''; ?>>در انتظار تایید (ابتدا)</option>
                    <option value="status_desc" <?php echo $st['ordering'] === 'status_desc' ? 'selected' : ''; ?>>تایید شده (ابتدا)</option>
                    <option value="title_asc"   <?php echo $st['ordering'] === 'title_asc' ? 'selected' : ''; ?>>نام بازی (الف تا ی)</option>
                    <option value="title_desc"  <?php echo $st['ordering'] === 'title_desc' ? 'selected' : ''; ?>>نام بازی (ی تا الف)</option>
                    <option value="author_asc"  <?php echo $st['ordering'] === 'author_asc' ? 'selected' : ''; ?>>نام سازنده</option>
                    <option value="reward_desc" <?php echo $st['ordering'] === 'reward_desc' ? 'selected' : ''; ?>>پاداش (بیشترین)</option>
                    <option value="reward_asc"  <?php echo $st['ordering'] === 'reward_asc' ? 'selected' : ''; ?>>پاداش (کمترین)</option>
                </select>

                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <?php foreach ([5, 10, 20, 50, 100, 0] as $lim) : ?>
                        <option value="<?php echo $lim; ?>" <?php echo (int) $st['limit'] === $lim ? 'selected' : ''; ?>>
                            <?php echo $lim === 0 ? 'همه' : $lim; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="bloxia-filter-box" class="mt-2 p-2 bg-light border rounded d-flex flex-wrap gap-2 <?php echo $hasFilters ? '' : 'd-none'; ?>">
            <select name="filter_status" id="filter_status" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">- همه وضعیت‌ها -</option>
                <option value="pending"  <?php echo $st['status'] === 'pending' ? 'selected' : ''; ?>>⏳ در انتظار تایید</option>
                <option value="approved" <?php echo $st['status'] === 'approved' ? 'selected' : ''; ?>>✅ تایید شده</option>
            </select>

            <select name="filter_tier" id="filter_tier" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">- همه سطوح -</option>
                <option value="simple" <?php echo $st['tier'] === 'simple' ? 'selected' : ''; ?>>ساده</option>
                <option value="staged" <?php echo $st['tier'] === 'staged' ? 'selected' : ''; ?>>مرحله‌ای</option>
                <option value="pro"    <?php echo $st['tier'] === 'pro' ? 'selected' : ''; ?>>حرفه‌ای</option>
            </select>
        </div>
    </div>

    <!-- نوار عملیات گروهی برای ردیف‌های انتخاب‌شده -->
    <div class="mb-2 d-flex gap-2">
        <button type="button" class="btn btn-sm btn-success" onclick="submitBulkTask('games.bulkApprove', 'آیا تغییرات/تایید برای موارد انتخاب‌شده اعمال شود؟')">
            ✔️ تایید / ذخیره انتخاب‌شده‌ها
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkTask('games.bulkReject', 'موارد انتخاب‌شده و فایل‌هایشان به صورت کامل حذف شوند؟')">
            🗑️ حذف انتخاب‌شده‌ها
        </button>
    </div>

    <!-- جدول بازی‌ها -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($this->games)) : ?>
                <div class="alert alert-info mb-0">هیچ بازی‌ای با این مشخصات یافت نشد.</div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:36px;" class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th>نام بازی</th>
                                <th>سازنده</th>
                                <th>سطح / پاداش (قابل ویرایش)</th>
                                <th>وضعیت</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->games as $i => $game) : ?>
                                <?php
                                $rowKey    = $game->user_id . '|' . $game->id;
                                $domKey    = $game->user_id . '_' . $game->id;
                                $rejectUrl = Route::_('index.php?option=com_bloxia&task=games.reject&user_id=' . $game->user_id . '&game_id=' . $game->id . '&' . $token . '=1');
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo htmlspecialchars($rowKey, ENT_QUOTES, 'UTF-8'); ?>"
                                               class="form-check-input" onclick="Joomla.isChecked(this.checked);">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($game->title, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($game->author, ENT_QUOTES, 'UTF-8'); ?> <small class="text-muted">(#<?php echo $game->user_id; ?>)</small></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <select name="tier[<?php echo htmlspecialchars($rowKey, ENT_QUOTES, 'UTF-8'); ?>]"
                                                    id="tier_<?php echo $domKey; ?>" class="form-select form-select-sm w-auto"
                                                    onchange="syncRewardByTier('<?php echo $domKey; ?>', this.value)">
                                                <option value="simple" <?php echo $game->tier === 'simple' ? 'selected' : ''; ?>>ساده</option>
                                                <option value="staged" <?php echo $game->tier === 'staged' ? 'selected' : ''; ?>>مرحله‌ای</option>
                                                <option value="pro"    <?php echo $game->tier === 'pro' ? 'selected' : ''; ?>>حرفه‌ای</option>
                                            </select>
                                            <input type="number" name="reward[<?php echo htmlspecialchars($rowKey, ENT_QUOTES, 'UTF-8'); ?>]"
                                                   id="reward_<?php echo $domKey; ?>" class="form-control form-control-sm"
                                                   style="width:85px;" min="0" value="<?php echo (int) $game->reward; ?>" title="مبلغ پاداش BitBlox">
                                            <span class="small text-muted">💎</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($game->status === 'approved') : ?>
                                            <span class="badge bg-success">✅ تایید شده</span>
                                        <?php else : ?>
                                            <span class="badge bg-warning text-dark">⏳ در انتظار تایید</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo htmlspecialchars($game->url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            👁️ تست بازی
                                        </a>

                                        <?php if ($game->status !== 'approved') : ?>
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="runRowAction('games.approve', <?php echo $game->user_id; ?>, '<?php echo $game->id; ?>', '<?php echo $domKey; ?>')">
                                                ✔️ تایید و واریز
                                            </button>
                                        <?php else : ?>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="runRowAction('games.saveEdit', <?php echo $game->user_id; ?>, '<?php echo $game->id; ?>', '<?php echo $domKey; ?>')">
                                                💾 ذخیره ویرایش
                                            </button>
                                        <?php endif; ?>

                                        <a href="<?php echo $rejectUrl; ?>" class="btn btn-sm btn-danger" onclick="return confirm('بازی و فایل‌های آن به صورت کامل حذف شوند؟');">
                                            🗑️ حذف
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- صفحه‌بندی بومی جوملا -->
                <div class="mt-3">
                    <?php echo $this->pagination->getListFooter(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <input type="hidden" name="task" id="bloxia-task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
    function syncRewardByTier(domKey, tier) {
        const map = { simple: 10, staged: 20, pro: 100 };
        const input = document.getElementById('reward_' + domKey);
        if (input && map[tier] !== undefined) {
            input.value = map[tier];
        }
    }

    function runRowAction(task, userId, gameId, domKey) {
        const tier = document.getElementById('tier_' + domKey).value;
        const reward = document.getElementById('reward_' + domKey).value;
        const msg = task === 'games.approve'
            ? 'آیا از تایید این بازی با پاداش ' + reward + ' BitBlox مطمئن هستید؟'
            : 'تغییرات سطح و پاداش ذخیره و مابه‌التفاوت BitBlox اعمال شود؟';

        if (!confirm(msg)) return;

        const baseUrl = '<?php echo Route::_('index.php?option=com_bloxia&' . $token . '=1', false); ?>';
        window.location.href = baseUrl + '&task=' + task + '&user_id=' + userId + '&game_id=' + encodeURIComponent(gameId) + '&tier=' + encodeURIComponent(tier) + '&reward=' + encodeURIComponent(reward);
    }

    function submitBulkTask(task, confirmMsg) {
        const checked = document.querySelectorAll('input[name="cid[]"]:checked');
        if (checked.length === 0) {
            alert('لطفاً ابتدا حداقل یک ردیف را انتخاب کنید.');
            return;
        }
        if (!confirm(confirmMsg)) return;
        document.getElementById('bloxia-task').value = task;
        document.getElementById('adminForm').submit();
    }
</script>