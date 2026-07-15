<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';

$page_title = 'Cek Status Order';
$order      = null;
$error      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET['id'])) {
    $oid = strtoupper(trim($_POST['order_id'] ?? $_GET['id'] ?? ''));
    if ($oid) {
        $order = db_row("
            SELECT o.*, p.icon AS game_icon
            FROM orders o
            LEFT JOIN products p ON p.id = o.product_id
            WHERE o.id = ?
        ", [$oid]);
        if (!$order) $error = "Order ID <strong>$oid</strong> tidak ditemukan. Pastikan ID sudah benar.";
    }
}

$status_steps = [
    'pending'    => 1,
    'processing' => 2,
    'completed'  => 3,
    'cancelled'  => 0,
    'refunded'   => 0,
];
$status_info = [
    'pending'    => ['⏳','Menunggu Verifikasi','Pembayaran sedang menunggu dikonfirmasi admin.','var(--warning)'],
    'processing' => ['⚡','Sedang Diproses','Pembayaran dikonfirmasi, item sedang diproses ke akun game.','var(--blue)'],
    'completed'  => ['✅','Selesai','Top up berhasil! Item sudah masuk ke akun game kamu.','var(--success)'],
    'cancelled'  => ['❌','Dibatalkan','Order ini dibatalkan. Hubungi admin jika ada pertanyaan.','var(--danger)'],
    'refunded'   => ['🔄','Direfund','Dana sudah dikembalikan ke metode pembayaran kamu.','#8B5CF6'],
];

require_once 'includes/header.php';
?>

<style>
.track-page{padding:100px 0 4rem;min-height:calc(100vh - 68px)}
.track-wrap{max-width:680px;margin:0 auto;padding:0 1.5rem}
.progress-steps{display:flex;align-items:center;margin:1.5rem 0}
.ps{display:flex;flex-direction:column;align-items:center;gap:.35rem;flex:1}
.ps-circle{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0;transition:.3s}
.ps-circle.done{background:var(--success);color:#fff;box-shadow:0 0 12px rgba(34,197,94,.4)}
.ps-circle.active{background:var(--blue);color:#fff;box-shadow:0 0 14px rgba(37,99,235,.45)}
.ps-circle.wait{background:var(--bg3);color:var(--gray);border:2px solid var(--border)}
.ps-label{font-size:.68rem;font-weight:600;text-align:center;white-space:nowrap}
.ps-line{flex:1;height:2px;background:var(--border);border-radius:1px;transition:.3s}
.ps-line.done{background:var(--success)}
.detail-row{display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid rgba(48,54,61,.4);font-size:.875rem}
.detail-row:last-child{border-bottom:none}
</style>

<div class="track-page">
<div class="track-wrap">

    <div style="text-align:center;margin-bottom:2rem">
        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
            border:1px solid rgba(37,99,235,.25);color:var(--cyan);
            padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
            📦 Lacak Pesanan
        </div>
        <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.5rem">Cek Status Order</h1>
        <p style="color:var(--gray);font-size:.875rem">Masukkan Order ID untuk melihat status pesananmu</p>
    </div>

    <!-- Search Form -->
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1.5rem">
        <form method="POST" action="tracking.php" style="display:flex;gap:.625rem">
            <div style="flex:1;position:relative">
                <input type="text" name="order_id" class="form-input" required
                       placeholder="Contoh: GS2401150001"
                       value="<?= htmlspecialchars($_POST['order_id'] ?? $_GET['id'] ?? '') ?>"
                       style="text-transform:uppercase;font-family:monospace;font-size:.95rem;padding-left:2.75rem">
                <span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);opacity:.6;font-size:1rem">🔍</span>
            </div>
            <button type="submit" class="btn-primary" style="padding:.75rem 1.25rem;white-space:nowrap">
                Cek Status
            </button>
        </form>
        <p style="font-size:.75rem;color:var(--gray);margin-top:.625rem">
            Order ID dikirim ke WhatsApp kamu setelah konfirmasi pembayaran. Format: GS + tanggal + nomor urut
        </p>
    </div>

    <!-- Error -->
    <?php if ($error): ?>
    <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:var(--radius);
        padding:1rem 1.25rem;margin-bottom:1.25rem;color:var(--danger);font-size:.875rem">
        ⚠️ <?= $error ?>
    </div>
    <?php endif; ?>

    <!-- Order Result -->
    <?php if ($order): 
        $step = $status_steps[$order['status']] ?? 0;
        [$sico,$slbl,$sdesc,$scol] = $status_info[$order['status']] ?? ['❓','Unknown','','var(--gray)'];
    ?>
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">

        <!-- Status Header -->
        <div style="background:linear-gradient(135deg,#0f1f4a,#1a3080);padding:1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.08)">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
                <div>
                    <div style="font-size:.72rem;color:rgba(255,255,255,.5);margin-bottom:.2rem">Order ID</div>
                    <div style="font-size:1.15rem;font-weight:900;font-family:monospace;color:var(--cyan)">
                        <?= htmlspecialchars($order['id']) ?>
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:.72rem;color:rgba(255,255,255,.5);margin-bottom:.2rem">Status</div>
                    <div style="display:inline-flex;align-items:center;gap:.375rem;
                        background:rgba(0,0,0,.2);border:1px solid <?= $scol ?>44;
                        padding:.3rem .875rem;border-radius:100px;font-size:.8rem;font-weight:700;color:<?= $scol ?>">
                        <?= $sico ?> <?= $slbl ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <?php if ($step > 0): ?>
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
            <div class="progress-steps">
                <?php
                $psteps = [['💳','Bayar'],['✅','Verifikasi'],['⚡','Proses'],['🎮','Selesai']];
                foreach ($psteps as $i => [$ico,$lbl]):
                    $sn = $i + 1;
                    $cls = $sn < $step ? 'done' : ($sn === $step ? 'active' : 'wait');
                ?>
                <?php if ($i > 0): ?>
                <div class="ps-line <?= $sn <= $step ? 'done' : '' ?>"></div>
                <?php endif; ?>
                <div class="ps">
                    <div class="ps-circle <?= $cls ?>"><?= $sn < $step ? '✓' : $ico ?></div>
                    <div class="ps-label <?= $cls === 'active' ? '' : 'style="color:var(--gray)"' ?>"><?= $lbl ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <p style="font-size:.8rem;color:var(--gray);text-align:center;margin-top:.5rem"><?= $sdesc ?></p>
        </div>
        <?php endif; ?>

        <!-- Order Details -->
        <div style="padding:1.25rem 1.5rem">
            <div style="font-weight:700;font-size:.82rem;color:var(--gray2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.875rem">Detail Pesanan</div>

            <div class="detail-row">
                <span style="color:var(--gray)">Game</span>
                <span style="font-weight:600;display:flex;align-items:center;gap:.375rem">
                    <span><?= htmlspecialchars($order['game_icon'] ?? '🎮') ?></span>
                    <?= htmlspecialchars($order['product_name']) ?>
                </span>
            </div>
            <div class="detail-row">
                <span style="color:var(--gray)">Paket</span>
                <span style="font-weight:600"><?= htmlspecialchars($order['package_amount']) ?> <?= htmlspecialchars($order['currency']) ?></span>
            </div>
            <div class="detail-row">
                <span style="color:var(--gray)">User ID Game</span>
                <span style="font-family:monospace;font-weight:600"><?= htmlspecialchars($order['game_user_id']) ?></span>
            </div>
            <div class="detail-row">
                <span style="color:var(--gray)">Metode Bayar</span>
                <span style="font-weight:600"><?= htmlspecialchars($order['payment_method']) ?></span>
            </div>
            <div class="detail-row">
                <span style="color:var(--gray)">Total</span>
                <span style="font-weight:800;color:var(--cyan)"><?= formatRupiah((int)$order['total']) ?></span>
            </div>
            <div class="detail-row">
                <span style="color:var(--gray)">Tanggal Order</span>
                <span style="font-size:.82rem"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?> WIB</span>
            </div>
            <?php if ($order['completed_at']): ?>
            <div class="detail-row">
                <span style="color:var(--gray)">Selesai</span>
                <span style="font-size:.82rem;color:var(--success)"><?= date('d/m/Y H:i', strtotime($order['completed_at'])) ?> WIB</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div style="padding:.875rem 1.5rem;background:var(--bg3);border-top:1px solid var(--border);
            display:flex;gap:.625rem;flex-wrap:wrap">
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Halo+admin,+saya+mau+tanya+status+order+<?= urlencode($order['id']) ?>"
               target="_blank"
               style="display:inline-flex;align-items:center;gap:.4rem;background:#25D366;color:#fff;
                   padding:.5rem 1rem;border-radius:8px;font-size:.8rem;font-weight:700;text-decoration:none">
                💬 Tanya Admin
            </a>
            <?php if (in_array($order['status'], ['pending','processing'])): ?>
            <button onclick="location.reload()"
                    style="display:inline-flex;align-items:center;gap:.4rem;background:var(--bg2);
                        border:1px solid var(--border);color:var(--white);padding:.5rem 1rem;
                        border-radius:8px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit">
                🔄 Refresh Status
            </button>
            <?php endif; ?>
            <a href="products.php"
               style="display:inline-flex;align-items:center;gap:.4rem;background:var(--bg2);
                   border:1px solid var(--border);color:var(--white);padding:.5rem 1rem;
                   border-radius:8px;font-size:.8rem;font-weight:700;text-decoration:none">
                🎮 Order Lagi
            </a>
        </div>
    </div>

    <!-- Auto refresh jika pending -->
    <?php if (in_array($order['status'] ?? '', ['pending','processing'])): ?>
    <div style="text-align:center;margin-top:.75rem;font-size:.75rem;color:var(--gray2)">
        Halaman akan otomatis direfresh setiap 30 detik
    </div>
    <script>setTimeout(()=>location.reload(), 30000);</script>
    <?php endif; ?>

    <?php endif; ?>

    <!-- Tips -->
    <?php if (!$order): ?>
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem">
        <div style="font-weight:700;font-size:.875rem;margin-bottom:.875rem">💡 Dimana menemukan Order ID?</div>
        <div style="font-size:.82rem;color:var(--gray);line-height:1.75">
            Order ID dikirim ke WhatsApp kamu setelah konfirmasi pembayaran. Formatnya seperti:
            <div style="font-family:monospace;font-size:.9rem;color:var(--cyan);
                background:var(--bg3);padding:.5rem .875rem;border-radius:6px;margin:.625rem 0;
                display:inline-block">GS2401150001</div><br>
            Jika tidak menemukan Order ID, hubungi admin dengan bukti pembayaran kamu.
        </div>
        <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Halo+admin,+saya+tidak+menemukan+Order+ID+saya"
           target="_blank"
           style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.875rem;
               background:#25D366;color:#fff;padding:.625rem 1.125rem;border-radius:8px;
               font-size:.82rem;font-weight:700;text-decoration:none">
            💬 Tanya Admin WA
        </a>
    </div>
    <?php endif; ?>

</div>
</div>
<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
