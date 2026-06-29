-- ============================================================
-- GAMESTORE DATABASE SCHEMA
-- MySQL 5.7+ / MariaDB 10.3+
-- Jalankan file ini sekali via phpMyAdmin atau CLI:
--   mysql -u root -p gamestore < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gamestore`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `gamestore`;

-- ── Matikan FK check sementara ────────────────────────────────
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE: categories
-- ============================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50)  NOT NULL,
    `slug`       VARCHAR(50)  NOT NULL UNIQUE,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `slug`) VALUES
('Mobile', 'mobile'),
('PC',     'pc'),
('Console','console');

-- ============================================================
-- TABLE: products
-- ============================================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(120) NOT NULL UNIQUE,
    `currency`    VARCHAR(50)  NOT NULL,
    `icon`        VARCHAR(10)  NOT NULL DEFAULT '🎮',
    `img`         TEXT         DEFAULT NULL,
    `img_banner`  TEXT         DEFAULT NULL,
    `color`       VARCHAR(20)  NOT NULL DEFAULT '#2563EB',
    `badge`       VARCHAR(30)  NOT NULL DEFAULT '',
    `description` TEXT         DEFAULT NULL,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status`      (`status`),
    INDEX `idx_category`    (`category_id`),
    INDEX `idx_slug`        (`slug`),
    CONSTRAINT `fk_product_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products`
    (`category_id`,`name`,`slug`,`currency`,`icon`,`img`,`img_banner`,`color`,`badge`,`sort_order`) VALUES
(1,'Mobile Legends','mobile-legends','Diamond','⚔️',
 'https://play-lh.googleusercontent.com/DlBQ4v5CLDG2lHlHjJQQeR6L2HGTxYJgkqXTK7r_lkwpjWfKz6kL9kqT2g_Qvmu2fA=w480-h960',
 'https://play-lh.googleusercontent.com/DlBQ4v5CLDG2lHlHjJQQeR6L2HGTxYJgkqXTK7r_lkwpjWfKz6kL9kqT2g_Qvmu2fA=w480-h960',
 '#1a6fc4','Terlaris',1),
(1,'Free Fire','free-fire','Diamond','🔥',
 'https://play-lh.googleusercontent.com/WWcssdzTZvx7jbgGVbE-gu9xfXFWjNKUFaJoFHW0QKZZ_tPpgHm8wWbmQnqy47jSBA=w480-h960',
 'https://play-lh.googleusercontent.com/WWcssdzTZvx7jbgGVbE-gu9xfXFWjNKUFaJoFHW0QKZZ_tPpgHm8wWbmQnqy47jSBA=w480-h960',
 '#f97316','Populer',2),
(1,'PUBG Mobile','pubg-mobile','UC','🎯',
 'https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNqGDaw=w480-h960',
 'https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNqGDaw=w480-h960',
 '#eab308','',3),
(1,'Genshin Impact','genshin-impact','Genesis Crystal','✨',
 'https://play-lh.googleusercontent.com/D7spoHSbDHrEBv2FqnQkCPCXRHDHVJAX-q7GmFBVNmT5HA3ORoIBKJfU65Hj-qvJN5o=w480-h960',
 'https://play-lh.googleusercontent.com/D7spoHSbDHrEBv2FqnQkCPCXRHDHVJAX-q7GmFBVNmT5HA3ORoIBKJfU65Hj-qvJN5o=w480-h960',
 '#8b5cf6','Baru',4),
(1,'Call of Duty Mobile','codm','CP','🎖️',
 'https://play-lh.googleusercontent.com/LKGB6M3RSBV6BMWB91lOaLWJj76WxkDEVjPjVdnqWQBJjXqxknSLHaAbqv4HQXL7A=w480-h960',
 'https://play-lh.googleusercontent.com/LKGB6M3RSBV6BMWB91lOaLWJj76WxkDEVjPjVdnqWQBJjXqxknSLHaAbqv4HQXL7A=w480-h960',
 '#22c55e','',5),
(2,'Valorant','valorant','VP','🎮',
 'https://www.riotgames.com/darkroom/1440/playvalorant-keyart-valwebsite-1920x1080:b109a1773c0b47bd48c05ef67e895f7c.jpg',
 'https://www.riotgames.com/darkroom/1440/playvalorant-keyart-valwebsite-1920x1080:b109a1773c0b47bd48c05ef67e895f7c.jpg',
 '#ef4444','Hot',6),
(1,'Honkai Star Rail','honkai-star-rail','Oneiric Shard','⭐',
 'https://play-lh.googleusercontent.com/0R_rHETHiLiqoVFYJ_JCXk4jEkEn8g7fBVCl9oOMpwB24mDKdIjdJ-IzVNzAWDrYvA=w480-h960',
 'https://play-lh.googleusercontent.com/0R_rHETHiLiqoVFYJ_JCXk4jEkEn8g7fBVCl9oOMpwB24mDKdIjdJ-IzVNzAWDrYvA=w480-h960',
 '#06b6d4','Baru',7),
