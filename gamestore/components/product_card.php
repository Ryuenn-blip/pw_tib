<div class="product-card">

    <div class="product-image">

        <img
        src="uploads/game/<?= !empty($row['gambar']) ? $row['gambar'] : 'default.png'; ?>"
        alt="<?= htmlspecialchars($row['nama_game']); ?>">

        <div class="badge">
            POPULER
        </div>

    </div>

    <div class="product-body">

        <h3>
            <?= htmlspecialchars($row['nama_game']); ?>
        </h3>

        <p>
            <?= htmlspecialchars(substr($row['deskripsi'],0,70)); ?>...
        </p>

        <div class="product-info">

            <span class="rating">
                ⭐ 4.9
            </span>

            <span class="sold">
                10K+ Terjual
            </span>

        </div>

        <div class="price">

            Mulai dari

            <strong>
                Rp <?= number_format($row['harga'],0,',','.'); ?>
            </strong>

        </div>

        <a
        href="product/detail.php?id=<?= (int)$row['id']; ?>"
        class="buy-btn">
            Lihat Detail
        </a>

    </div>

</div>