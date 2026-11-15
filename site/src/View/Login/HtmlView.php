<?php
namespace BastanGraphic\Component\Bloxia\Site\View\Login;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        
        // اگر کاربر از قبل لاگین باشد، مستقیم به صفحه کاراکتر یا دنیا هدایت می‌شود
        if (!Factory::getUser()->guest) {
            $app->redirect('index.php?option=com_bloxia&view=world');
        }

        // اضافه کردن فایل‌های CSS و JS اختصاصی ما
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('com_bloxia.login', 'com_bloxia/style.css');
        $wa->registerAndUseScript('com_bloxia.login', 'com_bloxia/login.js', [], ['defer' => true]);

        parent::display($tpl);
    }
}