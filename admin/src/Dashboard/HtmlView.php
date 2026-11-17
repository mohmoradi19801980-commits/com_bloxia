<?php
namespace BastanGraphic\Component\Bloxia\Administrator\View\Dashboard;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        // در اینجا بعدا می‌توانیم تولبار و دکمه‌های مدیریتی را اضافه کنیم
        parent::display($tpl);
    }
}