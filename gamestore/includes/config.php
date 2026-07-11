<?php
/**
 * GameStore — Main Config
 * Sumber data: MySQL via PDO (db.php)
 */

// ── Environment settings ──────────────────────────────────────
// Set ke false di production, true saat development
define('APP_DEBUG', false);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Output buffering - mencegah "headers already sent" error
if (!ob_get_level()) ob_start();

// ── Security headers ──────────────────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Load database
require_once __DIR__ . '/db.php';

// ── Konstanta global ──────────────────────────────────────────
try {
    define('SITE_NAME',       get_setting('site_name',    'GameStore'));
    define('SITE_TAGLINE',    get_setting('site_tagline', 'Top Up Game Terlengkap & Termurah'));
    define('WHATSAPP_NUMBER', get_setting('wa_number',    '6281234567890'));
} catch (\Throwable $e) {
    if (!defined('SITE_NAME'))       define('SITE_NAME',       'GameStore');
    if (!defined('SITE_TAGLINE'))    define('SITE_TAGLINE',    'Top Up Game Terlengkap & Termurah');
    if (!defined('WHATSAPP_NUMBER')) define('WHATSAPP_NUMBER', '6281234567890');
}

// ── Format Rupiah ─────────────────────────────────────────────
function formatRupiah($number): string {
    return 'Rp ' . number_format((int)($number ?? 0), 0, ',', '.');
}

// ── Load produk dari DB (dengan fallback jika DB belum terhubung) ──
try {
    $games = get_active_products();
} catch (\Throwable $e) {
    $games = []; // Fallback kosong jika DB error
}

// Normalisasi agar kompatibel dengan template lama
// (template pakai $game['category'], bukan category_name)
foreach ($games as &$g) {
    $g['category']  = $g['category_name'] ?? $g['category'] ?? '';
    // packages diload on-demand di detail.php
    if (!isset($g['packages'])) $g['packages'] = [];
}
unset($g);

// ── Kategori dinamis ──────────────────────────────────────────
$_cats      = array_unique(array_column($games, 'category'));
sort($_cats);
$categories = array_merge(['Semua'], $_cats);

// ── Testimoni (statis) ────────────────────────────────────────
$testimonials = [
    ['name'=>'Budi Santoso',  'game'=>'Mobile Legends','rating'=>5,'avatar'=>'B',
     'text'=>'Proses top up super cepat, kurang dari 1 menit diamond sudah masuk!'],
    ['name'=>'Siti Rahayu',   'game'=>'Free Fire',     'rating'=>5,'avatar'=>'S',
     'text'=>'Harga paling murah, udah langganan disini dari 2022. Mantap!'],
    ['name'=>'Ahmad Rizki',   'game'=>'PUBG Mobile',   'rating'=>5,'avatar'=>'A',
     'text'=>'Awalnya ragu tapi ternyata aman banget. CS juga ramah!'],
    ['name'=>'Dewi Lestari',  'game'=>'Genshin Impact','rating'=>5,'avatar'=>'D',
     'text'=>'Top up genesis crystal langsung masuk, pelayanan 24 jam. Suka!'],
];
