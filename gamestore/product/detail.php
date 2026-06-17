<?php

require_once "../config/config.php";

$id = (int)$_GET['id'];

$game = mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT * FROM games WHERE id='$id'"
)
);

$products = mysqli_query(
$conn,
"SELECT * FROM products
WHERE game_id='$id'
ORDER BY harga ASC"
);

?>

<!DOCTYPE html>
<html>

<head>

<title>
<?= $game['nama_game']; ?>
</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="detail-container">

<div class="game-banner">

<img
src="../uploads/game/<?= $game['gambar']; ?>"
alt="">

<div class="overlay">

<h1>
<?= $game['nama_game']; ?>
</h1>

<p>
<?= $game['deskripsi']; ?>
</p>

</div>

</div>

<form
action="checkout.php"
method="POST">

<input
type="hidden"
name="game_id"
value="<?= $game['id']; ?>">

<div class="form-box">

<h2>Masukkan Data Akun</h2>

<input
type="text"
name="user_id"
placeholder="User ID"
required>

<input
type="text"
name="server_id"
placeholder="Server ID">

</div>

<div class="form-box">

<h2>Pilih Nominal</h2>

<div class="nominal-grid">

<?php while($p=mysqli_fetch_assoc($products)): ?>

<label class="nominal-card">

<input
type="radio"
name="product_id"
value="<?= $p['id']; ?>"
required>

<div>

<h3>
<?= $p['nama_produk']; ?>
</h3>

<p>
Rp <?= number_format($p['harga']); ?>
</p>

</div>

</label>

<?php endwhile; ?>

</div>

</div>

<div class="form-box">

<?php if(isset($_SESSION['user'])): ?>

<button
type="submit"
class="buy-btn">

Beli Sekarang

</button>

<?php else: ?>

<a
href="../auth/login.php"
class="buy-btn">

Login Untuk Melanjutkan

</a>

<?php endif; ?>

</div>

</form>

</div>

</body>
</html>