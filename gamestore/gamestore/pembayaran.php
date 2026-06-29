<?php
require_once 'includes/config.php';
session_start();

// Ambil data order dari query string atau session
$game_name  = $_GET['game']    ?? $_SESSION['pay_game']    ?? '';
$game_slug  = $_GET['slug']    ?? $_SESSION['pay_slug']    ?? '';
$currency   = $_GET['cur']     ?? $_SESSION['pay_cur']     ?? '';
$pkg_amount = $_GET['pkg']     ?? $_SESSION['pay_pkg']     ?? '';
$price      = (int)($_GET['price']  ?? $_SESSION['pay_price']  ?? 0);
$user_id    = $_GET['uid']     ?? $_SESSION['pay_uid']     ?? '';
$game_icon  = $_GET['icon']    ?? $_SESSION['pay_icon']    ?? '🎮';

// Validasi
if (!$game_name || !$price || !$pkg_amount) {
    header('Location: products.php');
    exit;
}

// Simpan ke session
$_SESSION['pay_game']  = $game_name;
$_SESSION['pay_slug']  = $game_slug;
$_SESSION['pay_cur']   = $currency;
$_SESSION['pay_pkg']   = $pkg_amount;
$_SESSION['pay_price'] = $price;
$_SESSION['pay_uid']   = $user_id;
$_SESSION['pay_icon']  = $game_icon;

$methods = unserialize(PAYMENT_METHODS);
$page_title = 'Pembayaran — ' . $game_name;
require_once 'includes/header.php';
?>

<style>
/* ── Payment Page Styles ── */
.pay-page {
    padding: 100px 0 4rem;
    min-height: 100vh;
}
.pay-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 1.75rem;
    align-items: start;
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Steps */
.pay-steps {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 2rem;
    max-width: 1100px;
    margin-left: auto;
    margin-right: auto;
    padding: 0 1.5rem;
}
.pay-step {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex: 1;
}
.pay-step:last-child { flex: 0; }
.step-circle {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .82rem;
    font-weight: 800;
    flex-shrink: 0;
    transition: .3s;
}
.step-circle.done    { background: var(--success); color: #fff; }
.step-circle.active  { background: var(--blue); color: #fff; box-shadow: 0 0 16px rgba(37,99,235,.5); }
.step-circle.waiting { background: var(--bg3); color: var(--gray); border: 1.5px solid var(--border); }
.step-label { font-size: .75rem; font-weight: 600; white-space: nowrap; }
.step-label.active  { color: var(--white); }
.step-label.waiting { color: var(--gray); }
.step-label.done    { color: var(--success); }
.step-line {
    flex: 1;
    height: 2px;
    background: var(--border);
    margin: 0 .5rem;
    border-radius: 1px;
    transition: .3s;
}
.step-line.done { background: var(--success); }

/* Payment method tabs */
.method-tabs {
    display: flex;
    gap: .5rem;
    margin-bottom: 1.125rem;
    flex-wrap: wrap;
}
.method-tab {
    padding: .45rem 1rem;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: transparent;
    color: var(--gray);
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    font-family: inherit;
}
.method-tab:hover { border-color: var(--blue); color: var(--white); }
.method-tab.active {
    background: rgba(37,99,235,.15);
    border-color: var(--blue);
    color: var(--cyan);
}

/* Payment method cards */
.method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: .625rem;
    margin-bottom: 1.25rem;
}
.method-card {
    background: var(--bg3);
    border: 2px solid var(--border);
    border-radius: var(--radius);
    padding: 1rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
    position: relative;
}
.method-card:hover { border-color: var(--blue); transform: translateY(-2px); }
.method-card.selected {
    border-color: var(--blue);
    background: rgba(37,99,235,.1);
    box-shadow: 0 0 0 3px rgba(37,99,235,.15);
}
.method-card .check {
    position: absolute;
    top: .5rem;
    right: .5rem;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--blue);
    color: #fff;
    font-size: .65rem;
    display: none;
    align-items: center;
    justify-content: center;
}
.method-card.selected .check { display: flex; }
.method-icon { font-size: 1.75rem; }
.method-name { font-size: .78rem; font-weight: 700; color: var(--white); text-align: center; }

