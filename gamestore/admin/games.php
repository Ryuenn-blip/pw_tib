<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$games =
mysqli_query(
$conn,
"SELECT * FROM games ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>
<head>

<title>Kelola Game</title>

<link rel="stylesheet"
href="../assets/css/admin.css">

</head>

<body>

<div class="sidebar">

<h2>GameStore</h2>

</div>

<div class="content">

<h1>Kelola Game</h1>

<a href="game_add.php">
Tambah Game
</a>

<br><br>

<table border="1"
cellpadding="10">

<tr>

<th>ID</th>
<th>Game</th>
<th>Gambar</th>
<th>Aksi</th>

</tr>

<?php while($row=mysqli_fetch_assoc($games)): ?>

<tr>

<td>
<?= $row['id'] ?>
</td>

<td>
<?= $row['nama_game'] ?>
</td>

<td>

<img
src="../uploads/game/<?= $row['gambar'] ?>"
width="80">

</td>

<td>

<a href="game_edit.php?id=<?= $row['id'] ?>">
Edit
</a>

|

<a href="game_delete.php?id=<?= $row['id'] ?>">
Hapus
</a>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

</body>
</html>