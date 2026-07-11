<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
user_require_login();

$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$tab        = $_GET['tab'] ?? 'overview';
$msg        = ''; $err = '';

// ── Handle POST: update profil ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['name']  ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (strlen($name) < 3) {
            $err = 'Nama minimal 3 karakter.'; $tab = 'profile';
        } else {
            db_exec("UPDATE customers SET name=?, phone=? WHERE id=?", [$name, $phone, $user_id]);
            $_SESSION['user_name']  = $name;
            $_SESSION['user_phone'] = $phone;
            $user_name  = $name;
            $msg = 'Profil berhasil diperbarui!'; $tab = 'profile';
        }
    }

    if ($action === 'change_password') {
        $old  = $_POST['old_pass']  ?? '';
        $new  = $_POST['new_pass']  ?? '';
        $conf = $_POST['conf_pass'] ?? '';
        $user_row = db_row("SELECT password FROM customers WHERE id=?", [$user_id]);
        if (!password_verify($old, $user_row['password'])) {
            $err = 'Password lama tidak cocok.'; $tab = 'profile';
        } elseif (strlen($new) < 8) {
            $err = 'Password baru minimal 8 karakter.'; $tab = 'profile';
        } elseif ($new !== $conf) {
            $err = 'Konfirmasi password tidak cocok.'; $tab = 'profile';
        } else {
            db_exec("UPDATE customers SET password=? WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), $user_id]);
            $msg = 'Password berhasil diubah!'; $tab = 'profile';
        }
    }
}

// Ambil data user + orders
$user   = db_row('SELECT * FROM customers WHERE id = ?', [$user_id]);
$orders = user_get_orders($user_id);

// Stats
$total_orders    = count($orders);
$total_spent     = array_sum(array_column(array_filter($orders, fn($o)=>$o['status']==='completed'), 'total'));
$pending_orders  = count(array_filter($orders, fn($o)=>$o['status']==='pending'));
$completed_orders= count(array_filter($orders, fn($o)=>$o['status']==='completed'));

// Status badge
function statusBadge(string $s): string {
    $map = [
        'pending'    => ['⏳ Pending',    'rgba(245,158,11,.15)',  '#F59E0B', 'rgba(245,158,11,.3)'],
        'paid'       => ['💳 Dibayar',    'rgba(37,99,235,.15)',   '#60A5FA', 'rgba(37,99,235,.3)'],
        'processing' => ['⚡ Diproses',   'rgba(37,99,235,.15)',   '#3B82F6', 'rgba(37,99,235,.3)'],
        'completed'  => ['✅ Selesai',    'rgba(34,197,94,.15)',   '#22C55E', 'rgba(34,197,94,.3)'],
        'cancelled'  => ['❌ Dibatalkan', 'rgba(239,68,68,.15)',   '#EF4444', 'rgba(239,68,68,.3)'],
        'refunded'   => ['🔄 Refund',     'rgba(139,92,246,.15)',  '#8B5CF6', 'rgba(139,92,246,.3)'],
    ];
    [$label,$bg,$color,$border] = $map[$s] ?? [$s,'var(--bg3)','var(--gray)','var(--border)'];
    return "<span style=\"display:inline-flex;align-items:center;padding:.25rem .625rem;border-radius:100px;
        font-size:.7rem;font-weight:700;background:{$bg};color:{$color};border:1px solid {$border}\">{$label}</span>";
}

$page_title = 'Dashboard — ' . htmlspecialchars($user_name);
require_once 'includes/header.php';
?>

