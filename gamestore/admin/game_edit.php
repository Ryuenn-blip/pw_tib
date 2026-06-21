<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$id = (int)$_GET['id'];

$game = mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT * FROM games WHERE id='$id'"
)
);

if(isset($_POST['update']))
{

$nama =
mysqli_real_escape_string(
$conn,
$_POST['nama_game']
);

$deskripsi =
mysqli_real_escape_string(
$conn,
$_POST['deskripsi']
);

if($_FILES['gambar']['name'])
{

$gambar =
time()."_".$_FILES['gambar']['name'];

move_uploaded_file(
$_FILES['gambar']['tmp_name'],
"../uploads/game/".$gambar
);

mysqli_query(
$conn,
"UPDATE games SET

nama_game='$nama',
deskripsi='$deskripsi',
gambar='$gambar'

WHERE id='$id'
");

}
else
{

mysqli_query(
$conn,
"UPDATE games SET

nama_game='$nama',
deskripsi='$deskripsi'

WHERE id='$id'
");

}

header("Location: games.php");
exit;

}

?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Game</title>

<link rel="stylesheet"
href="../assets/css/admin.css">

</head>

<body>

<div class="content">
    <a
href="games.php"
class="back-btn">

← Kembali

</a>

<h1>Edit Game</h1>

<form
method="POST"
enctype="multipart/form-data">

<input
type="text"
name="nama_game"
value="<?= $game['nama_game']; ?>"
required>

<br><br>

<textarea
name="deskripsi"
rows="6"><?= $game['deskripsi']; ?></textarea>

<br><br>

<img
src="../uploads/game/<?= $game['gambar']; ?>"
width="150">

<br><br>

<input
type="file"
name="gambar">

<br><br>

<button
type="submit"
name="update">

Update Game

</button>

</form>

</div>

</body>
</html>