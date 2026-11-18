<?php
namespace BastanGraphic\Component\Bloxia\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;

class MetaverseController extends BaseController
{
    // ۱. ارسال تمام اطلاعات کاراکتر، لباس‌ها و خانه به موتور بازی هنگام لود اولیه
    public function getCharacter()
    {
        $app = Factory::getApplication();
        $user = Factory::getUser();

        if ($user->guest) {
            echo new JsonResponse(null, 'Unauthorized', true);
            $app->close();
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
            
        $db->setQuery($query);
        $profile = $db->loadObject();

        // اسکلت‌بندی پیش‌فرض یک بازیکن جدید
        $state = [
            'userId' => $user->id,
            'name' => $user->name,
            'bitblox' => $profile ? (int) $profile->bloxia_points : 500,
            'freeCasUsed' => false,
            'appearance' => [
                'skinColor' => 0xffdbac,
                'hairStyle' => 'short',
                'hairColor' => 0x451a03,
                'shirtColor' => 0x3b82f6,
                'pantsColor' => 0x1e293b,
                'shoesColor' => 0xf8fafc,
                'hat' => null,
                'handProp' => null
            ],
            'wardrobe' => [],
            'furnitureInventory' => [],
            'homeLayout' => [],
            'myGames' => []
        ];

        // اگر قبلاً دیتایی ذخیره شده بود، آن را جایگزین پیش‌فرض‌ها کن
        if ($profile && !empty($profile->character_data)) {
            $savedData = json_decode($profile->character_data, true);
            if (is_array($savedData)) {
                $state = array_replace_recursive($state, $savedData);
            }
        }

        echo new JsonResponse($state);
        $app->close();
    }

    // ۲. ذخیره ظاهر و نام کاربر در اولین باری که کاراکتر می‌سازد (فرم ۱ بار رایگان)
    public function saveInitialCas()
    {
        Session::checkToken() or die('Invalid Token');
        $app = Factory::getApplication();
        $user = Factory::getUser();

        if ($user->guest) {
            echo new JsonResponse(null, 'Unauthorized', true);
            $app->close();
        }

        // خواندن دیتای خام JSON که از جاوا اسکریپت آمده
        $input = json_decode(file_get_contents('php://input'), true);
        $appearance = $input['appearance'] ?? [];
        $name = $input['name'] ?? $user->name;

        $db = Factory::getContainer()->get('DatabaseDriver');
        
        // خواندن وضعیت فعلی دیتابیس
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
        $db->setQuery($query);
        $profile = $db->loadObject();

        $state = [];
        if ($profile && !empty($profile->character_data)) {
            $state = json_decode($profile->character_data, true);
        }

        // جلوگیری از تقلب (فقط یک بار مجاز به استفاده از این فرم است)
        if (isset($state['freeCasUsed']) && $state['freeCasUsed'] === true) {
            echo new JsonResponse(null, 'ویرایش رایگان اولیه قبلا انجام شده!', true);
            $app->close();
        }

        // اعمال تغییرات جدید
        $state['appearance'] = array_merge($state['appearance'] ?? [], $appearance);
        $state['name'] = $name;
        $state['freeCasUsed'] = true;
        
        // همگام‌سازی نام کاربر در سیستم اصلی جوملا
        $joomlaUser = \Joomla\CMS\User\User::getInstance($user->id);
        $joomlaUser->name = $name;
        $joomlaUser->save();

        // ذخیره تغییرات در دیتابیس Bloxia
        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($state)))
            ->set($db->quoteName('has_onboarded') . ' = 1')
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
            
        $db->setQuery($updateQuery);
        $db->execute();

        echo new JsonResponse(['success' => true, 'state' => $state]);
        $app->close();
    }
}