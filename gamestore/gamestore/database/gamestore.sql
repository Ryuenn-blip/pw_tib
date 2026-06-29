-- ============================================================
--  GAMESTORE DATABASE SCHEMA
--  MySQL 5.7+ / MariaDB 10.3+
--  Run: mysql -u root -p < gamestore.sql
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `gamestore`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gamestore`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs, payment_proofs, chat_messages, chat_sessions,
    order_items, orders, promo_codes, packages, products, categories, customers, admins, settings;
SET FOREIGN_KEY_CHECKS = 1;

-- admins
CREATE TABLE `admins` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50)  NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `name`       VARCHAR(100) NOT NULL DEFAULT 'Admin',
    `email`      VARCHAR(150) NOT NULL DEFAULT '',
    `role`       ENUM('superadmin','admin','cs') NOT NULL DEFAULT 'admin',
    `last_login` DATETIME     DEFAULT NULL,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- categories
CREATE TABLE `categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50)  NOT NULL UNIQUE,
    `icon`       VARCHAR(10)  NOT NULL DEFAULT '🎮',
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- products
CREATE TABLE `products` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT UNSIGNED NOT NULL,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(120) NOT NULL UNIQUE,
    `currency`    VARCHAR(50)  NOT NULL,
    `icon`        VARCHAR(10)  NOT NULL DEFAULT '🎮',
    `img`         VARCHAR(500) DEFAULT NULL,
    `img_banner`  VARCHAR(500) DEFAULT NULL,
    `color`       VARCHAR(20)  NOT NULL DEFAULT '#2563EB',
    `badge`       VARCHAR(20)  DEFAULT NULL,
    `description` TEXT         DEFAULT NULL,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_status`   (`status`),
    CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- packages
CREATE TABLE `packages` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `amount`     INT UNSIGNED NOT NULL,
    `bonus`      INT UNSIGNED NOT NULL DEFAULT 0,
    `price`      INT UNSIGNED NOT NULL,
    `is_popular` TINYINT(1)   NOT NULL DEFAULT 0,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    CONSTRAINT `fk_pkg_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- customers
CREATE TABLE `customers` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(100) NOT NULL,
    `email`        VARCHAR(150) DEFAULT NULL UNIQUE,
    `phone`        VARCHAR(20)  DEFAULT NULL,
    `password`     VARCHAR(255) DEFAULT NULL,
    `total_orders` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_spent`  BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- orders
CREATE TABLE `orders` (
    `id`              VARCHAR(20)  NOT NULL,
    `customer_name`   VARCHAR(100) NOT NULL,
    `customer_phone`  VARCHAR(20)  DEFAULT NULL,
    `customer_id`     INT UNSIGNED DEFAULT NULL,
    `product_id`      INT UNSIGNED NOT NULL,
    `package_id`      INT UNSIGNED NOT NULL,
    `product_name`    VARCHAR(100) NOT NULL,
    `package_info`    VARCHAR(100) NOT NULL,
    `game_user_id`    VARCHAR(100) NOT NULL,
    `price`           INT UNSIGNED NOT NULL,
    `payment_method`  VARCHAR(50)  NOT NULL,
    `payment_account` VARCHAR(100) DEFAULT NULL,
    `promo_code`      VARCHAR(30)  DEFAULT NULL,
    `discount`        INT UNSIGNED NOT NULL DEFAULT 0,
    `total`           INT UNSIGNED NOT NULL,
    `status`          ENUM('pending','paid','processing','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `note`            TEXT         DEFAULT NULL,
    `admin_note`      TEXT         DEFAULT NULL,
    `paid_at`         DATETIME     DEFAULT NULL,
    `completed_at`    DATETIME     DEFAULT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status`   (`status`),
    KEY `idx_created`  (`created_at`),
    KEY `idx_product`  (`product_id`),
    CONSTRAINT `fk_order_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_order_pkg`  FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`)  ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- payment_proofs
CREATE TABLE `payment_proofs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`    VARCHAR(20)  NOT NULL,
    `file_name`   VARCHAR(255) NOT NULL,
    `file_path`   VARCHAR(500) NOT NULL,
    `file_size`   INT UNSIGNED NOT NULL DEFAULT 0,
    `mime_type`   VARCHAR(100) NOT NULL DEFAULT 'image/jpeg',
    `note`        TEXT         DEFAULT NULL,
    `verified`    TINYINT(1)   NOT NULL DEFAULT 0,
    `verified_at` DATETIME     DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    CONSTRAINT `fk_proof_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- chat_sessions
