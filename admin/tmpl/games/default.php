<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$token = Session::getFormToken();
$tierLabels = [
    'simple' => 'ساده',
    'staged' => 'مرحله‌ای',
    'pro'    => 'حرفه‌ای'
];
?>

<div class="card">
    <div class="card-body">
        <?php if (empty($this->games)) : ?>
            <div class="alert alert-info mb-0">
                هنوز هیچ بازی‌ای توسط دانش‌آموزان ارسال نشده است.
            </div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>نام بازی</th>
                            <th>سازنده</th>
                            <th>سطح / پاداش</th>
                            <th>وضعیت</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->games as $game) : ?>
                            <?php
                            $approveUrl = Route::_('index.php?option=com_bloxia&task=games.approve&user_id=' . $game->user_id . '&game_id=' . $game->id . '&' . $token . '=1');
                            $rejectUrl  = Route::_('index.php?option=com_bloxia&task=games.reject&user_id=' . $game->user_id . '&game_id=' . $game->id . '&' . $token . '=1');
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($game->title, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($game->author, ENT_QUOTES, 'UTF-8'); ?> <small class="text-muted">(#<?php echo $game->user_id; ?>)</small></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $tierLabels[$game->tier] ?? $game->tier; ?></span>
                                    <span class="badge bg-info text-dark">💎 <?php echo $game->reward; ?> BitBlox</span>
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
                                        <a href="<?php echo $approveUrl; ?>" class="btn btn-sm btn-success" onclick="return confirm('آیا از تایید این بازی و واریز BitBlox مطمئن هستید؟');">
                                            ✔️ تایید و واریز
                                        </a>
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
        <?php endif; ?>
    </div>
</div>