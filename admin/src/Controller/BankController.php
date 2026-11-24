<?php
namespace BastanGraphic\Component\Bloxia\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

class BankController extends BaseController
{
    public function saveSettings()
    {
        Session::checkToken() or die('Invalid Token');

        $app       = Factory::getApplication();
        $cardNum   = trim($app->input->post->getString('card_number', ''));
        $cardHold  = trim($app->input->post->getString('card_holder', ''));
        $smsSecret = trim($app->input->post->getString('sms_secret', ''));
        $videoUrl  = trim($app->input->post->getString('tutorial_video_url', ''));
        $pdfUrl    = trim($app->input->post->getString('tutorial_pdf_url', ''));

        if ($smsSecret === '') {
            $smsSecret = bin2hex(random_bytes(12));
        }

        BloxiaHelper::getBankSettings();
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_bank_settings'))
            ->set($db->quoteName('card_number') . ' = ' . $db->quote($cardNum))
            ->set($db->quoteName('card_holder') . ' = ' . $db->quote($cardHold))
            ->set($db->quoteName('sms_secret') . ' = ' . $db->quote($smsSecret))
            ->set($db->quoteName('tutorial_video_url') . ' = ' . $db->quote($videoUrl))
            ->set($db->quoteName('tutorial_pdf_url') . ' = ' . $db->quote($pdfUrl))
            ->where('id = 1');
        $db->setQuery($query)->execute();

        $app->enqueueMessage('تنظیمات بانک و کارت به کارت با موفقیت ذخیره شد.', 'success');
        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=bank', false));
    }

    public function approveOrder()
    {
        Session::checkToken('get') or die('Invalid Token');

        $app     = Factory::getApplication();
        $admin   = Factory::getUser();
        $orderId = $app->input->getInt('order_id', 0);

        BloxiaHelper::ensureTables();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_bank_orders'))->where('id = ' . $orderId));
        $order = $db->loadObject();

        if (!$order || $order->status === 'paid') {
            $app->enqueueMessage('سفارش یافت نشد یا قبلاً تایید شده است.', 'warning');
            $this->setRedirect(Route::_('index.php?option=com_bloxia&view=bank', false));
            return;
        }

        $now = Factory::getDate()->toSql();
        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_bank_orders'))
            ->set($db->quoteName('status') . ' = ' . $db->quote('paid'))
            ->set($db->quoteName('paid_at') . ' = ' . $db->quote($now))
            ->set($db->quoteName('sms_raw') . ' = ' . $db->quote('تایید دستی توسط ادمین #' . $admin->id))
            ->where('id = ' . $orderId)
            ->where($db->quoteName('status') . ' != ' . $db->quote('paid'));
        $db->setQuery($upd)->execute();

        if ($db->getAffectedRows() > 0) {
            BloxiaHelper::creditUserBitblox(
                (int) $order->user_id,
                (int) $order->bitblox_amount,
                'bank_topup',
                'شارژ حساب تایید دستی (مبلغ: ' . number_format((int) $order->total_toman) . ' تومان)',
                (int) $admin->id
            );
            $app->enqueueMessage('سفارش #' . $orderId . ' تایید و ' . $order->bitblox_amount . ' BitBlox واریز شد.', 'success');
        }

        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=bank', false));
    }
}
