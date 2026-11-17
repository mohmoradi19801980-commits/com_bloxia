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