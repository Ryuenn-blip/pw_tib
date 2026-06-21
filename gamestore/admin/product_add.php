<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";

$games = mysqli_query(
    $conn,
    "SELECT * FROM games ORDER BY nama_game ASC"
);


if(isset($_POST['simpan']))
{

    $game_id = (int) $_POST['game_id'];

    $nama_produk = mysqli_real_escape_string(
        $conn,
        $_POST['nama_produk']
    );

    $harga = (int) $_POST['harga'];

    $stok = (int) $_POST['stok'];


    // Upload gambar
    $gambar = "";

    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0)
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


        if(in_array($ext, $allowed))
        {

            $gambar = time() . "_" . uniqid() . "." . $ext;


            move_uploaded_file(
                $_FILES['gambar']['tmp_name'],
                "../uploads/product/" . $gambar
            );

        }

        else
        {

            die("Format gambar tidak didukung!");

        }

    }


    mysqli_query(
        $conn,
        "INSERT INTO products
        (
            game_id,
            nama_produk,
            harga,
            stok,
            gambar
        )
        VALUES
        (
            '$game_id',
            '$nama_produk',
            '$harga',
            '$stok',
            '$gambar'
        )"
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
Tambah Produk
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
Tambah Produk
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
value="<?= $game['id']; ?>">

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
placeholder="Contoh: 86 Diamond"
required>


<br><br>


<label>
Harga
</label>

<br>


<input
type="number"
name="harga"
placeholder="Contoh: 20000"
required>


<br><br>


<label>
Stok
</label>

<br>


<input
type="number"
name="stok"
placeholder="Contoh: 999"
required>


<br><br>


<label>
Gambar Produk
</label>

<br>


<input
type="file"
name="gambar"
accept=".jpg,.jpeg,.png,.webp"
required>


<br><br>


<button
type="submit"
name="simpan"
class="btn">

Simpan Produk

</button>


</form>


</div>


</body>

</html>