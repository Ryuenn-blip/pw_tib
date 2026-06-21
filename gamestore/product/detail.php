<?php

require_once "../config/config.php";

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id = (int) $_GET['id'];

// Ambil data game yang aktif
$gameQuery = mysqli_query(
    $conn,
    "SELECT * FROM games 
     WHERE id = $id 
     AND status = 1"
);

$game = mysqli_fetch_assoc($gameQuery);

if (!$game) {
    die("Game tidak ditemukan atau tidak tersedia.");
}

// Ambil produk yang tersedia
$products = mysqli_query(
    $conn,
    "SELECT * FROM products 
     WHERE game_id = $id 
     AND stok > 0
     ORDER BY harga ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
<?= htmlspecialchars($game['nama_game']); ?> - GameStore
</title>

<link rel="stylesheet"
href="../assets/css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<nav class="navbar">

<div class="logo">
    <i class="fas fa-gamepad"></i>
    GameStore
</div>

<ul>

<li>
    <a href="../index.php">
        Beranda
    </a>
</li>

<li>
    <a href="#">
        Kategori
    </a>
</li>

<li>
    <a href="#">
        Cara Order
    </a>
</li>

<li>
    <a href="#">
        Chat Admin
    </a>
</li>

</ul>


<?php if (isset($_SESSION['user'])): ?>

<div class="user-menu">

<span class="user-name">

Halo,
<?= htmlspecialchars($_SESSION['user']['nama']); ?>

</span>

<a href="../auth/logout.php"
class="login-btn">

Logout

</a>

</div>


<?php else: ?>

<a href="../auth/login.php"
class="login-btn">

Login

</a>

<?php endif; ?>


</nav>


<div class="detail-container">


<a href="javascript:history.back()"
class="back-btn">

← Kembali

</a>


<div class="game-banner">


<img
src="../uploads/game/<?= htmlspecialchars($game['gambar']); ?>"
alt="<?= htmlspecialchars($game['nama_game']); ?>"
loading="lazy">


<div class="overlay">

<h1>

<?= htmlspecialchars($game['nama_game']); ?>

</h1>


<p>

<?= htmlspecialchars($game['deskripsi']); ?>

</p>


</div>

</div>


<form action="checkout.php"
method="POST">


<input
type="hidden"
name="game_id"
value="<?= $game['id']; ?>">


<div class="form-box">

<h2>
1. Masukkan Data Akun
</h2>


<input
type="text"
name="user_id"
placeholder="Masukkan User ID"
required>


<input
type="text"
name="server_id"
placeholder="Masukkan Server ID (Opsional)">


</div>


<div class="form-box">


<h2>
2. Pilih Nominal Top Up
</h2>


<div class="nominal-grid">


<?php if (mysqli_num_rows($products) > 0): ?>


<?php while ($p = mysqli_fetch_assoc($products)): ?>


<label class="nominal-card">


<input
type="radio"
name="product_id"
value="<?= $p['id']; ?>"
required>


<div>


<h3>

<?= htmlspecialchars($p['nama_produk']); ?>

</h3>


<p>

Rp <?= number_format($p['harga']); ?>

</p>


</div>


</label>


<?php endwhile; ?>


<?php else: ?>


<p class="empty">

Nominal top up belum tersedia.

</p>


<?php endif; ?>


</div>


</div>


<div class="form-box">


<h2>
3. Checkout
</h2>


<?php if (isset($_SESSION['user'])): ?>


<button
type="submit"
class="buy-btn">

Beli Sekarang

</button>


<?php else: ?>


<a href="../auth/login.php"
class="buy-btn">

Login Untuk Melanjutkan

</a>


<?php endif; ?>


</div>


</form>


</div>


</body>

</html>