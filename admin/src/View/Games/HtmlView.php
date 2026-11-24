<?php
namespace BastanGraphic\Component\Bloxia\Administrator\View\Games;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Pagination\Pagination;

class HtmlView extends BaseHtmlView
{
    protected $games = [];
    protected $pagination;
    protected $state = [];

    public function display($tpl = null)
    {
        ToolbarHelper::title('مدیریت بازی‌های ارسالی Bloxia', 'gamepad');

        $app = Factory::getApplication();

        // خواندن فیلترها و تنظیمات مرتب‌سازی از درخواست
        $this->state = [
            'search'   => trim($app->getUserStateFromRequest('com_bloxia.games.search', 'filter_search', '', 'string')),
            'status'   => $app->getUserStateFromRequest('com_bloxia.games.status', 'filter_status', '', 'cmd'),
            'tier'     => $app->getUserStateFromRequest('com_bloxia.games.tier', 'filter_tier', '', 'cmd'),
            'ordering' => $app->getUserStateFromRequest('com_bloxia.games.ordering', 'list_fullordering', 'status_asc', 'cmd'),
            'limit'    => $app->getUserStateFromRequest('com_bloxia.games.limit', 'limit', 20, 'int'),
            'start'    => $app->input->getInt('limitstart', 0),
        ];

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select(['p.user_id', 'p.character_data', 'u.name', 'u.username'])
            ->from($db->quoteName('#__bloxia_user_profiles', 'p'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = p.user_id')
            ->where($db->quoteName('p.character_data') . ' IS NOT NULL');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $allGames = [];
        foreach ($rows as $row) {
            $data = json_decode($row->character_data, true);
            if (!empty($data['myGames']) && is_array($data['myGames'])) {
                foreach ($data['myGames'] as $g) {
                    $item = (object) [
                        'user_id' => (int) $row->user_id,
                        'author'  => $data['name'] ?? $row->name ?? $row->username,
                        'id'      => $g['id'] ?? '',
                        'title'   => $g['title'] ?? 'بدون عنوان',
                        'tier'       => $g['tier'] ?? 'simple',
                        'reward'     => (int) ($g['reward'] ?? 10),
                        'play_count' => (int) ($g['play_count'] ?? 0),
                        'status'     => $g['status'] ?? 'pending',
                        'url'        => $g['url'] ?? '#'
                    ];

                    // اعمال فیلتر جستجو
                    if ($this->state['search'] !== '') {
                        $s = mb_strtolower($this->state['search']);
                        if (mb_stripos($item->title, $s) === false && mb_stripos($item->author, $s) === false) {
                            continue;
                        }
                    }
                    // اعمال فیلتر وضعیت
                    if ($this->state['status'] !== '' && $item->status !== $this->state['status']) {
                        continue;
                    }
                    // اعمال فیلتر سطح
                    if ($this->state['tier'] !== '' && $item->tier !== $this->state['tier']) {
                        continue;
                    }

                    $allGames[] = $item;
                }
            }
        }

        // مرتب‌سازی بر اساس انتخاب ادمین
        $ord = $this->state['ordering'];
        usort($allGames, function ($a, $b) use ($ord) {
            switch ($ord) {
                case 'title_asc':   return strcmp($a->title, $b->title);
                case 'title_desc':  return strcmp($b->title, $a->title);
                case 'author_asc':  return strcmp($a->author, $b->author);
                case 'reward_desc': return $b->reward <=> $a->reward;
                case 'reward_asc':  return $a->reward <=> $b->reward;
                case 'status_desc': return strcmp($a->status, $b->status); // approved first
                case 'status_asc':
                default:
                    if ($a->status === $b->status) return 0;
                    return ($a->status === 'pending') ? -1 : 1; // pending first
            }
        });

        // صفحه‌بندی بومی جوملا
        $total = count($allGames);
        $limit = $this->state['limit'] > 0 ? $this->state['limit'] : $total;
        $this->pagination = new Pagination($total, $this->state['start'], $this->state['limit']);
        $this->games = $limit > 0 ? array_slice($allGames, $this->state['start'], $limit) : $allGames;

        parent::display($tpl);
    }
}