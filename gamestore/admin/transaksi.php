<?php

require_once "../config/config.php";
require_once "../middleware/admin.php";


// =====================
// UPDATE STATUS
// =====================

if (isset($_GET['aksi']) && isset($_GET['id'])) {

    $id = (int) $_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi == "success") {

        mysqli_query(
            $conn,
            "UPDATE orders
            SET status = 'success'
            WHERE id = $id
            AND status = 'paid'"
        );

    } elseif ($aksi == "cancel") {

        mysqli_query(
            $conn,
            "UPDATE orders
            SET status = 'cancel'
            WHERE id = $id
            AND status = 'paid'"
        );

    }

    header("Location: transaksi.php");
    exit;
}


// =====================
// AMBIL DATA TRANSAKSI
// =====================

$orders = mysqli_query(
    $conn,
    "SELECT 
        orders.*,
        users.nama AS nama_user,
        products.nama_produk,
        payments.metode,
        payments.bukti

    FROM orders

    JOIN users
    ON orders.user_id = users.id

    JOIN products
    ON orders.product_id = products.id

    LEFT JOIN payments
    ON payments.order_id = orders.id

    ORDER BY orders.id DESC"
);

?>


<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>Kelola Transaksi</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>


<body>


<div class="detail-container">


<h1>
Daftar Transaksi
</h1>


<table 
border="1"
width="100%"
cellpadding="10">


<tr>

<th>ID</th>
<th>Customer</th>
<th>Produk</th>
<th>User ID</th>
<th>Server</th>
<th>Total</th>
<th>Pembayaran</th>
<th>Bukti</th>
<th>Status</th>
<th>Aksi</th>

</tr>


<?php while ($row = mysqli_fetch_assoc($orders)): ?>


<tr>


<td>
#<?= $row['id']; ?>
</td>


<td>
<?= htmlspecialchars($row['nama_user']); ?>
</td>


<td>
<?= htmlspecialchars($row['nama_produk']); ?>
</td>


<td>
<?= htmlspecialchars($row['game_uid']); ?>
</td>


<td>
<?= htmlspecialchars($row['server_id']); ?>
</td>


<td>
Rp <?= number_format($row['total']); ?>
</td>


<td>

<?php if ($row['metode']): ?>

<?= htmlspecialchars($row['metode']); ?>

<?php else: ?>

Belum bayar

<?php endif; ?>

</td>


<td>

<?php if ($row['bukti']): ?>


<a 
href="../uploads/payment/<?= htmlspecialchars($row['bukti']); ?>"
target="_blank">

<img 
src="../uploads/payment/<?= htmlspecialchars($row['bukti']); ?>"
width="80">

</a>


<?php else: ?>

-

<?php endif; ?>


</td>


<td>

<?= strtoupper(htmlspecialchars($row['status'])); ?>

</td>


<td>


<?php if ($row['status'] == "paid"): ?>


<a 
onclick="return confirm('Selesaikan transaksi ini?')"
href="?aksi=success&id=<?= $row['id']; ?>">

✅ Selesai

</a>


<br><br>


<a 
onclick="return confirm('Tolak transaksi ini?')"
href="?aksi=cancel&id=<?= $row['id']; ?>">

❌ Tolak

</a>


<?php else: ?>

-

<?php endif; ?>


</td>


</tr>


<?php endwhile; ?>


</table>


</div>


</body>

</html>