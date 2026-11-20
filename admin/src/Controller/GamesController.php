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

        if ($this->updateGameStatus($userId, $gameId, 'approved', $tier, $reward)) {
            $app->enqueueMessage('بازی با سطح و پاداش انتخابی تایید شد و BitBlox به حساب دانش‌آموز واریز گردید.', 'success');
        } else {
            $app->enqueueMessage('خطا در یافتن بازی یا این بازی قبلاً تایید شده است.', 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=games', false));
    }

    public function reject()
    {
        Session::checkToken('get') or die('Invalid Token');

        $app    = Factory::getApplication();
        $userId = $app->input->getInt('user_id', 0);
        $gameId = $app->input->getCmd('game_id', '');

        if ($this->updateGameStatus($userId, $gameId, 'delete')) {
            $gameDir = JPATH_ROOT . '/media/com_bloxia/games/' . $userId . '/' . $gameId;
            if (is_dir($gameDir)) {
                Folder::delete($gameDir);
            }
            $app->enqueueMessage('بازی رد شد و فایل‌های آن از سرور حذف گردید.', 'info');
        } else {
            $app->enqueueMessage('خطا در حذف بازی.', 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=games', false));
    }

    private function updateGameStatus(int $userId, string $gameId, string $action, string $newTier = '', int $newReward = -1): bool
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
                if ($action === 'approved' && ($game['status'] ?? '') !== 'approved') {
                    // اعمال سطح و پاداش جدید انتخاب‌شده توسط ادمین
                    if (!empty($newTier)) {
                        $game['tier'] = $newTier;
                    }
                    $finalReward = ($newReward >= 0) ? $newReward : (int) ($game['reward'] ?? 10);
                    $game['reward'] = $finalReward;
                    $game['status'] = 'approved';

                    $state['bitblox'] = (int) ($state['bitblox'] ?? 0) + $finalReward;
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