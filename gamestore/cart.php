<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = 'Keranjang & Checkout';
require_once 'includes/header.php';
?>
<style>
.cart-page{padding:100px 0 4rem;min-height:calc(100vh - 68px)}
.cart-wrap{max-width:900px;margin:0 auto;padding:0 1.5rem}
.cart-grid{display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start}
.cart-item{display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;
    border-bottom:1px solid rgba(48,54,61,.5);transition:var(--transition)}
.cart-item:last-child{border-bottom:none}
.cart-item:hover{background:rgba(255,255,255,.02)}
.cart-icon{width:52px;height:52px;border-radius:10px;background:linear-gradient(135deg,rgba(37,99,235,.18),rgba(0,212,255,.09));
    display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0}
.sum-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;position:sticky;top:88px}
.sum-row{display:flex;justify-content:space-between;font-size:.875rem;padding:.4rem 0}
.sum-total{font-size:1.05rem;font-weight:900;border-top:1px solid var(--border);margin-top:.5rem;padding-top:.875rem}
.promo-row{display:flex;gap:.5rem;margin-bottom:.875rem}
.promo-inp{flex:1;background:var(--bg3);border:1.5px solid var(--border);border-radius:8px;
    padding:.55rem .875rem;color:var(--white);font-size:.82rem;outline:none;
    transition:var(--transition);font-family:inherit;text-transform:uppercase}
.promo-inp:focus{border-color:var(--blue)}
.btn-checkout{width:100%;background:linear-gradient(135deg,#25D366,#128C7E);color:#fff;border:none;
    border-radius:var(--radius);padding:1rem;font-size:.975rem;font-weight:800;cursor:pointer;
    transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:.5rem;
    font-family:inherit;box-shadow:0 4px 20px rgba(37,211,102,.35)}
.btn-checkout:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(37,211,102,.45)}
@media(max-width:768px){.cart-grid{grid-template-columns:1fr}.sum-card{position:static}}
</style>

