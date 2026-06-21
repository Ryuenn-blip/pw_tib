<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$totalGame = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM games")
)['total'];

$totalProduk = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM products")
)['total'];

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Dashboard Admin</title>

<link rel="stylesheet"
href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<div class="sidebar">

```
<h2>🎮 GameStore</h2>

<ul>

    <li>
        <a href="dashboard.php" class="active">
            Dashboard
        </a>
    </li>

    <li>
        <a href="games.php">
            Kelola Game
        </a>
    </li>

    <li>
        <a href="products.php">
            Kelola Produk
        </a>
    </li>

    <li>
        <a href="transaksi.php">
            Transaksi
        </a>
    </li>

    <li>
        <a href="chat.php">
            Chat Customer
        </a>
    </li>

    <li>
        <a href="../auth/logout.php">
            Logout
        </a>
    </li>

</ul>
```

</div>

<div class="content">

<?php include "../includes/back_button.php"; ?>

<h1>Dashboard Admin</h1>

<p>
Selamat datang di panel GameStore.
</p>

<div class="stats">

```
<div class="card">

    <i class="fas fa-gamepad"></i>

    <h2>
        <?= $totalGame ?>
    </h2>

    <p>Total Game</p>

</div>

<div class="card">

    <i class="fas fa-box"></i>

    <h2>
        <?= $totalProduk ?>
    </h2>

    <p>Total Produk</p>

</div>

<div class="card">

    <i class="fas fa-money-bill"></i>

    <h2>
        Rp 0
    </h2>

    <p>Pendapatan</p>

</div>

<div class="card">

    <i class="fas fa-users"></i>

    <h2>
        0
    </h2>

    <p>Customer</p>

</div>
```

</div>

<div class="menu-grid">

```
<a href="games.php" class="menu-card">
    <i class="fas fa-gamepad"></i>
    <span>Kelola Game</span>
</a>

<a href="products.php" class="menu-card">
    <i class="fas fa-box"></i>
    <span>Kelola Produk</span>
</a>

<a href="transaksi.php" class="menu-card">
    <i class="fas fa-receipt"></i>
    <span>Transaksi</span>
</a>

<a href="chat.php" class="menu-card">
    <i class="fas fa-comments"></i>
    <span>Chat Admin</span>
</a>
```

</div>

</div>

</body>

</html>
