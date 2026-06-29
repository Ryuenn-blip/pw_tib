<?php
/**
 * GameStore Admin — admin_config.php (MySQL version)
 */
session_start();

require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/config_db.php';

define('WA_NUMBER', WHATSAPP_NUMBER);

// ── Auth ──────────────────────────────────────────────────────
function requireLogin(): void {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

function admin_verify(string $username, string $password): ?array {
    $admin = DB::row("SELECT * FROM admins WHERE username = ?", [$username]);
    if (!$admin) return null;
    if (!password_verify($password, $admin['password'])) return null;
    // Update last login
    DB::exec("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);
    return $admin;
}

function log_activity(string $action, string $target = '', string $detail = ''): void {
    $admin_id = $_SESSION['admin_id'] ?? null;
    DB::exec("INSERT INTO activity_logs (admin_id, action, target, detail, ip) VALUES (?,?,?,?,?)",
        [$admin_id, $action, $target, $detail, $_SERVER['REMOTE_ADDR'] ?? '']);
}

// ── Format helpers ────────────────────────────────────────────
function formatRp(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function formatNum($n): string {
    $n = (int)$n;
    if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'jt';
    if ($n >= 1_000)     return round($n / 1_000, 1)     . 'rb';
    return (string)$n;
}

// ── Dashboard stats dari DB ───────────────────────────────────
$stats = DB::row("SELECT * FROM v_order_stats") ?? [
    'total_orders' => 0, 'completed' => 0, 'pending' => 0,
    'processing'   => 0, 'cancelled' => 0, 'total_revenue' => 0,
];
$total_orders    = (int)($stats['total_orders']  ?? 0);
$completed_orders= (int)($stats['completed']     ?? 0);
$pending_orders  = (int)($stats['pending']       ?? 0);
$cancelled_orders= (int)($stats['cancelled']     ?? 0);
$total_revenue   = (int)($stats['total_revenue'] ?? 0);

// ── Recent orders (untuk dashboard & orders page) ─────────────
$orders = DB::rows("
    SELECT o.*, p.icon AS icon
    FROM   orders o
    JOIN   products p ON p.id = o.product_id
    ORDER  BY o.created_at DESC
    LIMIT  60
");

// Normalize field names agar kompatibel dengan views lama
foreach ($orders as &$o) {
    $o['game']     = $o['product_name'] ?? '';
    $o['name']     = $o['customer_name'] ?? '';
    $o['amount']   = $o['pkg_amount'] ?? 0;
    $o['currency'] = $o['currency'] ?? '';
    $o['payment']  = $o['payment_method'] ?? '';
    $o['user_id']  = $o['game_user_id'] ?? '';
    $o['date']     = $o['created_at'] ?? '';
    $o['price']    = (int)($o['price'] ?? 0);
}
unset($o);

// ── Revenue chart (7 hari) ─────────────────────────────────────
$revenue_chart = [];
for ($d = 6; $d >= 0; $d--) {
    $day  = date('Y-m-d', strtotime("-$d days"));
    $row  = DB::row("
        SELECT COALESCE(SUM(price),0) AS rev, COUNT(*) AS cnt
        FROM   orders
        WHERE  DATE(created_at) = ? AND status = 'completed'
    ", [$day]);
    $revenue_chart[] = [
        'day' => date('d/m', strtotime($day)),
        'rev' => (int)($row['rev'] ?? 0),
        'cnt' => (int)($row['cnt'] ?? 0),
    ];
}

// ── Top games ──────────────────────────────────────────────────
$top_games_rows = DB::rows("SELECT * FROM v_top_products LIMIT 5");
$top_games = [];
foreach ($top_games_rows as $r) {
    $top_games[$r['name']] = (int)$r['revenue'];
}

// ── Products list (untuk halaman products) ─────────────────────
$products = DB::rows("SELECT * FROM v_products ORDER BY sort_order, id");

// ── Game list (untuk dropdowns) ───────────────────────────────
$game_list = DB::rows("SELECT id, name, currency, icon FROM products WHERE status = 'active'");
