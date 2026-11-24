<?php
namespace BastanGraphic\Component\Bloxia\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class BloxiaHelper
{
    private static bool $tablesChecked = false;

    /**
     * اطمینان از وجود جداول فاز ۳ و ۴ در دیتابیس (حتی در صورت ارتقای مستقیم فایل‌ها)
     */
    public static function ensureTables(): void
    {
        if (self::$tablesChecked) {
            return;
        }
        self::$tablesChecked = true;

        $db = Factory::getContainer()->get('DatabaseDriver');

        $presenceSql = "CREATE TABLE IF NOT EXISTS `#__bloxia_presence` (
            `user_id` int(11) UNSIGNED NOT NULL,
            `zone` varchar(64) NOT NULL DEFAULT 'plaza',
            `pos_x` float NOT NULL DEFAULT 0,
            `pos_z` float NOT NULL DEFAULT 0,
            `emote` varchar(32) DEFAULT NULL,
            `emote_at` datetime DEFAULT NULL,
            `chat_text` varchar(255) DEFAULT NULL,
            `chat_at` datetime DEFAULT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`user_id`),
            KEY `idx_zone_updated` (`zone`, `updated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $logsSql = "CREATE TABLE IF NOT EXISTS `#__bloxia_logs` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(11) UNSIGNED NOT NULL,
            `action_type` varchar(50) NOT NULL,
            `amount` int(11) NOT NULL DEFAULT 0,
            `target_user_id` int(11) UNSIGNED DEFAULT NULL,
            `details` text DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_action_type` (`action_type`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $chatSql = "CREATE TABLE IF NOT EXISTS `#__bloxia_chat_messages` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `sender_id` int(11) UNSIGNED NOT NULL,
            `sender_name` varchar(100) NOT NULL,
            `target_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
            `zone` varchar(64) NOT NULL DEFAULT 'plaza',
            `message` varchar(255) NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_zone_id` (`zone`, `id`),
            KEY `idx_target_id` (`target_id`, `id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pvpSql = "CREATE TABLE IF NOT EXISTS `#__bloxia_pvp_matches` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `challenger_id` int(11) UNSIGNED NOT NULL,
            `challenger_name` varchar(100) NOT NULL,
            `opponent_id` int(11) UNSIGNED NOT NULL,
            `opponent_name` varchar(100) NOT NULL,
            `game_type` varchar(20) NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `turn_user_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
            `winner_id` int(11) UNSIGNED DEFAULT NULL,
            `state_json` text NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_players_status` (`challenger_id`, `opponent_id`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $bankSettingsSql = "CREATE TABLE IF NOT EXISTS `#__bloxia_bank_settings` (
            `id` int(11) UNSIGNED NOT NULL,
            `card_number` varchar(32) NOT NULL DEFAULT '',
            `card_holder` varchar(120) NOT NULL DEFAULT '',
            `sms_secret` varchar(64) NOT NULL DEFAULT '',
            `tutorial_video_url` varchar(500) NOT NULL DEFAULT '',
            `tutorial_pdf_url` varchar(500) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $bankOrdersSql = "CREATE TABLE IF NOT EXISTS `#__bloxia_bank_orders` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(11) UNSIGNED NOT NULL,
            `package_key` varchar(32) NOT NULL,
            `bitblox_amount` int(11) UNSIGNED NOT NULL,
            `base_toman` bigint(20) UNSIGNED NOT NULL,
            `random_code` int(11) UNSIGNED NOT NULL,
            `total_toman` bigint(20) UNSIGNED NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `sms_raw` text DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `expires_at` datetime NOT NULL,
            `paid_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_status_expires` (`status`, `expires_at`),
            KEY `idx_total_toman` (`total_toman`),
            KEY `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $db->setQuery($presenceSql)->execute();
            $db->setQuery($logsSql)->execute();
            $db->setQuery($chatSql)->execute();
            $db->setQuery($pvpSql)->execute();
            $db->setQuery($bankSettingsSql)->execute();
            $db->setQuery($bankOrdersSql)->execute();
        } catch (\Throwable $e) {
            // نادیده گرفتن خطا در صورت محدودیت دسترسی DDL
        }
    }

    /**
     * ثبت یک رویداد در جدول #__bloxia_logs
     */
    public static function logActivity(int $userId, string $actionType, int $amount = 0, ?int $targetUserId = null, string $details = ''): void
    {
        if ($userId <= 0) {
            return;
        }

        self::ensureTables();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();

        $columns = ['user_id', 'action_type', 'amount', 'target_user_id', 'details', 'created_at'];
        $values = [
            $userId,
            $db->quote($actionType),
            $amount,
            $targetUserId !== null ? $targetUserId : 'NULL',
            $db->quote($details),
            $db->quote($now),
        ];

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__bloxia_logs'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        try {
            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            // ثبت لاگ نباید جریان اصلی بازی را متوقف کند
        }
    }

    /**
     * عنوان فارسی انواع رویدادها برای نمایش در پنل ادمین
     */
    public static function getActionLabel(string $actionType): string
    {
        $map = [
            'wallet_tx'           => '💎 تراکنش کیف پول',
            'buy_cart'            => '🛒 خرید از فروشگاه',
            'hair_salon'          => '💇 آرایشگاه',
            'cas_save'            => '👤 ویرایش کاراکتر',
            'save_home'           => '🛋️ چیدمان خانه',
            'color_change'        => '🎨 تغییر رنگ (پرداخت BitBlox)',
            'visit_home'          => '🏠 بازدید از خانه همسایه',
            'game_submit'         => '📤 ارسال بازی جدید',
            'game_approve'        => '✅ تایید بازی (ادمین)',
            'game_edit'           => '✏️ ویرایش پاداش بازی',
            'game_reject'         => '🗑️ رد و حذف بازی',
            'game_play'           => '🎮 اجرای بازی در تلویزیون',
            'host_income'         => '💰 درآمد میزبانی بازی مهمان',
            'pvp_win'             => '⚔️ پیروزی در چالش دونفره',
            'bank_topup'          => '🏦 شارژ حساب کارت به کارت',
            'admin_wallet_adjust' => '🏦 تغییر دستی BitBlox (ادمین)',
        ];

        return $map[$actionType] ?? $actionType;
    }

    /**
     * کاتالوگ پیش‌فرض تمام آیتم‌های فروشگاه (لباس، اکسسوری و لوازم خانگی کامل)
     */
    public static function getDefaultCatalog(): array
    {
        return [
            // پوشاک و اکسسوری
            ['key' => 'hat_crown',      'title' => '👑 تاج طلایی سلطنتی',       'type' => 'hat',      'shop' => 'shop_clothes',     'price' => 80,  'color' => 0xfbbf24],
            ['key' => 'hat_cap',        'title' => '🧢 کلاه اسپرت آبی',         'type' => 'hat',      'shop' => 'shop_clothes',     'price' => 40,  'color' => 0x3b82f6],
            ['key' => 'outfit_pink',    'title' => '👚 لباس مجلسی صورتی',       'type' => 'shirt',    'shop' => 'shop_clothes',     'price' => 60,  'color' => 0xec4899],
            ['key' => 'outfit_purple',  'title' => '👕 لباس کهکشانی بنفش',      'type' => 'shirt',    'shop' => 'shop_clothes',     'price' => 70,  'color' => 0x8b5cf6],
            ['key' => 'shoes_red',      'title' => '👟 کتانی اسپرت قرمز',       'type' => 'shoes',    'shop' => 'shop_clothes',     'price' => 45,  'color' => 0xef4444],
            ['key' => 'shoes_gold',     'title' => '👞 کفش طلایی درخشان',       'type' => 'shoes',    'shop' => 'shop_clothes',     'price' => 65,  'color' => 0xf59e0b],
            ['key' => 'prop_sword',     'title' => '⚔️ شمشیر شوالیه',          'type' => 'handProp', 'shop' => 'shop_accessories', 'price' => 90,  'color' => 0x38bdf8],
            ['key' => 'prop_axe',       'title' => '🪓 تبر چوب‌بری',            'type' => 'handProp', 'shop' => 'shop_accessories', 'price' => 85,  'color' => 0x94a3b8],
            ['key' => 'prop_flower',    'title' => '💐 دسته گل رنگارنگ',        'type' => 'handProp', 'shop' => 'shop_accessories', 'price' => 35,  'color' => 0xf43f5e],
            ['key' => 'prop_phone',     'title' => '📱 موبایل هوشمند',          'type' => 'handProp', 'shop' => 'shop_accessories', 'price' => 65,  'color' => 0x1e293b],

            // ۱. دیوار و کاغذ دیواری (حداقل ۳ مورد)
            ['key' => 'wall_paint_classic', 'title' => '🎨 رنگ دیوار کلاسیک با قرنیز', 'type' => 'wall_style', 'shop' => 'shop_furniture', 'price' => 40, 'color' => 0x94a3b8],
            ['key' => 'wall_wp_striped',    'title' => '📜 کاغذ دیواری راه‌راه مدرن',   'type' => 'wall_style', 'shop' => 'shop_furniture', 'price' => 55, 'color' => 0xcbd5e1],
            ['key' => 'wall_wp_floral',     'title' => '🌸 کاغذ دیواری گلدار کلاسیک',  'type' => 'wall_style', 'shop' => 'shop_furniture', 'price' => 65, 'color' => 0xfce7f3],
            ['key' => 'wall_wp_brick',      'title' => '🧱 دیوارپوش آجری و هندسی',      'type' => 'wall_style', 'shop' => 'shop_furniture', 'price' => 75, 'color' => 0xe2e8f0],

            // ۲. کفپوش پارکت و سرامیک (حداقل ۳ مورد برای هر کدام)
            ['key' => 'floor_parquet_oak',     'title' => '🪵 پارکت بلوط روشن',        'type' => 'floor_style', 'shop' => 'shop_furniture', 'price' => 50, 'color' => 0xd8b48b],
            ['key' => 'floor_parquet_walnut',  'title' => '🪵 پارکت گردویی تیره',      'type' => 'floor_style', 'shop' => 'shop_furniture', 'price' => 60, 'color' => 0x78350f],
            ['key' => 'floor_parquet_white',   'title' => '🪵 پارکت سفید اسکاندیناوی',  'type' => 'floor_style', 'shop' => 'shop_furniture', 'price' => 65, 'color' => 0xf1f5f9],
            ['key' => 'floor_ceramic_marble',  'title' => '⬜ سرامیک مرمر سفید طلایی',   'type' => 'floor_style', 'shop' => 'shop_furniture', 'price' => 75, 'color' => 0xf8fafc],
            ['key' => 'floor_ceramic_checker', 'title' => '🏁 سرامیک شطرنجی کلاسیک',    'type' => 'floor_style', 'shop' => 'shop_furniture', 'price' => 65, 'color' => 0xcbd5e1],
            ['key' => 'floor_ceramic_hex',     'title' => '🔷 سرامیک شش‌ضلعی فیروزه‌ای', 'type' => 'floor_style', 'shop' => 'shop_furniture', 'price' => 70, 'color' => 0x99f6e4],

            // ۳. قاب عکس دیواری (حداقل ۳ مورد)
            ['key' => 'furn_frame_nature', 'title' => '🖼️ قاب عکس منظره طبیعت', 'type' => 'frame', 'shop' => 'shop_furniture', 'price' => 35, 'color' => 0x1e293b],
            ['key' => 'furn_frame_art',    'title' => '🎨 قاب عکس نقاشی مدرن',  'type' => 'frame', 'shop' => 'shop_furniture', 'price' => 45, 'color' => 0x78350f],
            ['key' => 'furn_frame_city',   'title' => '🌃 قاب عکس شب‌های شهر',   'type' => 'frame', 'shop' => 'shop_furniture', 'price' => 50, 'color' => 0x0f172a],

            // ۴. لوازم آشپزخانه (حداقل ۳ مورد)
            ['key' => 'furn_fridge',         'title' => '🧊 یخچال دوقلو مدرن',        'type' => 'kitchen', 'shop' => 'shop_furniture', 'price' => 110, 'color' => 0xe2e8f0],
            ['key' => 'furn_stove',          'title' => '🍳 اجاق گاز و فر آشپزخانه',   'type' => 'kitchen', 'shop' => 'shop_furniture', 'price' => 95,  'color' => 0x334155],
            ['key' => 'furn_kitchen_island', 'title' => '☕ جزیره و قهوه‌ساز آشپزخانه', 'type' => 'kitchen', 'shop' => 'shop_furniture', 'price' => 85,  'color' => 0xf8fafc],

            // ۵. نورپردازی (حداقل ۳ مورد)
            ['key' => 'furn_lamp',         'title' => '💡 آباژور ایستاده طلایی', 'type' => 'lighting', 'shop' => 'shop_furniture', 'price' => 45, 'color' => 0xf59e0b],
            ['key' => 'furn_table_lamp',   'title' => '🪔 آباژور رومیزی کلاسیک', 'type' => 'lighting', 'shop' => 'shop_furniture', 'price' => 40, 'color' => 0xfbbf24],
            ['key' => 'furn_wall_sconce',  'title' => '🔦 چراغ دیواری مدرن',     'type' => 'lighting', 'shop' => 'shop_furniture', 'price' => 50, 'color' => 0xfef08a],

            // ۶. لوازم دکوراتیو و مبلمان منزل
            ['key' => 'furn_sofa',     'title' => '🛋️ مبل راحتی سه‌نفره',     'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 75,  'color' => 0xef4444],
            ['key' => 'furn_armchair', 'title' => '🪑 مبل تک‌نفره مدرن',       'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 55,  'color' => 0xf1f5f9],
            ['key' => 'furn_table',    'title' => '🪑 میز پذیرایی و عسلی',     'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 65,  'color' => 0xb45309],
            ['key' => 'furn_statue',   'title' => '🗿 مجسمه دکوراتیو هنری',    'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 70,  'color' => 0xfbbf24],
            ['key' => 'furn_rug',      'title' => '🧶 فرش طرح‌دار نشیمن',      'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 50,  'color' => 0xeab308],
            ['key' => 'furn_plant',    'title' => '🪴 گلدان برگ‌انجیری',       'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 30,  'color' => 0x16a34a],
            ['key' => 'furn_radiator', 'title' => '🔥 شوفاژ دکوراتیو رنگی',    'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 40,  'color' => 0xef4444],
            ['key' => 'furn_tv',       'title' => '📺 تلویزیون هوشمند اضافه',   'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 110, 'color' => 0x1e293b],
            ['key' => 'furn_arcade',   'title' => '🕹️ میز بازی آرکید',         'type' => 'decor', 'shop' => 'shop_furniture', 'price' => 95,  'color' => 0x10b981],

            // ۷. تخت خواب اتاق خواب (حداقل ۳ مورد)
            ['key' => 'furn_bed_single', 'title' => '🛏️ تخت تک‌نفره مدرن',   'type' => 'bedroom', 'shop' => 'shop_furniture', 'price' => 90,  'color' => 0x6366f1],
            ['key' => 'furn_bed_double', 'title' => '🛏️ تخت دونفره لوکس',    'type' => 'bedroom', 'shop' => 'shop_furniture', 'price' => 130, 'color' => 0xec4899],
            ['key' => 'furn_bed_bunk',   'title' => '🛏️ تخت دوطبقه فانتزی',  'type' => 'bedroom', 'shop' => 'shop_furniture', 'price' => 115, 'color' => 0x0ea5e9],
        ];
    }

    /**
     * همگام‌سازی آیتم‌های پیش‌فرض با جدول #__bloxia_items و بازگرداندن لیست زنده
     */
    public static function syncAndGetItems(bool $onlyPublished = false): array
    {
        self::ensureTables();
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_items'));
        $db->setQuery($query);
        $existing = $db->loadObjectList('asset_path') ?: [];

        $defaults = self::getDefaultCatalog();
        foreach ($defaults as $def) {
            if (!isset($existing[$def['key']])) {
                $ins = $db->getQuery(true)
                    ->insert($db->quoteName('#__bloxia_items'))
                    ->columns($db->quoteName(['title', 'type', 'price', 'asset_path', 'published']))
                    ->values(implode(',', [
                        $db->quote($def['title']),
                        $db->quote($def['type']),
                        (int) $def['price'],
                        $db->quote($def['key']),
                        1,
                    ]));
                try {
                    $db->setQuery($ins)->execute();
                } catch (\Throwable $e) {
                }
            }
        }

        $query2 = $db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_items'));
        if ($onlyPublished) {
            $query2->where($db->quoteName('published') . ' = 1');
        }
        $db->setQuery($query2);
        $rows = $db->loadObjectList('asset_path') ?: [];

        $metaByKey = [];
        foreach ($defaults as $d) {
            $metaByKey[$d['key']] = $d;
        }

        $result = [];
        foreach ($rows as $key => $row) {
            $meta = $metaByKey[$key] ?? ['shop' => 'shop_furniture', 'color' => 0x94a3b8];
            $result[] = [
                'id'        => (int) $row->id,
                'key'       => $key,
                'title'     => $row->title,
                'type'      => $row->type,
                'shop'      => $meta['shop'],
                'price'     => (int) $row->price,
                'color'     => $meta['color'],
                'published' => (int) $row->published,
            ];
        }

        return $result;
    }

    /**
     * پکیج‌های ثابت شارژ حساب BitBlox در بانک
     */
    public static function getBankPackages(): array
    {
        return [
            'pkg_100' => [
                'key'           => 'pkg_100',
                'bitblox'       => 100,
                'orig_toman'    => 500000,
                'discount_pct'  => 0,
                'base_toman'    => 500000,
                'label'         => '۱۰۰ BitBlox',
            ],
            'pkg_500' => [
                'key'           => 'pkg_500',
                'bitblox'       => 500,
                'orig_toman'    => 2500000,
                'discount_pct'  => 5,
                'base_toman'    => 2375000,
                'label'         => '۵۰۰ BitBlox (۵٪ تخفیف)',
            ],
            'pkg_2000' => [
                'key'           => 'pkg_2000',
                'bitblox'       => 2000,
                'orig_toman'    => 10000000,
                'discount_pct'  => 10,
                'base_toman'    => 9000000,
                'label'         => '۲۰۰۰ BitBlox (۱۰٪ تخفیف)',
            ],
        ];
    }

    /**
     * خواندن تنظیمات بانک (شماره کارت، کلید SMS Forwarder و لینک‌های آموزش)
     */
    public static function getBankSettings(): object
    {
        self::ensureTables();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_bank_settings'))->where('id = 1');
        $db->setQuery($query);
        $row = $db->loadObject();

        if (!$row) {
            $defaultSecret = bin2hex(random_bytes(12));
            $ins = $db->getQuery(true)
                ->insert($db->quoteName('#__bloxia_bank_settings'))
                ->columns($db->quoteName(['id', 'card_number', 'card_holder', 'sms_secret', 'tutorial_video_url', 'tutorial_pdf_url']))
                ->values('1, ' . $db->quote('') . ', ' . $db->quote('') . ', ' . $db->quote($defaultSecret) . ', ' . $db->quote('') . ', ' . $db->quote(''));
            try {
                $db->setQuery($ins)->execute();
            } catch (\Throwable $e) {
            }
            return (object) [
                'id'                 => 1,
                'card_number'        => '',
                'card_holder'        => '',
                'sms_secret'         => $defaultSecret,
                'tutorial_video_url' => '',
                'tutorial_pdf_url'   => '',
            ];
        }

        return $row;
    }

    /**
     * واریز BitBlox به حساب کاربر و همگام‌سازی JSON پروفایل + ثبت لاگ
     */
    public static function creditUserBitblox(int $userId, int $amount, string $actionType, string $details, ?int $targetUserId = null): bool
    {
        if ($userId <= 0 || $amount <= 0) {
            return false;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_user_profiles'))->where('user_id = ' . $userId);
        $db->setQuery($q);
        $profile = $db->loadObject();
        if (!$profile) {
            return false;
        }

        $state = !empty($profile->character_data) ? json_decode($profile->character_data, true) : [];
        if (!is_array($state)) {
            $state = [];
        }
        $cur = isset($state['bitblox']) ? (int) $state['bitblox'] : (int) $profile->bloxia_points;
        $newBalance = $cur + $amount;
        $state['bitblox'] = $newBalance;

        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_user_profiles'))
            ->set($db->quoteName('character_data') . ' = ' . $db->quote(json_encode($state)))
            ->set($db->quoteName('bloxia_points') . ' = ' . $newBalance)
            ->where('user_id = ' . $userId);
        $db->setQuery($upd)->execute();

        self::logActivity($userId, $actionType, $amount, $targetUserId, $details);
        return true;
    }
}
