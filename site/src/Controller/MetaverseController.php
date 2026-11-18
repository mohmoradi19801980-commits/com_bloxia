<?php
namespace BastanGraphic\Component\Bloxia\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class MetaverseController extends BaseController
{
    // ==========================================
    // توابع کمکی (خواندن و نوشتن امن در دیتابیس)
    // ==========================================
    private function loadUserState()
    {
        $user = Factory::getUser();
        if ($user->guest) return null;

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_user_profiles'))->where('user_id = ' . (int)$user->id);
        $db->setQuery($query);
        $profile = $db->loadObject();

        // ساختار حیاتی پیش‌فرض (جلوگیری از خطای خالی بودن دیتابیس هنگام خرید)
        $state = [
            'userId' => $user->id,
            'name' => $user->name,
            'bitblox' => $profile && (int)$profile->bloxia_points > 0 ? (int)$profile->bloxia_points : 500,
            'freeCasUsed' => false,
            'appearance' => [
                'skinColor' => 0xffdbac, 'hairStyle' => 'short', 'hairColor' => 0x451a03,
                'shirtColor' => 0x3b82f6, 'pantsColor' => 0x1e293b, 'shoesColor' => 0xf8fafc,
                'hat' => null, 'handProp' => null
            ],
            'wardrobe' => [],
            'furnitureInventory' => [],
            'homeLayout' => [],
            'myGames' => []
        ];

        // اگر دیتایی از قبل ذخیره شده بود، روی حالت پیش‌فرض می‌نشیند
        if ($profile && !empty($profile->character_data)) {
            $savedData = json_decode($profile->character_data, true);
            if (is_array($savedData)) {
                $state = array_replace_recursive($state, $savedData);
            }
        }
        return $state;
    }

