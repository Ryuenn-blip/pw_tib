<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Pengaturan';
$active_menu = 'settings';

// Handle form save (demo)
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_section'])) {
    $saved = $_POST['save_section'];
}

require_once 'includes/admin_layout.php';
?>

<div class="page-content">

    <?php if ($saved): ?>
    <div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:var(--radius);
        padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--success);font-weight:600;font-size:.875rem">
        ✅ Pengaturan <strong><?= htmlspecialchars($saved) ?></strong> berhasil disimpan!
    </div>
    <?php endif; ?>

    <!-- Tab navigation -->
    <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;border-bottom:1px solid var(--border);padding-bottom:0">
        <?php
        $tabs = ['umum'=>'⚙️ Umum','pembayaran'=>'💳 Pembayaran','notifikasi'=>'🔔 Notifikasi','keamanan'=>'🔒 Keamanan','tampilan'=>'🎨 Tampilan'];
        $active_tab = $_GET['tab'] ?? 'umum';
        foreach ($tabs as $key => $label):
        ?>
        <a href="?tab=<?= $key ?>"
           style="padding:.6rem 1rem;font-size:.85rem;font-weight:600;border-bottom:2px solid <?= $active_tab===$key ? 'var(--blue)' : 'transparent' ?>;
                  color:<?= $active_tab===$key ? 'var(--cyan)' : 'var(--gray)' ?>;text-decoration:none;
                  transition:.2s;white-space:nowrap">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ── TAB: UMUM ── -->
    <?php if ($active_tab === 'umum'): ?>
    <div class="settings-grid">
        <form method="POST" action="?tab=umum">
            <input type="hidden" name="save_section" value="Informasi Toko">
            <div class="card">
                <div class="card-header"><div class="card-title">🏪 Informasi Toko</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Nama Toko</label>
                        <input type="text" class="form-control" value="GameStore" name="site_name">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Tagline</label>
                        <input type="text" class="form-control" value="Top Up Game Terlengkap & Termurah" name="tagline">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Email Admin</label>
                        <input type="email" class="form-control" value="admin@gamestore.id" name="admin_email">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Nomor WhatsApp (tanpa +)</label>
                        <input type="text" class="form-control" value="6281234567890" name="wa_number">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Deskripsi Toko</label>
                        <textarea class="form-control" name="description" rows="3">Platform top up game terlengkap dan termurah di Indonesia. Proses instan, aman, dan terpercaya sejak 2020.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                </div>
            </div>
        </form>

        <form method="POST" action="?tab=umum">
            <input type="hidden" name="save_section" value="Jam Operasional">
            <div class="card">
                <div class="card-header"><div class="card-title">⏰ Jam Operasional</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem;background:var(--bg3);border-radius:var(--radius)">
                        <div>
                            <div style="font-weight:700;font-size:.875rem">Toko Online 24 Jam</div>
                            <div style="font-size:.75rem;color:var(--gray)">Customer bisa order kapanpun</div>
                        </div>
                        <label style="position:relative;display:inline-block;width:44px;height:24px;cursor:pointer">
                            <input type="checkbox" checked style="opacity:0;width:0;height:0">
                            <span style="position:absolute;inset:0;background:var(--blue);border-radius:12px;
                                display:flex;align-items:center;padding-left:3px">
                                <span style="width:18px;height:18px;background:#fff;border-radius:50%;transform:translateX(20px)"></span>
                            </span>
                        </label>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Pesan Offline (jika toko tutup)</label>
                        <textarea class="form-control" rows="2">Maaf, kami sedang offline. Silakan order kembali besok ya! 🙏</textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem">
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Jam Buka</label>
                            <input type="time" class="form-control" value="08:00">
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Jam Tutup</label>
                            <input type="time" class="form-control" value="23:00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                </div>
            </div>
        </form>
    </div>

    <!-- ── TAB: PEMBAYARAN ── -->
    <?php elseif ($active_tab === 'pembayaran'): ?>
    <form method="POST" action="?tab=pembayaran">
        <input type="hidden" name="save_section" value="Metode Pembayaran">
        <div class="card" style="margin-bottom:1rem">
            <div class="card-header"><div class="card-title">💳 Metode Pembayaran Aktif</div></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem">
                    <?php
                    $methods = [
                        ['name'=>'DANA',         'color'=>'#00AAFF','active'=>true],
                        ['name'=>'OVO',          'color'=>'#6B3FA0','active'=>true],
                        ['name'=>'GoPay',        'color'=>'#00AED6','active'=>true],
                        ['name'=>'ShopeePay',    'color'=>'#EE4D2D','active'=>true],
                        ['name'=>'Transfer BCA', 'color'=>'#005BAA','active'=>true],
                        ['name'=>'Transfer BRI', 'color'=>'#0066AE','active'=>false],
                        ['name'=>'Transfer Mandiri','color'=>'#003087','active'=>false],
                        ['name'=>'QRIS',         'color'=>'#E31837','active'=>true],
                        ['name'=>'LinkAja',      'color'=>'#E4202F','active'=>false],
                    ];
                    foreach ($methods as $m):
                    ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:.75rem;background:var(--bg3);border-radius:var(--radius);
                        border:1px solid <?= $m['active'] ? $m['color'].'44' : 'var(--border)' ?>">
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:10px;height:10px;border-radius:50%;background:<?= $m['color'] ?>"></div>
                            <span style="font-size:.85rem;font-weight:600"><?= $m['name'] ?></span>
                        </div>
                        <input type="checkbox" name="methods[]" value="<?= $m['name'] ?>"
                               <?= $m['active']?'checked':'' ?>
                               style="width:16px;height:16px;accent-color:var(--blue);cursor:pointer">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="settings-grid">
            <div class="card">
                <div class="card-header"><div class="card-title">🏦 Rekening Bank</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem">
                    <?php foreach (['BCA'=>'005BAA','Mandiri'=>'003087','BRI'=>'0066AE'] as $bank=>$clr): ?>
                    <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;border-left:3px solid #<?= $clr ?>">
                        <div style="font-weight:700;font-size:.85rem;margin-bottom:.5rem"><?= $bank ?></div>
                        <input type="text" class="form-control" placeholder="No. Rekening <?= $bank ?>" style="margin-bottom:.4rem">
                        <input type="text" class="form-control" placeholder="Atas Nama">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-title">📱 E-Wallet</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem">
                    <?php foreach (['DANA'=>'00AAFF','OVO'=>'6B3FA0','GoPay'=>'00AED6','ShopeePay'=>'EE4D2D'] as $ew=>$clr): ?>
                    <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;border-left:3px solid #<?= $clr ?>">
                        <div style="font-weight:700;font-size:.85rem;margin-bottom:.5rem"><?= $ew ?></div>
                        <input type="text" class="form-control" placeholder="Nomor <?= $ew ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div style="margin-top:1rem;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">💾 Simpan Pengaturan Pembayaran</button>
        </div>
    </form>

    <!-- ── TAB: NOTIFIKASI ── -->
    <?php elseif ($active_tab === 'notifikasi'): ?>
    <form method="POST" action="?tab=notifikasi">
        <input type="hidden" name="save_section" value="Notifikasi">
        <div class="settings-grid">
            <div class="card">
                <div class="card-header"><div class="card-title">🔔 Notifikasi Admin</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    <?php
                    $notifs = [
                        ['label'=>'Order Baru Masuk',   'desc'=>'Notif saat ada pesanan baru', 'on'=>true],
                        ['label'=>'Pembayaran Diterima', 'desc'=>'Saat customer konfirmasi bayar','on'=>true],
                        ['label'=>'Pesan Chat Baru',     'desc'=>'Notif saat ada chat masuk',   'on'=>true],
                        ['label'=>'Order Dibatalkan',    'desc'=>'Saat customer batalkan order', 'on'=>false],
                        ['label'=>'Laporan Harian',      'desc'=>'Ringkasan performa tiap hari', 'on'=>true],
                    ];
                    foreach ($notifs as $n):
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:.75rem;background:var(--bg3);border-radius:var(--radius)">
                        <div>
                            <div style="font-weight:600;font-size:.875rem"><?= $n['label'] ?></div>
                            <div style="font-size:.73rem;color:var(--gray)"><?= $n['desc'] ?></div>
                        </div>
                        <label style="cursor:pointer;display:flex;align-items:center;gap:.5rem">
                            <input type="checkbox" <?= $n['on']?'checked':'' ?>
                                   style="width:16px;height:16px;accent-color:var(--blue)">
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-title">📧 Email Notifikasi</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Email Tujuan Notifikasi</label>
                        <input type="email" class="form-control" value="admin@gamestore.id">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Email CC (opsional)</label>
                        <input type="email" class="form-control" placeholder="email@lain.com">
                    </div>
                    <div style="background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);border-radius:var(--radius);padding:.875rem;font-size:.8rem;color:var(--gray)">
                        💡 Pastikan email SMTP sudah dikonfigurasi di server untuk pengiriman email.
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top:1rem;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">💾 Simpan Notifikasi</button>
        </div>
    </form>

    <!-- ── TAB: KEAMANAN ── -->
    <?php elseif ($active_tab === 'keamanan'): ?>
    <div class="settings-grid">
        <form method="POST" action="?tab=keamanan">
            <input type="hidden" name="save_section" value="Password Admin">
            <div class="card">
                <div class="card-header"><div class="card-title">🔒 Ganti Password Admin</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="admin" name="username">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Password Lama</label>
                        <input type="password" class="form-control" name="old_pass" placeholder="Password saat ini">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control" name="new_pass" placeholder="Min. 8 karakter">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="confirm_pass" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">🔐 Ganti Password</button>
                </div>
            </div>
        </form>
        <div class="card">
            <div class="card-header"><div class="card-title">🛡️ Keamanan Tambahan</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                <?php
                $sec = [
                    ['label'=>'Proteksi Brute Force','desc'=>'Blokir IP setelah 5x login gagal','on'=>true],
                    ['label'=>'Session Timeout',     'desc'=>'Auto logout setelah 2 jam tidak aktif','on'=>true],
                    ['label'=>'Log Aktivitas Admin', 'desc'=>'Simpan semua aktivitas login & aksi','on'=>true],
                    ['label'=>'Proteksi CSRF',        'desc'=>'Token validasi setiap form submit','on'=>true],
                ];
                foreach ($sec as $s):
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:.75rem;background:var(--bg3);border-radius:var(--radius)">
                    <div>
                        <div style="font-weight:600;font-size:.875rem"><?= $s['label'] ?></div>
                        <div style="font-size:.73rem;color:var(--gray)"><?= $s['desc'] ?></div>
                    </div>
                    <input type="checkbox" <?= $s['on']?'checked':'' ?>
                           style="width:16px;height:16px;accent-color:var(--blue)">
                </div>
                <?php endforeach; ?>
                <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);
                    border-radius:var(--radius);padding:.875rem">
                    <div style="font-weight:700;font-size:.82rem;color:var(--danger);margin-bottom:.4rem">⚠️ Zona Berbahaya</div>
                    <button class="btn btn-danger btn-sm" onclick="if(confirm('Reset semua data sesi? Admin akan logout.')) showToast('✅ Semua sesi direset!')">
                        Reset Semua Sesi Login
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── TAB: TAMPILAN ── -->
    <?php elseif ($active_tab === 'tampilan'): ?>
    <form method="POST" action="?tab=tampilan">
        <input type="hidden" name="save_section" value="Tampilan">
        <div class="settings-grid">
            <div class="card">
                <div class="card-header"><div class="card-title">🎨 Warna & Tema</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Warna Utama</label>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                            <?php foreach (['#2563EB'=>'Biru','#8B5CF6'=>'Ungu','#10B981'=>'Hijau','#EF4444'=>'Merah','#F59E0B'=>'Kuning'] as $col=>$lbl): ?>
                            <div style="display:flex;flex-direction:column;align-items:center;gap:.3rem;cursor:pointer">
                                <div style="width:36px;height:36px;border-radius:50%;background:<?= $col ?>;
                                    border:3px solid <?= $col==='#2563EB'?'#fff':'transparent' ?>;
                                    box-shadow:0 0 0 2px <?= $col==='#2563EB'?$col:'transparent' ?>"></div>
                                <span style="font-size:.65rem;color:var(--gray)"><?= $lbl ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Mode Tampilan</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
                            <div style="padding:.875rem;background:var(--bg);border:2px solid var(--blue);border-radius:var(--radius);text-align:center;cursor:pointer">
                                <div style="font-size:1.25rem">🌙</div>
                                <div style="font-size:.8rem;font-weight:700;margin-top:.3rem">Dark Mode</div>
                                <div style="font-size:.7rem;color:var(--cyan)">Aktif</div>
                            </div>
                            <div style="padding:.875rem;background:#f8fafc;border:2px solid var(--border);border-radius:var(--radius);text-align:center;cursor:pointer;opacity:.5">
                                <div style="font-size:1.25rem">☀️</div>
                                <div style="font-size:.8rem;font-weight:700;margin-top:.3rem;color:#111">Light Mode</div>
                                <div style="font-size:.7rem;color:#666">Segera Hadir</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-title">🖼️ Logo & Branding</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    <div style="border:2px dashed var(--border);border-radius:var(--radius);padding:2rem;text-align:center;cursor:pointer"
                         onclick="document.getElementById('logoUpload').click()">
                        <div style="font-size:2rem;margin-bottom:.5rem">🎮</div>
                        <div style="font-size:.85rem;color:var(--gray)">Klik untuk upload logo</div>
                        <div style="font-size:.72rem;color:var(--gray2);margin-top:.25rem">PNG, JPG max 2MB</div>
                        <input type="file" id="logoUpload" style="display:none" accept="image/*">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Favicon URL</label>
                        <input type="url" class="form-control" placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top:1rem;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">💾 Simpan Tampilan</button>
        </div>
    </form>
    <?php endif; ?>

</div>
<?php require_once 'includes/admin_footer.php'; ?>
