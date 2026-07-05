-- ============================================================
--  GAMESTORE — DATABASE SCHEMA + SEED DATA
--  MySQL 5.7+ / MariaDB 10.3+
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_logs`,`chat_messages`,`chat_sessions`,
  `orders`,`promo_codes`,`packages`,`products`,`payment_methods`,
  `categories`,`customers`,`admins`,`settings`;

-- 1. SETTINGS
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT,
  `label` VARCHAR(200),
  `group` VARCHAR(50) DEFAULT 'general',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`,`value`,`label`,`group`) VALUES
('site_name','GameStore','Nama Toko','general'),
('site_tagline','Top Up Game Terlengkap & Termurah','Tagline','general'),
('admin_email','admin@gamestore.id','Email Admin','contact'),
('wa_number','6281234567890','Nomor WhatsApp','contact'),
('site_description','Platform top up game digital terlengkap dan terpercaya.','Deskripsi','general'),
('maintenance_mode','0','Mode Maintenance','general'),
('order_timeout','30','Timeout Order (mnt)','order'),
('max_upload_mb','5','Maks Upload MB','general'),
('always_open','1','Toko 24 Jam','general'),
('brute_force_protect','1','Proteksi Brute Force','security'),
('session_log','1','Log Aktivitas','security'),
('csrf_protect','1','Proteksi CSRF','security'),
('session_timeout','120','Session Timeout','security'),
('notif_new_order','1','Notif Order Baru','notification'),
('notif_payment','1','Notif Pembayaran','notification'),
('notif_chat','1','Notif Chat','notification'),
('notif_email','admin@gamestore.id','Email Notifikasi','notification');

-- 2. ADMINS
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL DEFAULT 'Admin',
  `email` VARCHAR(150),
  `role` ENUM('super','admin','cs') DEFAULT 'admin',
  `last_login` DATETIME,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password: admin123
INSERT INTO `admins` (`username`,`password`,`name`,`email`,`role`) VALUES
('admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Admin Utama','admin@gamestore.id','super');