<div class="cart-page">
<div class="cart-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
        <div>
            <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.2rem">🛒 Keranjang</h1>
            <p style="color:var(--gray);font-size:.875rem">Periksa pesanan sebelum checkout</p>
        </div>
        <button onclick="clearCart()" id="btnClear" style="display:none;background:rgba(239,68,68,.08);
            border:1px solid rgba(239,68,68,.2);color:var(--danger);padding:.5rem 1rem;
            border-radius:8px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit">
            🗑 Kosongkan
        </button>
    </div>

    <!-- Empty State -->
    <div id="emptyCart" style="display:none;text-align:center;padding:4rem 0">
        <div style="font-size:5rem;margin-bottom:1rem">🛒</div>
        <h2 style="font-weight:800;margin-bottom:.5rem">Keranjang Kosong</h2>
        <p style="color:var(--gray);margin-bottom:1.5rem">Yuk pilih game dan tambahkan ke keranjang!</p>
        <a href="products.php" class="btn-primary" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.75rem">
            🎮 Lihat Produk
        </a>
    </div>

    <!-- Cart Content -->
    <div id="cartContent" style="display:none">
    <div class="cart-grid">

        <!-- Left: Items -->
        <div>
            <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1rem">
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                    <span style="font-weight:700">Item Pesanan (<span id="cartCountLabel">0</span>)</span>
                </div>
                <div id="cartItemsList"></div>
            </div>

            <!-- Rekomendasi -->
            <div id="rekomendasi" style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.125rem">
                <div style="font-weight:700;font-size:.875rem;margin-bottom:.75rem;color:var(--gray2)">⚡ Mau tambah lagi?</div>
                <div id="rekomenList" style="display:flex;gap:.5rem;overflow-x:auto;padding-bottom:4px"></div>
            </div>
        </div>

        <!-- Right: Summary -->
        <div>
        <div class="sum-card">
            <div style="background:linear-gradient(135deg,#0f1f4a,#1a3080);padding:1.125rem 1.25rem;
                border-bottom:1px solid rgba(255,255,255,.08)">
                <div style="font-weight:800;font-size:.95rem">📋 Ringkasan Order</div>
            </div>
            <div style="padding:1.125rem 1.25rem">
                <!-- Promo -->
                <div style="font-size:.78rem;font-weight:600;color:var(--gray);margin-bottom:.375rem">🎟️ Kode Promo</div>
                <div class="promo-row">
                    <input type="text" class="promo-inp" id="promoCode" placeholder="Masukkan kode...">
                    <button onclick="applyPromo()" class="btn btn-ghost btn-sm" style="white-space:nowrap">Pakai</button>
                </div>
                <div id="promoMsg" style="font-size:.75rem;margin-bottom:.875rem;min-height:1rem"></div>

                <!-- Method -->
                <div style="font-size:.78rem;font-weight:600;color:var(--gray);margin-bottom:.375rem">💳 Metode Bayar</div>
                <select id="payMethod" class="form-input" style="margin-bottom:.875rem;padding:.6rem .875rem">
                    <option value="">-- Pilih Metode --</option>
                    <option>DANA</option><option>OVO</option><option>GoPay</option>
                    <option>ShopeePay</option><option>Transfer BCA</option>
                    <option>Transfer Mandiri</option><option>Transfer BRI</option><option>QRIS</option>
                </select>

                <!-- Summary rows -->
                <div class="sum-row"><span style="color:var(--gray)">Subtotal</span><span id="subtotalEl" style="font-weight:600">Rp 0</span></div>
                <div class="sum-row" id="discRow" style="display:none">
                    <span style="color:var(--success)">🎟️ Diskon</span>
                    <span id="discEl" style="color:var(--success);font-weight:700">—</span>
                </div>
                <div class="sum-row"><span style="color:var(--gray)">Biaya Layanan</span><span style="color:var(--success);font-weight:600">GRATIS</span></div>
                <div class="sum-row sum-total">
                    <span>Total Bayar</span>
                    <span id="totalEl" style="color:var(--cyan);font-size:1.1rem">Rp 0</span>
                </div>

                <button class="btn-checkout" onclick="checkoutWA()" style="margin-top:1rem">
                    💬 Checkout via WhatsApp
                </button>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.5rem">
                    <button class="btn-outline" onclick="checkoutDirect()"
                        style="padding:.75rem;font-size:.82rem;font-weight:700;cursor:pointer;
                            display:flex;align-items:center;justify-content:center;gap:.4rem;
                            background:none;border:1.5px solid var(--border);color:var(--white);
                            border-radius:var(--radius);transition:var(--transition);font-family:inherit"
                        onmouseover="this.style.borderColor='var(--blue)'"
                        onmouseout="this.style.borderColor='var(--border)'">
                        💳 Bayar Langsung
                    </button>
                    <a href="products.php"
                        style="padding:.75rem;font-size:.82rem;font-weight:700;
                            display:flex;align-items:center;justify-content:center;gap:.4rem;
                            background:none;border:1.5px solid var(--border);color:var(--white);
                            border-radius:var(--radius);transition:var(--transition);text-decoration:none"
                        onmouseover="this.style.borderColor='var(--blue)'"
                        onmouseout="this.style.borderColor='var(--border)'">
                        ➕ Tambah Item
                    </a>
                </div>
                <p style="text-align:center;font-size:.72rem;color:var(--gray);margin-top:.5rem">
                    Diarahkan ke WhatsApp Admin
                </p>

                <!-- Trust -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.875rem">
                    <?php foreach ([['🔒','Aman'],['⚡','Instan'],['💰','Termurah'],['🎧','24/7']] as [$ic,$lb]): ?>
                    <div style="background:var(--bg3);border:1px solid var(--border);border-radius:7px;
                        padding:.4rem;text-align:center;font-size:.68rem;color:var(--gray)">
                        <div style="font-size:.9rem;margin-bottom:.1rem"><?= $ic ?></div><?= $lb ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        </div>

    </div>
    </div><!-- /cartContent -->
