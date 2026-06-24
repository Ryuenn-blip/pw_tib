<?php
require_once 'includes/config.php';
$page_title = 'Keranjang';
require_once 'includes/header.php';
?>

<div style="min-height:calc(100vh - 68px); padding:100px 0 4rem">
    <div class="container" style="max-width:700px">
        <h1 style="font-size:1.75rem; font-weight:900; margin-bottom:2rem">🛒 Keranjang</h1>
        <div id="cartContent">
            <!-- filled by JS -->
        </div>
    </div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g) => [
    'name' => $g['name'], 'slug' => $g['slug'],
    'icon' => $g['icon'], 'currency' => $g['currency']
], $games)) ?>;

document.addEventListener('DOMContentLoaded', function () {
    const cart = JSON.parse(localStorage.getItem('gs_cart') || '[]');
    const el = document.getElementById('cartContent');
    if (!cart.length) {
        el.innerHTML = `
            <div style="text-align:center; padding:4rem 0; color:var(--gray)">
                <div style="font-size:4rem; margin-bottom:1rem">🛒</div>
                <p style="font-size:1.1rem; margin-bottom:.5rem">Keranjang kamu masih kosong</p>
                <a href="products.php" class="btn-primary" style="display:inline-flex; margin-top:1rem">
                    🎮 Lihat Produk
                </a>
            </div>`;
        return;
    }
    el.innerHTML = cart.map((item, i) => `
        <div style="background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius-lg);
                    padding:1.25rem; display:flex; justify-content:space-between; align-items:center;
                    margin-bottom:.75rem">
            <div>
                <div style="font-weight:700">${item.game}</div>
                <div style="color:var(--gray); font-size:.85rem">${item.pkg} • ${item.currency}</div>
                <div style="color:var(--cyan); font-weight:700; margin-top:.25rem">Rp ${Number(item.price).toLocaleString('id-ID')}</div>
            </div>
            <button onclick="removeItem(${i})"
                    style="background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.2); color:var(--danger);
                           padding:.5rem .875rem; border-radius:8px; cursor:pointer; font-size:.8rem; font-weight:600">
                🗑 Hapus
            </button>
        </div>`).join('') + `
        <button onclick="clearCart()"
                style="margin-top:.5rem; background:transparent; border:1px solid var(--border); color:var(--gray);
                       padding:.625rem 1.25rem; border-radius:8px; cursor:pointer; font-size:.8rem; font-weight:600; transition:var(--transition)"
                onmouseover="this.style.borderColor='var(--danger)'; this.style.color='var(--danger)'"
                onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--gray)'">
            Kosongkan Keranjang
        </button>`;
});

function removeItem(i) {
    const cart = JSON.parse(localStorage.getItem('gs_cart') || '[]');
    cart.splice(i, 1);
    localStorage.setItem('gs_cart', JSON.stringify(cart));
    location.reload();
}
function clearCart() {
    localStorage.removeItem('gs_cart');
    location.reload();
}
</script>

<?php require_once 'includes/footer.php'; ?>
