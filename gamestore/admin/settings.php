<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Pengaturan';
$active_menu = 'settings';

// ── Handle save ───────────────────────────────────────────────
$saved_section = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['section'])) {
    // CSRF check
    if (!empty($_POST) && !csrf_verify()) { header("Location: " . $_SERVER['PHP_SELF'] . "?csrf_error=1"); exit; }
    $section = $_POST['section'];

    // Fungsi simpan setting ke DB
    $save = function(string $key, string $val) {
        db_exec("INSERT INTO settings (`key`,`value`) VALUES (?,?)
                 ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), updated_at=NOW()",
                [$key, $val]);
    };

    switch ($section) {
        case 'general':
            $save('site_name',        trim($_POST['site_name']        ?? ''));
            $save('site_tagline',     trim($_POST['site_tagline']     ?? ''));
            $save('admin_email',      trim($_POST['admin_email']      ?? ''));
            $save('wa_number',        preg_replace('/\D/','',trim($_POST['wa_number']??'')));
            $save('site_description', trim($_POST['site_description'] ?? ''));
            $save('maintenance_mode', !empty($_POST['maintenance_mode']) ? '1' : '0');
            $save('always_open',      !empty($_POST['always_open'])      ? '1' : '0');
            break;
        case 'payment':
            $methods = $_POST['methods'] ?? [];
            $save('active_payments', implode(',', $methods));
            foreach ($_POST['pm_number'] ?? [] as $name => $num) {
                db_exec("UPDATE payment_methods SET number=?, account_name=? WHERE name=?",
                    [trim($num), trim($_POST['pm_name'][$name]??''), $name]);
            }
            break;
        case 'security':
            $old = trim($_POST['old_pass'] ?? '');
            $new = trim($_POST['new_pass'] ?? '');
            $con = trim($_POST['confirm_pass'] ?? '');
            if ($old && $new && $new === $con) {
                $admin = db_row("SELECT password FROM admins WHERE id=?", [$_SESSION['admin_id']??1]);
                if ($admin && password_verify($old, $admin['password'])) {
                    db_exec("UPDATE admins SET password=? WHERE id=?",
                        [password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']??1]);
                } else {
                    $_SESSION['settings_error'] = 'Password lama tidak cocok!';
                }
            }
            $save('brute_force_protect', !empty($_POST['brute_force_protect']) ? '1':'0');
            $save('session_timeout',     (int)($_POST['session_timeout']??120).'');
            break;
        case 'notification':
            $save('notif_new_order', !empty($_POST['notif_new_order']) ? '1':'0');
            $save('notif_payment',   !empty($_POST['notif_payment'])   ? '1':'0');
            $save('notif_chat',      !empty($_POST['notif_chat'])      ? '1':'0');
            $save('notif_email',     trim($_POST['notif_email']        ?? ''));
            break;
    }

    $saved_section = $section;
    log_activity('settings_update', "Pengaturan '$section' diperbarui oleh admin");
    header('Location: settings.php?tab='.$section.'&saved=1');
    exit;
}

// ── Load all settings ─────────────────────────────────────────
$all_settings = [];
foreach (db_rows("SELECT `key`,`value` FROM settings") as $row)
    $all_settings[$row['key']] = $row['value'];
$s = fn(string $k, string $def='') => $all_settings[$k] ?? $def;

// Load payment methods from DB
$pay_methods = db_rows("SELECT * FROM payment_methods ORDER BY sort_order");

$active_tab = $_GET['tab'] ?? 'general';
$saved      = !empty($_GET['saved']);
$err        = $_SESSION['settings_error'] ?? null;
unset($_SESSION['settings_error']);

require_once 'includes/admin_layout.php';
?>
<style>
.settings-tabs{display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:1.5rem}
.stab{padding:.625rem 1.125rem;font-size:.875rem;font-weight:600;cursor:pointer;text-decoration:none;
    color:var(--gray);border-bottom:2px solid transparent;transition:var(--tr)}
.stab:hover{color:var(--white)}
.stab.active{color:var(--cyan);border-bottom-color:var(--blue)}
.toggle-row{display:flex;align-items:center;justify-content:space-between;
    padding:.75rem;background:var(--bg3);border-radius:var(--radius);margin-bottom:.5rem}
.toggle-label .title{font-weight:600;font-size:.875rem}
.toggle-label .desc{font-size:.73rem;color:var(--gray);margin-top:.1rem}
.toggle-inp{width:18px;height:18px;accent-color:var(--blue);cursor:pointer}
</style>

<div class="page-content">

<?php if ($saved): ?>
<div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:var(--radius);
    padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--success);font-size:.875rem;font-weight:600">
    ✅ Pengaturan berhasil disimpan!
