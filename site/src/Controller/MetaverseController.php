<?php
namespace BastanGraphic\Component\Bloxia\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

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
            'homeStyle' => [
                'wallStyle' => 'wall_paint_classic',
                'wallColor' => 0x94a3b8,
                'floorStyle' => 'floor_parquet_oak',
                'floorColor' => 0xd8b48b,
                'kitchenCabinetColor' => 0xf8fafc,
                'kitchenCeramicColor' => 0xe2e8f0
            ],
            'unlockedStyles' => ['wall_paint_classic', 'floor_parquet_oak'],
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
        $items = BloxiaHelper::syncAndGetItems(true);
        foreach ($items as $it) {
            if ($it['key'] === $itemId) {
                return ['type' => $it['type'], 'price' => (int) $it['price'], 'color' => (int) $it['color']];
            }
        }
        return null;
    }

    public function getCatalog()
    {
        header('Content-Type: application/json');
        $items = BloxiaHelper::syncAndGetItems(true);
        $grouped = [
            'shop_clothes' => [],
            'shop_accessories' => [],
            'shop_furniture' => [],
        ];
        foreach ($items as $it) {
            $shopKey = $it['shop'] ?? 'shop_furniture';
            $grouped[$shopKey][] = [
                'id'    => $it['key'],
                'name'  => $it['title'],
                'price' => (int) $it['price'],
                'type'  => $it['type'],
                'color' => (int) $it['color'],
            ];
        }
        echo json_encode($grouped);
        exit;
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
        $reason = trim((string) ($input['reason'] ?? 'تراکنش کیف پول'));
        BloxiaHelper::logActivity((int) $state['userId'], 'wallet_tx', $delta, null, $reason);
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
        BloxiaHelper::logActivity((int) $state['userId'], 'hair_salon', -$cost, null, 'تغییر مدل مو: ' . $state['appearance']['hairStyle']);

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
            } elseif ($item['type'] === 'wall_style') {
                if (!isset($state['unlockedStyles']) || !is_array($state['unlockedStyles'])) {
                    $state['unlockedStyles'] = ['wall_paint_classic', 'floor_parquet_oak'];
                }
                if (!in_array($item['id'], $state['unlockedStyles'])) {
                    $state['unlockedStyles'][] = $item['id'];
                }
                $state['homeStyle']['wallStyle'] = $item['id'];
                $state['homeStyle']['wallColor'] = $item['color'];
            } elseif ($item['type'] === 'floor_style') {
                if (!isset($state['unlockedStyles']) || !is_array($state['unlockedStyles'])) {
                    $state['unlockedStyles'] = ['wall_paint_classic', 'floor_parquet_oak'];
                }
                if (!in_array($item['id'], $state['unlockedStyles'])) {
                    $state['unlockedStyles'][] = $item['id'];
                }
                $state['homeStyle']['floorStyle'] = $item['id'];
                $state['homeStyle']['floorColor'] = $item['color'];
            } elseif (in_array($item['type'], ['furniture', 'frame', 'kitchen', 'lighting', 'decor', 'bedroom'])) {
                $state['furnitureInventory'][] = $item['id'];
            }
        }

        $this->saveUserState($state);
        $boughtNames = implode(', ', array_column($itemsToProcess, 'id'));
        BloxiaHelper::logActivity((int) $state['userId'], 'buy_cart', -$totalCost, null, 'خرید آیتم‌ها: ' . $boughtNames);
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
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $state = $this->loadUserState();
        if (!$state) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $oldColors = [];
        foreach (($state['homeLayout'] ?? []) as $oldItem) {
            if (!empty($oldItem['uid'])) {
                $oldColors[$oldItem['uid']] = $oldItem['color'] ?? null;
            }
        }

        $newLayout = $input['layout'] ?? [];
        $colorChanges = 0;
        foreach ($newLayout as $newItem) {
            $uid = $newItem['uid'] ?? '';
            if ($uid !== '' && array_key_exists($uid, $oldColors)) {
                $oldC = $oldColors[$uid];
                $newC = $newItem['color'] ?? null;
                if ($newC !== null && (int) $newC !== (int) $oldC) {
                    $colorChanges++;
                }
            }
        }

        $cost = $colorChanges * 10;
        if ($cost > 0 && (int) $state['bitblox'] < $cost) {
            echo json_encode(['success' => false, 'message' => "برای تغییر رنگ به {$cost} BitBlox نیاز دارید!"]);
            exit;
        }

        if ($cost > 0) {
            $state['bitblox'] -= $cost;
        }
        $state['homeLayout'] = $newLayout;
        $state['furnitureInventory'] = $input['inventory'] ?? [];
        $this->saveUserState($state);

        if ($cost > 0) {
            BloxiaHelper::logActivity((int) $state['userId'], 'color_change', -$cost, null, "تغییر رنگ {$colorChanges} وسیله خانه");
        } else {
            BloxiaHelper::logActivity((int) $state['userId'], 'save_home', 0, null, 'ذخیره چیدمان خانه (' . count($state['homeLayout']) . ' آیتم)');
        }
        echo json_encode(['success' => true, 'cost' => $cost, 'state' => $state]);
        exit;
    }

    public function saveHomeStyle()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $state = $this->loadUserState();
        if (!$state) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if (!isset($state['homeStyle']) || !is_array($state['homeStyle'])) {
            $state['homeStyle'] = [];
        }

        $colorKeys = ['wallColor', 'floorColor', 'kitchenCabinetColor', 'kitchenCeramicColor'];
        $colorChanges = 0;
        foreach ($colorKeys as $ck) {
            if (array_key_exists($ck, $input)) {
                $oldVal = isset($state['homeStyle'][$ck]) ? (int) $state['homeStyle'][$ck] : null;
                $newVal = (int) $input[$ck];
                if ($oldVal !== $newVal) {
                    $colorChanges++;
                }
            }
        }

        $cost = $colorChanges * 10;
        if ($cost > 0 && (int) $state['bitblox'] < $cost) {
            echo json_encode(['success' => false, 'message' => "برای تغییر رنگ دکور خانه به {$cost} BitBlox نیاز دارید!"]);
            exit;
        }

        $allowedKeys = ['wallStyle', 'wallColor', 'floorStyle', 'floorColor', 'kitchenCabinetColor', 'kitchenCeramicColor'];
        foreach ($allowedKeys as $k) {
            if (array_key_exists($k, $input)) {
                $state['homeStyle'][$k] = str_ends_with($k, 'Color') ? (int) $input[$k] : (string) $input[$k];
            }
        }

        if ($cost > 0) {
            $state['bitblox'] -= $cost;
        }
        $this->saveUserState($state);

        if ($cost > 0) {
            BloxiaHelper::logActivity((int) $state['userId'], 'color_change', -$cost, null, "تغییر رنگ دکور اتاق‌ها ({$colorChanges} بخش)");
        } else {
            BloxiaHelper::logActivity((int) $state['userId'], 'save_home', 0, null, 'تغییر طرح دیوار/کف اتاق‌ها');
        }
        echo json_encode(['success' => true, 'cost' => $cost, 'state' => $state]);
        exit;
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
        BloxiaHelper::logActivity((int) $state['userId'], 'cas_save', 0, null, 'ساخت/ویرایش اولیه کاراکتر: ' . $state['name']);
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
        $homeStyle = [
            'wallStyle' => 'wall_paint_classic',
            'wallColor' => 0x94a3b8,
            'floorStyle' => 'floor_parquet_oak',
            'floorColor' => 0xd8b48b,
            'kitchenCabinetColor' => 0xf8fafc,
            'kitchenCeramicColor' => 0xe2e8f0
        ];

        if ($profile && !empty($profile->character_data)) {
            $data = json_decode($profile->character_data, true);
            $layout = $data['homeLayout'] ?? [];
            $allGames = $data['myGames'] ?? [];
            $games = $isMine ? $allGames : array_values(array_filter($allGames, fn($g) => ($g['status'] ?? '') === 'approved'));
            $ownerName = $data['name'] ?? 'شهروند';
            if (!empty($data['homeStyle']) && is_array($data['homeStyle'])) {
                $homeStyle = array_merge($homeStyle, $data['homeStyle']);
            }
        }

        if (!$isMine && !$user->guest && $ownerId > 0) {
            BloxiaHelper::logActivity((int) $user->id, 'visit_home', 0, $ownerId, 'بازدید از خانه ' . $ownerName);
        }

        header('Content-Type: application/json'); 
        echo json_encode([
            'ownerId' => $ownerId, 
            'ownerName' => $ownerName, 
            'isMine' => $isMine, 
            'layout' => $layout, 
            'homeStyle' => $homeStyle,
            'games' => $games
        ]); 
        exit; 
    }

    /**
     * فاز ۳: دریافت لیست همسایه‌های واقعی شهر
     */
    public function getNeighbors()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode([]);
            exit;
        }

        BloxiaHelper::ensureTables();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $onlineCutoff = Factory::getDate('-15 seconds')->toSql();

        $query = $db->getQuery(true)
            ->select([
                'p.user_id',
                'p.character_data',
                'p.bloxia_points',
                'u.name AS joomla_name',
                'pr.updated_at AS last_seen',
                'pr.zone AS current_zone'
            ])
            ->from($db->quoteName('#__bloxia_user_profiles', 'p'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON u.id = p.user_id')
            ->join('LEFT', $db->quoteName('#__bloxia_presence', 'pr') . ' ON pr.user_id = p.user_id')
            ->where($db->quoteName('p.user_id') . ' != ' . (int) $user->id)
            ->order($db->quoteName('pr.updated_at') . ' DESC, ' . $db->quoteName('p.bloxia_points') . ' DESC');

        $db->setQuery($query, 0, 50);
        $rows = $db->loadObjectList();

        $wallPalette = [0x93c5fd, 0xf9a8d4, 0x86efac, 0xfde68a, 0xc4b5fd, 0xfdba74];
        $roofPalette = [0xe11d48, 0xdb2777, 0x059669, 0xd97706, 0x7c3aed, 0x0284c7];

        $neighbors = [];
        foreach ($rows as $row) {
            $data = !empty($row->character_data) ? json_decode($row->character_data, true) : [];
            $games = $data['myGames'] ?? [];
            $approvedGames = array_filter($games, fn($g) => ($g['status'] ?? '') === 'approved');
            $isOnline = !empty($row->last_seen) && $row->last_seen >= $onlineCutoff;
            $paletteIdx = ((int) $row->user_id) % count($wallPalette);

            $neighbors[] = [
                'ownerId'       => (int) $row->user_id,
                'ownerName'     => $data['name'] ?? $row->joomla_name ?? ('شهروند #' . $row->user_id),
                'gamesCount'    => count($approvedGames),
                'isOnline'      => $isOnline,
                'currentZone'   => $row->current_zone ?? '',
                'color'         => $wallPalette[$paletteIdx],
                'roofColor'     => $roofPalette[$paletteIdx],
            ];
        }

        echo json_encode($neighbors);
        exit;
    }

    /**
     * فاز ۳: همگام‌سازی حضور، موقعیت، ایموت و چت بازیکنان در هر منطقه (Multiplayer)
     */
    public function syncWorld()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode([]);
            exit;
        }

        BloxiaHelper::ensureTables();
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $zone  = substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($input['zone'] ?? 'plaza')), 0, 64) ?: 'plaza';
        $posX  = (float) ($input['x'] ?? 0);
        $posZ  = (float) ($input['z'] ?? 0);
        $emote = isset($input['emote']) ? substr(trim((string) $input['emote']), 0, 32) : '';
        $chat  = isset($input['chat']) ? mb_substr(trim((string) $input['chat']), 0, 200) : '';

        $db  = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();

        // ثبت یا به‌روزرسانی موقعیت کاربر فعلی
        $sql = "INSERT INTO `#__bloxia_presence` (`user_id`, `zone`, `pos_x`, `pos_z`, `emote`, `emote_at`, `chat_text`, `chat_at`, `updated_at`)
                VALUES (" . (int) $user->id . ", " . $db->quote($zone) . ", " . $posX . ", " . $posZ . ", "
                . ($emote !== '' ? $db->quote($emote) : "NULL") . ", " . ($emote !== '' ? $db->quote($now) : "NULL") . ", "
                . ($chat !== '' ? $db->quote($chat) : "NULL") . ", " . ($chat !== '' ? $db->quote($now) : "NULL") . ", "
                . $db->quote($now) . ")
                ON DUPLICATE KEY UPDATE
                    `zone` = VALUES(`zone`),
                    `pos_x` = VALUES(`pos_x`),
                    `pos_z` = VALUES(`pos_z`),
                    `updated_at` = VALUES(`updated_at`)";

        if ($emote !== '') {
            $sql .= ", `emote` = VALUES(`emote`), `emote_at` = VALUES(`emote_at`)";
        }
        if ($chat !== '') {
            $sql .= ", `chat_text` = VALUES(`chat_text`), `chat_at` = VALUES(`chat_at`)";
        }

        $db->setQuery($sql)->execute();

        // دریافت بازیکنان آنلاین در همین منطقه (فعال در ۱۵ ثانیه اخیر)
        $cutoff      = Factory::getDate('-15 seconds')->toSql();
        $emoteCutoff = Factory::getDate('-6 seconds')->toSql();
        $chatCutoff  = Factory::getDate('-8 seconds')->toSql();

        $query = $db->getQuery(true)
            ->select([
                'pr.user_id',
                'pr.zone',
                'pr.pos_x',
                'pr.pos_z',
                'pr.emote',
                'pr.emote_at',
                'pr.chat_text',
                'pr.chat_at',
                'p.character_data',
                'u.name AS joomla_name'
            ])
            ->from($db->quoteName('#__bloxia_presence', 'pr'))
            ->join('LEFT', $db->quoteName('#__bloxia_user_profiles', 'p') . ' ON p.user_id = pr.user_id')
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = pr.user_id')
            ->where($db->quoteName('pr.zone') . ' = ' . $db->quote($zone))
            ->where($db->quoteName('pr.user_id') . ' != ' . (int) $user->id)
            ->where($db->quoteName('pr.updated_at') . ' >= ' . $db->quote($cutoff));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $others = [];
        foreach ($rows as $row) {
            $data = !empty($row->character_data) ? json_decode($row->character_data, true) : [];
            $appearance = $data['appearance'] ?? [
                'skinColor' => 0xffdbac, 'hairStyle' => 'short', 'hairColor' => 0x451a03,
                'shirtColor' => 0x3b82f6, 'pantsColor' => 0x1e293b, 'shoesColor' => 0xf8fafc,
                'hat' => null, 'handProp' => null
            ];

            $hasRecentEmote = !empty($row->emote) && !empty($row->emote_at) && $row->emote_at >= $emoteCutoff;
            $hasRecentChat  = !empty($row->chat_text) && !empty($row->chat_at) && $row->chat_at >= $chatCutoff;

            $others[] = [
                'id'         => (int) $row->user_id,
                'name'       => $data['name'] ?? $row->joomla_name ?? ('بازیکن #' . $row->user_id),
                'zone'       => $row->zone,
                'x'          => (float) $row->pos_x,
                'z'          => (float) $row->pos_z,
                'appearance' => $appearance,
                'emote'      => $hasRecentEmote ? $row->emote : null,
                'emoteAt'    => $hasRecentEmote ? $row->emote_at : null,
                'chat'       => $hasRecentChat ? $row->chat_text : '',
                'chatAt'     => $hasRecentChat ? $row->chat_at : null,
            ];
        }

        // دریافت پیام‌های جدید چت امن برای این کاربر
        $lastChatId = (int) ($input['lastChatId'] ?? 0);
        $chatQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_chat_messages'))
            ->where('((' . $db->quoteName('target_id') . ' = 0 AND ' . $db->quoteName('zone') . ' = ' . $db->quote($zone) . ') OR '
                . $db->quoteName('target_id') . ' = ' . (int) $user->id . ' OR '
                . $db->quoteName('sender_id') . ' = ' . (int) $user->id . ')');

        if ($lastChatId > 0) {
            $chatQuery->where($db->quoteName('id') . ' > ' . $lastChatId);
        } else {
            $recentChatCutoff = Factory::getDate('-2 minutes')->toSql();
            $chatQuery->where($db->quoteName('created_at') . ' >= ' . $db->quote($recentChatCutoff));
        }
        $chatQuery->order($db->quoteName('id') . ' ASC');
        $db->setQuery($chatQuery, 0, 30);
        $chatRows = $db->loadObjectList() ?: [];

        $chats = [];
        foreach ($chatRows as $c) {
            $chats[] = [
                'id'         => (int) $c->id,
                'senderId'   => (int) $c->sender_id,
                'senderName' => $c->sender_name,
                'targetId'   => (int) $c->target_id,
                'message'    => $c->message,
                'createdAt'  => $c->created_at,
            ];
        }

        // دریافت دعوت‌های PvP ورودی (در ۲ دقیقه اخیر)
        $inviteCutoff = Factory::getDate('-2 minutes')->toSql();
        $invQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_pvp_matches'))
            ->where($db->quoteName('opponent_id') . ' = ' . (int) $user->id)
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'))
            ->where($db->quoteName('updated_at') . ' >= ' . $db->quote($inviteCutoff))
            ->order($db->quoteName('id') . ' DESC');
        $db->setQuery($invQuery, 0, 5);
        $invRows = $db->loadObjectList() ?: [];
        $incomingInvites = array_map(fn($r) => $this->formatPvpMatch($r, (int) $user->id), $invRows);

        // دریافت مسابقه فعال فعلی کاربر (در حال بازی)
        $activeQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_pvp_matches'))
            ->where('(' . $db->quoteName('challenger_id') . ' = ' . (int) $user->id . ' OR ' . $db->quoteName('opponent_id') . ' = ' . (int) $user->id . ')')
            ->where($db->quoteName('status') . ' = ' . $db->quote('active'))
            ->order($db->quoteName('id') . ' DESC');
        $db->setQuery($activeQuery, 0, 1);
        $activeRow = $db->loadObject();

        echo json_encode([
            'players' => $others,
            'chats'   => $chats,
            'pvp'     => [
                'incomingInvites' => $incomingInvites,
                'activeMatch'     => $activeRow ? $this->formatPvpMatch($activeRow, (int) $user->id) : null,
            ],
        ]);
        exit;
    }

    /**
     * فاز ۳: ارسال پیام چت امن دوطرفه بین بازیکنان
     */
    public function sendChat()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false]);
            exit;
        }

        BloxiaHelper::ensureTables();
        $input    = json_decode(file_get_contents('php://input'), true) ?: [];
        $zone     = substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($input['zone'] ?? 'plaza')), 0, 64) ?: 'plaza';
        $targetId = max(0, (int) ($input['target_id'] ?? 0));
        $message  = mb_substr(trim((string) ($input['message'] ?? '')), 0, 200);

        if ($message === '') {
            echo json_encode(['success' => false]);
            exit;
        }

        $state = $this->loadUserState();
        $senderName = $state['name'] ?? $user->name;
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__bloxia_chat_messages'))
            ->columns($db->quoteName(['sender_id', 'sender_name', 'target_id', 'zone', 'message', 'created_at']))
            ->values(implode(',', [
                (int) $user->id,
                $db->quote($senderName),
                $targetId,
                $db->quote($zone),
                $db->quote($message),
                $db->quote($now),
            ]));
        $db->setQuery($query)->execute();
        $chatId = (int) $db->insertid();

        // به‌روزرسانی حباب چت بالای سر کاراکتر
        $presSql = "UPDATE `#__bloxia_presence` SET `chat_text` = " . $db->quote($message)
            . ", `chat_at` = " . $db->quote($now)
            . ", `emote` = 'wave', `emote_at` = " . $db->quote($now)
            . " WHERE `user_id` = " . (int) $user->id;
        $db->setQuery($presSql)->execute();

        echo json_encode([
            'success' => true,
            'chat'    => [
                'id'         => $chatId,
                'senderId'   => (int) $user->id,
                'senderName' => $senderName,
                'targetId'   => $targetId,
                'message'    => $message,
                'createdAt'  => $now,
            ],
        ]);
        exit;
    }

    /**
     * فاز ۳: ایجاد دعوت چالش دونفره (PvP) برای بازیکن آنلاین دیگر
     */
    public function pvpCreate()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        BloxiaHelper::ensureTables();
        $input      = json_decode(file_get_contents('php://input'), true) ?: [];
        $opponentId = (int) ($input['opponent_id'] ?? 0);
        $gameType   = preg_replace('/[^a-z]/', '', (string) ($input['game_type'] ?? 'rps'));

        if ($opponentId <= 0 || $opponentId === (int) $user->id) {
            echo json_encode(['success' => false, 'message' => 'حریف نامعتبر است.']);
            exit;
        }
        if (!in_array($gameType, ['rps', 'ttt', 'darts', 'ludo', 'gol'], true)) {
            $gameType = 'rps';
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        // لغو دعوت‌های معلق قبلی همین کاربر
        $cancelQ = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_pvp_matches'))
            ->set($db->quoteName('status') . ' = ' . $db->quote('cancelled'))
            ->where($db->quoteName('challenger_id') . ' = ' . (int) $user->id)
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'));
        $db->setQuery($cancelQ)->execute();

        $myState = $this->loadUserState();
        $challengerName = $myState['name'] ?? $user->name;

        // دریافت نام حریف
        $oppQuery = $db->getQuery(true)
            ->select(['p.character_data', 'u.name'])
            ->from($db->quoteName('#__users', 'u'))
            ->join('LEFT', $db->quoteName('#__bloxia_user_profiles', 'p') . ' ON p.user_id = u.id')
            ->where($db->quoteName('u.id') . ' = ' . $opponentId);
        $db->setQuery($oppQuery);
        $oppRow = $db->loadObject();
        if (!$oppRow) {
            echo json_encode(['success' => false, 'message' => 'بازیکن حریف یافت نشد.']);
            exit;
        }
        $oppData = !empty($oppRow->character_data) ? json_decode($oppRow->character_data, true) : [];
        $opponentName = $oppData['name'] ?? $oppRow->name ?? ('بازیکن #' . $opponentId);

        $initState = [];
        if ($gameType === 'rps') {
            $initState = ['moves' => [(string) $user->id => null, (string) $opponentId => null]];
        } elseif ($gameType === 'ttt') {
            $initState = [
                'board' => array_fill(0, 9, ''),
                'marks' => [(string) $user->id => '❌', (string) $opponentId => '⭕'],
            ];
        } elseif ($gameType === 'darts' || $gameType === 'ludo') {
            $initState = ['scores' => [(string) $user->id => null, (string) $opponentId => null]];
        } elseif ($gameType === 'gol') {
            $initState = ['hiddenHand' => null, 'guessHand' => null];
        }

        $now = Factory::getDate()->toSql();
        $insQ = $db->getQuery(true)
            ->insert($db->quoteName('#__bloxia_pvp_matches'))
            ->columns($db->quoteName([
                'challenger_id', 'challenger_name', 'opponent_id', 'opponent_name',
                'game_type', 'status', 'turn_user_id', 'winner_id', 'state_json', 'created_at', 'updated_at'
            ]))
            ->values(implode(',', [
                (int) $user->id,
                $db->quote($challengerName),
                $opponentId,
                $db->quote($opponentName),
                $db->quote($gameType),
                $db->quote('pending'),
                (int) $user->id,
                'NULL',
                $db->quote(json_encode($initState)),
                $db->quote($now),
                $db->quote($now),
            ]));
        $db->setQuery($insQ)->execute();
        $matchId = (int) $db->insertid();

        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_pvp_matches'))->where('id = ' . $matchId));
        $row = $db->loadObject();

        echo json_encode(['success' => true, 'match' => $this->formatPvpMatch($row, (int) $user->id)]);
        exit;
    }

    /**
     * فاز ۳: پاسخ به دعوت PvP (قبول، رد یا لغو)
     */
    public function pvpRespond()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false]);
            exit;
        }

        BloxiaHelper::ensureTables();
        $input   = json_decode(file_get_contents('php://input'), true) ?: [];
        $matchId = (int) ($input['match_id'] ?? 0);
        $action  = (string) ($input['action'] ?? '');

        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_pvp_matches'))->where('id = ' . $matchId));
        $row = $db->loadObject();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'مسابقه یافت نشد.']);
            exit;
        }

        $now = Factory::getDate()->toSql();
        $newStatus = null;

        if ($action === 'accept' && (int) $row->opponent_id === (int) $user->id && $row->status === 'pending') {
            $newStatus = 'active';
        } elseif ($action === 'decline' && (int) $row->opponent_id === (int) $user->id && $row->status === 'pending') {
            $newStatus = 'declined';
        } elseif ($action === 'cancel' && ((int) $row->challenger_id === (int) $user->id || (int) $row->opponent_id === (int) $user->id)) {
            $newStatus = 'cancelled';
        }

        if ($newStatus === null) {
            echo json_encode(['success' => false, 'message' => 'عملیات مجاز نیست.']);
            exit;
        }

        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_pvp_matches'))
            ->set($db->quoteName('status') . ' = ' . $db->quote($newStatus))
            ->set($db->quoteName('updated_at') . ' = ' . $db->quote($now))
            ->where('id = ' . $matchId);
        $db->setQuery($upd)->execute();

        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_pvp_matches'))->where('id = ' . $matchId));
        $updated = $db->loadObject();

        echo json_encode(['success' => true, 'match' => $this->formatPvpMatch($updated, (int) $user->id)]);
        exit;
    }

    /**
     * فاز ۳: دریافت وضعیت لحظه‌ای مسابقه PvP
     */
    public function pvpStatus()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false]);
            exit;
        }

        $app     = Factory::getApplication();
        $matchId = (int) $app->input->getInt('match_id', 0);
        if ($matchId <= 0) {
            $input   = json_decode(file_get_contents('php://input'), true) ?: [];
            $matchId = (int) ($input['match_id'] ?? 0);
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_pvp_matches'))->where('id = ' . $matchId));
        $row = $db->loadObject();

        if (!$row || ((int) $row->challenger_id !== (int) $user->id && (int) $row->opponent_id !== (int) $user->id)) {
            echo json_encode(['success' => false]);
            exit;
        }

        echo json_encode(['success' => true, 'match' => $this->formatPvpMatch($row, (int) $user->id)]);
        exit;
    }

    /**
     * فاز ۳: ثبت حرکت بازیکن در مسابقه دوطرفه و محاسبه برنده در سرور
     */
    public function pvpMove()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false]);
            exit;
        }

        BloxiaHelper::ensureTables();
        $input   = json_decode(file_get_contents('php://input'), true) ?: [];
        $matchId = (int) ($input['match_id'] ?? 0);
        $move    = is_array($input['move'] ?? null) ? $input['move'] : [];

        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_pvp_matches'))->where('id = ' . $matchId));
        $row = $db->loadObject();

        if (!$row || $row->status !== 'active') {
            echo json_encode(['success' => false, 'message' => 'مسابقه فعال نیست.']);
            exit;
        }

        $myId  = (int) $user->id;
        $cId   = (int) $row->challenger_id;
        $oId   = (int) $row->opponent_id;
        if ($myId !== $cId && $myId !== $oId) {
            echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز.']);
            exit;
        }

        $state    = json_decode($row->state_json, true) ?: [];
        $status   = 'active';
        $turnId   = (int) $row->turn_user_id;
        $winnerId = null;
        $gameType = $row->game_type;

        if ($gameType === 'rps') {
            $choice = (string) ($move['choice'] ?? '');
            if (!in_array($choice, ['r', 'p', 's'], true)) {
                echo json_encode(['success' => false, 'message' => 'انتخاب نامعتبر.']);
                exit;
            }
            if (!empty($state['moves'][(string) $myId])) {
                echo json_encode(['success' => false, 'message' => 'شما قبلاً انتخاب خود را ثبت کرده‌اید.']);
                exit;
            }
            $state['moves'][(string) $myId] = $choice;

            $mC = $state['moves'][(string) $cId] ?? null;
            $mO = $state['moves'][(string) $oId] ?? null;
            if ($mC && $mO) {
                $status = 'finished';
                if ($mC === $mO) {
                    $winnerId = 0; // مساوی
                } elseif (
                    ($mC === 'r' && $mO === 's') ||
                    ($mC === 'p' && $mO === 'r') ||
                    ($mC === 's' && $mO === 'p')
                ) {
                    $winnerId = $cId;
                } else {
                    $winnerId = $oId;
                }
            }
        } elseif ($gameType === 'ttt') {
            if ($turnId !== $myId) {
                echo json_encode(['success' => false, 'message' => 'نوبت حریف است!']);
                exit;
            }
            $cell = (int) ($move['cell'] ?? -1);
            if ($cell < 0 || $cell > 8 || ($state['board'][$cell] ?? '') !== '') {
                echo json_encode(['success' => false, 'message' => 'خانه نامعتبر است.']);
                exit;
            }
            $mark = $state['marks'][(string) $myId] ?? '❌';
            $state['board'][$cell] = $mark;

            $wins = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
            $won = false;
            foreach ($wins as [$a, $b, $c]) {
                if ($state['board'][$a] === $mark && $state['board'][$b] === $mark && $state['board'][$c] === $mark) {
                    $won = true;
                    break;
                }
            }
            if ($won) {
                $status   = 'finished';
                $winnerId = $myId;
            } elseif (!in_array('', $state['board'], true)) {
                $status   = 'finished';
                $winnerId = 0;
            } else {
                $turnId = ($myId === $cId) ? $oId : $cId;
            }
        } elseif ($gameType === 'darts' || $gameType === 'ludo') {
            if (($state['scores'][(string) $myId] ?? null) !== null) {
                echo json_encode(['success' => false, 'message' => 'شما قبلاً پرتاب کرده‌اید.']);
                exit;
            }
            $score = ($gameType === 'darts') ? rand(15, 100) : rand(1, 6);
            $state['scores'][(string) $myId] = $score;

            $sC = $state['scores'][(string) $cId] ?? null;
            $sO = $state['scores'][(string) $oId] ?? null;
            if ($sC !== null && $sO !== null) {
                $status = 'finished';
                if ($sC === $sO) {
                    $winnerId = 0;
                } else {
                    $winnerId = ($sC > $sO) ? $cId : $oId;
                }
            }
        } elseif ($gameType === 'gol') {
            $hand = isset($move['hand']) ? (int) $move['hand'] : -1;
            if ($hand !== 0 && $hand !== 1) {
                echo json_encode(['success' => false, 'message' => 'دست نامعتبر است.']);
                exit;
            }
            if ($state['hiddenHand'] === null && $myId === $cId) {
                $state['hiddenHand'] = $hand;
                $turnId = $oId;
            } elseif ($state['hiddenHand'] !== null && $state['guessHand'] === null && $myId === $oId) {
                $state['guessHand'] = $hand;
                $status   = 'finished';
                $winnerId = ($state['guessHand'] === $state['hiddenHand']) ? $oId : $cId;
            } else {
                echo json_encode(['success' => false, 'message' => 'نوبت شما نیست!']);
                exit;
            }
        }

        $now = Factory::getDate()->toSql();
        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_pvp_matches'))
            ->set($db->quoteName('state_json') . ' = ' . $db->quote(json_encode($state)))
            ->set($db->quoteName('status') . ' = ' . $db->quote($status))
            ->set($db->quoteName('turn_user_id') . ' = ' . $turnId)
            ->set($db->quoteName('winner_id') . ' = ' . ($winnerId !== null ? (int) $winnerId : 'NULL'))
            ->set($db->quoteName('updated_at') . ' = ' . $db->quote($now))
            ->where('id = ' . $matchId);
        $db->setQuery($upd)->execute();

        if ($status === 'finished' && $winnerId > 0) {
            $loserId   = ($winnerId === $cId) ? $oId : $cId;
            $loserName = ($winnerId === $cId) ? $row->opponent_name : $row->challenger_name;
            $this->awardPvpWinner($winnerId, $loserId, $gameType, $loserName);
        }

        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_pvp_matches'))->where('id = ' . $matchId));
        $updated = $db->loadObject();

        echo json_encode(['success' => true, 'match' => $this->formatPvpMatch($updated, $myId)]);
        exit;
    }

    private function formatPvpMatch($row, int $viewerId): array
    {
        $state = json_decode($row->state_json, true) ?: [];
        if ($row->status === 'active') {
            if ($row->game_type === 'rps' && isset($state['moves'])) {
                foreach ($state['moves'] as $uid => $val) {
                    if ((int) $uid !== $viewerId && $val !== null) {
                        $state['moves'][$uid] = 'hidden';
                    }
                }
            } elseif ($row->game_type === 'gol' && (int) $row->challenger_id !== $viewerId && $state['guessHand'] === null) {
                if ($state['hiddenHand'] !== null) {
                    $state['hiddenHand'] = 'hidden';
                }
            }
        }

        return [
            'id'             => (int) $row->id,
            'challengerId'   => (int) $row->challenger_id,
            'challengerName' => $row->challenger_name,
            'opponentId'     => (int) $row->opponent_id,
            'opponentName'   => $row->opponent_name,
            'gameType'       => $row->game_type,
            'status'         => $row->status,
            'turnUserId'     => (int) $row->turn_user_id,
            'winnerId'       => $row->winner_id !== null ? (int) $row->winner_id : null,
            'state'          => $state,
        ];
    }

    private function awardPvpWinner(int $winnerId, int $loserId, string $gameType, string $loserName): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . $winnerId);
        $db->setQuery($query);
        $profile = $db->loadObject();
        if (!$profile) {
            return;
        }

        $reward = 15;
        $state  = !empty($profile->character_data) ? json_decode($profile->character_data, true) : [];
        if (!is_array($state)) $state = [];
        $current = isset($state['bitblox']) ? (int) $state['bitblox'] : (int) $profile->bloxia_points;
        $newBalance = $current + $reward;
        $state['bitblox'] = $newBalance;

        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($state)))
            ->set($db->quoteName('bloxia_points') . ' = ' . $newBalance)
            ->where($db->quoteName('user_id') . ' = ' . $winnerId);
        $db->setQuery($upd)->execute();

        BloxiaHelper::logActivity(
            $winnerId,
            'pvp_win',
            $reward,
            $loserId,
            'پیروزی در چالش دونفره (' . $gameType . ') مقابل ' . $loserName
        );
    }

    /**
     * فاز ۳ و ۴: ثبت اجرای بازی روی تلویزیون و واریز درآمد میزبانی برای صاحب خانه
     */
    public function recordGamePlay()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        BloxiaHelper::ensureTables();
        $input   = json_decode(file_get_contents('php://input'), true) ?: [];
        $ownerId = (int) ($input['owner_id'] ?? 0);
        $gameId  = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($input['game_id'] ?? ''));

        if ($ownerId <= 0 || $gameId === '') {
            echo json_encode(['success' => false, 'message' => 'اطلاعات بازی نامعتبر است.']);
            exit;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . $ownerId);
        $db->setQuery($query);
        $profile = $db->loadObject();

        if (!$profile || empty($profile->character_data)) {
            echo json_encode(['success' => false, 'message' => 'پروفایل میزبان یافت نشد.']);
            exit;
        }

        $ownerState = json_decode($profile->character_data, true);
        if (empty($ownerState['myGames']) || !is_array($ownerState['myGames'])) {
            echo json_encode(['success' => false, 'message' => 'بازی یافت نشد.']);
            exit;
        }

        $foundGame = null;
        foreach ($ownerState['myGames'] as &$g) {
            if (($g['id'] ?? '') === $gameId && ($g['status'] ?? '') === 'approved') {
                $g['play_count'] = (int) ($g['play_count'] ?? 0) + 1;
                $foundGame = $g;
                break;
            }
        }
        unset($g);

        if (!$foundGame) {
            echo json_encode(['success' => false, 'message' => 'بازی تایید شده یافت نشد.']);
            exit;
        }

        $gameTitle   = $foundGame['title'] ?? 'بازی';
        $hostReward  = 0;
        $isGuestPlay = ((int) $user->id !== $ownerId);

        if ($isGuestPlay) {
            $rewardMap = ['simple' => 3, 'staged' => 5, 'pro' => 10];
            $candidateReward = $rewardMap[$foundGame['tier'] ?? 'simple'] ?? 5;

            // جلوگیری از اسپم پاداش برای یک مهمان در ۶۰ ثانیه اخیر
            $cooldownCutoff = Factory::getDate('-60 seconds')->toSql();
            $coolQuery = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bloxia_logs'))
                ->where($db->quoteName('user_id') . ' = ' . $ownerId)
                ->where($db->quoteName('target_user_id') . ' = ' . (int) $user->id)
                ->where($db->quoteName('action_type') . ' = ' . $db->quote('host_income'))
                ->where($db->quoteName('created_at') . ' >= ' . $db->quote($cooldownCutoff));
            $db->setQuery($coolQuery);
            $recentCount = (int) $db->loadResult();

            if ($recentCount === 0) {
                $hostReward = $candidateReward;
                $ownerState['bitblox'] = (int) ($ownerState['bitblox'] ?? 0) + $hostReward;
            }
        }

        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($ownerState)))
            ->set($db->quoteName('bloxia_points') . ' = ' . (int) ($ownerState['bitblox'] ?? 0))
            ->where($db->quoteName('user_id') . ' = ' . $ownerId);
        $db->setQuery($updateQuery)->execute();

        BloxiaHelper::logActivity(
            (int) $user->id,
            'game_play',
            0,
            $ownerId,
            'اجرای بازی «' . $gameTitle . '»' . ($isGuestPlay ? ' (به عنوان مهمان)' : ' (در خانه خود)')
        );

        if ($hostReward > 0) {
            BloxiaHelper::logActivity(
                $ownerId,
                'host_income',
                $hostReward,
                (int) $user->id,
                'درآمد بازی مهمان (' . $user->name . ') در بازی «' . $gameTitle . '»'
            );
        }

        echo json_encode([
            'success'    => true,
            'hostReward' => $hostReward,
            'ownerName'  => $ownerState['name'] ?? 'میزبان',
            'gameTitle'  => $gameTitle,
            'playCount'  => (int) $foundGame['play_count'],
        ]);
        exit;
    }
    
    public function submitGame()
    {
        header('Content-Type: application/json');
        $app = Factory::getApplication();
        $user = Factory::getUser();

        if ($user->guest) {
            echo json_encode(['success' => false, 'message' => 'لطفاً ابتدا وارد حساب کاربری شوید.']);
            exit;
        }

        $title = trim($app->input->post->getString('title', ''));
        $tier  = $app->input->post->getCmd('tier', 'simple');
        $file  = $app->input->files->get('game_file', null, 'raw');

        if (empty($title) || empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'لطفاً نام بازی و فایل ZIP معتبر را انتخاب کنید.']);
            exit;
        }

        // بررسی پسوند و حجم فایل (حداکثر ۱۵ مگابایت)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            echo json_encode(['success' => false, 'message' => 'فقط فایل‌های با فرمت ZIP مجاز هستند!']);
            exit;
        }
        if ($file['size'] > 15 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'حجم فایل نباید بیشتر از ۱۵ مگابایت باشد.']);
            exit;
        }

        // بررسی امنیتی محتوای فایل ZIP قبل از استخراج
        $zip = new \ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            echo json_encode(['success' => false, 'message' => 'فایل ZIP خراب است و باز نمی‌شود.']);
            exit;
        }

        $forbiddenExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'htaccess', 'sh', 'exe', 'bat', 'cgi', 'pl'];
        $hasIndexHtml = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            // جلوگیری از حمله Path Traversal
            if (strpos($entryName, '..') !== false) {
                $zip->close();
                echo json_encode(['success' => false, 'message' => 'ساختار فایل ZIP نامعتبر است.']);
                exit;
            }

            $entryExt = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
            if (in_array($entryExt, $forbiddenExts)) {
                $zip->close();
                echo json_encode(['success' => false, 'message' => 'فایل غیرمجاز (' . $entryExt . ') داخل آرشیو شناسایی شد!']);
                exit;
            }

            if (strtolower($entryName) === 'index.html') {
                $hasIndexHtml = true;
            }
        }

        if (!$hasIndexHtml) {
            $zip->close();
            echo json_encode(['success' => false, 'message' => 'فایل index.html باید مستقیماً در ریشه فایل ZIP وجود داشته باشد.']);
            exit;
        }

        // ساخت پوشه اختصاصی و استخراج بازی
        $gameId = 'g_' . time() . '_' . rand(100, 999);
        $relPath = 'media/com_bloxia/games/' . (int) $user->id . '/' . $gameId;
        $targetDir = JPATH_ROOT . '/' . $relPath;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zip->extractTo($targetDir);
        $zip->close();

        // ثبت در پروفایل کاربر با وضعیت در انتظار تایید
        $rewardMap = ['simple' => 10, 'staged' => 20, 'pro' => 100];
        $newGame = [
            'id'     => $gameId,
            'title'  => $title,
            'tier'   => $tier,
            'reward' => $rewardMap[$tier] ?? 10,
            'status' => 'pending',
            'url'    => Uri::root() . $relPath . '/index.html'
        ];

        $state = $this->loadUserState();
        if (!isset($state['myGames']) || !is_array($state['myGames'])) {
            $state['myGames'] = [];
        }
        $state['myGames'][] = $newGame;
        $this->saveUserState($state);
        BloxiaHelper::logActivity((int) $user->id, 'game_submit', 0, null, 'ارسال بازی: ' . $title . ' (' . $tier . ')');

        echo json_encode(['success' => true, 'state' => $state, 'game' => $newGame]);
        exit;
    }
    
    	public function saveCharacter()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $state = $this->loadUserState();
        if (!$state) { echo json_encode(['success' => false]); exit; }

        $newApp = $input['appearance'] ?? [];
        $colorKeys = ['skinColor', 'hairColor', 'shirtColor', 'pantsColor', 'shoesColor'];
        $colorChanges = 0;
        foreach ($colorKeys as $ck) {
            if (array_key_exists($ck, $newApp)) {
                $oldVal = isset($state['appearance'][$ck]) ? (int) $state['appearance'][$ck] : null;
                $newVal = (int) $newApp[$ck];
                if ($oldVal !== $newVal) {
                    $colorChanges++;
                }
            }
        }

        $cost = $colorChanges * 10;
        if ($cost > 0 && (int) $state['bitblox'] < $cost) {
            echo json_encode(['success' => false, 'message' => "برای تغییر رنگ کاراکتر به {$cost} BitBlox نیاز دارید!"]);
            exit;
        }

        if ($cost > 0) {
            $state['bitblox'] -= $cost;
        }
        $state['appearance'] = array_merge($state['appearance'] ?? [], $newApp);
        if (!empty($input['name'])) {
            $state['name'] = trim($input['name']);
        }
        $this->saveUserState($state);
        if ($cost > 0) {
            BloxiaHelper::logActivity((int) $state['userId'], 'color_change', -$cost, null, "تغییر رنگ کاراکتر ({$colorChanges} بخش)");
        } else {
            BloxiaHelper::logActivity((int) $state['userId'], 'cas_save', 0, null, 'ویرایش نام کاراکتر: ' . ($state['name'] ?? ''));
        }
        echo json_encode(['success' => true, 'cost' => $cost, 'state' => $state]);
        exit;
    }

}