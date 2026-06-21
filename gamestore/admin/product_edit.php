<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

if(!isset($_GET['id']))
{
    header("Location: product.php");
    exit;
}

$id = (int) $_GET['id'];


/*
|----------------------------------
| Ambil data produk
|----------------------------------
*/

$product = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM products WHERE id='$id'"
    )
);


if(!$product)
{
    die("Produk tidak ditemukan!");
}


/*
|----------------------------------
| Ambil daftar game
|----------------------------------
*/

$games = mysqli_query(
    $conn,
    "SELECT * FROM games ORDER BY nama_game ASC"
);



/*
|----------------------------------
| Proses update
|----------------------------------
*/

if(isset($_POST['update']))
{

    $game_id = (int) $_POST['game_id'];

    $nama_produk = mysqli_real_escape_string(
        $conn,
        $_POST['nama_produk']
    );

    $harga = (int) $_POST['harga'];

    $stok = (int) $_POST['stok'];


    $gambar = $product['gambar'];


    // Cek jika upload gambar baru
    if(isset($_FILES['gambar']) &&
       $_FILES['gambar']['error'] == 0)
    {

        $allowed = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


        $ext = strtolower(
            pathinfo(
                $_FILES['gambar']['name'],
                PATHINFO_EXTENSION
            )
        );


        if(!in_array($ext, $allowed))
        {
            die("Format gambar tidak didukung!");
        }


        $gambarBaru = time()
            . "_" .
            uniqid()
            . "." .
            $ext;


        move_uploaded_file(
            $_FILES['gambar']['tmp_name'],
            "../uploads/product/" . $gambarBaru
        );


        // Hapus gambar lama
        if(
            !empty($product['gambar']) &&
            file_exists(
                "../uploads/product/" .
                $product['gambar']
            )
        )
        {
            unlink(
                "../uploads/product/" .
                $product['gambar']
            );
        }


        $gambar = $gambarBaru;
    }



    mysqli_query(
        $conn,
        "UPDATE products SET

        game_id='$game_id',

        nama_produk='$nama_produk',

        harga='$harga',

        stok='$stok',

        gambar='$gambar'

        WHERE id='$id'"
    );


    header("Location: product.php");
    exit;

}

?>


<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>
Edit Produk
</title>


<link rel="stylesheet"
href="../assets/css/admin.css">


</head>


<body>


<div class="content">


<a href="product.php"
class="btn">

← Kembali

</a>


<h1>
Edit Produk
</h1>


<form 
method="POST"
enctype="multipart/form-data">


<label>
Pilih Game
</label>

<br>


<select 
name="game_id"
required>


<?php while($game = mysqli_fetch_assoc($games)): ?>


<option 
value="<?= $game['id']; ?>"

<?= 
$product['game_id'] == $game['id']
? "selected"
: "";
?>

>

<?= htmlspecialchars($game['nama_game']); ?>


</option>


<?php endwhile; ?>


</select>


<br><br>


<label>
Nama Produk
</label>

<br>


<input
type="text"
name="nama_produk"
value="<?= htmlspecialchars($product['nama_produk']); ?>"
required>


<br><br>


<label>
Harga
</label>

<br>


<input
type="number"
name="harga"
value="<?= $product['harga']; ?>"
required>


<br><br>


<label>
Stok
</label>

<br>


<input
type="number"
name="stok"
value="<?= $product['stok']; ?>"
required>


<br><br>


<label>
Gambar Saat Ini
</label>

<br><br>


<?php if($product['gambar']): ?>


<img
src="../uploads/product/<?= htmlspecialchars($product['gambar']); ?>"
width="150"
style="border-radius:10px; object-fit:cover;">


<?php else: ?>


<p>
Tidak ada gambar
</p>


<?php endif; ?>


<br><br>


<label>
Ganti Gambar (opsional)
</label>

<br>


<input
type="file"
name="gambar"
accept=".jpg,.jpeg,.png,.webp">


<br><br>


<button
type="submit"
name="update"
class="btn">

Update Produk

</button>


</form>


</div>


</body>

</html>