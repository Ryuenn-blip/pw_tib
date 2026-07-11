<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Profil Admin';
$active_menu = 'settings';

$admin_id = $_SESSION['admin_id'] ?? 1;
$msg = $err = '';

// ── Handle POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!empty($_POST) && !csrf_verify()) { header("Location: " . $_SERVER['PHP_SELF'] . "?csrf_error=1"); exit; }
    $section = $_POST['section'] ?? '';

    if ($section === 'profile') {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        if (!$name)  { $err = 'Nama wajib diisi'; }
        elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $err = 'Format email tidak valid'; }
        else {
            db_exec("UPDATE admins SET name=?, email=? WHERE id=?", [$name,$email,$admin_id]);
            $_SESSION['admin_name'] = $name;
            $msg = '✅ Profil berhasil diperbarui!';
            log_activity('update_profile', "Admin memperbarui profil: nama=$name, email=$email");
        }
    }

    elseif ($section === 'password') {
        $old  = $_POST['old_pass']  ?? '';
        $new  = $_POST['new_pass']  ?? '';
        $conf = $_POST['conf_pass'] ?? '';
        $admin_row = db_row("SELECT password FROM admins WHERE id=?", [$admin_id]);
        if (!$old || !$new) { $err = 'Semua field password wajib diisi'; }
        elseif (!password_verify($old, $admin_row['password'] ?? '')) { $err = 'Password lama tidak cocok'; }
        elseif (strlen($new) < 8) { $err = 'Password baru minimal 8 karakter'; }
        elseif ($new !== $conf) { $err = 'Konfirmasi password tidak cocok'; }
        else {
            db_exec("UPDATE admins SET password=? WHERE id=?",
                [password_hash($new, PASSWORD_DEFAULT), $admin_id]);
            $msg = '✅ Password berhasil diubah!';
            log_activity('change_password', 'Admin mengganti password');
        }
    }
}

// ── Load admin data ───────────────────────────────────────────
$admin = db_row("SELECT * FROM admins WHERE id=?", [$admin_id]);
$login_logs = db_rows("SELECT * FROM activity_logs WHERE admin_id=? AND action='login' ORDER BY created_at DESC LIMIT 5", [$admin_id]);
$total_actions = (int)(db_row("SELECT COUNT(*) AS c FROM activity_logs WHERE admin_id=?", [$admin_id])['c'] ?? 0);

require_once 'includes/admin_layout.php';
?>
<div class="page-content">

<?php if ($msg): ?>
<div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:var(--radius);
    padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--success);font-size:.875rem;font-weight:600">
    <?= $msg ?>
