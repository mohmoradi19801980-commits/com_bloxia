<?php
namespace BastanGraphic\Component\Bloxia\Administrator\View\Games;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $games = [];

    public function display($tpl = null)
    {
        ToolbarHelper::title('مدیریت بازی‌های ارسالی Bloxia', 'gamepad');

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
                    $allGames[] = (object) [
                        'user_id'  => (int) $row->user_id,
                        'author'   => $data['name'] ?? $row->name ?? $row->username,
                        'id'       => $g['id'] ?? '',
                        'title'    => $g['title'] ?? 'بدون عنوان',
                        'tier'     => $g['tier'] ?? 'simple',
                        'reward'   => (int) ($g['reward'] ?? 10),
                        'status'   => $g['status'] ?? 'pending',
                        'url'      => $g['url'] ?? '#'
                    ];
                }
            }
        }

        // مرتب‌سازی: بازی‌های در انتظار تایید همیشه در بالای لیست باشند
        usort($allGames, function ($a, $b) {
            if ($a->status === $b->status) return 0;
            return ($a->status === 'pending') ? -1 : 1;
        });

        $this->games = $allGames;

        parent::display($tpl);
    }
}