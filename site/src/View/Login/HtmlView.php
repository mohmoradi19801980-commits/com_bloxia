<?php
namespace BastanGraphic\Component\Bloxia\Site\View\Login;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = \Joomla\CMS\Factory::getApplication();
        
        if (!\Joomla\CMS\Factory::getUser()->guest) {
            $app->redirect('index.php?option=com_bloxia&view=world');
        }

        // استفاده از مسیر دقیق برای لود شدن قطعی JS و CSS در جوملا ۵
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('com_bloxia.login', 'media/com_bloxia/css/style.css');
        $wa->registerAndUseScript('com_bloxia.login', 'media/com_bloxia/js/login.js', [], ['defer' => true]);

        parent::display($tpl);
    }
}