<?php

require_once "../config/config.php";
require_once "../middleware/auth.php";


// Cek ID order
if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$order_id = (int) $_GET['id'];


// Ambil order
$order = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM orders 
        WHERE id = $order_id"
    )
);


// Validasi order
if (!$order) {
    die("Order tidak ditemukan");
}


// Cek kepemilikan order
if ($order['user_id'] != $_SESSION['user']['id']) {
    die("Akses ditolak");
}


// Proses upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $metode = trim($_POST['metode']);


    // Validasi metode
    if (empty($metode)) {
        die("Pilih metode pembayaran");
    }


    // Cek file
    if (!isset($_FILES['bukti']) || 
        $_FILES['bukti']['error'] != 0) {

        die("Bukti pembayaran wajib diupload");
    }


    $file = $_FILES['bukti'];


    // Ekstensi yang diizinkan
    $allow = [
        "jpg",
        "jpeg",
        "png"
    ];


    $ext = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );


    if (!in_array($ext, $allow)) {
        die("Format gambar harus JPG atau PNG");
    }


    // Nama file unik
    $filename = "PAY-" .
        time() .
        "-" .
        rand(1000,9999) .
        "." .
        $ext;


    $path = "../uploads/payment/" . $filename;


    // Upload file
    if (!move_uploaded_file(
        $file['tmp_name'],
        $path
    )) {
        die("Upload gagal");
    }


    // Simpan pembayaran
    $save = mysqli_query(
        $conn,
        "INSERT INTO payments
        (
            order_id,
            metode,
            bukti,
            status
        )
        VALUES
        (
            '$order_id',
            '$metode',
            '$filename',
            'pending'
        )"
    );


    if (!$save) {
        die(
            "Database error: " .
            mysqli_error($conn)
        );
    }


    // Update status order
    mysqli_query(
        $conn,
        "UPDATE orders 
        SET status = 'paid'
        WHERE id = $order_id"
    );


    // Sukses
    echo "
    <script>
        alert('Bukti pembayaran berhasil dikirim!');
        window.location='../customer/transaksi.php';
    </script>
    ";

    exit;
}

?>


<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Upload Pembayaran
</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>


<body>


<div class="detail-container">


<div class="form-box">


<h1>
Upload Bukti Pembayaran
</h1>


<form method="POST"
enctype="multipart/form-data">


<label>
Metode Pembayaran
</label>


<select name="metode" required>

<option value="">
Pilih metode
</option>

<option value="QRIS">
QRIS
</option>

<option value="DANA">
DANA
</option>

<option value="OVO">
OVO
</option>

<option value="GoPay">
GoPay
</option>

<option value="Transfer Bank">
Transfer Bank
</option>

</select>


<br><br>


<label>
Upload Bukti Transfer
</label>


<input
type="file"
name="bukti"
accept=".jpg,.jpeg,.png"
required>


<br><br>


<button
type="submit"
class="buy-btn">

Kirim Bukti Pembayaran

</button>


</form>


</div>


</div>


</body>

</html>