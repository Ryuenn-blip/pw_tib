<?php
// $active_menu must be set before including this file
// Load chat unread count
require_once dirname(__DIR__) . '/chat/chat_engine.php';
$chat_unread_count = chat_total_unread_admin();

$menu = [
    ['icon'=>'📊','label'=>'Dashboard',  'href'=>'index.php',    'key'=>'dashboard'],
    ['icon'=>'📋','label'=>'Pesanan',    'href'=>'orders.php',   'key'=>'orders'],
    ['icon'=>'🎮','label'=>'Produk',     'href'=>'products.php', 'key'=>'products'],
    ['icon'=>'👥','label'=>'Pelanggan',  'href'=>'customers.php','key'=>'customers'],
    ['icon'=>'💬','label'=>'Live Chat',  'href'=>'chat.php',     'key'=>'chat'],
    ['icon'=>'📈','label'=>'Laporan',    'href'=>'reports.php',  'key'=>'reports'],
    ['icon'=>'⚙️','label'=>'Pengaturan', 'href'=>'settings.php', 'key'=>'settings'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin' ?> — <?= SITE_NAME ?> Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
    /* Admin page-in animation */
    .page-content { animation: adminPageIn .35s ease both; }
    @keyframes adminPageIn {
        from { opacity:0; transform:translateY(10px); }
        to   { opacity:1; transform:translateY(0); }
    }
    /* Stat cards stagger */
    .stats-grid .stat-card:nth-child(1) { animation: adminPageIn .4s .05s ease both; }
    .stats-grid .stat-card:nth-child(2) { animation: adminPageIn .4s .10s ease both; }
    .stats-grid .stat-card:nth-child(3) { animation: adminPageIn .4s .15s ease both; }
    .stats-grid .stat-card:nth-child(4) { animation: adminPageIn .4s .20s ease both; }
    /* Top progress bar for admin */
    #gs-progress { top: 0; }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-icon">🎮</span>
            <span class="logo-text">Game<span class="logo-accent">Store</span></span>
        </div>
        <button class="sidebar-close" id="sidebarClose">✕</button>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($menu as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="nav-item <?= ($active_menu ?? '') === $item['key'] ? 'active' : '' ?>">
            <span class="nav-icon"><?= $item['icon'] ?></span>
            <span class="nav-label"><?= $item['label'] ?></span>
            <?php if ($item['key'] === 'orders' && $pending_orders > 0): ?>
            <span class="nav-badge"><?= $pending_orders ?></span>
            <?php elseif ($item['key'] === 'chat' && $chat_unread_count > 0): ?>
            <span class="nav-badge"><?= $chat_unread_count ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../index.php" target="_blank" class="nav-item">
            <span class="nav-icon">🌐</span>
            <span class="nav-label">Lihat Website</span>
        </a>
        <a href="logout.php" class="nav-item nav-logout">
            <span class="nav-icon">🚪</span>
            <span class="nav-label">Logout</span>
        </a>
    </div>
</aside>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main wrapper -->
<div class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" id="sidebarToggle">☰</button>
            <div class="topbar-title"><?= $page_title ?? 'Dashboard' ?></div>
        </div>
        <div class="topbar-right">
            <div class="topbar-date"><?= date('l, d F Y') ?></div>
            <div class="topbar-notif" title="Pesanan Pending">
                🔔 <span class="notif-count"><?= $pending_orders ?></span>
            </div>
            <?php if ($chat_unread_count > 0): ?>
            <a href="chat.php" class="topbar-notif" title="Pesan chat belum dibaca" style="text-decoration:none">
                💬 <span class="notif-count" style="background:var(--blue)"><?= $chat_unread_count ?></span>
            </a>
            <?php endif; ?>
            <div class="topbar-avatar" title="Admin">A</div>
        </div>
    </header>

    <!-- Page content goes below -->