<style>
.dash-page { padding: 100px 0 4rem; }
.dash-grid { display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; max-width: 1100px; margin: 0 auto; padding: 0 1.5rem; }
.dash-sidebar { position: sticky; top: 88px; }
.dash-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, var(--blue), var(--cyan));
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 900; margin: 0 auto 1rem;
    border: 3px solid rgba(37,99,235,.4);
}
.dash-nav a {
    display: flex; align-items: center; gap: .625rem;
    padding: .625rem .875rem; border-radius: var(--radius);
    font-size: .875rem; font-weight: 500; color: var(--gray);
    text-decoration: none; transition: var(--transition);
    margin-bottom: 2px;
}
.dash-nav a:hover, .dash-nav a.active { background: var(--bg3); color: var(--white); }
.dash-nav a.active { color: var(--cyan); }
.stat-mini {
    background: var(--bg3); border: 1px solid var(--border);
    border-radius: var(--radius); padding: .875rem;
    text-align: center; transition: var(--transition);
}
.stat-mini:hover { border-color: var(--blue); }
.stat-mini .val { font-size: 1.5rem; font-weight: 900; color: var(--white); }
.stat-mini .lbl { font-size: .72rem; color: var(--gray); margin-top: .2rem; }
.order-row {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(48,54,61,.5);
    transition: var(--transition);
}
.order-row:last-child { border-bottom: none; }
.order-row:hover { background: rgba(255,255,255,.02); }
@media(max-width:768px) {
    .dash-grid { grid-template-columns: 1fr; }
    .dash-sidebar { position: static; }
}
</style>

