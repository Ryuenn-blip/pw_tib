<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

if(isset($_POST['simpan'])){

$nama =
$_POST['nama_game'];

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
"INSERT INTO games(
nama_game,
gambar,
deskripsi
)
VALUES(
'$nama',
'$gambar',
'$deskripsi'
)"
);

header("Location: games.php");
}

?>

<form method="POST"
enctype="multipart/form-data">

<input type="text"
name="nama_game"
placeholder="Nama Game">

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