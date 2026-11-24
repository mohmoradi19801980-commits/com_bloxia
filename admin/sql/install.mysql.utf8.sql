-- جدول پروفایل و امتیازات کاربران
CREATE TABLE IF NOT EXISTS `#__bloxia_user_profiles` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `bloxia_points` int(11) NOT NULL DEFAULT 0,
  `character_type` varchar(50) DEFAULT NULL,
  `character_data` text DEFAULT NULL, -- این ستون برای ذخیره JSON کاراکتر و بازی اضافه شد
  `has_onboarded` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول آیتم‌های فروشگاه (لباس‌ها، اکسسوری‌ها)
CREATE TABLE IF NOT EXISTS `#__bloxia_items` (
  `id` int(11) UNSIGNED AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `asset_path` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول دارایی‌های هر کاربر (Inventory)
CREATE TABLE IF NOT EXISTS `#__bloxia_inventory` (
  `id` int(11) UNSIGNED AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `item_id` int(11) UNSIGNED NOT NULL,
  `acquired_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_item` (`user_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول حضور آنلاین بازیکنان در دنیای متاورس (فاز ۳)
CREATE TABLE IF NOT EXISTS `#__bloxia_presence` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول لاگ فعالیت‌ها و تراکنش‌های BitBlox (فاز ۴)
CREATE TABLE IF NOT EXISTS `#__bloxia_logs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول پیام‌های چت امن بین کاربران (فاز ۳)
CREATE TABLE IF NOT EXISTS `#__bloxia_chat_messages` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول مسابقات دوطرفه PvP بین کاربران (فاز ۳)
CREATE TABLE IF NOT EXISTS `#__bloxia_pvp_matches` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;