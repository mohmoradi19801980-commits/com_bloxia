<?php
namespace BastanGraphic\Component\Bloxia\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

class UsersController extends BaseController
{
    /**
     * تغییر دستی موجودی BitBlox کاربر توسط ادمین و ثبت در جدول لاگ‌ها
     */
    public function adjustWallet()
    {
        Session::checkToken() or die('Invalid Token');

        $app      = Factory::getApplication();
        $admin    = Factory::getUser();
        $userId   = $app->input->post->getInt('user_id', 0);
        $mode     = $app->input->post->getCmd('mode', 'add'); // add | sub | set
        $amount   = max(0, $app->input->post->getInt('amount', 0));
        $reason   = trim($app->input->post->getString('reason', ''));

        if ($userId <= 0) {
            $app->enqueueMessage('کاربر نامعتبر است.', 'error');
            $this->setRedirect(Route::_('index.php?option=com_bloxia&view=users', false));
            return;
        }

        BloxiaHelper::ensureTables();
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . $userId);
        $db->setQuery($query);
        $profile = $db->loadObject();

        if (!$profile) {
            $app->enqueueMessage('پروفایل کاربر در Bloxia یافت نشد.', 'warning');
            $this->setRedirect(Route::_('index.php?option=com_bloxia&view=users', false));
            return;
        }

        $state = !empty($profile->character_data) ? json_decode($profile->character_data, true) : [];
        if (!is_array($state)) {
            $state = [];
        }

        $oldBalance = isset($state['bitblox']) ? (int) $state['bitblox'] : (int) $profile->bloxia_points;
        $newBalance = $oldBalance;

        if ($mode === 'add') {
            $newBalance = $oldBalance + $amount;
        } elseif ($mode === 'sub') {
            $newBalance = max(0, $oldBalance - $amount);
        } elseif ($mode === 'set') {
            $newBalance = max(0, $amount);
        }

        $actualDelta = $newBalance - $oldBalance;
        $state['bitblox'] = $newBalance;

        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($state)))
            ->set($db->quoteName('bloxia_points') . ' = ' . $newBalance)
            ->where($db->quoteName('user_id') . ' = ' . $userId);
        $db->setQuery($updateQuery)->execute();

        $logDetail = 'تغییر دستی موجودی از ' . $oldBalance . ' به ' . $newBalance . ' BitBlox';
        if ($reason !== '') {
            $logDetail .= ' | علت: ' . $reason;
        }
        BloxiaHelper::logActivity($userId, 'admin_wallet_adjust', $actualDelta, (int) $admin->id, $logDetail);

        $app->enqueueMessage('موجودی BitBlox کاربر با موفقیت به‌روزرسانی شد (' . $oldBalance . ' ➔ ' . $newBalance . ').', 'success');
        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=users', false));
    }
}
