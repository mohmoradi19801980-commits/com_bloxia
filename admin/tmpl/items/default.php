<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$st = $this->state;
$typeLabels = [
    'wall_style'  => '🎨 دیوار و کاغذ دیواری',
    'floor_style' => '🪵 کفپوش (پارکت / سرامیک)',
    'frame'       => '🖼️ قاب عکس',
    'kitchen'     => '🍳 لوازم آشپزخانه',
    'lighting'    => '💡 نورپردازی',
    'decor'       => '🛋️ مبلمان و دکور',
    'bedroom'     => '🛏️ تخت خواب',
    'hat'         => '👑 کلاه',
    'shirt'       => '👕 لباس',
    'shoes'       => '👟 کفش',
    'handProp'    => '⚔️ لوازم دستی',
];
?>

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=games'); ?>" class="btn btn-outline-primary">🎮 مدیریت بازی‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=users'); ?>" class="btn btn-outline-primary">👥 کاربران و کیف پول BitBlox</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=items'); ?>" class="btn btn-primary">🛍️ فروشگاه و قیمت‌ها</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=bank'); ?>" class="btn btn-outline-primary">🏦 بانک و شارژ حساب</a>
    <a href="<?php echo Route::_('index.php?option=com_bloxia&view=logs'); ?>" class="btn btn-outline-secondary">📜 لاگ فعالیت‌ها</a>
</div>

<form action="<?php echo Route::_('index.php?option=com_bloxia&view=items'); ?>" method="post" class="mb-3 d-flex flex-wrap gap-2">
    <div class="input-group w-auto">
        <input type="text" name="filter_search" id="filter_search" class="form-control"
               placeholder="جستجو در نام یا کد آیتم..."
               value="<?php echo htmlspecialchars($st['search'], ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn btn-primary"><span class="icon-search"></span></button>
    </div>

    <select name="filter_type" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">- همه دسته‌بندی‌ها -</option>
        <?php foreach ($typeLabels as $k => $lbl) : ?>
            <option value="<?php echo $k; ?>" <?php echo $st['type'] === $k ? 'selected' : ''; ?>><?php echo $lbl; ?></option>
        <?php endforeach; ?>
    </select>

    <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value='';this.form.filter_type.value='';this.form.submit();">پاک کردن</button>
</form>

<form action="<?php echo Route::_('index.php?option=com_bloxia&task=items.savePrices'); ?>" method="post">
    <div class="mb-2">
        <button type="submit" class="btn btn-success">💾 ذخیره همه تغییرات قیمت و وضعیت</button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>کد آیتم</th>
                            <th>دسته‌بندی</th>
                            <th>عنوان نمایشی</th>
                            <th style="width:150px;">قیمت (BitBlox 💎)</th>
                            <th class="text-center" style="width:100px;">فعال در فروشگاه</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->items as $it) : ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($it['key'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $typeLabels[$it['type']] ?? $it['type']; ?></span></td>
                                <td>
                                    <input type="text" name="title[<?php echo (int) $it['id']; ?>]" class="form-control form-control-sm"
                                           value="<?php echo htmlspecialchars($it['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                </td>
                                <td>
                                    <input type="number" name="price[<?php echo (int) $it['id']; ?>]" class="form-control form-control-sm"
                                           min="0" value="<?php echo (int) $it['price']; ?>">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" name="published[<?php echo (int) $it['id']; ?>]" value="1"
                                           class="form-check-input" <?php echo $it['published'] ? 'checked' : ''; ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-2">
        <button type="submit" class="btn btn-success">💾 ذخیره همه تغییرات قیمت و وضعیت</button>
    </div>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
