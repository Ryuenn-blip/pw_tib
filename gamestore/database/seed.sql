-- ============================================================
--  GAMESTORE DATABASE SEED DATA
--  Jalankan SETELAH schema.sql
--  mysql -u root -p gamestore_db < seed.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── CATEGORIES ───────────────────────────────────────────────
INSERT INTO `categories` (`id`,`name`,`slug`,`icon`,`sort_order`) VALUES
(1, 'Mobile',  'mobile',  '📱', 1),
(2, 'PC',      'pc',      '💻', 2),
(3, 'Console', 'console', '🕹️', 3);

-- ── PRODUCTS ─────────────────────────────────────────────────
INSERT INTO `products` (`id`,`category_id`,`name`,`slug`,`currency`,`icon`,`img`,`img_banner`,`color`,`badge`,`sort_order`) VALUES
(1, 1, 'Mobile Legends',      'mobile-legends',   'Diamond',        '⚔️',
 'https://play-lh.googleusercontent.com/DlBQ4v5CLDG2lHlHjJQQeR6L2HGTxYJgkqXTK7r_lkwpjWfKz6kL9kqT2g_Qvmu2fA=w480-h960',
 'https://play-lh.googleusercontent.com/DlBQ4v5CLDG2lHlHjJQQeR6L2HGTxYJgkqXTK7r_lkwpjWfKz6kL9kqT2g_Qvmu2fA=w480-h960',
 '#1a6fc4', 'Terlaris', 1),

(2, 1, 'Free Fire',           'free-fire',        'Diamond',        '🔥',
 'https://play-lh.googleusercontent.com/WWcssdzTZvx7jbgGVbE-gu9xfXFWjNKUFaJoFHW0QKZZ_tPpgHm8wWbmQnqy47jSBA=w480-h960',
 'https://play-lh.googleusercontent.com/WWcssdzTZvx7jbgGVbE-gu9xfXFWjNKUFaJoFHW0QKZZ_tPpgHm8wWbmQnqy47jSBA=w480-h960',
 '#f97316', 'Populer', 2),

(3, 1, 'PUBG Mobile',         'pubg-mobile',      'UC',             '🎯',
 'https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNqGDaw=w480-h960',
 'https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNqGDaw=w480-h960',
 '#eab308', '', 3),

(4, 1, 'Genshin Impact',      'genshin-impact',   'Genesis Crystal','✨',
 'https://play-lh.googleusercontent.com/D7spoHSbDHrEBv2FqnQkCPCXRHDHVJAX-q7GmFBVNmT5HA3ORoIBKJfU65Hj-qvJN5o=w480-h960',
 'https://play-lh.googleusercontent.com/D7spoHSbDHrEBv2FqnQkCPCXRHDHVJAX-q7GmFBVNmT5HA3ORoIBKJfU65Hj-qvJN5o=w480-h960',
 '#8b5cf6', 'Baru', 4),

(5, 1, 'Call of Duty Mobile', 'codm',             'CP',             '🎖️',
 'https://play-lh.googleusercontent.com/LKGB6M3RSBV6BMWB91lOaLWJj76WxkDEVjPjVdnqWQBJjXqxknSLHaAbqv4HQXL7A=w480-h960',
 'https://play-lh.googleusercontent.com/LKGB6M3RSBV6BMWB91lOaLWJj76WxkDEVjPjVdnqWQBJjXqxknSLHaAbqv4HQXL7A=w480-h960',
 '#22c55e', '', 5),

(6, 2, 'Valorant',            'valorant',         'VP',             '🎮',
 'https://www.riotgames.com/darkroom/1440/playvalorant-keyart-valwebsite-1920x1080:b109a1773c0b47bd48c05ef67e895f7c.jpg',
 'https://www.riotgames.com/darkroom/1440/playvalorant-keyart-valwebsite-1920x1080:b109a1773c0b47bd48c05ef67e895f7c.jpg',
 '#ef4444', 'Hot', 6),

(7, 1, 'Honkai Star Rail',    'honkai-star-rail',  'Oneiric Shard', '⭐',
 'https://play-lh.googleusercontent.com/0R_rHETHiLiqoVFYJ_JCXk4jEkEn8g7fBVCl9oOMpwB24mDKdIjdJ-IzVNzAWDrYvA=w480-h960',
 'https://play-lh.googleusercontent.com/0R_rHETHiLiqoVFYJ_JCXk4jEkEn8g7fBVCl9oOMpwB24mDKdIjdJ-IzVNzAWDrYvA=w480-h960',
 '#06b6d4', 'Baru', 7),

(8, 2, 'Steam Wallet',        'steam',            'USD',            '🎲',
 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/480px-Steam_icon_logo.svg.png',
 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/480px-Steam_icon_logo.svg.png',
 '#1e40af', '', 8);