(2,'Steam Wallet','steam','USD','🎲',
 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/480px-Steam_icon_logo.svg.png',
 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/480px-Steam_icon_logo.svg.png',
 '#1e40af','',8);

-- ============================================================
-- TABLE: packages (paket harga per produk)
-- ============================================================
DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `amount`     INT UNSIGNED NOT NULL COMMENT 'Jumlah item (diamond, UC, dll)',
    `bonus`      INT UNSIGNED NOT NULL DEFAULT 0,
    `price`      INT UNSIGNED NOT NULL COMMENT 'Harga dalam Rupiah',
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `idx_product` (`product_id`),
    INDEX `idx_price`   (`price`),
    CONSTRAINT `fk_package_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mobile Legends packages (product_id=1)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(1,86,13000,0),(1,172,25000,0),(1,257,37000,0),(1,344,50000,0),
(1,514,73000,14),(1,706,100000,21),(1,1412,193000,42),(1,2195,300000,65);
-- Free Fire (product_id=2)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(2,70,11000,0),(2,140,21000,0),(2,355,50000,0),(2,720,100000,0),
(2,1450,193000,0),(2,2180,285000,0);
-- PUBG Mobile (product_id=3)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(3,60,25000,0),(3,300,98000,25),(3,600,190000,60),(3,1500,455000,150),(3,3000,900000,300);
-- Genshin Impact (product_id=4)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(4,60,13500,0),(4,300,65000,30),(4,980,200000,110),(4,1980,395000,260),
(4,3280,645000,600),(4,6480,1250000,1600);
-- CODM (product_id=5)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(5,80,16500,0),(5,400,75000,0),(5,800,145000,0),(5,2000,345000,0),(5,4000,680000,0);
-- Valorant (product_id=6)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(6,475,20000,0),(6,950,39000,0),(6,2050,82000,100),(6,3650,145000,150),
(6,5350,210000,350),(6,11000,420000,1000);
-- Honkai Star Rail (product_id=7)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(7,60,14000,0),(7,300,68000,30),(7,980,210000,110),(7,1980,410000,260);
-- Steam Wallet (product_id=8)
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
(8,5,85000,0),(8,10,165000,0),(8,20,325000,0),(8,50,800000,0),(8,100,1580000,0);

-- ============================================================
-- TABLE: users (customer accounts)
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `phone`      VARCHAR(20)  DEFAULT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `status`     ENUM('active','banned') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_email`  (`email`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admins
-- ============================================================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50)  NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL COMMENT 'password_hash()',
    `name`       VARCHAR(100) NOT NULL DEFAULT 'Admin',
    `email`      VARCHAR(150) DEFAULT NULL,
    `last_login` TIMESTAMP    NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username=admin, password=admin123
