<?php
namespace BastanGraphic\Component\Bloxia\Site\View\World;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $user = Factory::getUser();
        
        // اگر کاربر مهمان بود و لاگین نکرده بود، پرتاب شود به صفحه ورود
        if ($user->guest) {
            Factory::getApplication()->redirect('index.php?option=com_bloxia&view=login');
        }

        // نمایش فایل قالب (default.php)
        parent::display($tpl);
    }
}