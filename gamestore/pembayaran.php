<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Ambil data dari GET atau session
$game_name  = trim($_GET['game']  ?? $_SESSION['pay_game']  ?? '');
$game_slug  = trim($_GET['slug']  ?? $_SESSION['pay_slug']  ?? '');
$currency   = trim($_GET['cur']   ?? $_SESSION['pay_cur']   ?? '');
$pkg_amount = trim($_GET['pkg']   ?? $_SESSION['pay_pkg']   ?? '');
$price      = (int)($_GET['price']?? $_SESSION['pay_price'] ?? 0);
$user_id_game = trim($_GET['uid'] ?? $_SESSION['pay_uid']   ?? '');
$game_icon  = trim($_GET['icon']  ?? $_SESSION['pay_icon']  ?? '🎮');
$pkg_id     = (int)($_GET['pkg_id'] ?? $_SESSION['pay_pkg_id'] ?? 0);

if (!$game_name || !$price) { header('Location: products.php'); exit; }

// Simpan ke session
$_SESSION['pay_game']   = $game_name;
$_SESSION['pay_slug']   = $game_slug;
$_SESSION['pay_cur']    = $currency;
$_SESSION['pay_pkg']    = $pkg_amount;
$_SESSION['pay_price']  = $price;
$_SESSION['pay_uid']    = $user_id_game;
$_SESSION['pay_icon']   = $game_icon;
$_SESSION['pay_pkg_id'] = $pkg_id;

// ── Load payment methods dari DB ──────────────────────────────
$methods_raw = db_rows("SELECT * FROM payment_methods WHERE status='active' ORDER BY sort_order");
$methods = ['ewallet'=>[], 'bank'=>[], 'qris'=>[]];
foreach ($methods_raw as $m) {
    $methods[$m['type']][$m['name']] = [
        'number'=> $m['number'] ?? '',
        'name'  => $m['account_name'] ?? '',
        'color' => $m['color'],
        'icon'  => $m['icon'],
    ];
}

// ── Handle submit konfirmasi (AJAX/POST) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['confirm_payment'])) {
    header('Content-Type: application/json');

    $method     = trim($_POST['payment_method'] ?? '');
    $uid        = trim($_POST['user_id_game']   ?? $user_id_game);
    $note       = trim($_POST['note']           ?? '');
    $cust_name  = trim($_POST['customer_name']  ?? ($_SESSION['user_name'] ?? 'Tamu'));
    $cust_wa    = trim($_POST['customer_wa']    ?? '');
    $promo_code = strtoupper(trim($_POST['promo_code'] ?? ''));
    $discount   = 0;
    $final_price= $price;

    // Validasi promo
    if ($promo_code) {
        $promo = db_row("SELECT * FROM promo_codes WHERE code=? AND is_active=1
            AND (valid_until IS NULL OR valid_until>=CURDATE())
            AND (max_use=0 OR used_count<max_use)", [$promo_code]);
        if ($promo && $price >= (int)$promo['min_purchase']) {
            $discount = $promo['type']==='percent'
                ? (int)round($price * $promo['value'] / 100)
                : (int)$promo['value'];
            if ($promo['max_discount']) $discount = min($discount, (int)$promo['max_discount']);
            $discount   = min($discount, $price);
            $final_price = $price - $discount;
        }
    }

    // Upload bukti
    $proof_file = null;
    if (!empty($_FILES['proof']['tmp_name'])) {
        $ext  = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','heic'];
        if (!in_array($ext, $allowed) || $_FILES['proof']['size'] > 5*1024*1024) {
            echo json_encode(['success'=>false,'msg'=>'File tidak valid (maks 5MB, format JPG/PNG)']);
            exit;
        }
        $fname = 'proof_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $dest  = __DIR__ . '/../uploads/proofs/' . $fname;
        if (!move_uploaded_file($_FILES['proof']['tmp_name'], $dest)) {
            echo json_encode(['success'=>false,'msg'=>'Gagal mengupload bukti. Coba lagi.']);
            exit;
        }
        $proof_file = $fname;
    }

    // Generate order ID
    $total_today = (int)(db_row("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at)=CURDATE()")['c'] ?? 0);
    $order_id = 'GS'.date('ymd').str_pad($total_today+1, 4, '0', STR_PAD_LEFT);

    // Simpan order ke DB
    try {
        db_exec("INSERT INTO orders
            (id, customer_id, customer_name, customer_wa, product_id, package_id,
             product_name, package_amount, currency, game_user_id,
             payment_method, subtotal, discount, promo_code, total,
             proof_image, note, status, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending',NOW())", [
            $order_id,
            $_SESSION['user_id'] ?? null,
            $cust_name,
            $cust_wa,
            db_row("SELECT id FROM products WHERE slug=?",[$game_slug])['id'] ?? null,
            $pkg_id ?: null,
            $game_name,
            $pkg_amount,
            $currency,
            $uid,
            $method,
            $price,
            $discount,
            $promo_code ?: null,
            $final_price,
            $proof_file,
            $note ?: null,
        ]);

        // Update promo used_count
        if ($promo_code && $discount > 0) {
            db_exec("UPDATE promo_codes SET used_count=used_count+1 WHERE code=?", [$promo_code]);
        }

        // Clear session
        foreach (['pay_game','pay_slug','pay_cur','pay_pkg','pay_price','pay_uid','pay_icon','pay_pkg_id'] as $k)
            unset($_SESSION[$k]);

        echo json_encode(['success'=>true,'order_id'=>$order_id,'total'=>$final_price]);
    } catch (Exception $e) {
        error_log('[ORDER ERROR] '.$e->getMessage());
        echo json_encode(['success'=>false,'msg'=>'Gagal menyimpan order. Coba lagi.']);
    }
    exit;
}