CREATE TABLE `chat_sessions` (
    `id`              VARCHAR(30)  NOT NULL,
    `customer_name`   VARCHAR(100) NOT NULL,
    `customer_email`  VARCHAR(150) DEFAULT NULL,
    `topic`           VARCHAR(100) NOT NULL DEFAULT 'Umum',
    `status`          ENUM('open','pending','resolved') NOT NULL DEFAULT 'open',
    `unread_admin`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `unread_customer` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `last_message`    VARCHAR(255) DEFAULT NULL,
    `last_time`       INT UNSIGNED NOT NULL DEFAULT 0,
    `admin_typing`    TINYINT(1)   NOT NULL DEFAULT 0,
    `customer_typing` TINYINT(1)   NOT NULL DEFAULT 0,
    `typing_ts`       INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status`  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- chat_messages
CREATE TABLE `chat_messages` (
    `id`          VARCHAR(20)  NOT NULL,
    `session_id`  VARCHAR(30)  NOT NULL,
    `sender`      ENUM('admin','customer') NOT NULL,
    `sender_name` VARCHAR(100) NOT NULL,
    `message`     TEXT         NOT NULL,
    `type`        ENUM('text','image','file') NOT NULL DEFAULT 'text',
    `is_system`   TINYINT(1)   NOT NULL DEFAULT 0,
    `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_session` (`session_id`),
    CONSTRAINT `fk_msg_session` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- promo_codes
CREATE TABLE `promo_codes` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`         VARCHAR(30)  NOT NULL UNIQUE,
    `description`  VARCHAR(200) DEFAULT NULL,
    `type`         ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    `value`        INT UNSIGNED NOT NULL,
    `min_purchase` INT UNSIGNED NOT NULL DEFAULT 0,
    `max_discount` INT UNSIGNED DEFAULT NULL,
    `used_count`   INT UNSIGNED NOT NULL DEFAULT 0,
    `max_use`      INT UNSIGNED DEFAULT NULL,
    `valid_from`   DATE         DEFAULT NULL,
    `valid_until`  DATE         DEFAULT NULL,
    `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- settings
CREATE TABLE `settings` (
    `key`        VARCHAR(100) NOT NULL,
    `value`      TEXT         DEFAULT NULL,
    `type`       ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
    `label`      VARCHAR(200) DEFAULT NULL,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- activity_logs
CREATE TABLE `activity_logs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id`   INT UNSIGNED    DEFAULT NULL,
    `action`     VARCHAR(100)    NOT NULL,
    `target`     VARCHAR(100)    DEFAULT NULL,
    `target_id`  VARCHAR(50)     DEFAULT NULL,
    `detail`     TEXT            DEFAULT NULL,
    `ip_address` VARCHAR(45)     DEFAULT NULL,
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_admin`   (`admin_id`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SEED DATA ─────────────────────────────────────────────────

-- Admin (password: admin123)
INSERT INTO `admins` (`username`,`password`,`name`,`email`,`role`) VALUES
('admin','$2y$10$TKh8H1.PfQ0A32L6sTROYOiIKIWEW6lM.3rVovQaI0lFdxrIuWVde','Admin GameStore','admin@gamestore.id','superadmin');

-- Categories
INSERT INTO `categories` (`id`,`name`,`icon`,`sort_order`) VALUES
(1,'Mobile','📱',1),(2,'PC','💻',2),(3,'Console','🎮',3);

-- Products
INSERT INTO `products` (`id`,`category_id`,`name`,`slug`,`currency`,`icon`,`img`,`img_banner`,`color`,`badge`,`sort_order`) VALUES
(1,1,'Mobile Legends','mobile-legends','Diamond','⚔️','https://play-lh.googleusercontent.com/DlBQ4v5CLDG2lHlHjJQQeR6L2HGTxYJgkqXTK7r_lkwpjWfKz6kL9kqT2g_Qvmu2fA=w480-h960','https://play-lh.googleusercontent.com/DlBQ4v5CLDG2lHlHjJQQeR6L2HGTxYJgkqXTK7r_lkwpjWfKz6kL9kqT2g_Qvmu2fA=w480-h960','#1a6fc4','Terlaris',1),
(2,1,'Free Fire','free-fire','Diamond','🔥','https://play-lh.googleusercontent.com/WWcssdzTZvx7jbgGVbE-gu9xfXFWjNKUFaJoFHW0QKZZ_tPpgHm8wWbmQnqy47jSBA=w480-h960','https://play-lh.googleusercontent.com/WWcssdzTZvx7jbgGVbE-gu9xfXFWjNKUFaJoFHW0QKZZ_tPpgHm8wWbmQnqy47jSBA=w480-h960','#f97316','Populer',2),
(3,1,'PUBG Mobile','pubg-mobile','UC','🎯','https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNqGDaw=w480-h960','https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNqGDaw=w480-h960','#eab308',NULL,3),
(4,1,'Genshin Impact','genshin-impact','Genesis Crystal','✨','https://play-lh.googleusercontent.com/D7spoHSbDHrEBv2FqnQkCPCXRHDHVJAX-q7GmFBVNmT5HA3ORoIBKJfU65Hj-qvJN5o=w480-h960','https://play-lh.googleusercontent.com/D7spoHSbDHrEBv2FqnQkCPCXRHDHVJAX-q7GmFBVNmT5HA3ORoIBKJfU65Hj-qvJN5o=w480-h960','#8b5cf6','Baru',4),
(5,1,'Call of Duty Mobile','codm','CP','🎖️','https://play-lh.googleusercontent.com/LKGB6M3RSBV6BMWB91lOaLWJj76WxkDEVjPjVdnqWQBJjXqxknSLHaAbqv4HQXL7A=w480-h960','https://play-lh.googleusercontent.com/LKGB6M3RSBV6BMWB91lOaLWJj76WxkDEVjPjVdnqWQBJjXqxknSLHaAbqv4HQXL7A=w480-h960','#22c55e',NULL,5),
(6,2,'Valorant','valorant','VP','🎮','https://www.riotgames.com/darkroom/1440/playvalorant-keyart-valwebsite-1920x1080:b109a1773c0b47bd48c05ef67e895f7c.jpg','https://www.riotgames.com/darkroom/1440/playvalorant-keyart-valwebsite-1920x1080:b109a1773c0b47bd48c05ef67e895f7c.jpg','#ef4444','Hot',6),
(7,1,'Honkai Star Rail','honkai-star-rail','Oneiric Shard','⭐','https://play-lh.googleusercontent.com/0R_rHETHiLiqoVFYJ_JCXk4jEkEn8g7fBVCl9oOMpwB24mDKdIjdJ-IzVNzAWDrYvA=w480-h960','https://play-lh.googleusercontent.com/0R_rHETHiLiqoVFYJ_JCXk4jEkEn8g7fBVCl9oOMpwB24mDKdIjdJ-IzVNzAWDrYvA=w480-h960','#06b6d4','Baru',7),
(8,2,'Steam Wallet','steam','USD','🎲','https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/480px-Steam_icon_logo.svg.png','https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/480px-Steam_icon_logo.svg.png','#1e40af',NULL,8);

-- Packages: Mobile Legends
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`,`is_popular`) VALUES
(1,86,0,13000,0),(1,172,0,25000,0),(1,257,0,37000,0),(1,344,0,50000,0),
(1,514,14,73000,1),(1,706,21,100000,0),(1,1412,42,193000,0),(1,2195,65,300000,0);

-- Packages: Free Fire
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`) VALUES
(2,70,0,11000),(2,140,0,21000),(2,355,0,50000),(2,720,0,100000),(2,1450,0,193000),(2,2180,0,285000);

-- Packages: PUBG Mobile
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`) VALUES
(3,60,0,25000),(3,300,25,98000),(3,600,60,190000),(3,1500,150,455000),(3,3000,300,900000);

-- Packages: Genshin Impact
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`) VALUES
(4,60,0,13500),(4,300,30,65000),(4,980,110,200000),(4,1980,260,395000),(4,3280,600,645000),(4,6480,1600,1250000);

