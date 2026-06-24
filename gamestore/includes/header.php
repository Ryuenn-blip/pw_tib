<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — ' . SITE_NAME : SITE_NAME . ' — Top Up Game Terlengkap' ?></title>
    <meta name="description" content="Top up game terlengkap dan termurah. Mobile Legends, Free Fire, PUBG, Genshin Impact, Valorant dan ratusan game lainnya. Proses instan 24 jam.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="container nav-container">
        <a href="index.php" class="nav-logo">
            <span class="logo-icon">🎮</span>
            <span class="logo-text">Ryuen<span class="logo-accent">Store</span></span>
        </a>

        <div class="nav-search">
            <input type="text" id="searchInput" placeholder="Cari game, item, atau produk..." autocomplete="off">
            <span class="search-icon">🔍</span>
            <div class="search-dropdown" id="searchDropdown"></div>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="<?= $current_page === 'index' ? 'active' : '' ?>">Beranda</a></li>
            <li class="nav-dropdown">
                <a href="products.php" class="<?= $current_page === 'products' ? 'active' : '' ?>">Kategori <span>▾</span></a>
                <div class="dropdown-menu">
                    <a href="products.php?cat=Mobile">📱 Mobile Game</a>
                    <a href="products.php?cat=PC">💻 PC Game</a>
                    <a href="products.php">🎮 Semua Game</a>
                </div>
            </li>
            <li><a href="cara-order.php" class="<?= $current_page === 'cara-order' ? 'active' : '' ?>">Cara Order</a></li>
            <li><a href="contact.php" class="<?= $current_page === 'contact' ? 'active' : '' ?>">Chat Admin</a></li>
        </ul>

        <div class="nav-actions">
            <a href="cart.php" class="btn-cart">
                🛒 <span class="cart-badge" id="cartBadge">0</span>
            </a>
            <a href="login.php" class="btn-login">Login</a>
        </div>

        <button class="nav-hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
