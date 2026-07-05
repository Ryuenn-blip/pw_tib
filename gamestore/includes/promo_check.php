<?php
require_once dirname(__DIR__) . '/includes/config.php';
header('Content-Type: application/json');

$code  = strtoupper(trim($_GET['code']  ?? ''));
$price = (int)($_GET['price'] ?? 0);

if (!$code || !$price) {
    echo json_encode(['valid'=>false,'msg'=>'Parameter tidak valid']);
    exit;
}

$promo = db_row("
    SELECT * FROM promo_codes
    WHERE code = ? AND is_active = 1
      AND (valid_until IS NULL OR valid_until >= CURDATE())
", [$code]);

if (!$promo) {
    echo json_encode(['valid'=>false,'msg'=>'Kode promo tidak valid atau sudah kedaluwarsa']);
    exit;
}
if ($promo['max_use'] > 0 && $promo['used_count'] >= $promo['max_use']) {
    echo json_encode(['valid'=>false,'msg'=>'Kuota kode promo sudah habis']);
    exit;
}
if ($price < (int)$promo['min_purchase']) {
    echo json_encode(['valid'=>false,'msg'=>'Minimal pembelian '.formatRupiah((int)$promo['min_purchase'])]);
    exit;
}

// Hitung diskon
if ($promo['type'] === 'percent') {
    $disc = (int)round($price * $promo['value'] / 100);
    if ($promo['max_discount']) $disc = min($disc, (int)$promo['max_discount']);
    $msg = "Diskon {$promo['value']}% berhasil!";
} else {
    $disc = (int)$promo['value'];
    $msg  = 'Diskon '.formatRupiah($disc).' berhasil!';
}
$disc = min($disc, $price);

echo json_encode(['valid'=>true,'discount'=>$disc,'msg'=>$msg]);