<div class="dash-page">
<div class="dash-grid">

    <!-- Sidebar -->
    <div class="dash-sidebar">
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1rem;text-align:center">
            <div class="dash-avatar"><?= strtoupper($user_name[0]) ?></div>
            <div style="font-weight:800;font-size:1rem"><?= htmlspecialchars($user_name) ?></div>
            <div style="font-size:.78rem;color:var(--gray);margin-top:.2rem"><?= htmlspecialchars($user_email) ?></div>
            <div style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(34,197,94,.1);
                border:1px solid rgba(34,197,94,.25);color:var(--success);padding:.25rem .625rem;
                border-radius:100px;font-size:.7rem;font-weight:700;margin-top:.625rem">
                ● Member Aktif
            </div>
        </div>

        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1rem;margin-bottom:1rem">
            <nav class="dash-nav">
                <a href="dashboard.php" class="<?= $tab==='overview'?'active':'' ?>">📊 Overview</a>
                <a href="dashboard.php?tab=orders" class="<?= $tab==='orders'?'active':'' ?>">📋 Riwayat Order</a>
                <a href="tracking.php">📦 Cek Status Order</a>
                <a href="dashboard.php?tab=profile" class="<?= $tab==='profile'?'active':'' ?>">👤 Edit Profil</a>
                <a href="products.php">🎮 Top Up Sekarang</a>
                <a href="contact.php">💬 Hubungi Admin</a>
            </nav>
        </div>

        <a href="logout-user.php"
           style="display:flex;align-items:center;justify-content:center;gap:.5rem;
               background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);
               color:var(--danger);padding:.75rem;border-radius:var(--radius);
               font-size:.85rem;font-weight:700;text-decoration:none;transition:.2s"
           onmouseover="this.style.background='rgba(239,68,68,.15)'"
           onmouseout="this.style.background='rgba(239,68,68,.08)'">
            🚪 Logout
        </a>
    </div>

    <!-- Main Content -->
    <div>
        <?php if ($msg): ?>
        <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:var(--radius);
            padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--success);font-size:.875rem;font-weight:600">
            ✅ <?= htmlspecialchars($msg) ?>
        </div>
        <?php endif; ?>
        <?php if ($err): ?>
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:var(--radius);
            padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--danger);font-size:.875rem">
            ⚠️ <?= htmlspecialchars($err) ?>
        </div>
        <?php endif; ?>

        <?php if ($tab === 'profile'): ?>
        <!-- ── PROFILE TAB ── -->
        <?php $u = db_row("SELECT * FROM customers WHERE id=?", [$user_id]); ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
            <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);font-weight:800">👤 Edit Profil</div>
                <form method="POST" style="padding:1.25rem;display:flex;flex-direction:column;gap:.875rem">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-input" required
                               value="<?= htmlspecialchars($u['name'] ?? $user_name) ?>">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" disabled
                               value="<?= htmlspecialchars($u['email'] ?? $user_email) ?>"
                               style="opacity:.6">
                        <small style="color:var(--gray);font-size:.72rem">Email tidak bisa diubah</small>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="tel" name="phone" class="form-input"
                               value="<?= htmlspecialchars($u['phone'] ?? '') ?>"
                               placeholder="08xxxxxxxxxx">
                    </div>
                    <button type="submit" class="btn-primary" style="justify-content:center;padding:.875rem">
                        💾 Simpan Perubahan
                    </button>
                </form>
            </div>

            <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);font-weight:800">🔐 Ganti Password</div>
                <form method="POST" style="padding:1.25rem;display:flex;flex-direction:column;gap:.875rem">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="old_pass" class="form-input" required placeholder="••••••••">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Password Baru (min. 8)</label>
                        <input type="password" name="new_pass" class="form-input" required placeholder="••••••••">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="conf_pass" class="form-input" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-primary" style="justify-content:center;padding:.875rem;
                        background:linear-gradient(135deg,#8B5CF6,#6D28D9)">
                        🔐 Ganti Password
                    </button>
                </form>
            </div>
        </div>

        <?php elseif ($tab === 'orders'): ?>
        <!-- ── ALL ORDERS TAB ── -->
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);font-weight:800">
                📋 Semua Riwayat Order (<?= count($orders) ?>)
            </div>
            <?php if (empty($orders)): ?>
            <div style="text-align:center;padding:3rem;color:var(--gray)">
                <div style="font-size:3rem;margin-bottom:.75rem">📦</div>
                <div style="font-weight:600;color:var(--white);margin-bottom:.5rem">Belum ada order</div>
                <a href="products.php" class="btn-primary" style="display:inline-flex;padding:.75rem 1.5rem;margin-top:.875rem">
                    🎮 Mulai Top Up
                </a>
            </div>
            <?php else: ?>
            <?php foreach ($orders as $o):
                $scol_map = [
                    'completed'  => ['✅','var(--success)'],
                    'processing' => ['⚡','var(--blue)'],
                    'pending'    => ['⏳','var(--warning)'],
                    'cancelled'  => ['❌','var(--danger)'],
                ];
                $scol = $scol_map[$o['status']] ?? ['🔄','var(--gray)'];
            ?>
            <div style="display:flex;align-items:center;gap:.875rem;padding:.875rem 1.25rem;
                border-bottom:1px solid rgba(48,54,61,.4);transition:var(--transition)"
                onmouseover="this.style.background='rgba(255,255,255,.02)'"
                onmouseout="this.style.background='none'">
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.2rem">
                        <span style="font-weight:700;font-size:.875rem"><?= htmlspecialchars($o['product_name']) ?></span>
                        <span style="font-size:.7rem;font-weight:700;color:<?= $scol[1] ?>"><?= $scol[0] ?> <?= ucfirst($o['status']) ?></span>
                    </div>
                    <div style="font-size:.75rem;color:var(--gray)">
                        <?= htmlspecialchars($o['package_amount']) ?> <?= htmlspecialchars($o['currency']) ?>
                        · ID: <span style="font-family:monospace"><?= htmlspecialchars($o['game_user_id']) ?></span>
                        · <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-weight:800;color:var(--cyan)"><?= formatRupiah((int)$o['total']) ?></div>
                    <div style="display:flex;gap:.375rem;margin-top:.3rem;justify-content:flex-end">
                        <a href="tracking.php?id=<?= $o['id'] ?>" style="font-size:.68rem;color:var(--blue)">📦 Track</a>
                        <?php if ($o['status']==='completed'): ?>
                        <span style="color:var(--gray2)">·</span>
                        <a href="invoice.php?id=<?= $o['id'] ?>" style="font-size:.68rem;color:var(--cyan)">🧾 Invoice</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- ── OVERVIEW TAB (default) ── -->

        <!-- Stats row -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.875rem;margin-bottom:1.25rem">
            <div class="stat-mini">
                <div class="val"><?= $total_orders ?></div>
                <div class="lbl">Total Order</div>
            </div>
            <div class="stat-mini">
                <div class="val"><?= $completed_orders ?></div>
                <div class="lbl">Selesai</div>
            </div>
            <div class="stat-mini">
                <div class="val"><?= $pending_orders ?></div>
                <div class="lbl">Pending</div>
            </div>
            <div class="stat-mini">
                <div class="val" style="font-size:1.1rem;color:var(--cyan)"><?= formatRupiah($total_spent) ?></div>
                <div class="lbl">Total Spent</div>
            </div>
        </div>

        <!-- Recent orders -->
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.25rem">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between">
                <div style="font-weight:800">📋 Riwayat Order</div>
                <?php if ($total_orders > 5): ?>
                <a href="dashboard.php?tab=orders" style="font-size:.8rem;color:var(--blue)">Lihat Semua →</a>
                <?php endif; ?>
            </div>

            <?php if (empty($orders)): ?>
            <div style="text-align:center;padding:3rem;color:var(--gray)">
                <div style="font-size:3rem;margin-bottom:.75rem">📦</div>
                <div style="font-weight:600;color:var(--white);margin-bottom:.5rem">Belum ada order</div>
                <p style="font-size:.85rem;margin-bottom:1.25rem">Yuk mulai top up game favoritmu!</p>
                <a href="products.php" class="btn-primary"
                    style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem">
                    🎮 Lihat Produk
                </a>
            </div>
            <?php else: ?>
            <?php foreach (array_slice($orders, 0, 5) as $o): ?>
            <div class="order-row">
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem">
                        <span style="font-weight:700;font-size:.875rem"><?= htmlspecialchars($o['product_name']) ?></span>
                        <?= statusBadge($o['status']) ?>
                    </div>
                    <div style="font-size:.75rem;color:var(--gray)">
                        <?= htmlspecialchars(($o['package_amount'] ?? '').' '.($o['currency'] ?? '')) ?>
                        · ID: <span style="font-family:monospace"><?= htmlspecialchars($o['game_user_id']) ?></span>
                        · <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-weight:800;color:var(--cyan);font-size:.9rem"><?= formatRupiah($o['total']) ?></div>
                    <div style="display:flex;gap:.375rem;margin-top:.375rem;justify-content:flex-end">
                        <a href="tracking.php?id=<?= $o['id'] ?>" style="font-size:.68rem;color:var(--blue)">📦 Cek</a>
                        <?php if ($o['status']==='completed'): ?>
                        <span style="color:var(--gray2)">·</span>
                        <a href="invoice.php?id=<?= $o['id'] ?>" style="font-size:.68rem;color:var(--cyan)">🧾 Invoice</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Quick actions -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.875rem">
            <?php foreach ([
                ['🎮','Top Up Game','Pilih game & paket','products.php','var(--blue)'],
                ['📦','Cek Status','Lacak pesananmu','tracking.php','var(--cyan)'],
                ['💬','Chat Admin','Tanya atau konfirmasi','contact.php','#25D366'],
                ['❓','FAQ','Pertanyaan umum','faq.php','#8b5cf6'],
            ] as [$ic,$title,$desc,$url,$col]): ?>
            <a href="<?= $url ?>" style="background:var(--bg2);border:1px solid var(--border);
                border-radius:var(--radius-lg);padding:1.25rem;text-decoration:none;
                display:flex;flex-direction:column;align-items:center;gap:.5rem;text-align:center;
                transition:var(--transition)"
                onmouseover="this.style.borderColor='<?= $col ?>'"
                onmouseout="this.style.borderColor='var(--border)'">
                <span style="font-size:2rem"><?= $ic ?></span>
                <div style="font-weight:700;font-size:.875rem;color:var(--white)"><?= $title ?></div>
                <div style="font-size:.72rem;color:var(--gray)"><?= $desc ?></div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php endif; // end tab switch ?>

    </div>
</div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
