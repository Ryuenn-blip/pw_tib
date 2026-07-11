<?php
// Start session jika belum
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — ' . SITE_NAME : SITE_NAME . ' — Top Up Game Terlengkap' ?></title>
    <meta name="description" content="Top up game terlengkap dan termurah. Mobile Legends, Free Fire, PUBG, Genshin Impact, Valorant dan ratusan game lainnya. Proses instan 24 jam.">
    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="<?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GameStore' ?>">
    <meta property="og:title"       content="<?= isset($page_title) ? htmlspecialchars($page_title.' — '.(defined('SITE_NAME')?SITE_NAME:'GameStore')) : 'GameStore' ?>">
    <meta property="og:description" content="Top up game terlengkap dan termurah. Proses instan 24 jam.">
    <meta property="og:url"         content="<?= 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>">
    <!-- Twitter Card -->
    <meta name="twitter:card"       content="summary">
    <meta name="twitter:title"      content="<?= isset($page_title) ? htmlspecialchars($page_title) : 'GameStore' ?>">
    <meta name="robots"             content="index, follow">
    <link rel="canonical"           href="<?= 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/loading.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="container nav-container">
        <a href="index.php" class="nav-logo">
            <span class="logo-icon">🎮</span>
            <span class="logo-text">Game<span class="logo-accent">Store</span></span>
        </a>

        <div class="nav-search">
            <input type="text" id="searchInput" placeholder="Cari game..." autocomplete="off"
                   onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='search.php?q='+encodeURIComponent(this.value.trim())">
            <span class="search-icon" onclick="const v=document.getElementById('searchInput').value.trim();if(v)location.href='search.php?q='+encodeURIComponent(v)">🔍</span>
            <div class="search-dropdown" id="searchDropdown"></div>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="<?= $current_page === 'index' ? 'active' : '' ?>">Beranda</a></li>
            <li class="nav-dropdown">
                <a href="products.php" class="<?= $current_page === 'products' ? 'active' : '' ?>">Produk <span>▾</span></a>
                <div class="dropdown-menu">
                    <a href="products.php">🎮 Semua Game</a>
                    <?php
                    $nav_cats = $categories ?? ['Mobile','PC','Console'];
                    $cat_icons = ['Mobile'=>'📱','PC'=>'💻','Console'=>'🎮'];
                    foreach ($nav_cats as $c):
                        if ($c === 'Semua') continue;
                    ?>
                    <a href="products.php?cat=<?= urlencode($c) ?>"><?= $cat_icons[$c] ?? '🎮' ?> <?= htmlspecialchars($c) ?> Game</a>
                    <?php endforeach; ?>
                </div>
            </li>
            <li><a href="cara-order.php" class="<?= $current_page === 'cara-order' ? 'active' : '' ?>">Cara Order</a></li>
            <li><a href="contact.php" class="<?= $current_page === 'contact' ? 'active' : '' ?>">Chat Admin</a></li>
        </ul>

        <div class="nav-actions">
            <a href="cart.php" class="btn-cart">
                🛒 <span class="cart-badge" id="cartBadge">0</span>
            </a>
            <?php if (function_exists('user_logged_in') && user_logged_in()): ?>
            <a href="dashboard.php" class="btn-login" style="display:flex;align-items:center;gap:.4rem">
                <span style="width:24px;height:24px;border-radius:50%;
                    background:linear-gradient(135deg,var(--blue),var(--cyan));
                    display:flex;align-items:center;justify-content:center;
                    font-size:.7rem;font-weight:900;flex-shrink:0">
                    <?= strtoupper($_SESSION['user_name'][0]) ?>
                </span>
                <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
            </a>
            <?php else: ?>
            <a href="login.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>

        <button class="nav-hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
