<?php
namespace BastanGraphic\Component\Bloxia\Administrator\View\Bank;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

class HtmlView extends BaseHtmlView
{
    protected object $settings;
    protected array $orders = [];
    protected string $webhookUrl = '';

    public function display($tpl = null)
    {
        ToolbarHelper::title('تنظیمات بانک، کارت به کارت و SMS Forwarder', 'credit');

        $this->settings = BloxiaHelper::getBankSettings();
        $this->webhookUrl = Uri::root() . 'index.php?option=com_bloxia&task=bank.smsWebhook&format=json&key=' . urlencode($this->settings->sms_secret);

        $db = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();

        // منقضی کردن سفارش‌های قدیمی‌تر از ۱۰ دقیقه
        $expQ = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_bank_orders'))
            ->set($db->quoteName('status') . ' = ' . $db->quote('expired'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'))
            ->where($db->quoteName('expires_at') . ' < ' . $db->quote($now));
        $db->setQuery($expQ)->execute();

        $q = $db->getQuery(true)
            ->select(['o.*', 'u.name AS user_name', 'u.username'])
            ->from($db->quoteName('#__bloxia_bank_orders', 'o'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = o.user_id')
            ->order($db->quoteName('o.id') . ' DESC');
        $db->setQuery($q, 0, 50);
        $this->orders = $db->loadObjectList() ?: [];

        parent::display($tpl);
    }
}