-- Packages: CODM
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`) VALUES
(5,80,0,16500),(5,400,0,75000),(5,800,0,145000),(5,2000,0,345000),(5,4000,0,680000);

-- Packages: Valorant
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`) VALUES
(6,475,0,20000),(6,950,0,39000),(6,2050,100,82000),(6,3650,150,145000),(6,5350,350,210000),(6,11000,1000,420000);

-- Packages: Honkai Star Rail
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`) VALUES
(7,60,0,14000),(7,300,30,68000),(7,980,110,210000),(7,1980,260,410000);

-- Packages: Steam Wallet
INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`) VALUES
(8,5,0,85000),(8,10,0,165000),(8,20,0,325000),(8,50,0,800000),(8,100,0,1580000);

-- Promo Codes
INSERT INTO `promo_codes` (`code`,`description`,`type`,`value`,`min_purchase`,`max_use`,`valid_until`) VALUES
('MLBB10','Diskon 10% Mobile Legends','percent',10,10000,1000,DATE_ADD(CURDATE(),INTERVAL 3 MONTH)),
('FF20','Diskon 20% Free Fire','percent',20,10000,500,DATE_ADD(CURDATE(),INTERVAL 3 MONTH)),
('GAMESTORE5','Diskon 5% Semua Produk','percent',5,0,NULL,DATE_ADD(CURDATE(),INTERVAL 6 MONTH)),
('NEWMEMBER','Diskon Rp5.000 Member Baru','fixed',5000,20000,1000,DATE_ADD(CURDATE(),INTERVAL 1 MONTH)),
('QRIS15','Diskon 15% Bayar QRIS','percent',15,15000,200,DATE_ADD(CURDATE(),INTERVAL 2 MONTH));

