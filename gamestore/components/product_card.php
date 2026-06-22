<div class="product-card">

    <div class="product-image">

        <img
        src="uploads/game/<?= !empty($row['gambar']) ? $row['gambar'] : 'default.png'; ?>"
        alt="<?= htmlspecialchars($row['nama_game']); ?>">

    </div>

    <div class="product-body">

        <h3>
            <?= htmlspecialchars($row['nama_game']); ?>
        </h3>

        <p>
            <?= htmlspecialchars($row['nama_produk']); ?>
        </p>

        <div class="price">

            Rp <?= number_format($row['harga'],0,',','.'); ?>

        </div>

        <a
        href="product/detail.php?id=<?= (int)$row['id']; ?>"
        class="buy-btn">

            <i class="fas fa-cart-shopping"></i>
            Lihat Detail

        </a>

    </div>

</div>