</div>
<?php endif; ?>
<?php if ($err): ?>
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:var(--radius);
    padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--danger);font-size:.875rem">
    ⚠️ <?= htmlspecialchars($err) ?>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="settings-tabs">
    <?php foreach ([
        'general'=>'⚙️ Umum', 'payment'=>'💳 Pembayaran',
        'notification'=>'🔔 Notifikasi', 'security'=>'🔒 Keamanan', 'display'=>'🎨 Tampilan'
    ] as $key=>$lbl): ?>
    <a href="?tab=<?= $key ?>" class="stab <?= $active_tab===$key?'active':'' ?>"><?= $lbl ?></a>
    <?php endforeach; ?>
</div>

<?php if ($active_tab === 'general'): ?>
<!-- ═══ GENERAL ═══ -->
<form method="POST">
<input type="hidden" name="section" value="general">
                    <?= csrf_field() ?>
<div class="settings-grid">
    <div class="card">
        <div class="card-header"><div class="card-title">🏪 Informasi Toko</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
            <?php foreach ([
                ['site_name','Nama Toko','GameStore','text'],
                ['site_tagline','Tagline','Top Up Terlengkap & Termurah','text'],
                ['admin_email','Email Admin','admin@gamestore.id','email'],
                ['wa_number','Nomor WhatsApp (tanpa +)','6281234567890','text'],
            ] as [$key,$lbl,$ph,$type]): ?>
            <div class="form-group" style="margin:0">
                <label class="form-label"><?= $lbl ?></label>
                <input type="<?= $type ?>" name="<?= $key ?>" class="form-control"
                       placeholder="<?= $ph ?>" value="<?= htmlspecialchars($s($key,$ph)) ?>">
            </div>
            <?php endforeach; ?>
            <div class="form-group" style="margin:0">
                <label class="form-label">Deskripsi Toko</label>
                <textarea name="site_description" class="form-control" rows="3"><?= htmlspecialchars($s('site_description')) ?></textarea>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">💾 Simpan</button></div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">⚙️ Pengaturan Umum</div></div>
        <div class="card-body">
            <div class="toggle-row">
                <div class="toggle-label">
                    <div class="title">Mode Maintenance</div>
                    <div class="desc">Website tidak dapat diakses pengunjung</div>
                </div>
                <input type="checkbox" name="maintenance_mode" class="toggle-inp"
                       <?= $s('maintenance_mode')==='1'?'checked':'' ?>>
            </div>
            <div class="toggle-row">
                <div class="toggle-label">
                    <div class="title">Toko 24 Jam</div>
                    <div class="desc">Customer bisa order kapanpun</div>
                </div>
                <input type="checkbox" name="always_open" class="toggle-inp"
                       <?= $s('always_open','1')==='1'?'checked':'' ?>>
            </div>
            <div class="form-group" style="margin-top:.875rem">
                <label class="form-label">Timeout Order (menit)</label>
                <input type="number" name="order_timeout" class="form-control" min="5" max="1440"
                       value="<?= htmlspecialchars($s('order_timeout','30')) ?>">
            </div>
        </div>
    </div>
</div>
</form>

<?php elseif ($active_tab === 'payment'): ?>
<!-- ═══ PAYMENT ═══ -->
<form method="POST">
<input type="hidden" name="section" value="payment">
                    <?= csrf_field() ?>
