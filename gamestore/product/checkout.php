<?php

require_once "../config/config.php";

// User harus login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Hanya menerima POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}


// Ambil data form
$game_id = (int) $_POST['game_id'];
$product_id = (int) $_POST['product_id'];

$game_uid = trim($_POST['user_id']);
$server_id = trim($_POST['server_id']);

$user_id = $_SESSION['user']['id'];


// Validasi input
if (empty($game_uid)) {
    die("User ID game wajib diisi.");
}


// Cek apakah game tersedia
$game = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT id 
        FROM games 
        WHERE id = $game_id
        AND status = 1"
    )
);


if (!$game) {
    die("Game tidak ditemukan.");
}


// Cek produk dan ambil harga asli
$product = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT *
        FROM products
        WHERE id = $product_id
        AND game_id = $game_id
        AND stok > 0"
    )
);


if (!$product) {
    die("Produk tidak tersedia.");
}


// Ambil harga asli dari database
$total = $product['harga'];


// Simpan order
$query = mysqli_query(
    $conn,
    "INSERT INTO orders
    (
        user_id,
        product_id,
        game_uid,
        server_id,
        total,
        status
    )
    VALUES
    (
        '$user_id',
        '$product_id',
        '$game_uid',
        '$server_id',
        '$total',
        'pending'
    )"
);


// Cek apakah berhasil
if (!$query) {

    die(
        "Gagal membuat pesanan: "
        . mysqli_error($conn)
    );

}


// Ambil ID order terbaru
$order_id = mysqli_insert_id($conn);


// Redirect ke pembayaran
header(
    "Location: payment.php?id=$order_id"
);

exit;

?>