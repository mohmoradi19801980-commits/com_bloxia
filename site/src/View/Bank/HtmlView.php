<?php
namespace BastanGraphic\Component\Bloxia\Site\View\Bank;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

if (!class_exists(BloxiaHelper::class)) {
    $helperPath = JPATH_ADMINISTRATOR . '/components/com_bloxia/src/Helper/BloxiaHelper.php';
    if (is_file($helperPath)) {
        require_once $helperPath;
    }
}

class HtmlView extends BaseHtmlView
{
    protected int $bitblox = 0;
    protected string $playerName = '';
    protected object $settings;
    protected array $packages = [];

    public function display($tpl = null)
    {
        $user = Factory::getUser();
        if ($user->guest) {
            Factory::getApplication()->redirect('index.php?option=com_bloxia&view=login');
            return;
        }

        $this->settings = BloxiaHelper::getBankSettings();
        $this->packages = BloxiaHelper::getBankPackages();

        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_user_profiles'))->where('user_id = ' . (int) $user->id);
        $db->setQuery($q);
        $profile = $db->loadObject();

        $this->bitblox = $profile ? (int) $profile->bloxia_points : 500;
        $this->playerName = $user->name;
        if ($profile && !empty($profile->character_data)) {
            $data = json_decode($profile->character_data, true);
            if (isset($data['bitblox'])) {
                $this->bitblox = (int) $data['bitblox'];
            }
            if (!empty($data['name'])) {
                $this->playerName = $data['name'];
            }
        }

        parent::display($tpl);
    }
}