// ── Load game object untuk foto ───────────────────────────────
$game_obj = db_row("SELECT * FROM products WHERE slug=?", [$game_slug]);
$page_title = 'Pembayaran — '.$game_name;
require_once 'includes/header.php';
?>

<style>
.pay-page{padding:100px 0 4rem;min-height:100vh}
.pay-wrap{max-width:1080px;margin:0 auto;padding:0 1.5rem;display:grid;grid-template-columns:1fr 380px;gap:1.75rem;align-items:start}
.pay-steps{max-width:1080px;margin:0 auto;padding:0 1.5rem 1.75rem;display:flex;align-items:center}
.sc{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:800;flex-shrink:0;transition:.3s}
.sc.done{background:var(--success);color:#fff}
.sc.active{background:var(--blue);color:#fff;box-shadow:0 0 16px rgba(37,99,235,.5)}
.sc.wait{background:var(--bg3);color:var(--gray);border:1.5px solid var(--border)}
.sl{font-size:.75rem;font-weight:600;white-space:nowrap}
.sl.active{color:var(--white)}.sl.done{color:var(--success)}.sl.wait{color:var(--gray)}
.sline{flex:1;height:2px;background:var(--border);margin:0 .5rem;border-radius:1px;transition:.3s}
.sline.done{background:var(--success)}
.method-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.625rem;margin-bottom:1rem}
.mc{background:var(--bg3);border:2px solid var(--border);border-radius:var(--radius);padding:.875rem;cursor:pointer;
    display:flex;flex-direction:column;align-items:center;gap:.4rem;position:relative;transition:var(--transition)}
.mc:hover{border-color:var(--blue);transform:translateY(-2px)}
.mc.sel{border-color:var(--blue);background:rgba(37,99,235,.1);box-shadow:0 0 0 3px rgba(37,99,235,.15)}
.mc .chk{position:absolute;top:.4rem;right:.4rem;width:17px;height:17px;border-radius:50%;background:var(--blue);color:#fff;
    font-size:.6rem;display:none;align-items:center;justify-content:center}
.mc.sel .chk{display:flex}
.acc-box{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.125rem;margin-bottom:.5rem;
    display:flex;align-items:center;justify-content:space-between}
.copy-btn{background:rgba(37,99,235,.12);border:1px solid rgba(37,99,235,.25);color:var(--cyan);
    padding:.4rem .875rem;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer;
    transition:var(--transition);font-family:inherit;white-space:nowrap;flex-shrink:0}
.copy-btn:hover{background:rgba(37,99,235,.22)}
.copy-btn.ok{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.25);color:var(--success)}
.upload-zone{border:2px dashed var(--border);border-radius:var(--radius-lg);padding:1.75rem;text-align:center;
    cursor:pointer;transition:var(--transition);position:relative}
.upload-zone:hover,.upload-zone.drag{border-color:var(--blue);background:rgba(37,99,235,.04)}
.upload-zone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.timer-box{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:var(--radius);
    padding:.875rem 1.125rem;display:flex;align-items:center;gap:.75rem;margin-bottom:1rem}
