<?php
require_once "config/config.php";
?>

<!DOCTYPE html>
<html>

<head>

<title>GameStore</title>

<link rel="stylesheet"
href="assets/css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<nav class="navbar">

<div class="logo">
<i class="fas fa-gamepad"></i>
GameStore
</div>

<div class="search">
<input
type="text"
placeholder="Cari game, item, atau produk">
</div>

<ul>

<li>
<a href="#">Beranda</a>
</li>

<li>
<a href="#">Kategori</a>
</li>

<li>
<a href="#">Cara Order</a>
</li>

<li>
<a href="#">Chat Admin</a>
</li>

</ul>

<?php if(isset($_SESSION['user'])): ?>

<div class="user-menu">

<span class="user-name">
Halo, <?= $_SESSION['user']['nama']; ?>
</span>

<a href="auth/logout.php"
class="login-btn">
Logout
</a>

</div>

<?php else: ?>

<a href="auth/login.php"
class="login-btn">
Login
</a>

<?php endif; ?>

</nav>

<section class="hero">

<div class="hero-left">

<h1>
Top Up Game & Item
<br>

<span>
Terlengkap & Termurah
</span>

</h1>

<p>
Dapatkan berbagai item game favorit
dengan harga terbaik dan proses cepat.
</p>

<a href="#games"
class="btn-primary">

Lihat Semua Produk

</a>

</div>

</section>

<section
id="games"
class="products-grid">

<?php

$games = mysqli_query(
$conn,
"SELECT * FROM games ORDER BY id DESC"
);

while(
$row = mysqli_fetch_assoc($games)
){

include "components/product_card.php";

}

?>

</section>

</body>
</html>