<div class="card" style="margin-bottom:1rem">
    <div class="card-header"><div class="card-title">💳 Metode Aktif</div></div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.625rem">
            <?php foreach ($pay_methods as $pm): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem;
                background:var(--bg3);border-radius:var(--radius);
                border:1px solid <?= $pm['status']==='active'?$pm['color'].'44':'var(--border)' ?>">
                <div style="display:flex;align-items:center;gap:.4rem">
                    <div style="width:9px;height:9px;border-radius:50%;background:<?= $pm['color'] ?>"></div>
                    <span style="font-size:.85rem;font-weight:600"><?= htmlspecialchars($pm['name']) ?></span>
                </div>
                <input type="checkbox" name="methods[]" value="<?= $pm['id'] ?>"
                       <?= $pm['status']==='active'?'checked':'' ?>
                       class="toggle-inp">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="settings-grid">
    <!-- E-Wallet numbers -->
    <div class="card">
        <div class="card-header"><div class="card-title">📱 Nomor E-Wallet</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem">
            <?php foreach (array_filter($pay_methods, fn($m)=>$m['type']==='ewallet') as $pm): ?>
            <div style="background:var(--bg3);border-radius:var(--radius);padding:.875rem;border-left:3px solid <?= $pm['color'] ?>">
                <div style="font-weight:700;font-size:.85rem;margin-bottom:.5rem"><?= htmlspecialchars($pm['name']) ?></div>
                <input type="text" name="pm_number[<?= htmlspecialchars($pm['name']) ?>]" class="form-control"
                       placeholder="Nomor" value="<?= htmlspecialchars($pm['number']??'') ?>" style="margin-bottom:.375rem">
                <input type="text" name="pm_name[<?= htmlspecialchars($pm['name']) ?>]" class="form-control"
                       placeholder="Atas Nama" value="<?= htmlspecialchars($pm['account_name']??'') ?>">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Bank numbers -->
    <div class="card">
        <div class="card-header"><div class="card-title">🏦 Rekening Bank</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem">
            <?php foreach (array_filter($pay_methods, fn($m)=>$m['type']==='bank') as $pm): ?>
            <div style="background:var(--bg3);border-radius:var(--radius);padding:.875rem;border-left:3px solid <?= $pm['color'] ?>">
                <div style="font-weight:700;font-size:.85rem;margin-bottom:.5rem"><?= htmlspecialchars($pm['name']) ?></div>
                <input type="text" name="pm_number[<?= htmlspecialchars($pm['name']) ?>]" class="form-control"
                       placeholder="No. Rekening" value="<?= htmlspecialchars($pm['number']??'') ?>" style="margin-bottom:.375rem">
                <input type="text" name="pm_name[<?= htmlspecialchars($pm['name']) ?>]" class="form-control"
                       placeholder="Atas Nama" value="<?= htmlspecialchars($pm['account_name']??'') ?>">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div style="margin-top:1rem;display:flex;justify-content:flex-end">
    <button type="submit" class="btn btn-primary">💾 Simpan Pembayaran</button>
</div>
</form>

<?php elseif ($active_tab === 'notification'): ?>
<!-- ═══ NOTIFICATION ═══ -->
<form method="POST">
<input type="hidden" name="section" value="notification">
                    <?= csrf_field() ?>
<div class="settings-grid">
    <div class="card">
        <div class="card-header"><div class="card-title">🔔 Notifikasi Admin</div></div>
        <div class="card-body">
            <?php foreach ([
                ['notif_new_order','Order Baru Masuk','Notif saat ada pesanan baru'],
                ['notif_payment',  'Pembayaran Masuk','Saat customer konfirmasi bayar'],
                ['notif_chat',     'Chat Baru','Saat ada pesan live chat masuk'],
            ] as [$key,$title,$desc]): ?>
            <div class="toggle-row">
                <div class="toggle-label">
                    <div class="title"><?= $title ?></div>
                    <div class="desc"><?= $desc ?></div>
                </div>
                <input type="checkbox" name="<?= $key ?>" class="toggle-inp"
                       <?= $s($key,'1')==='1'?'checked':'' ?>>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📧 Email Notifikasi</div></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Email Tujuan</label>
                <input type="email" name="notif_email" class="form-control"
                       value="<?= htmlspecialchars($s('notif_email',$s('admin_email','admin@gamestore.id'))) ?>">
            </div>
            <div style="background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.18);border-radius:var(--radius);
                padding:.875rem;font-size:.8rem;color:var(--gray)">
                💡 Pastikan konfigurasi SMTP sudah disetup di server untuk pengiriman email.
            </div>
        </div>
    </div>
</div>
<div style="margin-top:1rem;display:flex;justify-content:flex-end">
    <button type="submit" class="btn btn-primary">💾 Simpan Notifikasi</button>
</div>
</form>

<?php elseif ($active_tab === 'security'): ?>
<!-- ═══ SECURITY ═══ -->
<form method="POST">
<input type="hidden" name="section" value="security">
                    <?= csrf_field() ?>