</div>
</div>

<script>
const WA_NUM    = '<?= WHATSAPP_NUMBER ?>';
const fmtRp     = n => 'Rp '+Number(n).toLocaleString('id-ID');
const PROMO_API = 'includes/promo_check.php';
let promoDisc   = 0;
let promoCode   = '';

// ── Cart helpers ──────────────────────────────────────────────
const getCart  = () => { try{return JSON.parse(localStorage.getItem('gs_cart')||'[]')}catch{return[]} };
const saveCart = c  => { localStorage.setItem('gs_cart',JSON.stringify(c)); updateCartBadge(); renderCart(); };

function removeItem(idx) {
    const c=getCart(); c.splice(idx,1); saveCart(c);
    showToast('🗑️ Item dihapus','info');
}
function clearCart() {
    if(!confirm('Kosongkan keranjang?')) return;
    localStorage.removeItem('gs_cart'); updateCartBadge(); renderCart();
}

// ── Render ────────────────────────────────────────────────────
function renderCart() {
    const cart  = getCart();
    const empty = document.getElementById('emptyCart');
    const full  = document.getElementById('cartContent');
    const clear = document.getElementById('btnClear');

    if (!cart.length) {
        empty.style.display = 'block';
        full.style.display  = 'none';
        clear.style.display = 'none';
        return;
    }
    empty.style.display = 'none';
    full.style.display  = 'block';
    clear.style.display = '';
    document.getElementById('cartCountLabel').textContent = cart.length;

    const subtotal = cart.reduce((s,i)=>s+Number(i.price||0),0);
    const disc     = promoDisc;
    const total    = subtotal - disc;

    document.getElementById('subtotalEl').textContent = fmtRp(subtotal);
    document.getElementById('totalEl').textContent    = fmtRp(total);
    if (disc > 0) {
        document.getElementById('discRow').style.display = 'flex';
        document.getElementById('discEl').textContent   = '-'+fmtRp(disc);
    } else {
        document.getElementById('discRow').style.display = 'none';
    }

    document.getElementById('cartItemsList').innerHTML = cart.map((item,i) => `
        <div class="cart-item">
            <div class="cart-icon">${item.icon||'🎮'}</div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:.9rem">${item.game||''}</div>
                <div style="font-size:.78rem;color:var(--gray);margin-top:.15rem">
                    ${item.pkg||''} ${item.currency||''}
                    ${item.userId ? '· ID: <span style="font-family:monospace">'+item.userId+'</span>' : ''}
                </div>
                ${item.payment ? '<div style="font-size:.72rem;color:var(--gray2);margin-top:.1rem">'+item.payment+'</div>' : ''}
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-weight:800;color:var(--cyan)">${fmtRp(item.price||0)}</div>
                <button onclick="removeItem(${i})"
                    style="background:none;border:none;color:var(--gray);font-size:.72rem;cursor:pointer;
                        margin-top:.2rem;transition:.2s;font-family:inherit"
                    onmouseover="this.style.color='var(--danger)'"
                    onmouseout="this.style.color='var(--gray)'">🗑 Hapus</button>
            </div>
        </div>`).join('');
}

// ── Rekomendasi game ──────────────────────────────────────────
function renderRekomendasi() {
    const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'min_price'=>$g['min_price']??0], array_slice($games,0,6))) ?>;
    const cartGames = getCart().map(i=>i.slug);
    const list = document.getElementById('rekomenList');
    list.innerHTML = gamesData.filter(g=>!cartGames.includes(g.slug)).slice(0,4).map(g=>`
        <a href="detail.php?slug=${g.slug}"
           style="flex-shrink:0;background:var(--bg3);border:1px solid var(--border);border-radius:10px;
               padding:.625rem .875rem;text-decoration:none;display:flex;align-items:center;gap:.5rem;
               transition:var(--transition);white-space:nowrap"
           onmouseover="this.style.borderColor='var(--blue)'" onmouseout="this.style.borderColor='var(--border)'">
            <span style="font-size:1.25rem">${g.icon}</span>
            <div><div style="font-size:.78rem;font-weight:700;color:var(--white)">${g.name}</div>
                 <div style="font-size:.68rem;color:var(--cyan)">+ Top Up</div></div>
        </a>`).join('');
}