    private function saveUserState($state)
    {
        $user = Factory::getUser();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($state)))
            ->set($db->quoteName('bloxia_points') . ' = ' . (int)$state['bitblox'])
            ->set($db->quoteName('has_onboarded') . ' = 1')
            ->where('user_id = ' . (int)$user->id);
        $db->setQuery($query)->execute();
    }

    private function getItemDetails($itemId)
    {
        $catalog = [
            'hat_crown' => ['type'=>'hat', 'price'=>80, 'color'=>0xfbbf24],
            'hat_cap' => ['type'=>'hat', 'price'=>40, 'color'=>0x3b82f6],
            'outfit_pink' => ['type'=>'shirt', 'price'=>60, 'color'=>0xec4899],
            'outfit_purple' => ['type'=>'shirt', 'price'=>70, 'color'=>0x8b5cf6],
            'shoes_red' => ['type'=>'shoes', 'price'=>45, 'color'=>0xef4444],
            'shoes_gold' => ['type'=>'shoes', 'price'=>65, 'color'=>0xf59e0b],
            'prop_sword' => ['type'=>'handProp', 'price'=>90, 'color'=>0x38bdf8],
            'prop_axe' => ['type'=>'handProp', 'price'=>85, 'color'=>0x94a3b8],
            'prop_flower' => ['type'=>'handProp', 'price'=>35, 'color'=>0xf43f5e],
            'prop_phone' => ['type'=>'handProp', 'price'=>65, 'color'=>0x1e293b],
            'furn_sofa' => ['type'=>'furniture', 'price'=>75, 'color'=>0xef4444],
            'furn_tv' => ['type'=>'furniture', 'price'=>110, 'color'=>0x1e293b],
            'furn_table' => ['type'=>'furniture', 'price'=>65, 'color'=>0xb45309],
            'furn_bed' => ['type'=>'furniture', 'price'=>120, 'color'=>0x6366f1],
            'furn_lamp' => ['type'=>'furniture', 'price'=>45, 'color'=>0xf59e0b],
            'furn_plant' => ['type'=>'furniture', 'price'=>30, 'color'=>0x16a34a]
        ];
        return $catalog[$itemId] ?? null;
    }

    // ==========================================
    // فاز اول: اقتصاد و سیستم ذخیره‌سازی
    // ==========================================

    public function getCharacter()
    {
        $state = $this->loadUserState();
        if (!$state) { header('Content-Type: application/json'); echo json_encode(['error'=>'Unauthorized']); exit; }
        header('Content-Type: application/json'); echo json_encode($state); exit;
    }

    public function walletTx()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $delta = (int) ($input['delta'] ?? 0);
        $state = $this->loadUserState();

        if ($delta < 0 && $state['bitblox'] < abs($delta)) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'موجودی BitBlox کافی نیست!']); exit;
        }

        $state['bitblox'] += $delta;
        $this->saveUserState($state);
        header('Content-Type: application/json'); echo json_encode(['success' => true, 'state' => $state]); exit;
    }

    public function hairSalon()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $cost = (int) ($input['cost'] ?? 0);
        $state = $this->loadUserState();

        if ($state['bitblox'] < $cost) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'موجودی BitBlox کافی نیست!']); exit;
        }

        $state['bitblox'] -= $cost;
        $state['appearance']['hairStyle'] = $input['hairStyle'] ?? 'short';
        $state['appearance']['hairColor'] = (int) ($input['hairColor'] ?? 0x451a03);
        $this->saveUserState($state);

        header('Content-Type: application/json'); echo json_encode(['success' => true, 'state' => $state]); exit;
    }

    public function buyCart()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $itemIds = $input['items'] ?? [];
        $state = $this->loadUserState();

        $totalCost = 0;
        $itemsToProcess = [];
        foreach ($itemIds as $id) {
            $details = $this->getItemDetails($id);
            if ($details) {
                $totalCost += $details['price'];
                $itemsToProcess[] = ['id' => $id, 'type' => $details['type'], 'color' => $details['color']];
            }
        }

        if ($state['bitblox'] < $totalCost) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'موجودی BitBlox کافی نیست!']); exit;
        }

        $state['bitblox'] -= $totalCost;

        foreach ($itemsToProcess as $item) {
            if (in_array($item['type'], ['hat', 'shirt', 'shoes', 'handProp'])) {
                if (!in_array($item['id'], $state['wardrobe'])) { $state['wardrobe'][] = $item['id']; }
                if ($item['type'] == 'hat') $state['appearance']['hat'] = $item['id'];
                if ($item['type'] == 'shirt') $state['appearance']['shirtColor'] = $item['color'];
                if ($item['type'] == 'shoes') $state['appearance']['shoesColor'] = $item['color'];
                if ($item['type'] == 'handProp') $state['appearance']['handProp'] = $item['id'];
            } elseif ($item['type'] == 'furniture') {
                $state['furnitureInventory'][] = $item['id'];
            }
        }

        $this->saveUserState($state);
        header('Content-Type: application/json'); echo json_encode(['success' => true, 'state' => $state]); exit;
    }

    public function equip()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $itemId = $input['item_id'] ?? '';
        $state = $this->loadUserState();
        $details = $this->getItemDetails($itemId);

        if ($details && in_array($itemId, $state['wardrobe'])) {
            $type = $details['type'];
            if ($type == 'hat') $state['appearance']['hat'] = ($state['appearance']['hat'] === $itemId) ? null : $itemId;
            elseif ($type == 'shirt') $state['appearance']['shirtColor'] = $details['color'];
            elseif ($type == 'shoes') $state['appearance']['shoesColor'] = $details['color'];
            elseif ($type == 'handProp') $state['appearance']['handProp'] = ($state['appearance']['handProp'] === $itemId) ? null : $itemId;
            $this->saveUserState($state);
        }

        header('Content-Type: application/json'); echo json_encode($state); exit;
    }

    public function saveHome()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $state = $this->loadUserState();
        $state['homeLayout'] = $input['layout'] ?? [];
        $state['furnitureInventory'] = $input['inventory'] ?? [];
        $this->saveUserState($state);
        header('Content-Type: application/json'); echo json_encode($state); exit;
    }

    public function saveInitialCas()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $state = $this->loadUserState();

        if (isset($state['freeCasUsed']) && $state['freeCasUsed'] === true) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'ویرایش رایگان اولیه قبلا انجام شده!']); exit;
        }

        $state['appearance'] = array_merge($state['appearance'] ?? [], $input['appearance'] ?? []);
        $state['name'] = $input['name'] ?? Factory::getUser()->name;
        $state['freeCasUsed'] = true;
        
        $user = Factory::getUser();
        $joomlaUser = \Joomla\CMS\User\User::getInstance($user->id);
        $joomlaUser->name = $state['name'];
        $joomlaUser->save();

        $this->saveUserState($state);
        header('Content-Type: application/json'); echo json_encode(['success' => true, 'state' => $state]); exit;
    }
    
    public function getHome() 
    { 
        $app = Factory::getApplication();
        $user = Factory::getUser();
        $ownerId = (int) $app->input->get('owner_id', 0);
        
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_user_profiles'))->where('user_id = ' . $ownerId);
        $db->setQuery($query);
        $profile = $db->loadObject();

        // تشخیص اینکه آیا کاربری که وارد خانه شده، خودش صاحب‌خانه است یا خیر
        $isMine = (!$user->guest && $user->id == $ownerId);
        $layout = [];
        $games = [];
        $ownerName = 'شهروند';

        if ($profile && !empty($profile->character_data)) {
            $data = json_decode($profile->character_data, true);
            $layout = $data['homeLayout'] ?? [];
            $games = $data['myGames'] ?? [];
            $ownerName = $data['name'] ?? 'شهروند';
        }

        header('Content-Type: application/json'); 
        echo json_encode([
            'ownerId' => $ownerId, 
            'ownerName' => $ownerName, 
            'isMine' => $isMine, 
            'layout' => $layout, 
            'games' => $games
        ]); 
        exit; 
    }
    
    public function syncWorld() 
    { 
        header('Content-Type: application/json'); 
        echo json_encode([]); 
        exit; 
    }
}