INSERT INTO `admins` (`username`,`password`,`name`,`email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@gamestore.id');

-- ============================================================
-- TABLE: orders
-- ============================================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id`             VARCHAR(20)  NOT NULL COMMENT 'Format: GS241201XXXX',
    `user_id`        INT UNSIGNED NULL     COMMENT 'NULL jika guest',
    `product_id`     INT UNSIGNED NOT NULL,
    `package_id`     INT UNSIGNED NOT NULL,
    `game_user_id`   VARCHAR(100) NOT NULL COMMENT 'ID akun game customer',
    `game_name`      VARCHAR(100) NOT NULL,
    `pkg_amount`     INT UNSIGNED NOT NULL,
    `pkg_currency`   VARCHAR(50)  NOT NULL,
    `price`          INT UNSIGNED NOT NULL,
    `payment_method` VARCHAR(50)  NOT NULL,
    `customer_name`  VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20)  DEFAULT NULL,
    `notes`          TEXT         DEFAULT NULL,
    `proof_img`      VARCHAR(255) DEFAULT NULL COMMENT 'Path bukti transfer',
    `status`         ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
    `admin_note`     TEXT         DEFAULT NULL,
    `processed_at`   TIMESTAMP    NULL,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status`     (`status`),
    INDEX `idx_product`    (`product_id`),
    INDEX `idx_user`       (`user_id`),
    INDEX `idx_created`    (`created_at`),
    INDEX `idx_payment`    (`payment_method`),
    CONSTRAINT `fk_order_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_order_package`
        FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: chat_sessions
-- ============================================================
DROP TABLE IF EXISTS `chat_sessions`;
CREATE TABLE `chat_sessions` (
    `id`               VARCHAR(20)  NOT NULL,
    `user_id`          INT UNSIGNED NULL,
    `customer_name`    VARCHAR(100) NOT NULL,
    `customer_email`   VARCHAR(150) DEFAULT NULL,
    `topic`            VARCHAR(100) NOT NULL DEFAULT 'Umum',
    `status`           ENUM('open','pending','resolved') NOT NULL DEFAULT 'open',
    `unread_admin`     SMALLINT     NOT NULL DEFAULT 0,
    `unread_customer`  SMALLINT     NOT NULL DEFAULT 0,
    `last_message`     VARCHAR(200) DEFAULT NULL,
    `last_time`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `admin_typing`     TINYINT(1)   NOT NULL DEFAULT 0,
    `customer_typing`  TINYINT(1)   NOT NULL DEFAULT 0,
    `typing_ts`        INT UNSIGNED DEFAULT NULL,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status`    (`status`),
    INDEX `idx_last_time` (`last_time`),
    INDEX `idx_user`      (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: chat_messages
-- ============================================================
DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
    `id`          VARCHAR(20)  NOT NULL,
    `session_id`  VARCHAR(20)  NOT NULL,
    `sender`      ENUM('admin','customer') NOT NULL,
    `sender_name` VARCHAR(100) NOT NULL,
    `message`     TEXT         NOT NULL,
    `is_system`   TINYINT(1)   NOT NULL DEFAULT 0,
    `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_session`  (`session_id`),
    INDEX `idx_created`  (`created_at`),
    CONSTRAINT `fk_msg_session`
        FOREIGN KEY (`session_id`) REFERENCES `chat_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payment_methods (konfigurasi dari admin)
-- ============================================================
DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       ENUM('ewallet','bank','qris') NOT NULL,
    `name`       VARCHAR(50)  NOT NULL,
    `number`     VARCHAR(50)  NOT NULL,
    `holder`     VARCHAR(100) NOT NULL,
    `color`      VARCHAR(20)  NOT NULL DEFAULT '#2563EB',
    `icon`       VARCHAR(10)  NOT NULL DEFAULT '💳',
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `idx_type`   (`type`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_methods` (`type`,`name`,`number`,`holder`,`color`,`icon`,`sort_order`) VALUES
('ewallet','DANA',      '081234567890','GameStore Official','#00AAFF','💙',1),
('ewallet','OVO',       '081234567890','GameStore Official','#6B3FA0','💜',2),
('ewallet','GoPay',     '081234567890','GameStore Official','#00AED6','💚',3),
('ewallet','ShopeePay', '081234567890','GameStore Official','#EE4D2D','🧡',4),
('ewallet','LinkAja',   '081234567890','GameStore Official','#E4202F','❤️',5),
('bank','BCA',          '1234567890',  'PT GameStore Indonesia','#005BAA','🏦',6),
('bank','Mandiri',      '1234567890',  'PT GameStore Indonesia','#003087','🏦',7),
('bank','BRI',          '1234567890',  'PT GameStore Indonesia','#0066AE','🏦',8),
('bank','BNI',          '1234567890',  'PT GameStore Indonesia','#E8601E','🏦',9),
('qris','QRIS',         'QRIS GameStore','Scan & Pay',       '#E31837','📲',10);

-- ============================================================
-- TABLE: promo_codes
-- ============================================================
DROP TABLE IF EXISTS `promo_codes`;
CREATE TABLE `promo_codes` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(30)  NOT NULL UNIQUE,
    `discount_pct` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Persen diskon 0-100',
    `min_purchase` INT UNSIGNED NOT NULL DEFAULT 0,
    `max_use`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    `used_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `valid_from`  DATE         NOT NULL,
    `valid_until` DATE         NOT NULL,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `promo_codes` (`code`,`discount_pct`,`min_purchase`,`max_use`,`valid_from`,`valid_until`) VALUES
('MLBB10',   10, 0,     0,    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('FF20',     20, 0,     100,  CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY)),
('GAMESTORE5', 5, 50000, 0,   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY));

-- ============================================================
-- TABLE: settings (konfigurasi toko)
-- ============================================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `key`   VARCHAR(100) NOT NULL,
    `value` TEXT         DEFAULT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`,`value`) VALUES
('site_name',       'GameStore'),
('site_tagline',    'Top Up Game Terlengkap & Termurah'),
('wa_number',       '6281234567890'),
('admin_email',     'admin@gamestore.id'),
('maintenance',     '0'),
('shop_open',       '1'),
('open_hours_start','00:00'),
('open_hours_end',  '23:59'),
('offline_message', 'Maaf, kami sedang offline. Silakan order kembali besok!');

-- ── Aktifkan FK check kembali ─────────────────────────────────
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SELESAI — Total 9 tabel
-- ============================================================
-- categories, products, packages, users, admins,
-- orders, chat_sessions, chat_messages,
-- payment_methods, promo_codes, settings
-- ============================================================
