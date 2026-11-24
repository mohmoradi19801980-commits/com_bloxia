<?php
namespace BastanGraphic\Component\Bloxia\Administrator\View\Users;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Pagination\Pagination;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

class HtmlView extends BaseHtmlView
{
    protected array $users = [];
    protected ?Pagination $pagination = null;
    protected array $state = [];
    protected array $summary = [
        'total_users'   => 0,
        'online_users'  => 0,
        'total_bitblox' => 0,
        'total_plays'   => 0,
    ];

    public function display($tpl = null)
    {
        ToolbarHelper::title('مدیریت کاربران و کیف پول BitBlox', 'users');
        BloxiaHelper::ensureTables();

        $app = Factory::getApplication();
        $this->state = [
            'search'   => trim($app->getUserStateFromRequest('com_bloxia.users.search', 'filter_search', '', 'string')),
            'ordering' => $app->getUserStateFromRequest('com_bloxia.users.ordering', 'list_fullordering', 'points_desc', 'cmd'),
            'limit'    => $app->getUserStateFromRequest('com_bloxia.users.limit', 'limit', 20, 'int'),
            'start'    => $app->input->getInt('limitstart', 0),
        ];

        $db = Factory::getContainer()->get('DatabaseDriver');
        $onlineCutoff = Factory::getDate('-15 seconds')->toSql();

        // شمارش تعداد اجرای بازی ثبت‌شده در لاگ برای هر کاربر (بازی‌هایی که دیگران یا خودش اجرا کرده)
        $playCountsByUser = [];
        try {
            $playQuery = $db->getQuery(true)
                ->select(['user_id', 'COUNT(*) AS cnt'])
                ->from($db->quoteName('#__bloxia_logs'))
                ->where($db->quoteName('action_type') . ' = ' . $db->quote('game_play'))
                ->group($db->quoteName('user_id'));
            $db->setQuery($playQuery);
            foreach ($db->loadObjectList() as $pRow) {
                $playCountsByUser[(int) $pRow->user_id] = (int) $pRow->cnt;
            }
        } catch (\Throwable $e) {
        }

        $query = $db->getQuery(true)
            ->select([
                'p.user_id',
                'p.bloxia_points',
                'p.character_data',
                'u.name',
                'u.username',
                'u.email',
                'pr.updated_at AS last_seen',
                'pr.zone AS current_zone'
            ])
            ->from($db->quoteName('#__bloxia_user_profiles', 'p'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON u.id = p.user_id')
            ->join('LEFT', $db->quoteName('#__bloxia_presence', 'pr') . ' ON pr.user_id = p.user_id');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $allUsers = [];
        foreach ($rows as $row) {
            $data = !empty($row->character_data) ? json_decode($row->character_data, true) : [];
            $games = $data['myGames'] ?? [];
            $approvedGames = 0;
            $hostedPlays = 0;
            foreach ($games as $g) {
                if (($g['status'] ?? '') === 'approved') {
                    $approvedGames++;
                }
                $hostedPlays += (int) ($g['play_count'] ?? 0);
            }

            $bitblox = isset($data['bitblox']) ? (int) $data['bitblox'] : (int) $row->bloxia_points;
            $isOnline = !empty($row->last_seen) && $row->last_seen >= $onlineCutoff;
            $playedCount = $playCountsByUser[(int) $row->user_id] ?? 0;

            $this->summary['total_users']++;
            if ($isOnline) $this->summary['online_users']++;
            $this->summary['total_bitblox'] += $bitblox;
            $this->summary['total_plays'] += $hostedPlays;

            $item = (object) [
                'user_id'        => (int) $row->user_id,
                'character_name' => $data['name'] ?? $row->name,
                'joomla_name'    => $row->name,
                'username'       => $row->username,
                'bitblox'        => $bitblox,
                'games_total'    => count($games),
                'games_approved' => $approvedGames,
                'hosted_plays'   => $hostedPlays,
                'played_count'   => $playedCount,
                'is_online'      => $isOnline,
                'current_zone'   => $row->current_zone ?? '',
            ];

            if ($this->state['search'] !== '') {
                $s = mb_strtolower($this->state['search']);
                if (
                    mb_stripos($item->character_name, $s) === false &&
                    mb_stripos($item->joomla_name, $s) === false &&
                    mb_stripos($item->username, $s) === false
                ) {
                    continue;
                }
            }

            $allUsers[] = $item;
        }

        $ord = $this->state['ordering'];
        usort($allUsers, function ($a, $b) use ($ord) {
            switch ($ord) {
                case 'points_asc':  return $a->bitblox <=> $b->bitblox;
                case 'plays_desc':  return $b->hosted_plays <=> $a->hosted_plays;
                case 'name_asc':    return strcmp($a->character_name, $b->character_name);
                case 'points_desc':
                default:            return $b->bitblox <=> $a->bitblox;
            }
        });

        $total = count($allUsers);
        $limit = $this->state['limit'] > 0 ? $this->state['limit'] : $total;
        $this->pagination = new Pagination($total, $this->state['start'], $this->state['limit']);
        $this->users = $limit > 0 ? array_slice($allUsers, $this->state['start'], $limit) : $allUsers;

        parent::display($tpl);
    }
}
