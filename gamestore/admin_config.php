<?php
session_start();

// ── Credentials (ganti sesuai kebutuhan) ──────────────────────
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', password_hash('admin123', PASSWORD_DEFAULT)); // password: admin123
define('SITE_NAME',  'GameStore');
define('WA_NUMBER',  '6281234567890');

// ── Guard: redirect ke login jika belum login ─────────────────
function requireLogin() {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

function formatRp($n) {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function formatNum($n) {
    if ($n >= 1000000) return round($n/1000000, 1) . 'jt';
    if ($n >= 1000)    return round($n/1000, 1)    . 'rb';
    return $n;
}

// ── Dummy Orders ──────────────────────────────────────────────
$statuses = ['pending','processing','completed','cancelled'];
$game_list = [
    ['name'=>'Mobile Legends','currency'=>'Diamond','icon'=>'⚔️'],
    ['name'=>'Free Fire',     'currency'=>'Diamond','icon'=>'🔥'],
    ['name'=>'PUBG Mobile',   'currency'=>'UC',     'icon'=>'🎯'],
    ['name'=>'Genshin Impact','currency'=>'Genesis Crystal','icon'=>'✨'],
    ['name'=>'Valorant',      'currency'=>'VP',     'icon'=>'🎮'],
    ['name'=>'CODM',          'currency'=>'CP',     'icon'=>'🎖️'],
];
$payments  = ['DANA','OVO','GoPay','ShopeePay','Transfer BCA','QRIS'];
$firstnames= ['Budi','Siti','Ahmad','Dewi','Rizki','Nurul','Eko','Rina','Hendra','Maya'];
$lastnames = ['Santoso','Rahayu','Kurniawan','Lestari','Pratama','Wulandari','Susanto','Hidayat'];

srand(42); // reproducible
$orders = [];
for ($i = 1; $i <= 60; $i++) {
    $game   = $game_list[array_rand($game_list)];
    $amount = [86,172,257,344,514,706,60,300,980,475,950][rand(0,10)];
    $price  = rand(1,5) * 10000 + rand(0,9) * 1000;
    $days   = rand(0, 29);
    $status = $statuses[rand(0,3)];
    if ($i <= 5) $status = 'pending';         // pastikan ada pending
    $orders[] = [
        'id'      => 'GS' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'name'    => $firstnames[array_rand($firstnames)] . ' ' . $lastnames[array_rand($lastnames)],
        'game'    => $game['name'],
        'icon'    => $game['icon'],
        'currency'=> $game['currency'],
        'amount'  => $amount,
        'price'   => $price,
        'payment' => $payments[array_rand($payments)],
        'user_id' => rand(100000000, 999999999),
        'status'  => $status,
        'date'    => date('Y-m-d H:i:s', strtotime("-$days days -" . rand(0,23) . " hours")),
    ];
}
usort($orders, fn($a,$b) => strtotime($b['date']) - strtotime($a['date']));

// ── Stats ─────────────────────────────────────────────────────
$total_revenue   = array_sum(array_column(array_filter($orders, fn($o)=>$o['status']==='completed'), 'price'));
$total_orders    = count($orders);
$pending_orders  = count(array_filter($orders, fn($o)=>$o['status']==='pending'));
$completed_orders= count(array_filter($orders, fn($o)=>$o['status']==='completed'));
$cancelled_orders= count(array_filter($orders, fn($o)=>$o['status']==='cancelled'));

// Revenue per day (last 7 days)
$revenue_chart = [];
for ($d = 6; $d >= 0; $d--) {
    $day = date('d/m', strtotime("-$d days"));
    $rev = 0;
    foreach ($orders as $o) {
        if ($o['status']==='completed' && date('d/m', strtotime($o['date']))===$day)
            $rev += $o['price'];
    }
    $revenue_chart[] = ['day'=>$day, 'rev'=>$rev];
}

// Top games
$game_revenue = [];
foreach ($orders as $o) {
    if ($o['status']==='completed') {
        $game_revenue[$o['game']] = ($game_revenue[$o['game']] ?? 0) + $o['price'];
    }
}
arsort($game_revenue);
$top_games = array_slice($game_revenue, 0, 5, true);

// Products list (for manage page)
$products = [
    ['id'=>1,'name'=>'Mobile Legends','category'=>'Mobile','currency'=>'Diamond','min_price'=>13000,'packages'=>8,'status'=>'active','icon'=>'⚔️'],
    ['id'=>2,'name'=>'Free Fire',     'category'=>'Mobile','currency'=>'Diamond','min_price'=>11000,'packages'=>6,'status'=>'active','icon'=>'🔥'],
    ['id'=>3,'name'=>'PUBG Mobile',   'category'=>'Mobile','currency'=>'UC',     'min_price'=>25000,'packages'=>5,'status'=>'active','icon'=>'🎯'],
    ['id'=>4,'name'=>'Genshin Impact','category'=>'Mobile','currency'=>'Genesis Crystal','min_price'=>13500,'packages'=>6,'status'=>'active','icon'=>'✨'],
    ['id'=>5,'name'=>'CODM',          'category'=>'Mobile','currency'=>'CP',     'min_price'=>16500,'packages'=>5,'status'=>'active','icon'=>'🎖️'],
    ['id'=>6,'name'=>'Valorant',      'category'=>'PC',    'currency'=>'VP',     'min_price'=>20000,'packages'=>6,'status'=>'active','icon'=>'🎮'],
    ['id'=>7,'name'=>'Honkai Star Rail','category'=>'Mobile','currency'=>'Oneiric Shard','min_price'=>14000,'packages'=>4,'status'=>'active','icon'=>'⭐'],
    ['id'=>8,'name'=>'Steam Wallet',  'category'=>'PC',    'currency'=>'USD',    'min_price'=>85000,'packages'=>5,'status'=>'inactive','icon'=>'🎲'],
];