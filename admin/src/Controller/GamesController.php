<?php
namespace BastanGraphic\Component\Bloxia\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\Filesystem\Folder;

class GamesController extends BaseController
{
    public function approve()
    {
        Session::checkToken('get') or die('Invalid Token');

        $app    = Factory::getApplication();
        $userId = $app->input->getInt('user_id', 0);
        $gameId = $app->input->getCmd('game_id', '');
        $tier   = $app->input->getCmd('tier', '');
        $reward = $app->input->getInt('reward', -1);

        if ($this->processGame($userId, $gameId, 'approve', $tier, $reward)) {
            $app->enqueueMessage('بازی تایید شد و پاداش BitBlox واریز گردید.', 'success');
        } else {
            $app->enqueueMessage('خطا در تایید بازی.', 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=games', false));
    }

    // ویرایش سطح و پاداش (حتی برای بازی‌های تاییدشده همراه با محاسبه مابه‌التفاوت BitBlox)
    public function saveEdit()
    {
        Session::checkToken('get') or die('Invalid Token');

        $app    = Factory::getApplication();
        $userId = $app->input->getInt('user_id', 0);
        $gameId = $app->input->getCmd('game_id', '');
        $tier   = $app->input->getCmd('tier', '');
        $reward = $app->input->getInt('reward', -1);

        if ($this->processGame($userId, $gameId, 'edit', $tier, $reward)) {
            $app->enqueueMessage('تغییرات سطح و پاداش با موفقیت ذخیره و موجودی دانش‌آموز به‌روزرسانی شد.', 'success');
        } else {
            $app->enqueueMessage('خطا در ذخیره تغییرات.', 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=games', false));
    }

    public function reject()
    {
        Session::checkToken('get') or die('Invalid Token');

        $app    = Factory::getApplication();
        $userId = $app->input->getInt('user_id', 0);
        $gameId = $app->input->getCmd('game_id', '');

        if ($this->processGame($userId, $gameId, 'delete')) {
            $this->deleteGameFolder($userId, $gameId);
            $app->enqueueMessage('بازی و فایل‌های آن حذف شد.', 'info');
        } else {
            $app->enqueueMessage('خطا در حذف بازی.', 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=games', false));
    }

    // تایید گروهی ردیف‌های تیک‌خورده
    public function bulkApprove()
    {
        Session::checkToken() or die('Invalid Token');
        $app = Factory::getApplication();
        $cids = $app->input->post->get('cid', [], 'array');
        $tiers = $app->input->post->get('tier', [], 'array');
        $rewards = $app->input->post->get('reward', [], 'array');

        $count = 0;
        foreach ($cids as $key) {
            [$userId, $gameId] = explode('|', $key);
            $tier   = $tiers[$key] ?? '';
            $reward = isset($rewards[$key]) ? (int) $rewards[$key] : -1;
            if ($this->processGame((int) $userId, $gameId, 'approve', $tier, $reward)) {
                $count++;
            }
        }

        $app->enqueueMessage($count . ' بازی با موفقیت تایید/به‌روزرسانی شد.', 'success');
        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=games', false));
    }

    // حذف گروهی ردیف‌های تیک‌خورده
    public function bulkReject()
    {
        Session::checkToken() or die('Invalid Token');
        $app = Factory::getApplication();
        $cids = $app->input->post->get('cid', [], 'array');

        $count = 0;
        foreach ($cids as $key) {
            [$userId, $gameId] = explode('|', $key);
            if ($this->processGame((int) $userId, $gameId, 'delete')) {
                $this->deleteGameFolder((int) $userId, $gameId);
                $count++;
            }
        }

        $app->enqueueMessage($count . ' بازی و فایل‌های آن‌ها حذف شد.', 'info');
        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=games', false));
    }

    private function deleteGameFolder(int $userId, string $gameId): void
    {
        $gameDir = JPATH_ROOT . '/media/com_bloxia/games/' . $userId . '/' . $gameId;
        if (is_dir($gameDir)) {
            Folder::delete($gameDir);
        }
    }

    private function processGame(int $userId, string $gameId, string $action, string $newTier = '', int $newReward = -1): bool
    {
        if (!$userId || empty($gameId)) return false;

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . $userId);
        $db->setQuery($query);
        $profile = $db->loadObject();

        if (!$profile || empty($profile->character_data)) return false;

        $state = json_decode($profile->character_data, true);
        if (empty($state['myGames']) || !is_array($state['myGames'])) return false;

        $updated = false;
        foreach ($state['myGames'] as $index => &$game) {
            if (($game['id'] ?? '') === $gameId) {
                $oldStatus = $game['status'] ?? 'pending';
                $oldReward = (int) ($game['reward'] ?? 0);
                $finalReward = ($newReward >= 0) ? $newReward : $oldReward;

                if ($action === 'approve' || $action === 'edit') {
                    if (!empty($newTier)) {
                        $game['tier'] = $newTier;
                    }
                    $game['reward'] = $finalReward;

                    if ($action === 'approve' && $oldStatus !== 'approved') {
                        // اولین تایید: واریز کل پاداش
                        $game['status'] = 'approved';
                        $state['bitblox'] = (int) ($state['bitblox'] ?? 0) + $finalReward;
                    } elseif ($oldStatus === 'approved') {
                        // ویرایش بازیِ قبلاً تاییدشده: اعمال مابه‌التفاوت پاداش
                        $diff = $finalReward - $oldReward;
                        $state['bitblox'] = max(0, (int) ($state['bitblox'] ?? 0) + $diff);
                    }
                    $updated = true;
                } elseif ($action === 'delete') {
                    unset($state['myGames'][$index]);
                    $state['myGames'] = array_values($state['myGames']);
                    $updated = true;
                }
                break;
            }
        }

        if ($updated) {
            $updateQuery = $db->getQuery(true)
                ->update($db->quoteName('#__bloxia_user_profiles'))
                ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($state)))
                ->set($db->quoteName('bloxia_points') . ' = ' . (int) ($state['bitblox'] ?? 0))
                ->where($db->quoteName('user_id') . ' = ' . $userId);
            $db->setQuery($updateQuery)->execute();
        }

        return $updated;
    }
}