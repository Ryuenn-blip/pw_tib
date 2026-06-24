<?php
// $active_menu must be set before including this file
$menu = [
    ['icon'=>'📊','label'=>'Dashboard',  'href'=>'index.php',    'key'=>'dashboard'],
    ['icon'=>'📋','label'=>'Pesanan',    'href'=>'orders.php',   'key'=>'orders'],
    ['icon'=>'🎮','label'=>'Produk',     'href'=>'products.php', 'key'=>'products'],
    ['icon'=>'👥','label'=>'Pelanggan',  'href'=>'customers.php','key'=>'customers'],
    ['icon'=>'💬','label'=>'Pesan WA',   'href'=>'messages.php', 'key'=>'messages'],
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
            <div class="topbar-avatar" title="Admin">A</div>
        </div>
    </header>

    <!-- Page content goes below -->