-- 3. CUSTOMERS
CREATE TABLE `customers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) UNIQUE,
  `phone` VARCHAR(20),
  `password` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `remember_token` VARCHAR(64),
  `failed_attempts` TINYINT UNSIGNED DEFAULT 0,
  `locked_until` DATETIME,
  `last_login` DATETIME,
  `reset_token` VARCHAR(64),
  `reset_expires` DATETIME,
  `total_orders` INT UNSIGNED DEFAULT 0,
  `total_spent` BIGINT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_phone` (`phone`),
  KEY `idx_remember` (`remember_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample customers (password: admin123)
INSERT INTO `customers` (`name`,`email`,`phone`,`password`) VALUES
('Budi Santoso','budi@example.com','081234500001','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Siti Rahayu','siti@example.com','081234500002','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Ahmad Rizki','ahmad@example.com','081234500003','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 4. CATEGORIES
CREATE TABLE `categories` (
  `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(60) NOT NULL UNIQUE,
  `icon` VARCHAR(10) DEFAULT '🎮',
  `sort_order` TINYINT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`,`slug`,`icon`,`sort_order`) VALUES
('Mobile','mobile','📱',1),('PC','pc','💻',2),('Console','console','🎮',3);

-- 5. PRODUCTS
CREATE TABLE `products` (
  `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` TINYINT UNSIGNED DEFAULT 1,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `currency` VARCHAR(50) DEFAULT 'Diamond',
  `icon` VARCHAR(10) DEFAULT '🎮',
  `color` VARCHAR(20) DEFAULT '#2563EB',
  `img` TEXT,
  `img_banner` TEXT,
  `badge` VARCHAR(30),
  `description` TEXT,
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_category` (`category_id`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`category_id`,`name`,`slug`,`currency`,`icon`,`color`,`img`,`badge`,`sort_order`) VALUES
(1,'Mobile Legends','mobile-legends','Diamond','⚔️','#1a6fc4','https://play-lh.googleusercontent.com/DlBQ4v5CLDG2lHlHjJQQeR6L2HGTxYJgkqXTK7r_lkwpjWfKz6kL9kqT2g_Qvmu2fA=w480','Terlaris',1),
(1,'Free Fire','free-fire','Diamond','🔥','#f97316','https://play-lh.googleusercontent.com/WWcssdzTZvx7jbgGVbE-gu9xfXFWjNKUFaJoFHW0QKZZ_tPpgHm8wWbmQnqy47jSBA=w480','Populer',2),
(1,'PUBG Mobile','pubg-mobile','UC','🎯','#eab308','https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNqGDaw=w480','',3),
(1,'Genshin Impact','genshin-impact','Genesis Crystal','✨','#8b5cf6','https://play-lh.googleusercontent.com/D7spoHSbDHrEBv2FqnQkCPCXRHDHVJAX-q7GmFBVNmT5HA3ORoIBKJfU65Hj-qvJN5o=w480','Baru',4),
(1,'Call of Duty Mobile','codm','CP','🎖️','#22c55e','https://play-lh.googleusercontent.com/LKGB6M3RSBV6BMWB91lOaLWJj76WxkDEVjPjVdnqWQBJjXqxknSLHaAbqv4HQXL7A=w480','',5),
(2,'Valorant','valorant','VP','🎮','#ef4444',NULL,'Hot',6),
(1,'Honkai Star Rail','honkai-star-rail','Oneiric Shard','⭐','#06b6d4','https://play-lh.googleusercontent.com/0R_rHETHiLiqoVFYJ_JCXk4jEkEn8g7fBVCl9oOMpwB24mDKdIjdJ-IzVNzAWDrYvA=w480','Baru',7),
(2,'Steam Wallet','steam','USD','🎲','#1e40af',NULL,'',8);

-- 6. PACKAGES
CREATE TABLE `packages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` SMALLINT UNSIGNED NOT NULL,
  `amount` INT UNSIGNED NOT NULL,
  `bonus` INT UNSIGNED DEFAULT 0,
  `price` INT UNSIGNED NOT NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  KEY `idx_product` (`product_id`),
  KEY `idx_price` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `packages` (`product_id`,`amount`,`bonus`,`price`,`is_popular`) VALUES
(1,86,0,13000,0),(1,172,0,25000,0),(1,257,0,37000,0),(1,344,0,50000,0),(1,514,14,73000,1),(1,706,21,100000,0),(1,1412,42,193000,0),(1,2195,65,300000,0),
(2,70,0,11000,0),(2,140,0,21000,0),(2,355,0,50000,1),(2,720,0,100000,0),(2,1450,0,193000,0),(2,2180,0,285000,0),
(3,60,0,25000,0),(3,300,25,98000,1),(3,600,60,190000,0),(3,1500,150,455000,0),(3,3000,300,900000,0),
(4,60,0,13500,0),(4,300,30,65000,0),(4,980,110,200000,1),(4,1980,260,395000,0),(4,3280,600,645000,0),(4,6480,1600,1250000,0),
(5,80,0,16500,0),(5,400,0,75000,0),(5,800,0,145000,1),(5,2000,0,345000,0),(5,4000,0,680000,0),
(6,475,0,20000,0),(6,950,0,39000,1),(6,2050,100,82000,0),(6,3650,150,145000,0),(6,5350,350,210000,0),(6,11000,1000,420000,0),
(7,60,0,14000,0),(7,300,30,68000,1),(7,980,110,210000,0),(7,1980,260,410000,0),
(8,5,0,85000,0),(8,10,0,165000,0),(8,20,0,325000,1),(8,50,0,800000,0),(8,100,0,1580000,0);

-- 7. PAYMENT METHODS
CREATE TABLE `payment_methods` (
  `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('ewallet','bank','qris') NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `number` VARCHAR(50),
  `account_name` VARCHAR(100),
  `color` VARCHAR(20) DEFAULT '#2563EB',
  `icon` VARCHAR(10) DEFAULT '💳',
  `sort_order` TINYINT UNSIGNED DEFAULT 0,
  `status` ENUM('active','inactive') DEFAULT 'active',
  KEY `idx_type` (`type`), KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_methods` (`type`,`name`,`number`,`account_name`,`color`,`icon`,`sort_order`) VALUES
('ewallet','DANA','081234567890','GameStore Official','#00AAFF','💙',1),
('ewallet','OVO','081234567890','GameStore Official','#6B3FA0','💜',2),
('ewallet','GoPay','081234567890','GameStore Official','#00AED6','💚',3),
('ewallet','ShopeePay','081234567890','GameStore Official','#EE4D2D','🧡',4),
('ewallet','LinkAja','081234567890','GameStore Official','#E4202F','❤️',5),
('bank','BCA','1234567890','PT GameStore Indonesia','#005BAA','🏦',6),
('bank','Mandiri','1234567890','PT GameStore Indonesia','#003087','🏦',7),
('bank','BRI','1234567890','PT GameStore Indonesia','#0066AE','🏦',8),
('bank','BNI','1234567890','PT GameStore Indonesia','#E8601E','🏦',9),
('qris','QRIS',NULL,'GameStore Official','#E31837','📱',10);

-- 8. PROMO CODES
CREATE TABLE `promo_codes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `description` VARCHAR(200),
  `type` ENUM('percent','flat') DEFAULT 'percent',
  `value` INT UNSIGNED NOT NULL,
  `min_purchase` INT UNSIGNED DEFAULT 0,
  `max_discount` INT UNSIGNED,
  `used_count` INT UNSIGNED DEFAULT 0,
  `max_use` INT UNSIGNED,
  `valid_from` DATE,
  `valid_until` DATE,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `promo_codes` (`code`,`description`,`type`,`value`,`min_purchase`,`max_use`,`valid_until`) VALUES
('MLBB10','Diskon 10% Mobile Legends','percent',10,10000,1000,DATE_ADD(CURDATE(),INTERVAL 3 MONTH)),
('FF20','Diskon 20% Free Fire','percent',20,10000,500,DATE_ADD(CURDATE(),INTERVAL 3 MONTH)),
('GAMESTORE5','Diskon 5% Semua Produk','percent',5,0,NULL,DATE_ADD(CURDATE(),INTERVAL 6 MONTH)),
('NEWMEMBER','Diskon Rp5.000 Member Baru','flat',5000,20000,1000,DATE_ADD(CURDATE(),INTERVAL 1 MONTH)),
('QRIS15','Diskon 15% Bayar QRIS','percent',15,15000,200,DATE_ADD(CURDATE(),INTERVAL 2 MONTH));

-- 9. ORDERS
CREATE TABLE `orders` (
  `id` VARCHAR(20) NOT NULL PRIMARY KEY,
  `customer_id` INT UNSIGNED,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_wa` VARCHAR(20),
  `product_id` SMALLINT UNSIGNED,
  `package_id` INT UNSIGNED,
  `product_name` VARCHAR(100) NOT NULL,
  `package_amount` INT UNSIGNED DEFAULT 0,
  `currency` VARCHAR(50) DEFAULT '',
  `game_user_id` VARCHAR(100) DEFAULT '',
  `payment_method` VARCHAR(50) DEFAULT '',
  `subtotal` INT UNSIGNED DEFAULT 0,
  `discount` INT UNSIGNED DEFAULT 0,
  `promo_code` VARCHAR(30),
  `total` INT UNSIGNED DEFAULT 0,
  `proof_image` VARCHAR(255),
  `note` TEXT,
  `admin_note` TEXT,
  `status` ENUM('pending','processing','completed','cancelled','refunded') DEFAULT 'pending',
  `processed_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `paid_at` DATETIME,
  `completed_at` DATETIME,
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_product` (`product_id`),
  KEY `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`,`customer_name`,`customer_wa`,`product_id`,`product_name`,`package_amount`,`currency`,`game_user_id`,`payment_method`,`subtotal`,`total`,`status`,`created_at`,`paid_at`,`completed_at`) VALUES
('GS2401150001','Budi Santoso','08100000001',1,'Mobile Legends',514,'Diamond','123456789','DANA',73000,73000,'completed',NOW()-INTERVAL 1 DAY,NOW()-INTERVAL 23 HOUR,NOW()-INTERVAL 22 HOUR),
('GS2401150002','Siti Rahayu','08100000002',2,'Free Fire',355,'Diamond','987654321','OVO',50000,50000,'completed',NOW()-INTERVAL 2 DAY,NOW()-INTERVAL 47 HOUR,NOW()-INTERVAL 46 HOUR),
('GS2401150003','Ahmad Rizki','08100000003',6,'Valorant',950,'VP','AhRI2024','GoPay',39000,39000,'completed',NOW()-INTERVAL 3 DAY,NOW()-INTERVAL 71 HOUR,NOW()-INTERVAL 70 HOUR),
('GS2401150004','Dewi Lestari','08100000004',4,'Genshin Impact',980,'Genesis Crystal','DLST001','BCA',200000,200000,'processing',NOW()-INTERVAL 1 HOUR,NOW()-INTERVAL 50 MINUTE,NULL),
('GS2401150005','Rizky Pratama','08100000005',3,'PUBG Mobile',300,'UC','RP9988','ShopeePay',98000,98000,'pending',NOW()-INTERVAL 30 MINUTE,NULL,NULL),
('GS2401150006','Nurul Hidayah','08100000006',1,'Mobile Legends',1412,'Diamond','NH111222','QRIS',193000,193000,'completed',NOW()-INTERVAL 5 DAY,NOW()-INTERVAL 119 HOUR,NOW()-INTERVAL 118 HOUR),
('GS2401150007','Eko Susanto','08100000007',7,'Honkai Star Rail',300,'Oneiric Shard','EKOS77','DANA',68000,68000,'completed',NOW()-INTERVAL 4 DAY,NOW()-INTERVAL 95 HOUR,NOW()-INTERVAL 94 HOUR),
('GS2401150008','Maya Wulandari','08100000008',5,'Call of Duty Mobile',800,'CP','MAYA8899','OVO',145000,145000,'cancelled',NOW()-INTERVAL 6 DAY,NULL,NULL),
('GS2401150009','Dika Pratama','08100000009',2,'Free Fire',720,'Diamond','DIKA0099','BCA',100000,100000,'completed',NOW()-INTERVAL 7 DAY,NOW()-INTERVAL 167 HOUR,NOW()-INTERVAL 166 HOUR),
('GS2401150010','Rini Susanti','08100000010',1,'Mobile Legends',344,'Diamond','RINI5678','DANA',50000,50000,'completed',NOW()-INTERVAL 8 DAY,NOW()-INTERVAL 191 HOUR,NOW()-INTERVAL 190 HOUR);

-- 10. CHAT SESSIONS
CREATE TABLE `chat_sessions` (
  `id` VARCHAR(30) NOT NULL PRIMARY KEY,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(150),
  `topic` VARCHAR(100) DEFAULT 'Umum',
  `status` ENUM('open','pending','resolved') DEFAULT 'open',
  `unread_admin` SMALLINT UNSIGNED DEFAULT 0,
  `unread_customer` SMALLINT UNSIGNED DEFAULT 0,
  `last_message` VARCHAR(255),
  `last_time` INT UNSIGNED DEFAULT 0,
  `admin_typing` TINYINT(1) DEFAULT 0,
  `customer_typing` TINYINT(1) DEFAULT 0,
  `typing_ts` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. CHAT MESSAGES
CREATE TABLE `chat_messages` (
  `id` VARCHAR(20) NOT NULL PRIMARY KEY,
  `session_id` VARCHAR(30) NOT NULL,
  `sender` ENUM('admin','customer') NOT NULL,
  `sender_name` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('text','image','file') DEFAULT 'text',
  `is_system` TINYINT(1) DEFAULT 0,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_session` (`session_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. ACTIVITY LOGS
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED,
  `action` VARCHAR(100) NOT NULL,
  `detail` TEXT,
  `ip` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_admin` (`admin_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- VIEWS
CREATE OR REPLACE VIEW `v_order_summary` AS
SELECT DATE(created_at) AS order_date, COUNT(*) AS total_orders,
  SUM(status='completed') AS completed, SUM(status='pending') AS pending,
  SUM(status='cancelled') AS cancelled,
  COALESCE(SUM(IF(status='completed',total,0)),0) AS revenue
FROM orders GROUP BY DATE(created_at);

CREATE OR REPLACE VIEW `v_product_revenue` AS
SELECT p.id, p.name, p.icon, COUNT(o.id) AS total_orders,
  COALESCE(SUM(IF(o.status='completed',o.total,0)),0) AS revenue
FROM products p LEFT JOIN orders o ON o.product_id=p.id GROUP BY p.id;
-- ============================================================
--  SELESAI: 12 tabel + 2 views
--  Login admin  : admin / admin123
--  Login customer: budi@example.com / admin123
-- ============================================================