</div>
<?php endif; ?>
<?php if ($err): ?>
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:var(--radius);
    padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--danger);font-size:.875rem">
    ⚠️ <?= htmlspecialchars($err) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start">

    <!-- Sidebar profil -->
    <div>
        <div class="card" style="text-align:center;padding:2rem 1.25rem;margin-bottom:1rem">
            <!-- Avatar -->
            <div style="width:80px;height:80px;border-radius:50%;margin:0 auto 1rem;
                background:linear-gradient(135deg,var(--blue),var(--cyan));
                display:flex;align-items:center;justify-content:center;
                font-size:2rem;font-weight:900;border:3px solid rgba(37,99,235,.4)">
                <?= strtoupper(($admin['name'] ?? 'A')[0]) ?>
            </div>
            <div style="font-weight:800;font-size:1.05rem"><?= htmlspecialchars($admin['name'] ?? '') ?></div>
            <div style="font-size:.8rem;color:var(--gray);margin:.25rem 0">@<?= htmlspecialchars($admin['username'] ?? '') ?></div>
            <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(37,99,235,.12);
                border:1px solid rgba(37,99,235,.25);color:var(--blue-l);padding:.25rem .75rem;
                border-radius:100px;font-size:.72rem;font-weight:700">
                <?= ucfirst($admin['role'] ?? 'admin') ?>
            </span>
        </div>

        <!-- Stats mini -->
        <div class="card">
            <div class="card-body" style="display:flex;flex-direction:column;gap:.625rem">
                <?php foreach ([
                    ['🎯','Total Aksi',  $total_actions.' aksi'],
                    ['📅','Bergabung',   date('d M Y', strtotime($admin['created_at'] ?? 'now'))],
                    ['🕐','Login Terakhir', $admin['last_login'] ? date('d/m/Y H:i', strtotime($admin['last_login'])) : '—'],
                    ['🌐','IP Terakhir', $login_logs[0]['ip'] ?? '—'],
                ] as [$ico,$lbl,$val]): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:.4rem 0;border-bottom:1px solid rgba(48,54,61,.4);font-size:.82rem">
                    <span style="color:var(--gray)"><?= $ico ?> <?= $lbl ?></span>
                    <span style="font-weight:600;font-size:.78rem;text-align:right;max-width:55%"><?= htmlspecialchars($val) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        <!-- Edit profil -->
        <form method="POST">
        <input type="hidden" name="section" value="profile">
        <div class="card">
            <div class="card-header"><div class="card-title">👤 Edit Profil</div></div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($admin['username'] ?? '') ?>" disabled
                               style="opacity:.5;cursor:not-allowed">
                        <small style="color:var(--gray2);font-size:.72rem">Username tidak bisa diubah</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($admin['email'] ?? '') ?>" placeholder="admin@gamestore.id">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars(ucfirst($admin['role'] ?? 'admin')) ?>" disabled
                               style="opacity:.5;cursor:not-allowed">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">💾 Simpan Profil</button>
            </div>
        </div>
        </form>

        <!-- Ganti password -->
        <form method="POST">
        <input type="hidden" name="section" value="password">
        <div class="card">
            <div class="card-header"><div class="card-title">🔒 Ganti Password</div></div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1">
                        <label class="form-label">Password Lama *</label>
                        <div style="position:relative">
                            <input type="password" name="old_pass" id="passOld" class="form-control"
                                   placeholder="••••••••" style="padding-right:3rem">
                            <button type="button" onclick="toggleP('passOld','eo')"
                                style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                    background:none;border:none;color:var(--gray);cursor:pointer" id="eo">👁</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru * <small style="color:var(--gray)">(min. 8 karakter)</small></label>
                        <div style="position:relative">
                            <input type="password" name="new_pass" id="passNew" class="form-control"
                                   placeholder="••••••••" style="padding-right:3rem"
                                   oninput="checkStrength(this.value)">
                            <button type="button" onclick="toggleP('passNew','en')"
                                style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                    background:none;border:none;color:var(--gray);cursor:pointer" id="en">👁</button>
                        </div>
                        <div id="strWrap" style="display:none;margin-top:.4rem">
                            <div style="height:4px;background:var(--bg3);border-radius:2px;overflow:hidden">
                                <div id="strBar" style="height:100%;border-radius:2px;transition:.3s"></div>
                            </div>
                            <div id="strLabel" style="font-size:.7rem;margin-top:.2rem"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password Baru *</label>
                        <div style="position:relative">
                            <input type="password" name="conf_pass" id="passConf" class="form-control"
                                   placeholder="••••••••" style="padding-right:3rem">
                            <button type="button" onclick="toggleP('passConf','ec')"
                                style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                                    background:none;border:none;color:var(--gray);cursor:pointer" id="ec">👁</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">🔐 Ganti Password</button>
            </div>
        </div>
        </form>

        <!-- Login history -->
        <div class="card">
            <div class="card-header"><div class="card-title">🕐 Riwayat Login Terakhir</div></div>
            <?php if (empty($login_logs)): ?>
            <div style="padding:1.5rem;text-align:center;color:var(--gray);font-size:.85rem">Belum ada riwayat login</div>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Waktu</th><th>IP Address</th><th>Keterangan</th></tr></thead>
                    <tbody>
                    <?php foreach ($login_logs as $ll): ?>
                    <tr>
                        <td style="font-size:.82rem;white-space:nowrap"><?= date('d/m/Y H:i:s', strtotime($ll['created_at'])) ?></td>
                        <td style="font-family:monospace;font-size:.8rem;color:var(--gray)"><?= htmlspecialchars($ll['ip'] ?? '—') ?></td>
                        <td>
                            <span class="badge badge-completed"><span class="badge-dot"></span>Berhasil Login</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</div>

<script>
function toggleP(id,eid){
    const i=document.getElementById(id),b=document.getElementById(eid);
    i.type=i.type==='password'?'text':'password';b.textContent=i.type==='password'?'👁':'🙈';
}
function checkStrength(v){
    const w=document.getElementById('strWrap'),bar=document.getElementById('strBar'),lbl=document.getElementById('strLabel');
    if(!v){w.style.display='none';return;}w.style.display='block';
    let sc=0;
    if(v.length>=8)sc++;if(v.length>=12)sc++;if(/[A-Z]/.test(v))sc++;if(/[0-9]/.test(v))sc++;if(/[^A-Za-z0-9]/.test(v))sc++;
    const lv=[[20,'#EF4444','Sangat Lemah'],[40,'#F59E0B','Lemah'],[60,'#EAB308','Cukup'],[80,'#22C55E','Kuat'],[100,'#00D4FF','Sangat Kuat']];
    const[p,c,t]=lv[Math.min(sc,4)];bar.style.width=p+'%';bar.style.background=c;lbl.textContent='Kekuatan: '+t;lbl.style.color=c;
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
