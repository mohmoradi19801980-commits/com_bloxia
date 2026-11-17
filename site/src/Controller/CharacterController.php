<?php
namespace BastanGraphic\Component\Bloxia\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

class CharacterController extends BaseController
{
    public function save()
    {
        Session::checkToken() or die('Invalid Token');
        $app = Factory::getApplication();
        $user = Factory::getUser();

        if ($user->guest) {
            echo new JsonResponse(null, 'دسترسی غیرمجاز', true);
            $app->close();
        }

        // دریافت دیتای JSON که از جاوا اسکریپت ارسال شده
        $characterData = $app->input->post->get('character_data', '', 'raw');

        if (empty($characterData)) {
            echo new JsonResponse(null, 'دیتای کاراکتر خالی است', true);
            $app->close();
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        
        // آپدیت پروفایل کاربر
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote($characterData))
            ->set($db->quoteName('has_onboarded') . ' = 1')
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
            
        $db->setQuery($query);
        $db->execute();

        // هدایت کاربر به صفحه شهر سه‌بعدی بعد از ذخیره موفق
        $redirectUrl = Route::_('index.php?option=com_bloxia&view=world', false);
        echo new JsonResponse(['redirect' => $redirectUrl], 'کاراکتر با موفقیت ذخیره شد');
        $app->close();
    }
}