-- ── PACKAGES ─────────────────────────────────────────────────
INSERT INTO `packages` (`product_id`,`amount`,`price`,`bonus`) VALUES
-- Mobile Legends Diamond
(1,   86,   13000,  0),
(1,  172,   25000,  0),
(1,  257,   37000,  0),
(1,  344,   50000,  0),
(1,  514,   73000, 14),
(1,  706,  100000, 21),
(1, 1412,  193000, 42),
(1, 2195,  300000, 65),
-- Free Fire Diamond
(2,   70,   11000,  0),
(2,  140,   21000,  0),
(2,  355,   50000,  0),
(2,  720,  100000,  0),
(2, 1450,  193000,  0),
(2, 2180,  285000,  0),
-- PUBG UC
(3,   60,   25000,  0),
(3,  300,   98000, 25),
(3,  600,  190000, 60),
(3, 1500,  455000,150),
(3, 3000,  900000,300),
-- Genshin Genesis Crystal
(4,   60,   13500,  0),
(4,  300,   65000, 30),
(4,  980,  200000,110),
(4, 1980,  395000,260),
(4, 3280,  645000,600),
(4, 6480, 1250000,1600),
-- CODM CP
(5,   80,   16500,  0),
(5,  400,   75000,  0),
(5,  800,  145000,  0),
(5, 2000,  345000,  0),
(5, 4000,  680000,  0),
-- Valorant VP
(6,  475,   20000,  0),
(6,  950,   39000,  0),
(6, 2050,   82000,100),
(6, 3650,  145000,150),
(6, 5350,  210000,350),
(6,11000,  420000,1000),
-- Honkai Star Rail
(7,   60,   14000,  0),
(7,  300,   68000, 30),
(7,  980,  210000,110),
(7, 1980,  410000,260),
-- Steam Wallet
(8,    5,   85000,  0),
(8,   10,  165000,  0),
(8,   20,  325000,  0),
(8,   50,  800000,  0),
(8,  100, 1580000,  0);

-- ── ADMIN USER ───────────────────────────────────────────────
-- Password: admin123 (gunakan db.php untuk generate hash baru)
INSERT INTO `users` (`name`,`email`,`password_hash`,`role`,`status`) VALUES
('Admin','admin@gamestore.id', '$2y$12$YKbVK8gZ8h2VZ1VZ2VZ3VeGHqK5ZvkH5KZhKZ2VZ1VZ2VZ3VeGH6', 'admin', 'active');
-- ⚠️  Hash di atas hanya placeholder. Lihat db.php untuk cara generate hash yang benar.

-- ── PROMO CODES ──────────────────────────────────────────────
INSERT INTO `promo_codes` (`code`,`description`,`type`,`value`,`max_discount`,`min_purchase`,`max_use`,`valid_until`) VALUES
('MLBB10',     'Diskon 10% Mobile Legends',       'percent', 10,  20000,  10000, 1000, DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('FF20',       'Diskon 20% Free Fire',             'percent', 20,  25000,  10000,  500, DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('GAMESTORE5', 'Diskon 5% semua produk',           'percent',  5,  10000,      0, NULL, DATE_ADD(CURDATE(), INTERVAL 90 DAY)),
('NEWMEMBER',  'Bonus member baru Rp 5.000',       'fixed',  5000,   NULL,  15000,  500, DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
('QRIS15',     'Diskon 15% bayar pakai QRIS',      'percent', 15,  30000,  20000,  300, DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
('WEEKEND',    'Promo weekend 8%',                 'percent',  8,  15000,  10000, NULL, DATE_ADD(CURDATE(), INTERVAL 7  DAY));

-- ── SETTINGS ─────────────────────────────────────────────────
INSERT INTO `settings` (`key`,`value`,`group`) VALUES
-- General
('site_name',        'GameStore',                           'general'),
('site_tagline',     'Top Up Game Terlengkap & Termurah',  'general'),
('site_url',         'https://yourdomain.com',             'general'),
('admin_email',      'admin@gamestore.id',                 'general'),
('wa_number',        '6281234567890',                      'general'),
('wa_number_display','0812-3456-7890',                     'general'),
('store_open',       '1',                                  'general'),
('store_hours',      '08:00-23:00',                        'general'),
('offline_message',  'Kami sedang offline. Kembali besok!','general'),
-- Payment accounts
('dana_number',      '081234567890',   'payment'),
('ovo_number',       '081234567890',   'payment'),
('gopay_number',     '081234567890',   'payment'),
('shopeepay_number', '081234567890',   'payment'),
('bca_number',       '1234567890',     'payment'),
('bca_name',         'PT GameStore Indonesia', 'payment'),
('mandiri_number',   '1234567890',     'payment'),
('mandiri_name',     'PT GameStore Indonesia', 'payment'),
('bri_number',       '1234567890',     'payment'),
('bri_name',         'PT GameStore Indonesia', 'payment'),
('bni_number',       '1234567890',     'payment'),
('bni_name',         'PT GameStore Indonesia', 'payment'),
-- Notifications
('notif_new_order',  '1', 'notif'),
('notif_new_chat',   '1', 'notif'),
('notif_payment',    '1', 'notif'),
-- Security
('login_max_attempt','5',  'security'),
('session_lifetime', '7200','security');

SET FOREIGN_KEY_CHECKS = 1;
