<?php
namespace BastanGraphic\Component\Bloxia\Administrator\View\Logs;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Pagination\Pagination;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

class HtmlView extends BaseHtmlView
{
    protected array $logs = [];
    protected array $userOptions = [];
    protected ?Pagination $pagination = null;
    protected array $state = [];
    protected array $stats = [
        'total_logs'    => 0,
        'total_plays'   => 0,
        'total_income'  => 0,
        'total_spent'   => 0,
    ];

    public function display($tpl = null)
    {
        ToolbarHelper::title('لاگ فعالیت‌ها و گزارش بازی‌های Bloxia', 'list');
        BloxiaHelper::ensureTables();

        $app = Factory::getApplication();
        $this->state = [
            'user_id' => $app->getUserStateFromRequest('com_bloxia.logs.user_id', 'filter_user_id', 0, 'int'),
            'action'  => $app->getUserStateFromRequest('com_bloxia.logs.action', 'filter_action', '', 'cmd'),
            'search'  => trim($app->getUserStateFromRequest('com_bloxia.logs.search', 'filter_search', '', 'string')),
            'limit'   => $app->getUserStateFromRequest('com_bloxia.logs.limit', 'limit', 30, 'int'),
            'start'   => $app->input->getInt('limitstart', 0),
        ];

        $db = Factory::getContainer()->get('DatabaseDriver');

        // لیست کاربران برای فیلتر
        $uQuery = $db->getQuery(true)
            ->select(['u.id', 'u.name', 'u.username'])
            ->from($db->quoteName('#__bloxia_user_profiles', 'p'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON u.id = p.user_id')
            ->order($db->quoteName('u.name') . ' ASC');
        $db->setQuery($uQuery);
        $this->userOptions = $db->loadObjectList() ?: [];

        // آمار کلی
        $statQuery = $db->getQuery(true)
            ->select([
                'COUNT(*) AS total_logs',
                'SUM(CASE WHEN action_type = ' . $db->quote('game_play') . ' THEN 1 ELSE 0 END) AS total_plays',
                'SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) AS total_income',
                'SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) AS total_spent',
            ])
            ->from($db->quoteName('#__bloxia_logs'));
        if ($this->state['user_id'] > 0) {
            $statQuery->where($db->quoteName('user_id') . ' = ' . (int) $this->state['user_id']);
        }
        $db->setQuery($statQuery);
        $statRow = $db->loadObject();
        if ($statRow) {
            $this->stats = [
                'total_logs'   => (int) $statRow->total_logs,
                'total_plays'  => (int) $statRow->total_plays,
                'total_income' => (int) $statRow->total_income,
                'total_spent'  => (int) $statRow->total_spent,
            ];
        }

        // ساخت کوئری اصلی با فیلترها
        $query = $db->getQuery(true)
            ->select([
                'l.*',
                'u.name AS actor_name',
                'u.username AS actor_username',
                'tu.name AS target_name'
            ])
            ->from($db->quoteName('#__bloxia_logs', 'l'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = l.user_id')
            ->join('LEFT', $db->quoteName('#__users', 'tu') . ' ON tu.id = l.target_user_id');

        if ($this->state['user_id'] > 0) {
            $query->where($db->quoteName('l.user_id') . ' = ' . (int) $this->state['user_id']);
        }
        if ($this->state['action'] !== '') {
            $query->where($db->quoteName('l.action_type') . ' = ' . $db->quote($this->state['action']));
        }
        if ($this->state['search'] !== '') {
            $like = $db->quote('%' . $db->escape($this->state['search'], true) . '%', false);
            $query->where('(' . $db->quoteName('l.details') . ' LIKE ' . $like . ' OR ' . $db->quoteName('u.name') . ' LIKE ' . $like . ')');
        }

        // شمارش کل ردیف‌های فیلترشده برای صفحه‌بندی
        $countQuery = clone $query;
        $countQuery->clear('select')->select('COUNT(*)');
        $db->setQuery($countQuery);
        $total = (int) $db->loadResult();

        $query->order($db->quoteName('l.id') . ' DESC');
        $limit = $this->state['limit'] > 0 ? $this->state['limit'] : 0;
        $db->setQuery($query, $this->state['start'], $limit);
        $this->logs = $db->loadObjectList() ?: [];

        $this->pagination = new Pagination($total, $this->state['start'], $this->state['limit']);

        parent::display($tpl);
    }
}