// ── Promo ─────────────────────────────────────────────────────
function applyPromo() {
    const code  = document.getElementById('promoCode').value.trim().toUpperCase();
    const msg   = document.getElementById('promoMsg');
    const cart  = getCart();
    const total = cart.reduce((s,i)=>s+Number(i.price||0),0);
    if (!code) { msg.innerHTML='<span style="color:var(--warning)">⚠️ Masukkan kode promo</span>'; return; }
    if (!total) { msg.innerHTML='<span style="color:var(--warning)">⚠️ Keranjang kosong</span>'; return; }

    fetch(`${PROMO_API}?code=${encodeURIComponent(code)}&price=${total}`)
        .then(r=>r.json()).then(d=>{
            if (d.valid) {
                promoDisc = d.discount;
                promoCode = code;
                msg.innerHTML = `<span style="color:var(--success)">✅ ${d.msg}</span>`;
            } else {
                promoDisc = 0; promoCode = '';
                msg.innerHTML = `<span style="color:var(--danger)">❌ ${d.msg}</span>`;
            }
            renderCart();
        }).catch(()=>{ msg.innerHTML='<span style="color:var(--danger)">⚠️ Gagal cek promo</span>'; });
}

// ── Checkout Langsung ke Pembayaran ──────────────────────────
function checkoutDirect() {
    const cart = getCart();
    if (!cart.length) { showToast('⚠️ Keranjang kosong!','error'); return; }
    if (cart.length > 1) {
        showToast('💡 Bayar langsung hanya untuk 1 item. Gunakan WA untuk multiple item.','info');
        return;
    }
    const method = document.getElementById('payMethod').value;
    if (!method) { showToast('⚠️ Pilih metode pembayaran!','error'); return; }
    const item   = cart[0];
    const total  = Number(item.price||0) - promoDisc;
    const params = new URLSearchParams({
        game:  item.game  || '',
        slug:  item.slug  || '',
        cur:   item.currency || '',
        pkg:   item.pkg   || '',
        price: total,
        uid:   item.userId|| '',
        icon:  item.icon  || '🎮',
    });
    window.location.href = 'pembayaran.php?' + params.toString();
}

// ── Checkout via WA ───────────────────────────────────────────
function checkoutWA() {
    const cart   = getCart();
    if (!cart.length) { showToast('⚠️ Keranjang kosong!','error'); return; }
    const method = document.getElementById('payMethod').value;
    if (!method)  { showToast('⚠️ Pilih metode pembayaran!','error'); return; }

    const subtotal = cart.reduce((s,i)=>s+Number(i.price||0),0);
    const total    = subtotal - promoDisc;

    let msg = `🛒 *ORDER GAMESTORE*\n\n`;
    cart.forEach((item,i) => {
        msg += `${i+1}. ${item.icon||'🎮'} ${item.game}\n`;
        msg += `   💎 ${item.pkg} ${item.currency}\n`;
        if (item.userId) msg += `   🆔 User ID: ${item.userId}\n`;
        msg += `   💰 ${fmtRp(item.price||0)}\n\n`;
    });
    msg += `━━━━━━━━━━━━━\n`;
    if (promoDisc>0) msg += `🎟️ Diskon (${promoCode}): -${fmtRp(promoDisc)}\n`;
    msg += `💰 Total: ${fmtRp(total)}\n`;
    msg += `💳 Bayar: ${method}\n\n`;
    msg += `Mohon konfirmasinya. Terima kasih! 🙏`;

    window.open(`https://wa.me/${WA_NUM}?text=${encodeURIComponent(msg)}`,'_blank');
}

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    renderRekomendasi();
});

const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
