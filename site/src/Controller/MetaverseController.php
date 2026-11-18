<?php
namespace BastanGraphic\Component\Bloxia\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

class MetaverseController extends BaseController
{
    // ۱. دریافت اطلاعات کاراکتر
    public function getCharacter()
    {
        $app = Factory::getApplication();
        $user = Factory::getUser();

        if ($user->guest) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            $app->close();
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
            
        $db->setQuery($query);
        $profile = $db->loadObject();

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

        if ($profile && !empty($profile->character_data)) {
            $savedData = json_decode($profile->character_data, true);
            if (is_array($savedData)) {
                $state = array_replace_recursive($state, $savedData);
            }
        }

        // استفاده از json_encode استاندارد برای تطابق دقیق با کدهای جاوا اسکریپت
        header('Content-Type: application/json');
        echo json_encode($state);
        $app->close();
    }

    // ۲. ذخیره ساخت اولیه کاراکتر
    public function saveInitialCas()
    {
        // Session::checkToken() or die(json_encode(['error' => 'Invalid Token']));
        $app = Factory::getApplication();
        $user = Factory::getUser();

        if ($user->guest) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            $app->close();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $appearance = $input['appearance'] ?? [];
        $name = $input['name'] ?? $user->name;

        $db = Factory::getContainer()->get('DatabaseDriver');
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

        if (isset($state['freeCasUsed']) && $state['freeCasUsed'] === true) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ویرایش رایگان اولیه قبلا انجام شده!']);
            $app->close();
        }

        $state['appearance'] = array_merge($state['appearance'] ?? [], $appearance);
        $state['name'] = $name;
        $state['freeCasUsed'] = true;
        
        $joomlaUser = \Joomla\CMS\User\User::getInstance($user->id);
        $joomlaUser->name = $name;
        $joomlaUser->save();

        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($state)))
            ->set($db->quoteName('has_onboarded') . ' = 1')
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
            
        $db->setQuery($updateQuery);
        $db->execute();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'state' => $state]);
        $app->close();
    }

    // متدهای خالی برای جلوگیری از ارور ۴۰۴ وقتی کاربر روی بقیه دکمه‌ها کلیک می‌کند
    // (در مراحل بعدی منطق این متدها را کامل می‌کنیم)
    public function getHome() { header('Content-Type: application/json'); echo json_encode([]); Factory::getApplication()->close(); }
    public function sync() { header('Content-Type: application/json'); echo json_encode([]); Factory::getApplication()->close(); }
    public function hairSalon() { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'در حال توسعه']); Factory::getApplication()->close(); }
    public function submitGame() { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'در حال توسعه']); Factory::getApplication()->close(); }
    public function walletTx() { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'در حال توسعه']); Factory::getApplication()->close(); }
    public function buyCart() { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'در حال توسعه']); Factory::getApplication()->close(); }
    public function equip() { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'در حال توسعه']); Factory::getApplication()->close(); }
    public function saveHome() { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'در حال توسعه']); Factory::getApplication()->close(); }
}