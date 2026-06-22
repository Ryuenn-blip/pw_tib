<?php
require_once "config/config.php";

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>GameStore - Top Up Game Murah</title>

<link rel="stylesheet" href="assets/css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>


<body>


<!-- NAVBAR -->

<nav class="navbar">

<div class="logo">
<i class="fas fa-gamepad"></i>
GameStore
</div>


<div class="search">

<input 
type="text"
placeholder="Cari game favoritmu...">

</div>


<ul>

<li>
<a href="#">Beranda</a>
</li>


<li>
<a href="#games">Produk</a>
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

Halo,
<?= htmlspecialchars($_SESSION['user']['nama']); ?>

</span>


<a href="auth/logout.php" class="login-btn">

Logout

</a>


</div>


<?php else: ?>


<a href="auth/login.php" class="login-btn">

Login

</a>


<?php endif; ?>


</nav>


<!-- HERO -->

<section class="hero">


<!-- KIRI -->

<div class="hero-left">


<h1>

Top Up Game & Item

<br>

<span>
Terlengkap & Termurah
</span>

</h1>


<p>

Dapatkan diamond, UC, Genesis Crystal,
dan berbagai item game favorit dengan
harga murah, proses instan, dan aman.

</p>



<div class="hero-stats">


<div class="stat-box">

<h3>100K+</h3>

<span>Transaksi</span>

</div>


<div class="stat-box">

<h3>24/7</h3>

<span>Support</span>

</div>


<div class="stat-box">

<h3>100%</h3>

<span>Aman</span>

</div>


</div>



<a href="#games" class="btn-primary">

Top Up Sekarang

</a>


</div>


<!-- KANAN -->

<div class="hero-right">


<img 
src="assets/images/ultra.png"
alt="Game Character">


</div>


</section>



<!-- KEUNGGULAN -->

<section class="features">


<div class="feature-card">

<i class="fas fa-shield-halved"></i>

<h3>
100% Aman
</h3>

<p>
Transaksi aman dan terpercaya.
</p>

</div>



<div class="feature-card">


<i class="fas fa-bolt"></i>


<h3>
Proses Cepat
</h3>


<p>
Pesanan selesai hanya dalam hitungan menit.
</p>


</div>



<div class="feature-card">


<i class="fas fa-headset"></i>


<h3>
Support 24 Jam
</h3>


<p>
Admin selalu siap membantu kapan saja.
</p>


</div>



<div class="feature-card">


<i class="fas fa-tags"></i>


<h3>
Harga Murah
</h3>


<p>
Harga terbaik dengan promo menarik.
</p>


</div>


</section>




<!-- PRODUK -->

<section class="section-title">


<h2>

🔥 Produk Populer

</h2>


<p>

Pilih game favoritmu dan lakukan top up dengan mudah.

</p>


</section>


<section id="games" class="products-grid">


<?php


$games = mysqli_query(
$conn,
"SELECT * FROM games ORDER BY id DESC"
);


if(mysqli_num_rows($games) > 0)
{


while($row = mysqli_fetch_assoc($games))
{

include "components/product_card.php";

}


}
else
{


echo "
<h3 class='empty'>
Produk belum tersedia
</h3>
";


}
$products = mysqli_query(
$conn,
"SELECT
p.id,
p.nama_produk,
p.harga,
p.gambar,
g.nama_game
FROM products p
JOIN games g
ON p.game_id = g.id
ORDER BY p.id DESC
LIMIT 6"
);

while($row = mysqli_fetch_assoc($products))
{
    include "components/product_card.php";
}


?>


</section>




<!-- FOOTER -->


<footer>


<h2>

<i class="fas fa-gamepad"></i>

GameStore

</h2>


<p>

Platform top up game terpercaya,
cepat, murah, dan aman untuk semua gamer.

</p>


<p>

© 2026 GameStore. All Rights Reserved.

</p>


</footer>




<!-- FLOAT CHAT -->


<a href="#" class="floating-chat">

<i class="fas fa-comment"></i>

</a>



</body>

</html>