<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$query = mysqli_query($conn,"
SELECT products.*,
games.nama_game
FROM products
JOIN games
ON products.game_id=games.id
ORDER BY products.id DESC
");

?>

<!DOCTYPE html>
<html>
<head>

<title>Produk</title>

<link rel="stylesheet"
href="../assets/css/admin.css">

</head>

<body>

<div class="sidebar">

<h2>GameStore</h2>

<ul>

<li>
<a href="dashboard.php">
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
<a href="#">
Transaksi
</a>
</li>

<li>
<a href="../auth/logout.php">
Logout
</a>
</li>

</ul>

</div>

<div class="content">

<h1>Kelola Produk</h1>

<a href="product_add.php"
class="btn">
Tambah Produk
</a>

<br><br>

<table>

<tr>

<th>ID</th>
<th>Game</th>
<th>Produk</th>
<th>Harga</th>
<th>Aksi</th>

</tr>

<?php while($row=mysqli_fetch_assoc($query)): ?>

<tr>

<td><?= $row['id'] ?></td>

<td><?= $row['nama_game'] ?></td>

<td><?= $row['nama_produk'] ?></td>

<td>
Rp <?= number_format($row['harga']) ?>
</td>

<td>

<a href="product_edit.php?id=<?= $row['id'] ?>">
Edit
</a>

|

<a href="product_delete.php?id=<?= $row['id'] ?>">
Hapus
</a>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

</body>
</html>