.timer-count{font-size:1.25rem;font-weight:900;color:var(--warning);font-variant-numeric:tabular-nums}
.timer-count.urgent{color:var(--danger);animation:pulse 1s infinite}
.pay-summary{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;position:sticky;top:88px}
.sum-hdr{background:linear-gradient(135deg,#0f1f4a,#1a3080);padding:1.125rem 1.25rem;
    display:flex;align-items:center;gap:.875rem;border-bottom:1px solid rgba(255,255,255,.08)}
.sum-body{padding:1.125rem 1.25rem}
.sum-row{display:flex;justify-content:space-between;align-items:center;font-size:.875rem;padding:.375rem 0}
.sum-row.total{font-size:1.05rem;font-weight:900;border-top:1px solid var(--border);margin-top:.5rem;padding-top:.875rem}
.sum-row.total .amount{color:var(--cyan);font-size:1.2rem}
.btn-confirm{width:100%;background:linear-gradient(135deg,#22C55E,#16A34A);color:#fff;border:none;
    border-radius:var(--radius);padding:1rem;font-size:.975rem;font-weight:800;cursor:pointer;
    transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:.5rem;
    font-family:inherit;box-shadow:0 4px 20px rgba(34,197,94,.35);margin-top:1rem}
.btn-confirm:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(34,197,94,.45)}
.btn-confirm:disabled{opacity:.45;cursor:default;transform:none}
.success-wrap{text-align:center;padding:2.5rem 1.5rem;display:none}
.success-circle{width:80px;height:80px;border-radius:50%;background:rgba(34,197,94,.12);border:3px solid var(--success);
    display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 1.25rem;
    animation:successPop .5s cubic-bezier(.34,1.56,.64,1) both}
@keyframes successPop{from{transform:scale(0)}to{transform:scale(1)}}
.promo-inp{display:flex;gap:.5rem;margin-top:.625rem}
@media(max-width:768px){.pay-wrap{grid-template-columns:1fr}.pay-summary{position:static}.method-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:480px){.method-grid{grid-template-columns:repeat(2,1fr)}}
</style>

<div class="pay-page">
<!-- Steps -->
<div class="pay-steps">
    <div style="display:flex;align-items:center;gap:.5rem"><div class="sc active" id="sc1">1</div><div class="sl active" id="sl1">Pilih Bayar</div></div>
    <div class="sline" id="sline1"></div>
    <div style="display:flex;align-items:center;gap:.5rem"><div class="sc wait" id="sc2">2</div><div class="sl wait" id="sl2">Transfer</div></div>
    <div class="sline" id="sline2"></div>
    <div style="display:flex;align-items:center;gap:.5rem"><div class="sc wait" id="sc3">3</div><div class="sl wait" id="sl3">Konfirmasi</div></div>
    <div class="sline" id="sline3"></div>
    <div style="display:flex;align-items:center;gap:.5rem"><div class="sc wait" id="sc4">✓</div><div class="sl wait" id="sl4">Selesai</div></div>
</div>

<div class="pay-wrap">
<!-- LEFT -->
<div>

<!-- STEP 1: Metode -->
<div id="p1">
<div class="card" style="margin-bottom:1rem">
<div class="card-header" style="padding:1.125rem 1.25rem"><div style="font-weight:800">💳 Pilih Metode Pembayaran</div></div>
<div style="padding:1.25rem">
    <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
        <button class="method-tab active" onclick="switchTab(this,'ew')">📱 E-Wallet</button>
        <button class="method-tab" onclick="switchTab(this,'bk')">🏦 Transfer Bank</button>
        <button class="method-tab" onclick="switchTab(this,'qr')">📲 QRIS</button>
    </div>

    <div id="tab-ew" class="method-grid">
    <?php foreach ($methods['ewallet'] as $nm=>$m): ?>
    <div class="mc" onclick="selectMethod('<?= addslashes($nm) ?>','<?= $m['color'] ?>','<?= $m['icon'] ?>','ewallet')" data-m="<?= $nm ?>">
        <div class="chk">✓</div><div style="font-size:1.75rem"><?= $m['icon'] ?></div>
        <div style="font-size:.78rem;font-weight:700"><?= htmlspecialchars($nm) ?></div>
    </div>
    <?php endforeach; ?>
    </div>

    <div id="tab-bk" class="method-grid" style="display:none">
    <?php foreach ($methods['bank'] as $nm=>$m): ?>
    <div class="mc" onclick="selectMethod('<?= addslashes($nm) ?>','<?= $m['color'] ?>','<?= $m['icon'] ?>','bank')" data-m="<?= $nm ?>">
        <div class="chk">✓</div><div style="font-size:1.75rem"><?= $m['icon'] ?></div>
        <div style="font-size:.78rem;font-weight:700"><?= htmlspecialchars($nm) ?></div>
    </div>
    <?php endforeach; ?>
    </div>

    <div id="tab-qr" style="display:none">
    <?php foreach ($methods['qris'] as $nm=>$m): ?>
    <div class="mc" style="flex-direction:row;justify-content:center;padding:1.125rem 1.5rem"
         onclick="selectMethod('<?= addslashes($nm) ?>','<?= $m['color'] ?>','<?= $m['icon'] ?>','qris')" data-m="<?= $nm ?>">
        <div class="chk">✓</div><span style="font-size:2rem">📲</span>
        <div><div style="font-weight:700">QRIS – Semua E-Wallet</div>
             <div style="font-size:.72rem;color:var(--gray)">GoPay, OVO, DANA, dll</div></div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- Detail rekening -->
    <div id="detailBox" style="display:none;margin-top:1rem">
        <div style="font-weight:800;font-size:.875rem;margin-bottom:.75rem">
            <span id="dIcon"></span> Rekening <span id="dName" style="color:var(--cyan)"></span>
        </div>
        <div id="accRows"></div>
        <div id="qrisBox" style="display:none;text-align:center;padding:1.5rem">
            <div style="width:160px;height:160px;background:#fff;border-radius:12px;margin:0 auto .875rem;
                display:flex;align-items:center;justify-content:center;font-size:4rem;
                box-shadow:0 4px 20px rgba(0,0,0,.3)">📲</div>
            <div style="font-size:.82rem;color:var(--gray)">Scan menggunakan e-wallet atau m-banking<br>
                <strong style="color:var(--white)">Nominal: <?= formatRupiah($price) ?></strong></div>
        </div>
        <div style="background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.18);
            border-radius:8px;padding:.75rem;margin-top:.75rem;font-size:.78rem;color:var(--gray);line-height:1.6">
            ⚡ Setelah transfer, klik <strong style="color:var(--white)">"Saya Sudah Transfer"</strong> untuk melanjutkan.
        </div>
    </div>

    <button class="btn-order" id="btnNext1" onclick="goStep2()" style="display:none;margin-top:.875rem">
        Saya Sudah Transfer →
    </button>
</div></div>
</div><!-- /p1 -->

<!-- STEP 2: Upload Bukti -->
<div id="p2" style="display:none">
<div class="card">
<div class="card-header" style="padding:1.125rem 1.25rem"><div style="font-weight:800">📸 Upload Bukti Pembayaran</div></div>
<div style="padding:1.25rem">
    <div class="timer-box">
        <span style="font-size:1.3rem">⏰</span>
        <div><div style="font-size:.78rem;color:var(--gray)">Selesaikan dalam</div>
             <div class="timer-count" id="timerDisplay">15:00</div></div>
    </div>

    <!-- Recap -->
    <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;margin-bottom:1.125rem;
        display:flex;justify-content:space-between;flex-wrap:wrap;gap:.75rem;font-size:.875rem">
        <div><div style="font-size:.72rem;color:var(--gray)">Metode</div><div style="font-weight:700" id="recapM">—</div></div>
        <div><div style="font-size:.72rem;color:var(--gray)">Tujuan</div><div style="font-weight:700" id="recapN">—</div></div>
        <div><div style="font-size:.72rem;color:var(--gray)">Nominal</div>
             <div style="font-weight:800;color:var(--cyan)"><?= formatRupiah($price) ?></div></div>
    </div>

    <!-- Upload -->
    <label class="upload-zone" id="uploadZone">
        <input type="file" id="fileInput" accept="image/*" onchange="handleFile(this)">
        <div id="uploadPlaceholder">
            <div style="font-size:2.5rem;margin-bottom:.75rem">📸</div>
            <div style="font-weight:700;margin-bottom:.3rem">Upload Bukti Transfer</div>
            <div style="font-size:.78rem;color:var(--gray)">Klik / drag & drop · JPG, PNG · Maks 5MB</div>
        </div>
        <div id="uploadPreview" style="display:none;position:relative">
            <img id="previewImg" style="max-width:100%;max-height:180px;object-fit:contain;border-radius:var(--radius);border:1px solid var(--border)" alt="">
            <button type="button" onclick="removeFile(event)"
                style="position:absolute;top:.4rem;right:.4rem;width:26px;height:26px;border-radius:50%;
                    background:var(--danger);color:#fff;border:none;cursor:pointer;font-size:.8rem">✕</button>
        </div>
    </label>

    <!-- Info tambahan -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.875rem">
        <div>
            <label style="display:block;font-size:.78rem;font-weight:600;color:var(--gray);margin-bottom:.35rem">👤 Nama Kamu *</label>
            <input type="text" id="custName" class="form-input"
                   placeholder="Nama lengkap"
                   value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>"
                   style="padding:.6rem .875rem">
        </div>
        <div>
            <label style="display:block;font-size:.78rem;font-weight:600;color:var(--gray);margin-bottom:.35rem">📱 WhatsApp *</label>
            <input type="tel" id="custWa" class="form-input"
                   placeholder="08xxxxxxxxxx"
                   style="padding:.6rem .875rem">
        </div>
    </div>
    <div style="margin-top:.75rem">
        <label style="display:block;font-size:.78rem;font-weight:600;color:var(--gray);margin-bottom:.35rem">📝 Catatan (opsional)</label>
        <textarea id="payNote" class="form-input" rows="2"
            style="width:100%;resize:vertical;padding:.6rem .875rem"
            placeholder="Contoh: Transfer BCA jam 14.30"></textarea>
    </div>

    <!-- Promo -->
    <div style="margin-top:.875rem">
        <label style="display:block;font-size:.78rem;font-weight:600;color:var(--gray);margin-bottom:.35rem">🎟️ Kode Promo</label>
        <div class="promo-inp">
            <input type="text" id="promoInput" class="form-input" placeholder="MLBB10" style="padding:.55rem .875rem;text-transform:uppercase">
            <button class="btn btn-ghost btn-sm" onclick="applyPromo()" style="white-space:nowrap">Gunakan</button>
        </div>
        <div id="promoMsg" style="font-size:.75rem;margin-top:.35rem"></div>
    </div>

    <div style="display:flex;gap:.625rem;margin-top:1.125rem">
        <button class="btn-outline" onclick="goStep1()" style="padding:.875rem 1.25rem;flex-shrink:0">← Kembali</button>
        <button class="btn-confirm" id="btnConfirm" onclick="submitPayment()">✅ Konfirmasi Pembayaran</button>
    </div>
</div></div>
</div><!-- /p2 -->

<!-- STEP 3: Sukses -->
<div id="p3" style="display:none">
<div class="card">
<div class="success-wrap" id="successWrap">
    <div class="success-circle">✅</div>
    <h2 style="font-weight:900;font-size:1.35rem;margin-bottom:.5rem">Pembayaran Dikonfirmasi!</h2>
    <p style="color:var(--gray);margin-bottom:1.5rem;line-height:1.7;font-size:.9rem">
        Bukti pembayaran kamu sudah kami terima.<br>
        Item akan diproses dalam <strong style="color:var(--cyan)">1–5 menit</strong>.
    </p>
    <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem">
        <div style="font-size:.75rem;color:var(--gray);margin-bottom:.3rem">ID Order Kamu</div>
        <div id="finalOrderId" style="font-size:1.2rem;font-weight:900;color:var(--cyan);letter-spacing:1px;font-family:monospace">—</div>
        <div style="font-size:.72rem;color:var(--gray);margin-top:.3rem">Screenshot ID ini untuk tracking</div>
    </div>
    <?php foreach ([['✅','Admin verifikasi pembayaran','1–3 menit'],['🎮','Item diproses ke akun','Otomatis'],['📲','Konfirmasi via Live Chat','Kamu diberitahu']] as [$ic,$t,$s]): ?>
    <div style="display:flex;align-items:flex-start;gap:.75rem;text-align:left;margin-bottom:.625rem">
        <span style="font-size:1.1rem;flex-shrink:0"><?= $ic ?></span>
        <div><div style="font-size:.84rem;font-weight:600"><?= $t ?></div><div style="font-size:.73rem;color:var(--gray)"><?= $s ?></div></div>
    </div>
    <?php endforeach; ?>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
        <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank"
           class="btn-primary" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">💬 Chat Admin</a>
        <a href="products.php" class="btn-outline"
           style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">🎮 Beli Lagi</a>
    </div>
</div>
</div>
</div><!-- /p3 -->

</div><!-- LEFT -->

<!-- RIGHT: Summary -->
<div>
<div class="pay-summary">
    <div class="sum-hdr">
        <?php if (!empty($game_obj['img'])): ?>
        <img src="<?= htmlspecialchars($game_obj['img']) ?>" alt=""
             style="width:52px;height:52px;border-radius:10px;object-fit:cover;flex-shrink:0"
             onerror="this.style.display='none'">
        <?php else: ?>
        <div style="width:52px;height:52px;border-radius:10px;background:rgba(37,99,235,.2);
            display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0"><?= htmlspecialchars($game_icon) ?></div>
        <?php endif; ?>
        <div>
            <div style="font-weight:800;font-size:.95rem"><?= htmlspecialchars($game_name) ?></div>
            <div style="font-size:.75rem;color:rgba(255,255,255,.55)"><?= htmlspecialchars($currency) ?></div>
        </div>
    </div>
    <div class="sum-body">
        <div class="sum-row"><span style="color:var(--gray)">Paket</span><span style="font-weight:600"><?= htmlspecialchars($pkg_amount).' '.htmlspecialchars($currency) ?></span></div>
        <?php if ($user_id_game): ?>
        <div class="sum-row"><span style="color:var(--gray)">User ID</span><span style="font-family:monospace;font-size:.82rem"><?= htmlspecialchars($user_id_game) ?></span></div>
        <?php endif; ?>
        <div class="sum-row"><span style="color:var(--gray)">Biaya Layanan</span><span style="color:var(--success);font-weight:600">GRATIS</span></div>
        <div class="sum-row" id="discountRow" style="display:none">
            <span style="color:var(--success)">🎟️ Diskon</span>
            <span id="discountAmt" style="color:var(--success);font-weight:700">—</span>
        </div>
        <div class="sum-row total">
            <span>Total Bayar</span>
            <span class="amount" id="totalDisplay"><?= formatRupiah($price) ?></span>
        </div>

        <!-- Selected method recap -->
        <div id="methodRecap" style="display:none;margin-top:.875rem;background:var(--bg3);border-radius:var(--radius);padding:.875rem">
            <div style="font-size:.72rem;color:var(--gray);margin-bottom:.25rem">Metode Terpilih</div>
            <div style="font-weight:800;display:flex;align-items:center;gap:.4rem">
                <span id="rIcon"></span><span id="rName"></span>
            </div>
        </div>

        <!-- Trust badges -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:1rem">
            <?php foreach ([['🔒','100% Aman'],['⚡','Proses Cepat'],['💰','Garansi Refund'],['🎧','24/7 Support']] as [$ic,$lb]): ?>
            <div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:.5rem;text-align:center;font-size:.7rem;color:var(--gray)">
                <div style="font-size:1rem;margin-bottom:.15rem"><?= $ic ?></div><?= $lb ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div style="margin-top:1rem;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1rem;text-align:center">
    <div style="font-size:.8rem;color:var(--gray);margin-bottom:.5rem">Butuh bantuan?</div>
    <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Halo+admin,+saya+mau+konfirmasi+pembayaran+<?= urlencode($game_name) ?>"
       target="_blank" style="display:inline-flex;align-items:center;gap:.5rem;background:#25D366;
           color:#fff;padding:.6rem 1.25rem;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none">
        💬 Chat Admin WA
    </a>
</div>
</div>
</div><!-- RIGHT -->
</div></div><!-- pay-wrap / pay-page -->

<script>
const METHODS  = <?= json_encode($methods, JSON_UNESCAPED_UNICODE) ?>;
const PRICE    = <?= $price ?>;
const GAME     = <?= json_encode($game_name) ?>;
const PKG      = <?= json_encode($pkg_amount) ?>;
const CURR     = <?= json_encode($currency) ?>;
const UID_GAME = <?= json_encode($user_id_game) ?>;
const WA_NUM   = '<?= WHATSAPP_NUMBER ?>';
const fmtRp    = n => 'Rp '+Number(n).toLocaleString('id-ID');

let selMethod=null, selType=null, uploadFile=null, timerTmr=null, timerSec=900, promoDisc=0;

/* ── Tabs ── */
function switchTab(btn, tab) {
    document.querySelectorAll('.method-tab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    ['ew','bk','qr'].forEach(t => document.getElementById('tab-'+t).style.display = t===tab?'':'none');
    if (!['ew','bk','qr'].includes(tab)) return;
    // fix: qris tab id is tab-qr
}
// init method-tab styles
document.querySelectorAll('.method-tab').forEach(b=>{
    b.style.cssText='padding:.45rem 1rem;border-radius:8px;border:1.5px solid var(--border);background:transparent;color:var(--gray);font-size:.8rem;font-weight:600;cursor:pointer;transition:var(--transition);font-family:inherit';
    b.addEventListener('mouseenter',()=>{if(!b.classList.contains('active')){b.style.borderColor='var(--blue)';b.style.color='var(--white)'}});
    b.addEventListener('mouseleave',()=>{if(!b.classList.contains('active')){b.style.borderColor='var(--border)';b.style.color='var(--gray)'}});
});
function refreshTabStyle(){document.querySelectorAll('.method-tab').forEach(b=>{
    b.style.background=b.classList.contains('active')?'rgba(37,99,235,.15)':'transparent';
    b.style.borderColor=b.classList.contains('active')?'var(--blue)':'var(--border)';
    b.style.color=b.classList.contains('active')?'var(--cyan)':'var(--gray)';
});}
document.querySelector('.method-tab').classList.add('active'); refreshTabStyle();
function switchTab(btn,tab){
    document.querySelectorAll('.method-tab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); refreshTabStyle();
    document.getElementById('tab-ew').style.display=tab==='ew'?'grid':'none';
    document.getElementById('tab-bk').style.display=tab==='bk'?'grid':'none';
    document.getElementById('tab-qr').style.display=tab==='qr'?'block':'none';
}

/* ── Select method ── */
function selectMethod(name, color, icon, type) {
    document.querySelectorAll('.mc').forEach(c=>c.classList.remove('sel'));
    document.querySelector(`.mc[data-m="${name}"]`)?.classList.add('sel');
    selMethod=name; selType=type;
    const m=METHODS[type]?.[name];
    if(!m) return;
    const box=document.getElementById('detailBox');
    box.style.display='block';
    document.getElementById('dIcon').textContent=icon;
    document.getElementById('dName').textContent=name;
    const qb=document.getElementById('qrisBox');
    const rows=document.getElementById('accRows');
    if(type==='qris'){qb.style.display='block';rows.style.display='none';}
    else{
        qb.style.display='none';rows.style.display='block';
        rows.innerHTML=`
            <div class="acc-box">
                <div>
                    <div style="font-size:.72rem;color:var(--gray);margin-bottom:.2rem">${type==='bank'?'Nomor Rekening':'Nomor E-Wallet'}</div>
                    <div style="font-size:1.1rem;font-weight:800">${m.number||'—'}</div>
                    <div style="font-size:.75rem;color:var(--gray)">a.n. ${m.name||'—'}</div>
                </div>
                <button class="copy-btn" onclick="copyText('${m.number}',this)">📋 Salin</button>
            </div>
            <div class="acc-box">
                <div>
                    <div style="font-size:.72rem;color:var(--gray);margin-bottom:.2rem">Nominal Transfer (TEPAT)</div>
                    <div style="font-size:1.05rem;font-weight:800;color:var(--cyan)">${fmtRp(PRICE)}</div>
                </div>
                <button class="copy-btn" onclick="copyText('${PRICE}',this)">📋 Salin</button>
            </div>`;
    }
    document.getElementById('btnNext1').style.display='flex';
    document.getElementById('methodRecap').style.display='block';
    document.getElementById('rIcon').textContent=icon;
    document.getElementById('rName').textContent=name;
}

function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(()=>{
        btn.textContent='✅ Disalin!'; btn.classList.add('ok');
        setTimeout(()=>{btn.textContent='📋 Salin';btn.classList.remove('ok');},2000);
        showToast('✅ Berhasil disalin!','success');
    });
}

/* ── Steps ── */
function setStep(n){
    [1,2,3,4].forEach(i=>{
        const c=document.getElementById('sc'+i);
        const l=document.getElementById('sl'+i);
        if(!c)return;
        if(i<n){c.className='sc done';c.textContent='✓';l.className='sl done';}
        else if(i===n){c.className='sc active';c.textContent=i<4?i:'✓';l.className='sl active';}
        else{c.className='sc wait';c.textContent=i<4?i:'✓';l.className='sl wait';}
        if(i<4){const ln=document.getElementById('sline'+i);if(ln)ln.className='sline'+(i<n?' done':'');}
    });
}
function goStep1(){
    setStep(1);
    document.getElementById('p1').style.display='';
    document.getElementById('p2').style.display='none';
    document.getElementById('p3').style.display='none';
    clearInterval(timerTmr);
}
function goStep2(){
    if(!selMethod){showToast('⚠️ Pilih metode pembayaran dulu!','error');return;}
    setStep(2);
    document.getElementById('p1').style.display='none';
    document.getElementById('p2').style.display='';
    document.getElementById('p3').style.display='none';
    const m=METHODS[selType]?.[selMethod];
    document.getElementById('recapM').textContent=selMethod;
    document.getElementById('recapN').textContent=selType==='qris'?'Scan QR Code':(m?.number||'—');
    startTimer();
    window.scrollTo({top:0,behavior:'smooth'});
}
function goStep3(orderId){
    setStep(4);
    document.getElementById('p1').style.display='none';
    document.getElementById('p2').style.display='none';
    document.getElementById('p3').style.display='';
    document.getElementById('successWrap').style.display='block';
    document.getElementById('finalOrderId').textContent=orderId;
    clearInterval(timerTmr);
    window.scrollTo({top:0,behavior:'smooth'});
}

/* ── Timer ── */
function startTimer(){
    timerSec=900; clearInterval(timerTmr); updateTimer();
    timerTmr=setInterval(()=>{timerSec--;updateTimer();
        if(timerSec<=0){clearInterval(timerTmr);showToast('⏰ Waktu habis! Mulai ulang.','error');setTimeout(goStep1,2000);}
    },1000);
}
function updateTimer(){
    const m=Math.floor(timerSec/60),s=timerSec%60;
    const el=document.getElementById('timerDisplay');
    el.textContent=String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    el.classList.toggle('urgent',timerSec<120);
}

/* ── Upload ── */
function handleFile(inp){
    const f=inp.files[0]; if(!f)return;
    if(f.size>5*1024*1024){showToast('⚠️ File max 5MB!','error');return;}
    if(!f.type.startsWith('image/')){showToast('⚠️ Harus file gambar!','error');return;}
    uploadFile=f;
    const r=new FileReader();
    r.onload=e=>{
        document.getElementById('previewImg').src=e.target.result;
        document.getElementById('uploadPreview').style.display='block';
        document.getElementById('uploadPlaceholder').style.display='none';
    };
    r.readAsDataURL(f);
}
function removeFile(e){
    e.preventDefault();e.stopPropagation();
    uploadFile=null;
    document.getElementById('fileInput').value='';
    document.getElementById('uploadPreview').style.display='none';
    document.getElementById('uploadPlaceholder').style.display='block';
}
const uz=document.getElementById('uploadZone');
uz.addEventListener('dragover',e=>{e.preventDefault();uz.classList.add('drag');});
uz.addEventListener('dragleave',()=>uz.classList.remove('drag'));
uz.addEventListener('drop',e=>{e.preventDefault();uz.classList.remove('drag');
    const f=e.dataTransfer.files[0];if(f){document.getElementById('fileInput').files=e.dataTransfer.files;handleFile(document.getElementById('fileInput'));}
});

/* ── Promo ── */
function applyPromo(){
    const code=document.getElementById('promoInput').value.trim().toUpperCase();
    const msg=document.getElementById('promoMsg');
    if(!code){msg.innerHTML='<span style="color:var(--danger)">⚠️ Masukkan kode promo</span>';return;}
    fetch(`includes/promo_check.php?code=${encodeURIComponent(code)}&price=${PRICE}`)
        .then(r=>r.json()).then(d=>{
            if(d.valid){
                promoDisc=d.discount;
                const total=PRICE-promoDisc;
                msg.innerHTML=`<span style="color:var(--success)">✅ ${d.msg}</span>`;
                document.getElementById('discountRow').style.display='flex';
                document.getElementById('discountAmt').textContent='-'+fmtRp(promoDisc);
                document.getElementById('totalDisplay').textContent=fmtRp(total);
            } else {
                promoDisc=0;
                msg.innerHTML=`<span style="color:var(--danger)">❌ ${d.msg}</span>`;
                document.getElementById('discountRow').style.display='none';
                document.getElementById('totalDisplay').textContent=fmtRp(PRICE);
            }
        }).catch(()=>{msg.innerHTML='<span style="color:var(--danger)">⚠️ Gagal cek promo</span>';});
}

/* ── Submit ── */
function submitPayment(){
    const custName=document.getElementById('custName').value.trim();
    const custWa=document.getElementById('custWa').value.trim();
    if(!uploadFile){showToast('⚠️ Upload bukti pembayaran dulu!','error');document.getElementById('uploadZone').style.borderColor='var(--danger)';setTimeout(()=>document.getElementById('uploadZone').style.borderColor='',2000);return;}
    if(!custName){showToast('⚠️ Masukkan nama kamu!','error');document.getElementById('custName').focus();return;}
    if(!custWa){showToast('⚠️ Masukkan nomor WhatsApp!','error');document.getElementById('custWa').focus();return;}

    const btn=document.getElementById('btnConfirm');
    btn.disabled=true;btn.innerHTML='<span>⏳</span> Memproses...';

    const fd=new FormData();
    fd.append('confirm_payment','1');
    fd.append('payment_method', selMethod);
    fd.append('user_id_game',   UID_GAME||'—');
    fd.append('customer_name',  custName);
    fd.append('customer_wa',    custWa);
    fd.append('note',           document.getElementById('payNote').value.trim());
    fd.append('promo_code',     document.getElementById('promoInput').value.trim().toUpperCase());
    fd.append('proof',          uploadFile, uploadFile.name);

    fetch('pembayaran.php', {method:'POST', body:fd})
        .then(r=>r.json()).then(d=>{
            if(d.success){
                // Kirim notif WA
                const waMsg=`✅ *KONFIRMASI PEMBAYARAN*\n\n🆔 Order: ${d.order_id}\n🎮 Game: ${GAME}\n💎 Paket: ${PKG} ${CURR}\n🆔 User ID: ${UID_GAME||'—'}\n💳 Metode: ${selMethod}\n💰 Total: ${fmtRp(d.total)}\n👤 Nama: ${custName}\n📱 WA: ${custWa}\n\nBukti sudah diupload. Mohon verifikasi. 🙏`;
                setTimeout(()=>window.open('https://wa.me/'+WA_NUM+'?text='+encodeURIComponent(waMsg),'_blank'),500);
                goStep3(d.order_id);
            } else {
                showToast('⚠️ '+(d.msg||'Gagal memproses'),'error');
                btn.disabled=false;btn.innerHTML='✅ Konfirmasi Pembayaran';
            }
        }).catch(()=>{showToast('⚠️ Koneksi bermasalah, coba lagi.','error');btn.disabled=false;btn.innerHTML='✅ Konfirmasi Pembayaran';});
}

const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
