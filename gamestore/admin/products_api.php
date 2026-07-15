<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403); echo json_encode(['error'=>'Unauthorized']); exit;
}
require_once dirname(__DIR__) . '/includes/db.php';
require_once 'includes/products_engine.php';

function resp($d,$c=200){ http_response_code($c); echo json_encode($d,JSON_UNESCAPED_UNICODE); exit; }
function err($m,$c=400) { resp(['error'=>$m],$c); }

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'list':
        resp(['products' => product_get_all()]);

    case 'get':
        $p = product_get((int)($_GET['id'] ?? 0));
        if (!$p) err('Produk tidak ditemukan', 404);
        resp(['product' => $p]);

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('POST required');
        $name = trim($_POST['name'] ?? '');
        if (!$name) err('Nama produk wajib diisi');
        $id = product_create($_POST);
        log_activity('product_create', "Produk baru: $name (ID: $id)");
        resp(['success'=>true, 'id'=>$id, 'message'=>"Produk \"$name\" berhasil ditambahkan!"]);

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('POST required');
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if (!$id || !$name) err('Parameter tidak valid');
        product_update($id, $_POST);
        log_activity('product_update', "Produk diperbarui: $name (ID: $id)");
        resp(['success'=>true, 'message'=>'Produk berhasil diperbarui!']);

    case 'toggle':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('POST required');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) err('ID tidak valid');
        $s = product_toggle_status($id);
        $p = product_get($id);
        log_activity('product_toggle', "Status produk '{$p['name']}' (ID:$id) → $s");
        resp(['success'=>true, 'status'=>$s, 'message'=>"Status diubah ke: $s"]);

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('POST required');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) err('ID tidak valid');
        $p = product_get($id);
        product_delete($id);
        log_activity('product_delete', "Produk dihapus: " . ($p['name'] ?? $id));
        resp(['success'=>true, 'message'=>'Produk berhasil dihapus!']);

    case 'add_package':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('POST required');
        $pid = (int)($_POST['product_id'] ?? 0);
        $amt = (int)($_POST['amount']     ?? 0);
        $prc = (int)($_POST['price']      ?? 0);
        $bon = (int)($_POST['bonus']      ?? 0);
        if (!$pid || !$amt || $prc < 100) err('Parameter tidak valid');
        package_add($pid, $amt, $prc, $bon);
        $p = product_get($pid);
        log_activity('package_add', "Paket {$amt} ditambahkan ke produk: " . ($p['name'] ?? $pid));
        resp(['success'=>true, 'message'=>'Paket berhasil ditambahkan!']);

    case 'update_package':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('POST required');
        $pid   = (int)($_POST['product_id'] ?? 0);
        $pkgid = (int)($_POST['pkg_id']     ?? 0);
        $amt   = (int)($_POST['amount']     ?? 0);
        $prc   = (int)($_POST['price']      ?? 0);
        $bon   = (int)($_POST['bonus']      ?? 0);
        if (!$pid || !$pkgid || !$amt || !$prc) err('Parameter tidak valid');
        package_update($pid, $pkgid, $amt, $prc, $bon);
        log_activity('package_update', "Paket ID:$pkgid di produk ID:$pid diperbarui → {$amt} item @ " . number_format($prc,0,',','.'));
        resp(['success'=>true, 'message'=>'Paket berhasil diperbarui!']);

    case 'delete_package':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('POST required');
        $pkgid = (int)($_POST['pkg_id'] ?? 0);
        if (!$pkgid) err('pkg_id tidak valid');
        package_delete($pkgid);
        log_activity('package_delete', "Paket ID:$pkgid dihapus");
        resp(['success'=>true, 'message'=>'Paket berhasil dihapus!']);

    default:
        err('Action tidak dikenal');
}
