<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$id = (int)$_GET['id'];

$game = mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT * FROM games
WHERE id='$id'"
)
);

if($game)
{

$file =
"../uploads/game/".$game['gambar'];

if(file_exists($file))
{
unlink($file);
}

mysqli_query(
$conn,
"DELETE FROM games
WHERE id='$id'"
);

}

header("Location: games.php");
exit;