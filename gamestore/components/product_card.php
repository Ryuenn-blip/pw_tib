<div class="product-card">

    <div class="product-image">

        <img src="uploads/game/<?= $row['gambar']; ?>">

        <div class="badge">
            POPULER
        </div>

    </div>

    <div class="product-body">

        <h3>
            <?= $row['nama_game']; ?>
        </h3>

        <p>
            <?= substr($row['deskripsi'],0,70); ?>...
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
                Rp 5.000
            </strong>

        </div>

        <a
href="product/detail.php?id=<?= $row['id']; ?>"
class="buy-btn">
Lihat Detail
</a>

    </div>

</div>
