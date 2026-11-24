<?php
namespace BastanGraphic\Component\Bloxia\Administrator\View\Items;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

class HtmlView extends BaseHtmlView
{
    protected array $items = [];
    protected array $state = [];

    public function display($tpl = null)
    {
        ToolbarHelper::title('مدیریت فروشگاه و قیمت‌گذاری لوازم Bloxia', 'cart');

        $app = Factory::getApplication();
        $this->state = [
            'search' => trim($app->getUserStateFromRequest('com_bloxia.items.search', 'filter_search', '', 'string')),
            'type'   => $app->getUserStateFromRequest('com_bloxia.items.type', 'filter_type', '', 'cmd'),
        ];

        $all = BloxiaHelper::syncAndGetItems(false);

        $this->items = array_values(array_filter($all, function ($it) {
            if ($this->state['type'] !== '' && $it['type'] !== $this->state['type']) {
                return false;
            }
            if ($this->state['search'] !== '') {
                $s = mb_strtolower($this->state['search']);
                if (mb_stripos($it['title'], $s) === false && mb_stripos($it['key'], $s) === false) {
                    return false;
                }
            }
            return true;
        }));

        parent::display($tpl);
    }
}
