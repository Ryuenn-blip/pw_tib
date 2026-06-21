<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$query = mysqli_query(
    $conn,
    "SELECT 
        products.*,
        games.nama_game
    FROM products
    JOIN games
    ON products.game_id = games.id
    ORDER BY products.id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>
Kelola Produk
</title>

<link rel="stylesheet"
href="../assets/css/admin.css">

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

<h2>
GameStore
</h2>


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
<a href="product.php">
Kelola Produk
</a>
</li>


<li>
<a href="transaksi.php">
Kelola Transaksi
</a>
</li>


</ul>

</div>



<!-- CONTENT -->

<div class="content">


<h1>
Kelola Produk
</h1>


<a 
href="product_add.php"
class="btn">

+ Tambah Produk

</a>


<br><br>


<table>


<tr>

<th>ID</th>

<th>Game</th>

<th>Gambar</th>

<th>Produk</th>

<th>Harga</th>

<th>Stok</th>

<th>Aksi</th>

</tr>



<?php while($row = mysqli_fetch_assoc($query)): ?>


<tr>


<td>
<?= $row['id']; ?>
</td>


<td>
<?= htmlspecialchars($row['nama_game']); ?>
</td>


<td>

<?php if($row['gambar']): ?>

<img
src="../uploads/product/<?= htmlspecialchars($row['gambar']); ?>"
width="70"
height="70"
style="object-fit:cover; border-radius:10px;">

<?php else: ?>

Tidak ada gambar

<?php endif; ?>

</td>


<td>
<?= htmlspecialchars($row['nama_produk']); ?>
</td>


<td>
Rp <?= number_format($row['harga']); ?>
</td>


<td>
<?= $row['stok']; ?>
</td>


<td>


<a 
href="product_edit.php?id=<?= $row['id']; ?>">

Edit

</a>


|


<a 
href="product_delete.php?id=<?= $row['id']; ?>"
onclick="return confirm('Yakin ingin menghapus produk ini?')">

Hapus

</a>


</td>


</tr>


<?php endwhile; ?>


</table>


</div>


</body>

</html>