<div class="settings-grid">
    <div class="card">
        <div class="card-header"><div class="card-title">🔒 Ganti Password Admin</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
            <div class="form-group" style="margin:0">
                <label class="form-label">Password Lama</label>
                <input type="password" name="old_pass" class="form-control" placeholder="••••••••">
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label">Password Baru (min. 8 karakter)</label>
                <input type="password" name="new_pass" class="form-control" placeholder="••••••••">
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_pass" class="form-control" placeholder="••••••••">
            </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">🔐 Ganti Password</button></div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">🛡️ Keamanan Sistem</div></div>
        <div class="card-body">
            <?php foreach ([
                ['brute_force_protect','Proteksi Brute Force','Blokir IP setelah 5x login gagal','1'],
                ['session_log',        'Log Aktivitas Admin', 'Simpan semua aksi admin','1'],
                ['csrf_protect',       'Proteksi CSRF',       'Token validasi form','1'],
            ] as [$key,$title,$desc,$def]): ?>
            <div class="toggle-row">
                <div class="toggle-label">
                    <div class="title"><?= $title ?></div>
                    <div class="desc"><?= $desc ?></div>
                </div>
                <input type="checkbox" name="<?= $key ?>" class="toggle-inp"
                       <?= $s($key,$def)==='1'?'checked':'' ?>>
            </div>
            <?php endforeach; ?>
            <div class="form-group" style="margin-top:.875rem">
                <label class="form-label">Session Timeout (menit)</label>
                <input type="number" name="session_timeout" class="form-control" min="30" max="1440"
                       value="<?= htmlspecialchars($s('session_timeout','120')) ?>">
            </div>
            <div style="margin-top:.875rem;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.18);
                border-radius:var(--radius);padding:.875rem">
                <div style="font-weight:700;font-size:.82rem;color:var(--danger);margin-bottom:.5rem">⚠️ Zona Berbahaya</div>
                <button type="button" class="btn btn-danger btn-sm"
                        onclick="if(confirm('Reset semua sesi admin? Kamu akan logout.'))showToast('✅ Sesi direset!')">
                    Reset Semua Sesi
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<?php else: ?>
<!-- ═══ DISPLAY ═══ -->
<div class="settings-grid">
    <div class="card">
        <div class="card-header"><div class="card-title">🎨 Warna & Tema</div></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Warna Utama</label>
                <div style="display:flex;gap:.625rem;flex-wrap:wrap;align-items:center">
                    <?php foreach (['#2563EB'=>'Biru','#8B5CF6'=>'Ungu','#10B981'=>'Hijau','#EF4444'=>'Merah','#F59E0B'=>'Kuning'] as $col=>$nm): ?>
                    <div title="<?= $nm ?>" style="width:36px;height:36px;border-radius:50%;background:<?= $col ?>;cursor:pointer;
                        border:3px solid <?= $s('accent_color','#2563EB')===$col?'#fff':'transparent' ?>;
                        box-shadow:<?= $s('accent_color','#2563EB')===$col?'0 0 0 2px '.$col:'none' ?>;
                        transition:.2s" onclick="this.parentNode.querySelectorAll('div').forEach(d=>d.style.border='3px solid transparent');
                            this.style.border='3px solid #fff';this.style.boxShadow='0 0 0 2px <?= $col ?>'"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mode Tampilan</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem">
                    <div style="padding:.875rem;background:var(--bg);border:2px solid var(--blue);
                        border-radius:var(--radius);text-align:center;cursor:pointer">
                        <div style="font-size:1.25rem">🌙</div>
                        <div style="font-size:.8rem;font-weight:700;margin-top:.3rem">Dark Mode</div>
                        <div style="font-size:.7rem;color:var(--cyan)">Aktif</div>
                    </div>
                    <div style="padding:.875rem;background:#f8fafc;border:2px solid var(--border);
                        border-radius:var(--radius);text-align:center;cursor:pointer;opacity:.5">
                        <div style="font-size:1.25rem">☀️</div>
                        <div style="font-size:.8rem;font-weight:700;margin-top:.3rem;color:#111">Light Mode</div>
                        <div style="font-size:.7rem;color:#666">Segera Hadir</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📊 Info Sistem</div></div>
        <div class="card-body" style="font-size:.85rem">
            <?php foreach ([
                ['PHP Version', PHP_VERSION],
                ['Server',      $_SERVER['SERVER_SOFTWARE']??'—'],
                ['Disk Free',   round(disk_free_space('/')/1073741824, 1).' GB'],
                ['DB Tables',   (int)(db_row("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema=DATABASE()")['c']??0).' tabel'],
                ['Total Orders',$total_orders.' order'],
                ['Total Users', (int)(db_row("SELECT COUNT(*) AS c FROM customers")['c']??0).' pelanggan'],
            ] as [$lbl,$val]): ?>
            <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid rgba(48,54,61,.4)">
                <span style="color:var(--gray)"><?= $lbl ?></span>
                <span style="font-weight:600;font-size:.82rem"><?= htmlspecialchars((string)$val) ?></span>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:1rem;display:flex;gap:.5rem;flex-wrap:wrap">
                <button class="btn btn-ghost btn-sm" onclick="showToast('✅ Cache dibersihkan!')">🗑 Clear Cache</button>
                <a href="../database/gamestore.sql" download class="btn btn-ghost btn-sm">📥 Backup SQL</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
<?php require_once 'includes/admin_footer.php'; ?>
