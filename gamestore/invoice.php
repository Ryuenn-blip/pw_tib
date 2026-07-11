<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';

$order_id = strtoupper(trim($_GET['id'] ?? ''));
$order    = null;
$error    = '';

if ($order_id) {
    $order = db_row("
        SELECT o.*, p.name AS product_name_full, p.icon AS game_icon, p.slug AS game_slug
        FROM orders o
        LEFT JOIN products p ON p.id = o.product_id
        WHERE o.id = ?
    ", [$order_id]);

    if (!$order) {
        $error = "Invoice tidak ditemukan.";
    } elseif ($order['status'] !== 'completed') {
        $error = "Invoice hanya tersedia untuk order yang sudah selesai.";
        $order = null;
    }
}

$page_title = $order ? 'Invoice ' . $order['id'] : 'Invoice';
require_once 'includes/header.php';
?>

<style>
.invoice-page { padding: 100px 0 4rem; min-height: calc(100vh - 68px); }
.invoice-wrap { max-width: 680px; margin: 0 auto; padding: 0 1.5rem; }
.invoice-box {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
}
.invoice-header {
    background: linear-gradient(135deg, #0f1f4a, #1a3080);
    padding: 2rem; display: flex; justify-content: space-between;
    align-items: flex-start; flex-wrap: wrap; gap: 1rem;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.invoice-body { padding: 1.75rem; }
.inv-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .5rem 0; border-bottom: 1px solid rgba(48,54,61,.4);
    font-size: .875rem;
}
.inv-row:last-child { border-bottom: none; }
.inv-label { color: var(--gray); }
.inv-val { font-weight: 600; text-align: right; }
.inv-total {
    font-size: 1.05rem; font-weight: 900;
    border-top: 2px solid var(--border); margin-top: .5rem;
    padding-top: 1rem;
}
.inv-total .inv-val { color: var(--cyan); font-size: 1.25rem; }
.stamp {
    display: inline-flex; align-items: center; gap: .5rem;
    border: 3px solid var(--success); border-radius: 8px;
    padding: .5rem 1rem; color: var(--success); font-weight: 900;
    font-size: .9rem; transform: rotate(-3deg);
}
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; color: #000 !important; }
    .invoice-box { border: 1px solid #ccc !important; }
    .invoice-header { background: #1a2d5e !important; }
}
</style>

<div class="invoice-page">
<div class="invoice-wrap">

    <!-- Search form jika belum ada order_id -->
    <?php if (!$order_id): ?>
    <div style="text-align:center;margin-bottom:2rem">
        <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.5rem">🧾 Invoice / Nota</h1>
        <p style="color:var(--gray)">Masukkan Order ID untuk melihat invoice</p>
    </div>
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem">
        <form method="GET" style="display:flex;gap:.625rem">
            <input type="text" name="id" class="form-input" placeholder="Contoh: GS2401150001"
                   style="flex:1;font-family:monospace;text-transform:uppercase">
            <button type="submit" class="btn-primary" style="padding:.75rem 1.25rem;white-space:nowrap">
                Lihat Invoice
            </button>
        </form>
    </div>

    <?php elseif ($error): ?>
    <div style="text-align:center;padding:3rem 0">
        <div style="font-size:3rem;margin-bottom:1rem">❌</div>
        <h2 style="font-weight:800;margin-bottom:.5rem">Invoice Tidak Tersedia</h2>
        <p style="color:var(--gray);margin-bottom:1.5rem"><?= htmlspecialchars($error) ?></p>
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
            <a href="tracking.php?id=<?= urlencode($order_id) ?>" class="btn-primary"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
                📦 Cek Status Order
            </a>
            <a href="invoice.php" class="btn-outline"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
                🔍 Cari Invoice Lain
            </a>
        </div>
    </div>

    <?php else: ?>
    <!-- Invoice Header Actions -->
    <div class="no-print" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
        <div style="font-weight:800;font-size:1rem">🧾 Invoice Digital</div>
        <div style="display:flex;gap:.5rem">
            <button onclick="window.print()" class="btn-outline"
                style="padding:.5rem 1rem;font-size:.82rem;display:flex;align-items:center;gap:.4rem;
                background:none;border:1.5px solid var(--border);color:var(--white);border-radius:8px;
                cursor:pointer;font-family:inherit;transition:.2s"
                onmouseover="this.style.borderColor='var(--blue)'"
                onmouseout="this.style.borderColor='var(--border)'">
                🖨️ Print
            </button>
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= urlencode('Halo, saya butuh bantuan untuk order: '.$order['id']) ?>"
               target="_blank"
               style="display:inline-flex;align-items:center;gap:.4rem;background:#25D366;color:#fff;
                   padding:.5rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none">
                💬 Bantuan
            </a>
        </div>
    </div>

    <!-- Invoice Box -->
    <div class="invoice-box">

        <!-- Header -->
        <div class="invoice-header">
            <div>
                <div style="font-size:1.5rem;font-weight:900;margin-bottom:.25rem">
                    <?= SITE_NAME ?>
                </div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.6)">
                    Top Up Game Terlengkap & Termurah<br>
                    WA: <?= WHATSAPP_NUMBER ?>
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:.72rem;color:rgba(255,255,255,.5);margin-bottom:.2rem">INVOICE</div>
                <div style="font-size:1.1rem;font-weight:900;font-family:monospace;color:var(--cyan)">
                    <?= htmlspecialchars($order['id']) ?>
                </div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.5);margin-top:.25rem">
                    <?= date('d F Y, H:i', strtotime($order['created_at'])) ?> WIB
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-body">

            <!-- LUNAS stamp -->
            <?php if ($order['status'] === 'completed'): ?>
            <div style="display:flex;justify-content:flex-end;margin-bottom:1.25rem">
                <div class="stamp">✅ LUNAS</div>
            </div>
            <?php endif; ?>

            <!-- Customer info -->
            <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;margin-bottom:1.25rem">
                <div style="font-size:.72rem;color:var(--gray2);font-weight:700;text-transform:uppercase;
                    letter-spacing:.5px;margin-bottom:.625rem">Info Pelanggan</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.875rem">
                    <div>
                        <div style="color:var(--gray);font-size:.72rem">Nama</div>
                        <div style="font-weight:600"><?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                    <div>
                        <div style="color:var(--gray);font-size:.72rem">WhatsApp</div>
                        <div style="font-weight:600"><?= htmlspecialchars($order['customer_wa'] ?? '—') ?></div>
                    </div>
                </div>
            </div>

            <!-- Order details -->
            <div style="font-size:.72rem;color:var(--gray2);font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;margin-bottom:.625rem">Detail Pesanan</div>

            <div class="inv-row">
                <span class="inv-label">Game</span>
                <span class="inv-val"><?= htmlspecialchars($order['game_icon']??'🎮') ?> <?= htmlspecialchars($order['product_name']) ?></span>
            </div>
            <div class="inv-row">
                <span class="inv-label">Paket</span>
                <span class="inv-val"><?= htmlspecialchars($order['package_amount']) ?> <?= htmlspecialchars($order['currency']) ?></span>
            </div>
            <div class="inv-row">
                <span class="inv-label">User ID Game</span>
                <span class="inv-val" style="font-family:monospace"><?= htmlspecialchars($order['game_user_id']) ?></span>
            </div>
            <div class="inv-row">
                <span class="inv-label">Metode Bayar</span>
                <span class="inv-val"><?= htmlspecialchars($order['payment_method']) ?></span>
            </div>
            <div class="inv-row">
                <span class="inv-label">Subtotal</span>
                <span class="inv-val"><?= formatRupiah((int)$order['subtotal']) ?></span>
            </div>
            <?php if ((int)($order['discount'] ?? 0) > 0): ?>
            <div class="inv-row">
                <span class="inv-label" style="color:var(--success)">Diskon <?= $order['promo_code'] ? '('.$order['promo_code'].')' : '' ?></span>
                <span class="inv-val" style="color:var(--success)">-<?= formatRupiah((int)$order['discount']) ?></span>
            </div>
            <?php endif; ?>
            <div class="inv-row inv-total">
                <span>Total Dibayar</span>
                <span class="inv-val"><?= formatRupiah((int)$order['total']) ?></span>
            </div>

            <!-- Timestamps -->
            <div style="margin-top:1.25rem;background:var(--bg3);border-radius:var(--radius);padding:1rem;
                font-size:.8rem;color:var(--gray)">
                <div style="display:flex;justify-content:space-between;margin-bottom:.3rem">
                    <span>Waktu Order</span>
                    <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?> WIB</span>
                </div>
                <?php if ($order['completed_at']): ?>
                <div style="display:flex;justify-content:space-between">
                    <span>Waktu Selesai</span>
                    <span style="color:var(--success)"><?= date('d/m/Y H:i', strtotime($order['completed_at'])) ?> WIB</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Footer note -->
            <div style="margin-top:1.25rem;text-align:center;font-size:.75rem;color:var(--gray2);line-height:1.7;
                border-top:1px solid var(--border);padding-top:1rem">
                Terima kasih telah berbelanja di <strong style="color:var(--white)"><?= SITE_NAME ?></strong>!<br>
                Simpan invoice ini sebagai bukti transaksi. Hubungi kami jika ada pertanyaan.<br>
                <span style="font-size:.7rem">Invoice ini sah tanpa tanda tangan.</span>
            </div>
        </div>
    </div>

    <!-- Action buttons -->
    <div class="no-print" style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;margin-top:1.25rem">
        <a href="tracking.php?id=<?= urlencode($order['id']) ?>" class="btn-outline"
           style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
            📦 Status Order
        </a>
        <a href="products.php" class="btn-primary"
           style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
            🎮 Top Up Lagi
        </a>
    </div>
    <?php endif; ?>

</div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
