<?php
require_once 'includes/config.php';
$page_title = 'Keranjang & Checkout';
require_once 'includes/header.php';
?>

<div style="min-height:calc(100vh - 68px);padding:100px 0 4rem">
<div class="container" style="max-width:900px">

    <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.5rem">🛒 Keranjang</h1>
    <p style="color:var(--gray);font-size:.875rem;margin-bottom:2rem">Periksa pesanan kamu sebelum checkout</p>

    <div id="cartEmpty" style="display:none;text-align:center;padding:4rem 0">
        <div style="font-size:5rem;margin-bottom:1rem">🛒</div>
        <h2 style="font-weight:800;margin-bottom:.5rem">Keranjang Kosong</h2>
        <p style="color:var(--gray);margin-bottom:1.5rem">Belum ada produk yang ditambahkan.</p>
        <a href="products.php" class="btn-primary" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.75rem">
            🎮 Lihat Produk
        </a>
    </div>

    <div id="cartFull" style="display:none">
        <div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;align-items:start">

            <!-- Left: Cart items -->
            <div>
                <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);
                        display:flex;justify-content:space-between;align-items:center">
                        <span style="font-weight:700">Item Pesanan (<span id="cartCount">0</span>)</span>
                        <button onclick="clearAllCart()"
                            style="background:none;border:none;color:var(--gray);font-size:.8rem;cursor:pointer;
                                transition:.2s"
                            onmouseover="this.style.color='var(--danger)'"
                            onmouseout="this.style.color='var(--gray)'">
                            🗑 Hapus Semua
                        </button>
                    </div>
                    <div id="cartItems"></div>
                </div>

                <!-- Promo code -->
                <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);
                    padding:1.125rem 1.25rem;margin-top:1rem">
                    <div style="font-weight:700;font-size:.875rem;margin-bottom:.75rem">🎟️ Kode Promo</div>
                    <div style="display:flex;gap:.5rem">
                        <input type="text" id="promoInput" class="form-input"
                            placeholder="Masukkan kode promo (cth: MLBB10)"
                            style="flex:1;padding:.6rem .875rem">
                        <button onclick="applyPromo()"
                            class="btn-primary" style="padding:.6rem 1.25rem;font-size:.85rem;white-space:nowrap">
                            Gunakan
                        </button>
                    </div>
                    <div id="promoMsg" style="display:none;margin-top:.5rem;font-size:.78rem"></div>
                </div>
            </div>

            <!-- Right: Summary -->
            <div style="position:sticky;top:88px">
                <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border)">
                        <div style="font-weight:800;font-size:.95rem">📋 Ringkasan Order</div>
                    </div>
                    <div style="padding:1.125rem 1.25rem;display:flex;flex-direction:column;gap:.625rem">
                        <div style="display:flex;justify-content:space-between;font-size:.875rem">
                            <span style="color:var(--gray)">Subtotal</span>
                            <span id="subtotalEl" style="font-weight:600">Rp 0</span>
                        </div>
                        <div id="discountRow" style="display:none;display:flex;justify-content:space-between;font-size:.875rem">
                            <span style="color:var(--success)">🎟️ Diskon</span>
                            <span id="discountEl" style="font-weight:600;color:var(--success)">-Rp 0</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:.875rem">
                            <span style="color:var(--gray)">Biaya Layanan</span>
                            <span style="color:var(--success);font-weight:600">GRATIS</span>
                        </div>
                        <div style="border-top:1px solid var(--border);padding-top:.625rem;
                            display:flex;justify-content:space-between;font-size:1rem;font-weight:800">
                            <span>Total</span>
                            <span id="totalEl" style="color:var(--cyan)">Rp 0</span>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div style="padding:0 1.25rem 1.25rem">
                        <div style="font-size:.8rem;font-weight:700;color:var(--gray2);
                            text-transform:uppercase;letter-spacing:.5px;margin-bottom:.625rem">
                            Metode Pembayaran
                        </div>
                        <select id="paySelect" class="form-input" style="margin-bottom:.875rem;padding:.6rem .875rem">
                            <option value="">-- Pilih Metode --</option>
                            <option>DANA</option>
                            <option>OVO</option>
                            <option>GoPay</option>
                            <option>ShopeePay</option>
                            <option>Transfer BCA</option>
                            <option>Transfer Mandiri</option>
                            <option>Transfer BRI</option>
                            <option>QRIS</option>
                        </select>
                        <button onclick="checkoutWA()" id="checkoutBtn"
                            style="width:100%;background:linear-gradient(135deg,#25D366,#128C7E);
                                color:#fff;border:none;border-radius:var(--radius);padding:1rem;
                                font-size:.95rem;font-weight:700;cursor:pointer;transition:.25s;
                                display:flex;align-items:center;justify-content:center;gap:.5rem;font-family:inherit">
                            💬 Checkout via WhatsApp
                        </button>
                        <p style="text-align:center;font-size:.72rem;color:var(--gray);margin-top:.625rem">
                            Kamu akan diarahkan ke WhatsApp Admin
                        </p>
                    </div>
                </div>

                <!-- Trust badges -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.875rem">
                    <?php foreach ([['🛡️','100% Aman'],['⚡','Proses Instan'],['💰','Harga Terbaik'],['🎧','Support 24/7']] as [$ic,$lb]): ?>
                    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);
                        padding:.625rem;text-align:center;font-size:.72rem;color:var(--gray)">
                        <div style="font-size:1.1rem;margin-bottom:.2rem"><?= $ic ?></div>
                        <?= $lb ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
