<?php
session_start();

require_once dirname(__DIR__, 2) . '/includes/db.php';

define('ADMIN_USER', 'admin');
define('SITE_NAME',  get_setting('site_name', 'GameStore'));
define('WA_NUMBER',  get_setting('wa_number', '6281234567890'));

function requireLogin() {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: login.php'); exit;
    }
}

function formatRp(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function formatNum(int $n): string {
    if ($n >= 1000000) return round($n/1000000, 1) . 'jt';
    if ($n >= 1000)    return round($n/1000, 1)    . 'rb';
    return (string)$n;
}

// ── Load orders dari DB ────────────────────────────────────────
function get_orders(string $status = '', int $limit = 200): array {
    $where  = $status ? "WHERE o.status = :status" : "";
    $params = $status ? [':status' => $status]     : [];
    return db_rows("
        SELECT o.*, p.name AS product_name, p.icon AS product_icon, p.slug AS product_slug
        FROM   orders o
        JOIN   products p ON p.id = o.product_id
        $where
        ORDER BY o.created_at DESC
        LIMIT  $limit
    ", $params);
}

// ── Summary stats ──────────────────────────────────────────────
$orders_raw         = get_orders();
$orders             = $orders_raw;
$total_orders       = count($orders);
$pending_orders     = count(array_filter($orders, fn($o)=>$o['status']==='pending'));
$completed_orders   = count(array_filter($orders, fn($o)=>$o['status']==='completed'));
$cancelled_orders   = count(array_filter($orders, fn($o)=>$o['status']==='cancelled'));
$total_revenue      = array_sum(array_column(array_filter($orders, fn($o)=>$o['status']==='completed'), 'price'));

// ── Load chat unread dari DB (atau file engine fallback) ───────
function chat_total_unread_admin_db(): int {
    try {
        return (int)(db_row("SELECT COALESCE(SUM(unread_admin),0) AS total FROM chat_sessions WHERE status != 'resolved'")['total'] ?? 0);
    } catch (Exception $e) { return 0; }
}
$chat_unread_count = chat_total_unread_admin_db();

// Products untuk admin
$db_products = db_rows("
    SELECT p.*, c.name AS category_name,
           MIN(pk.price) AS min_price,
           COUNT(pk.id)  AS packages
    FROM   products p
    JOIN   categories c ON c.id = p.category_id
    LEFT JOIN packages pk ON pk.product_id = p.id
    GROUP BY p.id
    ORDER BY p.sort_order, p.id
");

// Revenue chart 7 hari
$revenue_chart = [];
for ($d = 6; $d >= 0; $d--) {
    $day    = date('Y-m-d', strtotime("-$d days"));
    $result = db_row("SELECT COALESCE(SUM(price),0) AS rev, COUNT(*) AS cnt
                      FROM orders WHERE DATE(created_at)=? AND status='completed'", [$day]);
    $revenue_chart[] = ['day' => date('d/m', strtotime($day)), 'rev' => (int)($result['rev'] ?? 0), 'cnt' => (int)($result['cnt'] ?? 0)];
}

// Top games
$top_games_raw = db_rows("
    SELECT p.name, p.icon, COALESCE(SUM(o.price),0) AS revenue
    FROM   products p
    LEFT JOIN orders o ON o.product_id = p.id AND o.status = 'completed'
    GROUP BY p.id
    ORDER BY revenue DESC
    LIMIT 5
");
$top_games = [];
foreach ($top_games_raw as $tg) $top_games[$tg['name']] = (int)$tg['revenue'];