-- Settings
INSERT INTO `settings` (`key`,`value`,`type`,`label`) VALUES
('site_name','GameStore','string','Nama Toko'),
('site_tagline','Top Up Game Terlengkap & Termurah','string','Tagline'),
('wa_number','6281234567890','string','Nomor WhatsApp'),
('admin_email','admin@gamestore.id','string','Email Admin'),
('dana_number','081234567890','string','Nomor DANA'),
('ovo_number','081234567890','string','Nomor OVO'),
('gopay_number','081234567890','string','Nomor GoPay'),
('shopeepay_number','081234567890','string','Nomor ShopeePay'),
('bca_number','1234567890','string','Rekening BCA'),
('bca_name','PT GameStore Indonesia','string','Nama Rekening BCA'),
('mandiri_number','1234567890','string','Rekening Mandiri'),
('mandiri_name','PT GameStore Indonesia','string','Nama Rekening Mandiri'),
('bri_number','1234567890','string','Rekening BRI'),
('bri_name','PT GameStore Indonesia','string','Nama Rekening BRI'),
('bni_number','1234567890','string','Rekening BNI'),
('bni_name','PT GameStore Indonesia','string','Nama Rekening BNI'),
('payment_timeout','15','integer','Timeout Pembayaran (menit)'),
('max_upload_mb','5','integer','Maks Upload Bukti (MB)'),
('maintenance_mode','0','boolean','Mode Maintenance');

-- Views
CREATE OR REPLACE VIEW `v_order_summary` AS
SELECT DATE(created_at) AS order_date, COUNT(*) AS total,
    SUM(status='completed') AS completed, SUM(status='pending') AS pending,
    SUM(IF(status='completed',total,0)) AS revenue
FROM `orders` GROUP BY DATE(created_at);

CREATE OR REPLACE VIEW `v_product_revenue` AS
SELECT p.id, p.name, p.icon,
    COUNT(o.id) AS total_orders,
    SUM(IF(o.status='completed',o.total,0)) AS revenue
FROM `products` p LEFT JOIN `orders` o ON o.product_id=p.id
GROUP BY p.id;

CREATE OR REPLACE VIEW `v_customer_stats` AS
SELECT customer_name, customer_phone,
    COUNT(*) AS total_orders,
    SUM(IF(status='completed',total,0)) AS total_spent,
    MAX(created_at) AS last_order
FROM `orders` GROUP BY customer_name, customer_phone;

-- payment_methods (ditambahkan setelah schema awal)
CREATE TABLE IF NOT EXISTS `payment_methods` (
    `id`           TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`         ENUM('ewallet','bank','qris') NOT NULL,
    `name`         VARCHAR(50)  NOT NULL,
    `number`       VARCHAR(50)  DEFAULT NULL,
    `account_name` VARCHAR(100) DEFAULT NULL,
    `color`        VARCHAR(20)  NOT NULL DEFAULT '#2563EB',
    `icon`         VARCHAR(10)  NOT NULL DEFAULT '💳',
    `sort_order`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`id`),
    KEY `idx_type`   (`type`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_methods` (`type`,`name`,`number`,`account_name`,`color`,`icon`,`sort_order`) VALUES
('ewallet','DANA',      '081234567890','GameStore Official','#00AAFF','💙',1),
('ewallet','OVO',       '081234567890','GameStore Official','#6B3FA0','💜',2),
('ewallet','GoPay',     '081234567890','GameStore Official','#00AED6','💚',3),
('ewallet','ShopeePay', '081234567890','GameStore Official','#EE4D2D','🧡',4),
('ewallet','LinkAja',   '081234567890','GameStore Official','#E4202F','❤️',5),
('bank',   'BCA',       '1234567890',  'PT GameStore Indonesia','#005BAA','🏦',6),
('bank',   'Mandiri',   '1234567890',  'PT GameStore Indonesia','#003087','🏦',7),
('bank',   'BRI',       '1234567890',  'PT GameStore Indonesia','#0066AE','🏦',8),
('bank',   'BNI',       '1234567890',  'PT GameStore Indonesia','#E8601E','🏦',9),
('qris',   'QRIS',       NULL,         'GameStore Official','#E31837','📱',10);
