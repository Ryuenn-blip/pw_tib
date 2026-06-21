<?php

require_once "../config/config.php";
require_once "../middleware/auth.php";


// Ambil ID user login
$user_id = $_SESSION['user']['id'];


// Ambil transaksi user
$orders = mysqli_query(
    $conn,
    "SELECT 
        orders.*,
        products.nama_produk,
        payments.metode

    FROM orders

    JOIN products
    ON orders.product_id = products.id

    LEFT JOIN payments
    ON payments.order_id = orders.id

    WHERE orders.user_id = $user_id

    ORDER BY orders.id DESC"
);

?>


<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Riwayat Transaksi
</title>


<link rel="stylesheet"
href="../assets/css/style.css">


</head>


<body>


<div class="detail-container">


<h1>
Riwayat Transaksi Saya
</h1>


<?php if(mysqli_num_rows($orders) > 0): ?>


<?php while($row = mysqli_fetch_assoc($orders)): ?>


<div class="form-box">


<h2>
Invoice #<?= $row['id']; ?>
</h2>


<hr><br>


<p>
Produk:
<b>
<?= htmlspecialchars($row['nama_produk']); ?>
</b>
</p>


<p>
User ID:
<b>
<?= htmlspecialchars($row['game_uid']); ?>
</b>
</p>


<p>
Server:
<b>
<?= htmlspecialchars($row['server_id']); ?>
</b>
</p>


<p>
Total:
<b>
Rp <?= number_format($row['total']); ?>
</b>
</p>


<p>
Metode Pembayaran:
<b>

<?php if($row['metode']): ?>

<?= htmlspecialchars($row['metode']); ?>

<?php else: ?>

Belum memilih metode pembayaran

<?php endif; ?>

</b>
</p>


<p>
Status:

<b>

<?php

$status = $row['status'];

if($status == "pending")
{
    echo "🟡 Menunggu Pembayaran";
}
elseif($status == "paid")
{
    echo "🔵 Menunggu Verifikasi Admin";
}
elseif($status == "success")
{
    echo "🟢 Transaksi Berhasil";
}
elseif($status == "cancel")
{
    echo "🔴 Transaksi Ditolak";
}
else
{
    echo htmlspecialchars($status);
}

?>

</b>

</p>


</div>


<?php endwhile; ?>


<?php else: ?>


<div class="form-box">


<h2>
Belum ada transaksi
</h2>


<p>
Silakan lakukan top up terlebih dahulu.
</p>


<a href="../index.php"
class="buy-btn">

Top Up Sekarang

</a>


</div>


<?php endif; ?>


</div>


</body>

</html>