<?php
namespace BastanGraphic\Component\Bloxia\Site\View\Character;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $user = Factory::getUser();
        
        // اگر کاربر لاگین نبود، برگردد به صفحه لاگین
        if ($user->guest) {
            Factory::getApplication()->redirect('index.php?option=com_bloxia&view=login');
        }

        parent::display($tpl);
    }
}