<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$games = mysqli_query(
$conn,
"SELECT * FROM games"
);

if(isset($_POST['simpan'])){

$game_id =
$_POST['game_id'];

$nama_produk =
$_POST['nama_produk'];

$harga =
$_POST['harga'];

$deskripsi =
$_POST['deskripsi'];

$gambar =
$_FILES['gambar']['name'];

move_uploaded_file(
$_FILES['gambar']['tmp_name'],
"../uploads/game/".$gambar
);

mysqli_query(
$conn,
"INSERT INTO products(
game_id,
nama_produk,
harga,
gambar,
deskripsi
)
VALUES(
'$game_id',
'$nama_produk',
'$harga',
'$gambar',
'$deskripsi'
)"
);

header("Location: products.php");

}

?>

<form method="POST"
enctype="multipart/form-data">

<select name="game_id">

<?php while($g=mysqli_fetch_assoc($games)): ?>

<option
value="<?= $g['id'] ?>">

<?= $g['nama_game'] ?>

</option>

<?php endwhile; ?>

</select>

<br><br>

<input type="text"
name="nama_produk"
placeholder="Nama Produk">

<br><br>

<input type="number"
name="harga"
placeholder="Harga">

<br><br>

<textarea
name="deskripsi">
</textarea>

<br><br>

<input type="file"
name="gambar">

<br><br>

<button name="simpan">
Simpan
</button>

</form>