/* Payment detail box */
.pay-detail-box {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    display: none;
}
.pay-detail-box.show { display: block; animation: fadeInUp .25s ease; }
.pay-account-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .875rem 1rem;
    background: var(--bg2);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    margin-bottom: .625rem;
}
.pay-account-label { font-size: .75rem; color: var(--gray); margin-bottom: .2rem; }
.pay-account-number { font-size: 1.1rem; font-weight: 800; color: var(--white); letter-spacing: .5px; }
.pay-account-name { font-size: .75rem; color: var(--gray); margin-top: .1rem; }
.copy-btn {
    background: rgba(37,99,235,.15);
    border: 1px solid rgba(37,99,235,.3);
    color: var(--cyan);
    padding: .45rem .875rem;
    border-radius: 6px;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    font-family: inherit;
    white-space: nowrap;
    flex-shrink: 0;
}
.copy-btn:hover { background: rgba(37,99,235,.25); }
.copy-btn.copied { background: rgba(34,197,94,.15); border-color: rgba(34,197,94,.3); color: var(--success); }

/* QRIS */
.qris-box {
    text-align: center;
    padding: 1.5rem;
}
.qris-code {
    width: 180px;
    height: 180px;
    margin: 0 auto 1rem;
    background: #fff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
}
.qris-pattern {
    position: absolute;
    inset: 0;
    background-image:
        repeating-linear-gradient(0deg, rgba(0,0,0,.1) 0px, rgba(0,0,0,.1) 2px, transparent 2px, transparent 8px),
        repeating-linear-gradient(90deg, rgba(0,0,0,.1) 0px, rgba(0,0,0,.1) 2px, transparent 2px, transparent 8px);
    opacity: .3;
}

/* Upload bukti */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-lg);
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: var(--blue);
    background: rgba(37,99,235,.04);
}
.upload-zone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-preview {
    display: none;
    position: relative;
    margin-top: 1rem;
}
.upload-preview img {
    max-width: 100%;
    max-height: 200px;
    object-fit: contain;
    border-radius: var(--radius);
    border: 1px solid var(--border);
}
.upload-remove {
    position: absolute;
    top: .5rem;
    right: .5rem;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--danger);
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: .8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Timer */
.pay-timer {
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.25);
    border-radius: var(--radius);
    padding: .875rem 1.125rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: 1rem;
}
.timer-icon { font-size: 1.3rem; }
.timer-label { font-size: .78rem; color: var(--gray); }
.timer-count { font-size: 1.25rem; font-weight: 900; color: var(--warning); font-variant-numeric: tabular-nums; }
.timer-count.urgent { color: var(--danger); animation: timerPulse 1s infinite; }
@keyframes timerPulse { 0%,100%{opacity:1} 50%{opacity:.5} }

