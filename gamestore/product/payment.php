<?php

require_once "../config/config.php";
require_once "../middleware/auth.php";


if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}


$id = (int) $_GET['id'];


$order = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT orders.*, products.nama_produk

        FROM orders

        JOIN products
        ON orders.product_id = products.id

        WHERE orders.id = $id"
    )
);


if (!$order) {
    die("Order tidak ditemukan");
}


// Cek apakah order milik user yang login
if ($order['user_id'] != $_SESSION['user']['id']) {
    die("Akses ditolak");
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Pembayaran
</title>


<link rel="stylesheet"
href="../assets/css/style.css">


</head>


<body>


<div class="detail-container">


<div class="form-box">


<h1>
Invoice #<?= $order['id']; ?>
</h1>


<hr><br>


<p>
Produk:
<b>
<?= htmlspecialchars($order['nama_produk']); ?>
</b>
</p>


<p>
User ID:
<b>
<?= htmlspecialchars($order['game_uid']); ?>
</b>
</p>


<p>
Server:
<b>
<?= htmlspecialchars($order['server_id']); ?>
</b>
</p>


<p>
Total Pembayaran:
<b>
Rp <?= number_format($order['total']); ?>
</b>
</p>


<br>


<h2>
Metode Pembayaran
</h2>


<div class="payment-list">

<div class="payment-item">
QRIS
</div>


<div class="payment-item">
DANA
</div>


<div class="payment-item">
OVO
</div>


<div class="payment-item">
GoPay
</div>


<div class="payment-item">
Transfer Bank
</div>


</div>


<br>


<a
href="upload_payment.php?id=<?= $order['id']; ?>"
class="buy-btn">

Saya Sudah Bayar

</a>


</div>


</div>


</body>

</html>