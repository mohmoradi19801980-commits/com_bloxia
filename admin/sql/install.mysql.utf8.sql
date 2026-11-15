-- جدول پروفایل و امتیازات کاربران
CREATE TABLE IF NOT EXISTS `#__bloxia_user_profiles` (
  `user_id` int(11) UNSIGNED NOT NULL, -- متصل به جدول users جوملا
  `bloxia_points` int(11) NOT NULL DEFAULT 0, -- کیف پول کاربر
  `character_type` varchar(50) DEFAULT NULL, -- مثلا girl_1 یا boy_headphone
  `has_onboarded` tinyint(1) NOT NULL DEFAULT 0, -- آیا مرحله انتخاب کاراکتر را رد کرده؟
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول آیتم‌های فروشگاه (لباس‌ها، اکسسوری‌ها)
CREATE TABLE IF NOT EXISTS `#__bloxia_items` (
  `id` int(11) UNSIGNED AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL, -- مثلا cloth, hat, shoes
  `price` int(11) NOT NULL DEFAULT 0, -- قیمت به Bloxia
  `asset_path` varchar(255) NOT NULL, -- مسیر فایل 3D یا تکسچر
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