/* Order summary card */
.pay-summary {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    position: sticky;
    top: 88px;
}
.pay-summary-header {
    background: linear-gradient(135deg, #0f1f4a, #1a3080);
    padding: 1.125rem 1.25rem;
    display: flex;
    align-items: center;
    gap: .875rem;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.pay-summary-game-img {
    width: 54px;
    height: 54px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    background: var(--bg3);
}
.pay-summary-game-name { font-weight: 800; font-size: .95rem; }
.pay-summary-game-cur  { font-size: .75rem; color: rgba(255,255,255,.6); }

.pay-summary-body { padding: 1.125rem 1.25rem; }
.sum-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .875rem;
    padding: .4rem 0;
}
.sum-row.divider { border-top: 1px solid var(--border); margin-top: .5rem; padding-top: .875rem; }
.sum-row.total { font-size: 1.05rem; font-weight: 900; }
.sum-row.total .amount { color: var(--cyan); font-size: 1.2rem; }
.sum-label { color: var(--gray); }
.sum-val { font-weight: 600; color: var(--white); }

/* Confirm btn */
.btn-confirm {
    width: 100%;
    background: linear-gradient(135deg, #22C55E, #16A34A);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    padding: 1rem;
    font-size: .975rem;
    font-weight: 800;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    font-family: inherit;
    box-shadow: 0 4px 20px rgba(34,197,94,.35);
    margin-top: 1rem;
}
.btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(34,197,94,.45); }
.btn-confirm:disabled { opacity: .5; cursor: default; transform: none; }

/* Success overlay */
.pay-success {
    display: none;
    text-align: center;
    padding: 2.5rem 1.5rem;
}
.success-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(34,197,94,.15);
    border: 3px solid var(--success);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 1.25rem;
    animation: successPop .5s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes successPop { from{transform:scale(0)} to{transform:scale(1)} }

@media(max-width:768px) {
    .pay-grid { grid-template-columns: 1fr; }
    .pay-summary { position: static; }
    .method-grid { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:480px) {
    .method-grid { grid-template-columns: repeat(2, 1fr); }
    .pay-steps { overflow-x: auto; padding-bottom: .5rem; }
}
</style>

<div class="pay-page">

    <!-- Steps indicator -->
    <div class="pay-steps" id="stepsBar">
        <div class="pay-step">
            <div class="step-circle active" id="sc1">1</div>
            <div class="step-label active" id="sl1">Pilih Bayar</div>
        </div>
        <div class="step-line" id="sline1"></div>
        <div class="pay-step">
            <div class="step-circle waiting" id="sc2">2</div>
            <div class="step-label waiting" id="sl2">Transfer</div>
        </div>
        <div class="step-line" id="sline2"></div>
        <div class="pay-step">
            <div class="step-circle waiting" id="sc3">3</div>
            <div class="step-label waiting" id="sl3">Konfirmasi</div>
        </div>
        <div class="step-line" id="sline3"></div>
        <div class="pay-step">
            <div class="step-circle waiting" id="sc4">✓</div>
            <div class="step-label waiting" id="sl4">Selesai</div>
        </div>
    </div>

    <div class="pay-grid">

        <!-- LEFT: Payment form -->
        <div>

            <!-- ── STEP 1: Pilih Metode ── -->
            <div id="step1Panel">
                <div class="card" style="margin-bottom:1rem">
                    <div class="card-header" style="padding:1.125rem 1.25rem">
                        <div style="font-weight:800;font-size:.975rem">💳 Pilih Metode Pembayaran</div>
                    </div>
                    <div style="padding:1.25rem">
                        <!-- Tabs -->
                        <div class="method-tabs">
                            <button class="method-tab active" onclick="switchTab(this,'ewallet')">📱 E-Wallet</button>
                            <button class="method-tab" onclick="switchTab(this,'bank')">🏦 Transfer Bank</button>
                            <button class="method-tab" onclick="switchTab(this,'qris')">📲 QRIS</button>
                        </div>

                        <!-- E-Wallet -->
                        <div class="method-grid" id="tab-ewallet">
                            <?php foreach ($methods['ewallet'] as $name => $m): ?>
                            <div class="method-card" onclick="selectMethod('<?= $name ?>','<?= $m['color'] ?>','<?= $m['icon'] ?>','ewallet')"
                                 data-method="<?= $name ?>" data-type="ewallet">
                                <div class="check">✓</div>
                                <div class="method-icon"><?= $m['icon'] ?></div>
                                <div class="method-name"><?= $name ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Bank -->
                        <div class="method-grid" id="tab-bank" style="display:none">
                            <?php foreach ($methods['bank'] as $name => $m): ?>
                            <div class="method-card" onclick="selectMethod('<?= $name ?>','<?= $m['color'] ?>','<?= $m['icon'] ?>','bank')"
                                 data-method="<?= $name ?>" data-type="bank">
                                <div class="check">✓</div>
                                <div class="method-icon"><?= $m['icon'] ?></div>
                                <div class="method-name"><?= $name ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- QRIS -->
                        <div class="method-grid" id="tab-qris" style="display:none">
                            <div class="method-card" onclick="selectMethod('QRIS','#E31837','📱','qris')"
                                 data-method="QRIS" data-type="qris"
                                 style="grid-column:1/-1;flex-direction:row;justify-content:center;padding:1.25rem 2rem">
                                <div class="check">✓</div>
                                <div class="method-icon">📲</div>
                                <div>
                                    <div class="method-name" style="text-align:left;font-size:.9rem">QRIS — Semua E-Wallet</div>
                                    <div style="font-size:.72rem;color:var(--gray);margin-top:.2rem">GoPay, OVO, DANA, ShopeePay, dll</div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment detail (tampil setelah pilih) -->
                        <div class="pay-detail-box" id="payDetailBox">
                            <div style="font-weight:800;font-size:.875rem;margin-bottom:.875rem;
                                display:flex;align-items:center;gap:.5rem">
                                <span id="detailIcon"></span>
                                Informasi Rekening <span id="detailMethodName" style="color:var(--cyan)"></span>
                            </div>

                            <!-- Nomor rekening -->
                            <div id="accountInfo">
                                <div class="pay-account-row">
                                    <div>
                                        <div class="pay-account-label" id="accTypeLabel">Nomor E-Wallet</div>
                                        <div class="pay-account-number" id="accNumber">—</div>
                                        <div class="pay-account-name" id="accName">—</div>
                                    </div>
                                    <button class="copy-btn" onclick="copyNumber()">📋 Salin</button>
                                </div>
                            </div>

                            <!-- Jumlah transfer -->
                            <div class="pay-account-row" style="margin-top:.625rem">
                                <div>
                                    <div class="pay-account-label">Jumlah Transfer (TEPAT)</div>
                                    <div class="pay-account-number" id="accAmount" style="color:var(--cyan)">
                                        <?= formatRupiah($price) ?>
                                    </div>
                                    <div class="pay-account-label" style="margin-top:.25rem">
                                        ⚠️ Transfer sesuai nominal agar mudah diverifikasi
                                    </div>
                                </div>
                                <button class="copy-btn" onclick="copyAmount()">📋 Salin</button>
                            </div>

                            <!-- QRIS box -->
                            <div class="qris-box" id="qrisBox" style="display:none">
                                <div class="qris-code">
                                    <div class="qris-pattern"></div>
                                    <span>📲</span>
                                </div>
                                <div style="font-size:.82rem;color:var(--gray);line-height:1.6">
                                    Scan QR code di atas menggunakan aplikasi e-wallet atau mobile banking kamu.<br>
                                    <strong style="color:var(--white)">Pastikan nominal sesuai: <?= formatRupiah($price) ?></strong>
                                </div>
                            </div>

                            <div style="background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.2);
                                border-radius:8px;padding:.75rem;margin-top:.75rem;font-size:.78rem;color:var(--gray);
                                line-height:1.6">
                                ⚡ Setelah transfer, klik <strong style="color:var(--white)">"Lanjut ke Konfirmasi"</strong> di bawah
                                dan upload bukti pembayaran. Item akan diproses dalam 1-5 menit.
                            </div>
                        </div>

                        <button class="btn-order" id="btnStep1" onclick="goStep2()"
                            style="display:none;margin-top:.5rem">
                            Saya Sudah Transfer →
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── STEP 2: Upload Bukti ── -->
            <div id="step2Panel" style="display:none">
                <div class="card" style="margin-bottom:1rem">
                    <div class="card-header" style="padding:1.125rem 1.25rem">
                        <div style="font-weight:800;font-size:.975rem">📸 Upload Bukti Pembayaran</div>
                    </div>
                    <div style="padding:1.25rem">

                        <!-- Timer -->
                        <div class="pay-timer">
                            <span class="timer-icon">⏰</span>
                            <div>
                                <div class="timer-label">Selesaikan pembayaran dalam</div>
                                <div class="timer-count" id="timerCount">15:00</div>
                            </div>
                        </div>

                        <!-- Recap pembayaran -->
                        <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;
                            margin-bottom:1.25rem;display:flex;justify-content:space-between;
                            align-items:center;flex-wrap:wrap;gap:.75rem">
                            <div>
                                <div style="font-size:.75rem;color:var(--gray)">Metode</div>
                                <div style="font-weight:800;font-size:.9rem" id="recapMethod">—</div>
                            </div>
                            <div>
                                <div style="font-size:.75rem;color:var(--gray)">Tujuan</div>
                                <div style="font-weight:700;font-size:.85rem" id="recapNumber">—</div>
                            </div>
                            <div>
                                <div style="font-size:.75rem;color:var(--gray)">Jumlah</div>
                                <div style="font-weight:800;color:var(--cyan);font-size:.95rem"><?= formatRupiah($price) ?></div>
                            </div>
                        </div>

                        <!-- Upload zone -->
                        <label class="upload-zone" id="uploadZone">
                            <input type="file" id="proofFile" accept="image/*" onchange="handleUpload(this)">
                            <div id="uploadPlaceholder">
                                <div style="font-size:2.5rem;margin-bottom:.75rem">📸</div>
                                <div style="font-weight:700;margin-bottom:.375rem">Upload Bukti Transfer</div>
                                <div style="font-size:.78rem;color:var(--gray)">
                                    Klik atau drag & drop foto di sini<br>
                                    Format: JPG, PNG, HEIC — Maks 5MB
                                </div>
                            </div>
                            <div class="upload-preview" id="uploadPreview">
                                <img id="previewImg" src="" alt="Bukti bayar">
                                <button type="button" class="upload-remove" onclick="removeUpload(event)">✕</button>
                            </div>
                        </label>

                        <!-- Catatan -->
                        <div style="margin-top:1rem">
                            <label style="display:block;font-size:.8rem;font-weight:600;color:var(--gray);margin-bottom:.4rem">
                                📝 Catatan (opsional)
                            </label>
                            <textarea id="payNote" class="form-input"
                                style="width:100%;resize:vertical;min-height:70px;padding:.65rem .875rem"
                                placeholder="Contoh: Sudah transfer via BCA mobile jam 14.30"></textarea>
                        </div>

                        <div style="display:flex;gap:.625rem;margin-top:1.125rem">
                            <button class="btn-outline" onclick="goStep1()"
                                style="padding:.875rem 1.25rem;flex-shrink:0">← Kembali</button>
                            <button class="btn-confirm" id="btnConfirm" onclick="submitPayment()">
                                ✅ Konfirmasi Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── STEP 3: Menunggu Verifikasi ── -->
            <div id="step3Panel" style="display:none">
                <div class="card">
                    <div class="pay-success" id="successPanel">
                        <div class="success-circle">✅</div>
                        <h2 style="font-weight:900;font-size:1.35rem;margin-bottom:.5rem">
                            Pembayaran Dikonfirmasi!
                        </h2>
                        <p style="color:var(--gray);margin-bottom:1.5rem;line-height:1.7;font-size:.9rem">
                            Bukti pembayaran kamu sudah kami terima.<br>
                            Item sedang diproses oleh admin dan akan masuk dalam <strong style="color:var(--cyan)">1–5 menit</strong>.
                        </p>

                        <!-- Order ID -->
                        <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;margin-bottom:1.25rem">
                            <div style="font-size:.75rem;color:var(--gray);margin-bottom:.3rem">ID Order Kamu</div>
                            <div style="font-size:1.2rem;font-weight:900;color:var(--cyan);
                                letter-spacing:1px;font-family:monospace" id="finalOrderId">—</div>
                            <div style="font-size:.72rem;color:var(--gray);margin-top:.3rem">
                                Simpan ID ini untuk tracking order kamu
                            </div>
                        </div>

                        <!-- What's next -->
                        <div style="text-align:left;background:var(--bg3);border-radius:var(--radius);
                            padding:1rem;margin-bottom:1.5rem">
                            <div style="font-weight:700;font-size:.82rem;margin-bottom:.75rem;color:var(--gray2);
                                text-transform:uppercase;letter-spacing:.5px">Langkah Selanjutnya</div>
                            <?php foreach ([
                                ['✅','Admin verifikasi pembayaran kamu','Biasanya 1-3 menit'],
                                ['🎮','Item diproses ke akun game','Otomatis setelah verifikasi'],
                                ['📲','Konfirmasi dikirim via Live Chat','Kamu akan diberitahu'],
                            ] as [$ic,$title,$sub]): ?>
                            <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.625rem">
                                <span style="font-size:1.1rem;flex-shrink:0"><?= $ic ?></span>
                                <div>
                                    <div style="font-size:.84rem;font-weight:600"><?= $title ?></div>
                                    <div style="font-size:.73rem;color:var(--gray)"><?= $sub ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
                            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank"
                               class="btn-primary" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
                                💬 Chat Admin
                            </a>
                            <a href="products.php" class="btn-outline"
                               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
                                🎮 Beli Lagi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT: Order Summary -->
        <div>
            <div class="pay-summary">
                <!-- Game header -->
                <div class="pay-summary-header">
                    <?php
                    $game_obj = null;
                    foreach ($games as $g) { if ($g['slug'] === $game_slug) { $game_obj = $g; break; } }
                    ?>
                    <?php if ($game_obj && !empty($game_obj['img'])): ?>
                    <img src="<?= htmlspecialchars($game_obj['img']) ?>"
                         class="pay-summary-game-img"
                         alt="<?= htmlspecialchars($game_name) ?>"
                         onerror="this.style.display='none'">
                    <?php else: ?>
                    <div class="pay-summary-game-img" style="display:flex;align-items:center;justify-content:center;
                        font-size:1.75rem;background:rgba(37,99,235,.2)"><?= htmlspecialchars($game_icon) ?></div>
                    <?php endif; ?>
                    <div>
                        <div class="pay-summary-game-name"><?= htmlspecialchars($game_name) ?></div>
                        <div class="pay-summary-game-cur"><?= htmlspecialchars($currency) ?></div>
                    </div>
                </div>

                <!-- Summary rows -->
                <div class="pay-summary-body">
                    <div class="sum-row">
                        <span class="sum-label">Paket</span>
                        <span class="sum-val"><?= htmlspecialchars($pkg_amount) ?> <?= htmlspecialchars($currency) ?></span>
                    </div>
                    <?php if ($user_id): ?>
                    <div class="sum-row">
                        <span class="sum-label">User ID</span>
                        <span class="sum-val" style="font-family:monospace;font-size:.82rem"><?= htmlspecialchars($user_id) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="sum-row">
                        <span class="sum-label">Biaya Layanan</span>
                        <span style="color:var(--success);font-weight:600">GRATIS</span>
                    </div>
                    <div class="sum-row divider total">
                        <span>Total Bayar</span>
                        <span class="amount"><?= formatRupiah($price) ?></span>
                    </div>

                    <!-- Method selected recap -->
                    <div id="selectedMethodRecap" style="display:none;margin-top:1rem;
                        background:var(--bg3);border-radius:var(--radius);padding:.875rem">
                        <div style="font-size:.72rem;color:var(--gray);margin-bottom:.3rem">Metode Terpilih</div>
                        <div style="font-weight:800;font-size:.9rem;display:flex;align-items:center;gap:.5rem">
                            <span id="recapMethodIcon"></span>
                            <span id="recapMethodLabel"></span>
                        </div>
                    </div>

                    <!-- Security badges -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:1.125rem">
                        <?php foreach ([['🔒','100% Aman'],['⚡','Proses Cepat'],['💰','Garansi Uang Kembali'],['🎧','Support 24/7']] as [$ic,$lb]): ?>
                        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;
                            padding:.5rem .625rem;text-align:center;font-size:.7rem;color:var(--gray)">
                            <div style="font-size:1rem;margin-bottom:.15rem"><?= $ic ?></div>
                            <?= $lb ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Need help? -->
            <div style="margin-top:1rem;background:var(--bg2);border:1px solid var(--border);
                border-radius:var(--radius-lg);padding:1.125rem;text-align:center">
                <div style="font-size:.82rem;color:var(--gray);margin-bottom:.625rem">
                    Butuh bantuan pembayaran?
                </div>
                <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Halo+admin,+saya+mau+konfirmasi+pembayaran+<?= urlencode($game_name) ?>"
                   target="_blank"
                   style="display:inline-flex;align-items:center;gap:.5rem;background:#25D366;
                       color:#fff;padding:.625rem 1.25rem;border-radius:8px;
                       font-size:.82rem;font-weight:700;text-decoration:none;transition:.2s"
                   onmouseover="this.style.opacity='.9'"
                   onmouseout="this.style.opacity='1'">
                    💬 Chat Admin WhatsApp
                </a>
            </div>
        </div>

    </div>
</div>

<script>
// ── Data dari PHP ─────────────────────────────────────────────
const PAY_METHODS = <?= json_encode($methods, JSON_UNESCAPED_UNICODE) ?>;
const PRICE       = <?= $price ?>;
const GAME_NAME   = <?= json_encode($game_name) ?>;
const PKG_AMOUNT  = <?= json_encode($pkg_amount) ?>;
const CURRENCY    = <?= json_encode($currency) ?>;
const USER_ID     = <?= json_encode($user_id) ?>;
const GAME_SLUG   = <?= json_encode($game_slug) ?>;
const GAME_ICON   = <?= json_encode($game_icon) ?>;
const WA_NUM      = '<?= WHATSAPP_NUMBER ?>';

let selectedMethod = null;
let selectedType   = null;
let uploadFile     = null;
let timerInterval  = null;
let timerSeconds   = 15 * 60; // 15 menit

// ── Format Rupiah ─────────────────────────────────────────────
const fmtRp = n => 'Rp ' + Number(n).toLocaleString('id-ID');

// ── Tab switcher ──────────────────────────────────────────────
function switchTab(btn, tab) {
    document.querySelectorAll('.method-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['ewallet','bank','qris'].forEach(t => {
        document.getElementById('tab-' + t).style.display = t === tab ? 'grid' : 'none';
    });
}

// ── Select payment method ─────────────────────────────────────
function selectMethod(name, color, icon, type) {
    // Deselect all
    document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
    // Select clicked
    const card = document.querySelector(`.method-card[data-method="${name}"]`);
    if (card) card.classList.add('selected');

    selectedMethod = name;
    selectedType   = type;

    // Get method data
    const m = PAY_METHODS[type]?.[name];
    if (!m) return;

    // Update detail box
    const box = document.getElementById('payDetailBox');
    box.classList.add('show');

    document.getElementById('detailIcon').textContent       = icon;
    document.getElementById('detailMethodName').textContent = name;

    const qrisBox   = document.getElementById('qrisBox');
    const accInfo   = document.getElementById('accountInfo');
    const typeLabel = document.getElementById('accTypeLabel');

    if (type === 'qris') {
        qrisBox.style.display  = 'block';
        accInfo.style.display  = 'none';
    } else {
        qrisBox.style.display  = 'none';
        accInfo.style.display  = 'block';
        typeLabel.textContent  = type === 'bank' ? 'Nomor Rekening' : 'Nomor E-Wallet';
        document.getElementById('accNumber').textContent = m.number;
        document.getElementById('accName').textContent   = 'a.n. ' + m.name;
    }

    document.getElementById('accAmount').textContent = fmtRp(PRICE);

    // Show next button
    document.getElementById('btnStep1').style.display = 'flex';

    // Update summary sidebar
    document.getElementById('selectedMethodRecap').style.display = 'block';
    document.getElementById('recapMethodIcon').textContent  = icon;
    document.getElementById('recapMethodLabel').textContent = name;
}

// ── Copy helpers ──────────────────────────────────────────────
function copyNumber() {
    const m = PAY_METHODS[selectedType]?.[selectedMethod];
    if (!m) return;
    navigator.clipboard.writeText(m.number).then(() => {
        const btn = document.querySelector('.copy-btn');
        if(btn){ btn.textContent='✅ Disalin!'; btn.classList.add('copied'); setTimeout(()=>{btn.textContent='📋 Salin';btn.classList.remove('copied');},2000); }
        showToast('✅ Nomor disalin!','success');
    });
}
function copyAmount() {
    navigator.clipboard.writeText(String(PRICE)).then(() => {
        showToast('✅ Nominal disalin!','success');
    });
}

// ── Steps navigation ──────────────────────────────────────────
function setStep(n) {
    [1,2,3,4].forEach(i => {
        const circle = document.getElementById('sc'+i);
        const label  = document.getElementById('sl'+i);
        if (!circle) return;
        if (i < n) {
            circle.className = 'step-circle done'; circle.textContent = '✓';
            label.className  = 'step-label done';
        } else if (i === n) {
            circle.className = 'step-circle active';
            circle.textContent = i < 4 ? i : '✓';
            label.className  = 'step-label active';
        } else {
            circle.className = 'step-circle waiting';
            circle.textContent = i < 4 ? i : '✓';
            label.className  = 'step-label waiting';
        }
        if (i < 4) {
            const line = document.getElementById('sline'+i);
            if (line) line.className = 'step-line' + (i < n ? ' done' : '');
        }
    });
}

function goStep1() {
    setStep(1);
    document.getElementById('step1Panel').style.display = '';
    document.getElementById('step2Panel').style.display = 'none';
    document.getElementById('step3Panel').style.display = 'none';
    clearInterval(timerInterval);
}

function goStep2() {
    if (!selectedMethod) {
        showToast('⚠️ Pilih metode pembayaran dulu!','error');
        return;
    }
    setStep(2);
    document.getElementById('step1Panel').style.display = 'none';
    document.getElementById('step2Panel').style.display = '';
    document.getElementById('step3Panel').style.display = 'none';

    // Update recap in step 2
    const m = PAY_METHODS[selectedType]?.[selectedMethod];
    document.getElementById('recapMethod').textContent = selectedMethod;
    document.getElementById('recapNumber').textContent = m ? (selectedType==='qris' ? 'Scan QR Code' : m.number) : '—';

    startTimer();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goStep3() {
    setStep(4);
    document.getElementById('step1Panel').style.display = 'none';
    document.getElementById('step2Panel').style.display = 'none';
    document.getElementById('step3Panel').style.display = '';
    clearInterval(timerInterval);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Timer ─────────────────────────────────────────────────────
function startTimer() {
    timerSeconds = 15 * 60;
    clearInterval(timerInterval);
    updateTimer();
    timerInterval = setInterval(() => {
        timerSeconds--;
        updateTimer();
        if (timerSeconds <= 0) {
            clearInterval(timerInterval);
            showToast('⏰ Waktu habis! Silakan mulai ulang pembayaran.', 'error');
            setTimeout(() => { goStep1(); }, 2000);
        }
    }, 1000);
}
function updateTimer() {
    const m = Math.floor(timerSeconds / 60);
    const s = timerSeconds % 60;
    const el = document.getElementById('timerCount');
    el.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    el.classList.toggle('urgent', timerSeconds < 120);
}

// ── File upload ───────────────────────────────────────────────
function handleUpload(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { showToast('⚠️ File terlalu besar! Maks 5MB','error'); return; }
    if (!file.type.startsWith('image/')) { showToast('⚠️ File harus berupa gambar!','error'); return; }

    uploadFile = file;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('previewImg').src      = e.target.result;
        document.getElementById('uploadPreview').style.display = 'block';
        document.getElementById('uploadPlaceholder').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function removeUpload(e) {
    e.preventDefault(); e.stopPropagation();
    uploadFile = null;
    document.getElementById('proofFile').value = '';
    document.getElementById('uploadPreview').style.display   = 'none';
    document.getElementById('uploadPlaceholder').style.display = 'block';
}

// Drag & drop
const uploadZone = document.getElementById('uploadZone');
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('proofFile').files = e.dataTransfer.files;
        handleUpload(document.getElementById('proofFile'));
    }
});

// ── Submit payment ────────────────────────────────────────────
function submitPayment() {
    if (!uploadFile) {
        showToast('⚠️ Upload bukti pembayaran dulu!', 'error');
        document.getElementById('uploadZone').style.borderColor = 'var(--danger)';
        setTimeout(() => document.getElementById('uploadZone').style.borderColor = '', 2000);
        return;
    }

    const btn = document.getElementById('btnConfirm');
    btn.disabled = true;
    btn.innerHTML = '<span style="animation:loaderSpin .8s linear infinite;display:inline-block">⏳</span> Memproses...';

    // Simulasi proses (kirim ke WA + generate order ID)
    const orderId = 'GS' + Date.now().toString().slice(-8);

    // Kirim ke WhatsApp admin
    const waMsg = `✅ *KONFIRMASI PEMBAYARAN*\n\n`
        + `🆔 Order ID: ${orderId}\n`
        + `🎮 Game: ${GAME_NAME}\n`
        + `💎 Paket: ${PKG_AMOUNT} ${CURRENCY}\n`
        + `🆔 User ID: ${USER_ID || '—'}\n`
        + `💳 Metode: ${selectedMethod}\n`
        + `💰 Nominal: ${fmtRp(PRICE)}\n\n`
        + `Bukti transfer sudah saya upload. Mohon diverifikasi. 🙏`;

    setTimeout(() => {
        // Tampilkan order ID
        document.getElementById('finalOrderId').textContent = orderId;
        // Buka WA
        window.open('https://wa.me/' + WA_NUM + '?text=' + encodeURIComponent(waMsg), '_blank');
        // Lanjut ke step 3
        goStep3();
        btn.disabled = false;
    }, 1800);
}

// ── gamesData for search ──────────────────────────────────────
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