const WA_NUM   = '<?= WHATSAPP_NUMBER ?>';
const gamesData = <?= json_encode(array_map(fn($g) => ['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']], $games)) ?>;

let discount = 0;
const PROMO_CODES = { 'MLBB10': 10, 'FF20': 20, 'GAMESTORE5': 5 };

function getCart() {
    try { return JSON.parse(localStorage.getItem('gs_cart') || '[]'); } catch { return []; }
}
function saveCart(c) { localStorage.setItem('gs_cart', JSON.stringify(c)); renderCart(); }

function renderCart() {
    const cart = getCart();
    document.getElementById('cartEmpty').style.display = cart.length ? 'none' : 'block';
    document.getElementById('cartFull').style.display  = cart.length ? 'block' : 'none';
    if (!cart.length) return;

    document.getElementById('cartCount').textContent = cart.length;
    const subtotal = cart.reduce((s,i) => s+Number(i.price||0), 0);
    const disc     = Math.round(subtotal * discount / 100);
    const total    = subtotal - disc;

    document.getElementById('subtotalEl').textContent = fmtRp(subtotal);
    document.getElementById('totalEl').textContent    = fmtRp(total);
    if (disc > 0) {
        document.getElementById('discountRow').style.display = 'flex';
        document.getElementById('discountEl').textContent    = '-' + fmtRp(disc);
    }

    document.getElementById('cartItems').innerHTML = cart.map((item, idx) => `
        <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;
            border-bottom:1px solid rgba(48,54,61,.5)">
            <div style="width:48px;height:48px;border-radius:10px;
                background:linear-gradient(135deg,rgba(37,99,235,.2),rgba(0,212,255,.1));
                display:flex;align-items:center;justify-content:center;
                font-size:1.6rem;flex-shrink:0">${item.icon||'🎮'}</div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:.9rem">${item.game||''}</div>
                <div style="font-size:.78rem;color:var(--gray)">${item.pkg||''} ${item.currency||''}</div>
                <div style="font-size:.75rem;color:var(--gray);margin-top:.1rem">ID: ${item.userId||'—'}</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-weight:800;color:var(--cyan)">${fmtRp(item.price||0)}</div>
                <button onclick="removeItem(${idx})"
                    style="background:none;border:none;color:var(--gray);font-size:.72rem;cursor:pointer;
                        margin-top:.25rem;transition:.2s"
                    onmouseover="this.style.color='var(--danger)'"
                    onmouseout="this.style.color='var(--gray)'">🗑 Hapus</button>
            </div>
        </div>`).join('');
}

function removeItem(idx) {
    const cart = getCart(); cart.splice(idx, 1); saveCart(cart);
    showToast('🗑️ Item dihapus dari keranjang');
}
function clearAllCart() {
    if (!confirm('Hapus semua item dari keranjang?')) return;
    localStorage.removeItem('gs_cart'); renderCart();
    showToast('🗑️ Keranjang dikosongkan');
}

function applyPromo() {
    const code = document.getElementById('promoInput').value.trim().toUpperCase();
    const msg  = document.getElementById('promoMsg');
    if (!code) { showMsg(msg,'⚠️ Masukkan kode promo','var(--warning)'); return; }
    if (PROMO_CODES[code]) {
        discount = PROMO_CODES[code];
        showMsg(msg, `✅ Kode berhasil! Diskon ${discount}% diterapkan`, 'var(--success)');
        renderCart();
    } else {
        discount = 0;
        showMsg(msg, '❌ Kode promo tidak valid atau sudah kedaluwarsa', 'var(--danger)');
    }
}
function showMsg(el, text, color) {
    el.textContent = text; el.style.color = color; el.style.display = 'block';
}

function checkoutWA() {
    const cart = getCart();
    if (!cart.length) { showToast('⚠️ Keranjang kosong!','error'); return; }
    const pay = document.getElementById('paySelect').value;
    if (!pay) { showToast('⚠️ Pilih metode pembayaran dulu!','error'); return; }

    const subtotal = cart.reduce((s,i) => s+Number(i.price||0), 0);
    const disc     = Math.round(subtotal * discount / 100);
    const total    = subtotal - disc;

    let msg = `Halo Admin! Saya mau checkout:\n\n`;
    cart.forEach((item,i) => {
        msg += `${i+1}. ${item.game} — ${item.pkg} ${item.currency}\n`;
        msg += `   💰 ${fmtRp(item.price||0)} | ID: ${item.userId||'—'}\n\n`;
    });
    msg += `━━━━━━━━━━━━\n`;
    if (disc > 0) msg += `Diskon (${discount}%): -${fmtRp(disc)}\n`;
    msg += `Total: ${fmtRp(total)}\n`;
    msg += `Pembayaran: ${pay}\n\nMohon konfirmasinya, terima kasih! 🙏`;

    window.open(`https://wa.me/${WA_NUM}?text=${encodeURIComponent(msg)}`, '_blank');
}

function fmtRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

document.addEventListener('DOMContentLoaded', renderCart);
</script>

<?php require_once 'includes/footer.php'; ?>
