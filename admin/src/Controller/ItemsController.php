<?php
namespace BastanGraphic\Component\Bloxia\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

class ItemsController extends BaseController
{
    public function savePrices()
    {
        Session::checkToken() or die('Invalid Token');

        $app       = Factory::getApplication();
        $prices    = $app->input->post->get('price', [], 'array');
        $titles    = $app->input->post->get('title', [], 'array');
        $published = $app->input->post->get('published', [], 'array');

        BloxiaHelper::ensureTables();
        $db = Factory::getContainer()->get('DatabaseDriver');

        $updated = 0;
        foreach ($prices as $id => $priceVal) {
            $itemId = (int) $id;
            if ($itemId <= 0) {
                continue;
            }
            $newPrice = max(0, (int) $priceVal);
            $newTitle = isset($titles[$id]) ? trim((string) $titles[$id]) : '';
            $isPub    = isset($published[$id]) ? 1 : 0;

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bloxia_items'))
                ->set($db->quoteName('price') . ' = ' . $newPrice)
                ->set($db->quoteName('published') . ' = ' . $isPub)
                ->where($db->quoteName('id') . ' = ' . $itemId);

            if ($newTitle !== '') {
                $query->set($db->quoteName('title') . ' = ' . $db->quote($newTitle));
            }

            $db->setQuery($query)->execute();
            $updated++;
        }

        $app->enqueueMessage('قیمت و وضعیت ' . $updated . ' آیتم فروشگاه با موفقیت ذخیره شد.', 'success');
        $this->setRedirect(Route::_('index.php?option=com_bloxia&view=items', false));
    }
}
