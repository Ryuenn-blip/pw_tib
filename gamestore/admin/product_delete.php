<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$id = (int)$_GET['id'];

$product = mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT * FROM products
WHERE id='$id'"
)
);

if($product)
{

$file =
"../uploads/product/".$product['gambar'];

if(file_exists($file))
{
unlink($file);
}

mysqli_query(
$conn,
"DELETE FROM products
WHERE id='$id'"
);

}

header("Location: products.php");
exit;