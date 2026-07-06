<?php
require_once 'includes/admin_config.php';
requireLogin();

// Muat semua sesi dari engine chat
require_once '../chat/chat_engine_db.php';

$page_title  = 'Live Chat';
$active_menu = 'chat';
$chat_unread = chat_total_unread_admin();

// Quick replies untuk dipass ke JS
$quick_replies = chat_quick_replies();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Live Chat — <?= SITE_NAME ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
<style>
/* ── Chat layout ── */
.chat-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    height: calc(100vh - var(--topbar-h));
    overflow: hidden;
}

/* ── Session list ── */
.chat-sessions {
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    overflow: hidden; background: var(--bg2);
}
.chat-sessions-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    display: flex; flex-direction: column; gap: .625rem;
    flex-shrink: 0;
}
.chat-sessions-title {
    display: flex; align-items: center; justify-content: space-between;
    font-weight: 800; font-size: .95rem;
}
.chat-search {
    position: relative;
}
.chat-search input {
    width: 100%; background: var(--bg3);
    border: 1px solid var(--border); border-radius: 8px;
    padding: .45rem .875rem .45rem 2rem;
    color: var(--white); font-size: .8rem; outline: none;
    transition: var(--tr); font-family: inherit;
}
.chat-search input:focus { border-color: var(--blue); }
.chat-search .ico { position: absolute; left: .625rem; top: 50%; transform: translateY(-50%); font-size: .8rem; }
.session-filter-tabs {
    display: flex; gap: 4px; padding: .625rem 1rem;
    border-bottom: 1px solid var(--border); flex-shrink: 0;
}
.sft { background: none; border: 1px solid var(--border); color: var(--gray);
    padding: .3rem .7rem; border-radius: 6px; font-size: .72rem;
    font-weight: 600; cursor: pointer; transition: var(--tr); font-family: inherit; }
.sft:hover { border-color: var(--blue); color: var(--white); }
.sft.active { background: var(--blue); border-color: var(--blue); color: #fff; }
.sessions-list {
    flex: 1; overflow-y: auto;
    padding: .5rem;
}
.session-item {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .75rem; border-radius: 10px;
    cursor: pointer; transition: var(--tr);
    position: relative; margin-bottom: 2px;
}
.session-item:hover  { background: var(--bg3); }
.session-item.active { background: rgba(37,99,235,.12); border: 1px solid rgba(37,99,235,.25); }
.session-avatar {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--blue), var(--cyan));
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .95rem; position: relative;
}
.session-online {
    position: absolute; bottom: 1px; right: 1px;
    width: 10px; height: 10px; border-radius: 50%;
    background: var(--success); border: 2px solid var(--bg2);
}
.session-info { flex: 1; min-width: 0; }
.session-name { font-weight: 700; font-size: .875rem; display: flex; align-items: center; justify-content: space-between; }
.session-time { font-size: .65rem; color: var(--gray2); }
.session-preview { font-size: .75rem; color: var(--gray); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: .15rem; }
.session-meta { display: flex; align-items: center; gap: .4rem; margin-top: .3rem; flex-wrap: wrap; }
.session-topic { font-size: .65rem; background: var(--bg3); border: 1px solid var(--border);
    color: var(--gray); padding: .15rem .45rem; border-radius: 4px; }
.s-unread {
    min-width: 18px; height: 18px; border-radius: 9px;
    background: var(--danger); color: #fff;
    font-size: .65rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px; flex-shrink: 0;
}
.s-status { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.s-status.open       { background: var(--success); }
.s-status.pending    { background: var(--warning); }
.s-status.resolved   { background: var(--gray2); }
.empty-sessions {
    text-align: center; padding: 3rem 1rem;
    color: var(--gray); font-size: .85rem;
}
.empty-sessions .ico { font-size: 2.5rem; display: block; margin-bottom: .75rem; }

/* ── Chat window ── */
.chat-window {
    display: flex; flex-direction: column;
    background: var(--bg); overflow: hidden;
}
.chat-empty-state {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    color: var(--gray); gap: .75rem;
}
.chat-empty-state .ico { font-size: 4rem; }
.chat-empty-state h3  { font-size: 1.1rem; font-weight: 700; color: var(--white); }
.chat-empty-state p   { font-size: .85rem; }

/* chat header */
.cw-header {
    background: var(--bg2); border-bottom: 1px solid var(--border);
    padding: .875rem 1.25rem;
    display: flex; align-items: center; gap: .875rem;
    flex-shrink: 0;
}
.cw-avatar {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg,var(--blue),var(--cyan));
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .95rem;
}
.cw-name  { font-weight: 800; font-size: .95rem; }
.cw-sub   { font-size: .75rem; color: var(--gray); display: flex; align-items: center; gap: .4rem; }
.cw-actions { margin-left: auto; display: flex; gap: .5rem; }

/* messages */
.cw-messages {
    flex: 1; overflow-y: auto; padding: 1.25rem;
    display: flex; flex-direction: column; gap: .625rem;
    scroll-behavior: smooth;
}
.cw-messages::-webkit-scrollbar { width: 4px; }
.cw-messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

.cw-msg {
    display: flex; flex-direction: column;
    max-width: 75%; animation: msgIn .2s ease;
}
@keyframes msgIn { from { opacity:0;transform:translateY(6px) } to { opacity:1;transform:none } }
.cw-msg.customer { align-self: flex-start; }
.cw-msg.admin    { align-self: flex-end; }
.cw-msg.system   { align-self: center; max-width: 90%; }
.cw-sender-label { font-size: .68rem; color: var(--gray2); margin-bottom: .2rem; }
.cw-bubble {
    padding: .6rem .95rem; border-radius: 14px;
    font-size: .875rem; line-height: 1.55; color: var(--white); word-break: break-word;
}
.cw-msg.customer .cw-bubble { background: var(--bg2); border: 1px solid var(--border); border-bottom-left-radius: 4px; }
.cw-msg.admin    .cw-bubble { background: linear-gradient(135deg,#1a3a7a,#2563EB); border-bottom-right-radius: 4px; }
.cw-msg.system   .cw-bubble {
    background: rgba(37,99,235,.08); border: 1px solid rgba(37,99,235,.2);
    font-size: .78rem; color: var(--gray); text-align: center; border-radius: 8px;
}
.cw-meta { font-size: .65rem; color: var(--gray2); margin-top: .2rem; display: flex; align-items: center; gap: .3rem; }
.cw-msg.admin .cw-meta { justify-content: flex-end; }
.read-tick { color: var(--cyan); }

/* typing */
.cw-typing {
    padding: .5rem 1.25rem; font-size: .75rem; color: var(--gray);
    display: none; align-items: center; gap: .5rem; flex-shrink: 0;
}
.tdots { display:flex; gap:3px }
.tdots span { width:5px;height:5px;border-radius:50%;background:var(--gray); animation:tdot 1.2s infinite }
.tdots span:nth-child(2){animation-delay:.2s}
.tdots span:nth-child(3){animation-delay:.4s}
@keyframes tdot { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-4px)} }

/* input area */
.cw-input-area {
    border-top: 1px solid var(--border);
    background: var(--bg2); padding: .75rem 1rem;
    display: flex; flex-direction: column; gap: .625rem;
    flex-shrink: 0;
}
.quick-replies {
    display: flex; gap: .375rem; flex-wrap: nowrap; overflow-x: auto;
    padding-bottom: 2px;
}
.quick-replies::-webkit-scrollbar { height: 3px; }
.quick-replies::-webkit-scrollbar-thumb { background: var(--border); }
.qr-btn {
    background: var(--bg3); border: 1px solid var(--border);
    color: var(--gray); padding: .3rem .7rem;
    border-radius: 6px; font-size: .72rem; font-weight: 600;
    cursor: pointer; white-space: nowrap; transition: var(--tr); font-family: inherit;
}
.qr-btn:hover { border-color: var(--blue); color: var(--cyan); }
.cw-input-row {
    display: flex; align-items: flex-end; gap: .625rem;
}
.cw-textarea {
    flex: 1; background: var(--bg3); border: 1.5px solid var(--border);
    border-radius: 10px; padding: .6rem .875rem;
    color: var(--white); font-size: .875rem; resize: none;
    max-height: 120px; outline: none; transition: var(--tr);
    font-family: inherit; line-height: 1.5;
}
.cw-textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
.cw-send {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg,var(--blue),var(--blue-l));
    border: none; color: #fff; font-size: 1.1rem;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: var(--tr); flex-shrink: 0;
}
.cw-send:hover { transform: scale(1.08); }
.cw-send:disabled { opacity: .35; cursor: default; transform: none; }

/* info panel */
.cw-info-panel {
    width: 260px; border-left: 1px solid var(--border);
    background: var(--bg2); padding: 1.25rem; overflow-y: auto;
    display: none; flex-direction: column; gap: 1.25rem;
    flex-shrink: 0;
}
.cw-info-panel.show { display: flex; }
.info-section h4 { font-size: .78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--gray2); margin-bottom: .75rem; }
.info-row { display: flex; justify-content: space-between; font-size: .82rem; padding: .3rem 0;
    border-bottom: 1px solid rgba(48,54,61,.4); }
.info-row:last-child { border-bottom: none; }
.info-label { color: var(--gray); }
.info-value { font-weight: 600; color: var(--white); text-align: right; max-width: 60%; }

/* date divider */
.date-divider {
    align-self: center; font-size: .7rem; color: var(--gray2);
    background: var(--bg3); border: 1px solid var(--border);
    padding: .2rem .75rem; border-radius: 100px; margin: .5rem 0;
}

/* mobile responsive */
@media(max-width:900px) {
    .chat-layout { grid-template-columns: 1fr; }
    .chat-sessions { display: none; }
    .chat-sessions.mobile-show { display: flex; position: fixed; inset: var(--topbar-h) 0 0 var(--sidebar-w); z-index: 50; }
    .cw-info-panel { display: none !important; }
}
</style>
<script>
// showToast fallback jika admin.js belum load
function showToast(msg, type) {
    type = type || 'success';
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;' +
        'background:' + (type==='error'?'#EF4444':type==='info'?'#3B82F6':'#22C55E') + ';' +
        'color:#fff;padding:.75rem 1.25rem;border-radius:10px;font-size:.85rem;font-weight:700;' +
        'box-shadow:0 4px 20px rgba(0,0,0,.3);animation:slideIn .3s ease;max-width:320px';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
</script>
</head>
<body>

<?php
// Sidebar (manual karena tidak pakai admin_layout.php — layout chat special)
$menu_items = [
    ['icon'=>'📊','label'=>'Dashboard',  'href'=>'index.php',    'key'=>'dashboard'],
    ['icon'=>'📋','label'=>'Pesanan',    'href'=>'orders.php',   'key'=>'orders'],
    ['icon'=>'🎮','label'=>'Produk',     'href'=>'products.php', 'key'=>'products'],
    ['icon'=>'👥','label'=>'Pelanggan',  'href'=>'customers.php','key'=>'customers'],
    ['icon'=>'💬','label'=>'Live Chat',  'href'=>'chat.php',     'key'=>'chat'],
    ['icon'=>'📈','label'=>'Laporan',    'href'=>'reports.php',  'key'=>'reports'],
    ['icon'=>'⚙️','label'=>'Pengaturan', 'href'=>'settings.php', 'key'=>'settings'],
];
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo"><span class="logo-icon">🎮</span><span class="logo-text">Game<span class="logo-accent">Store</span></span></div>
        <button class="sidebar-close" id="sidebarClose">✕</button>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($menu_items as $item): ?>
        <a href="<?= $item['href'] ?>" class="nav-item <?= $active_menu===$item['key']?'active':'' ?>">
            <span class="nav-icon"><?= $item['icon'] ?></span>
            <span class="nav-label"><?= $item['label'] ?></span>
            <?php if ($item['key']==='chat' && $chat_unread > 0): ?>
            <span class="nav-badge"><?= $chat_unread ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank" class="nav-item"><span class="nav-icon">🌐</span><span class="nav-label">Lihat Website</span></a>
        <a href="logout.php" class="nav-item nav-logout"><span class="nav-icon">🚪</span><span class="nav-label">Logout</span></a>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" id="sidebarToggle">☰</button>
            <div class="topbar-title">💬 Live Chat</div>
        </div>
        <div class="topbar-right">
            <div class="topbar-date"><?= date('d F Y') ?></div>
            <div class="topbar-notif" id="chatUnreadBadge" title="Pesan belum dibaca" style="<?= $chat_unread ? '' : 'display:none' ?>">
                💬 <span class="notif-count" id="chatUnreadCount"><?= $chat_unread ?></span>
            </div>
            <div class="topbar-avatar">A</div>
        </div>
    </header>

    <!-- Chat Layout -->
    <div class="chat-layout">

        <!-- LEFT: Session List -->
        <div class="chat-sessions" id="chatSessions">
            <div class="chat-sessions-header">
                <div class="chat-sessions-title">
                    <span>Percakapan</span>
                    <span id="sessionCount" style="font-size:.75rem;color:var(--gray);font-weight:500">0 sesi</span>
                </div>
                <div class="chat-search">
                    <span class="ico">🔍</span>
                    <input type="text" id="sessionSearch" placeholder="Cari nama / topik...">
                </div>
            </div>
            <div class="session-filter-tabs">
                <button class="sft active" data-filter="all">Semua</button>
                <button class="sft" data-filter="open">Aktif</button>
                <button class="sft" data-filter="pending">Pending</button>
                <button class="sft" data-filter="resolved">Selesai</button>
            </div>
            <div class="sessions-list" id="sessionsList">
                <div class="empty-sessions">
                    <span class="ico">💬</span>
                    Memuat percakapan...
                </div>
            </div>
        </div>

        <!-- RIGHT: Chat Window -->
        <div class="chat-window" id="chatWindow">
            <!-- Empty state -->
            <div class="chat-empty-state" id="chatEmptyState">
                <div class="ico">💬</div>
                <h3>Pilih Percakapan</h3>
                <p>Pilih sesi chat dari daftar kiri untuk mulai membalas</p>
            </div>

            <!-- Active chat (hidden initially) -->
            <div id="activeChatPanel" style="display:none; flex-direction:column; height:100%; overflow:hidden; flex:1;">

                <!-- Chat header -->
                <div class="cw-header" id="cwHeader">
                    <div class="cw-avatar" id="cwAvatar">?</div>
                    <div>
                        <div class="cw-name" id="cwName">—</div>
                        <div class="cw-sub">
                            <span id="cwTopic">—</span>
                            <span>·</span>
                            <span id="cwStatus">—</span>
                        </div>
                    </div>
                    <div class="cw-actions">
                        <button class="btn btn-ghost btn-sm" onclick="toggleInfoPanel()" title="Info pelanggan">ℹ️</button>
                        <button class="btn btn-success btn-sm" id="btnResolve" onclick="resolveSession()">✅ Selesaikan</button>
                        <button class="btn btn-danger btn-sm" style="display:none" id="btnReopen" onclick="reopenSession()">🔄 Buka Lagi</button>
                    </div>
                </div>

                <!-- Messages area -->
                <div class="cw-messages" id="cwMessages"></div>

                <!-- Typing indicator -->
                <div class="cw-typing" id="cwTyping">
                    <div class="tdots"><span></span><span></span><span></span></div>
                    Customer sedang mengetik...
                </div>

                <!-- Input area -->
                <div class="cw-input-area" id="cwInputArea">
                    <div class="quick-replies" id="quickReplies"></div>
                    <div class="cw-input-row">
                        <textarea class="cw-textarea" id="cwInput" rows="1"
                            placeholder="Ketik balasan..."
                            oninput="cwAutoResize(this); cwSendTyping()"
                            onkeydown="cwKeyDown(event)"></textarea>
                        <button class="cw-send" id="cwSendBtn" onclick="cwSend()" disabled>➤</button>
                    </div>
                </div>

            </div>

            <!-- Info side panel -->
            <div class="cw-info-panel" id="cwInfoPanel">
                <div class="info-section">
                    <h4>Info Pelanggan</h4>
                    <div id="infoContent"></div>
                </div>
                <div class="info-section">
                    <h4>Tindakan Cepat</h4>
                    <div style="display:flex;flex-direction:column;gap:.5rem">
                        <button class="btn btn-ghost btn-sm" onclick="cwSetQuick('Pesanan kamu sudah selesai diproses! ✅')">✅ Konfirmasi Selesai</button>
                        <button class="btn btn-ghost btn-sm" onclick="cwSetQuick('Pesanan sedang kami proses, mohon tunggu sebentar ya. 🙏')">⏳ Sedang Proses</button>
                        <button class="btn btn-ghost btn-sm" onclick="cwSetQuick('Boleh kirim User ID game kamu? 🆔')">🆔 Minta User ID</button>
                        <button class="btn btn-ghost btn-sm" onclick="cwSetQuick('Pembayaran bisa via DANA, OVO, GoPay, ShopeePay, atau Transfer Bank. 💳')">💳 Info Bayar</button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- .chat-layout -->
</div><!-- .main-wrapper -->

<div id="adminToast" class="toast"></div>

<script>
// ── Config ────────────────────────────────────────────────────
const API          = '../chat/api.php';
const QUICK        = <?= json_encode($quick_replies, JSON_UNESCAPED_UNICODE) ?>;
let activeSession  = null;   // current session object
let lastMsgId      = null;
let pollTimer      = null;
let sessionTimer   = null;
let typingTimer    = null;
let infoPanelOpen  = false;
let currentFilter  = 'all';

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    buildQuickReplies();
    loadSessions();
    sessionTimer = setInterval(loadSessions, 5000);  // refresh sesi tiap 5 detik

    // Search
    document.getElementById('sessionSearch').addEventListener('input', function() {
        filterSessions(this.value, currentFilter);
    });
    // Filter tabs
    document.querySelectorAll('.sft').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.sft').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterSessions(document.getElementById('sessionSearch').value, currentFilter);
        });
    });
    // Sidebar
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('show');
    });
    document.getElementById('sidebarClose')?.addEventListener('click', closeSidebar);
    document.getElementById('sidebarOverlay')?.addEventListener('click', closeSidebar);
});
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

// ── Load sessions ─────────────────────────────────────────────
function loadSessions() {
    fetch(API + '?action=sessions')
        .then(r => r.json())
        .then(d => {
            if (d.error) return;
            renderSessions(d.sessions);
            // Update total unread badge
            const unread = d.total_unread || 0;
            const badge = document.getElementById('chatUnreadBadge');
            const count = document.getElementById('chatUnreadCount');
            badge.style.display = unread > 0 ? '' : 'none';
            count.textContent = unread;
        });
}

function renderSessions(sessions) {
    const list = document.getElementById('sessionsList');
    const search = document.getElementById('sessionSearch').value.toLowerCase();

    if (!sessions || !sessions.length) {
        list.innerHTML = `<div class="empty-sessions"><span class="ico">💬</span>Belum ada percakapan masuk</div>`;
        document.getElementById('sessionCount').textContent = '0 sesi';
        return;
    }

    document.getElementById('sessionCount').textContent = sessions.length + ' sesi';

    // Update active session data if open
    if (activeSession) {
        const updated = sessions.find(s => s.id === activeSession.id);
        if (updated) activeSession = updated;
    }

    list.innerHTML = '';
    let shown = 0;
    sessions.forEach(s => {
        // Filter by tab
        if (currentFilter !== 'all' && s.status !== currentFilter) return;
        // Filter by search
        if (search && !s.name.toLowerCase().includes(search) && !s.topic.toLowerCase().includes(search)) return;

        shown++;
        const initial = s.name.charAt(0).toUpperCase();
        const timeStr = timeAgo(s.last_time);
        const isActive = activeSession && activeSession.id === s.id;
        const unread   = s.unread_admin || 0;
        const lastMsg  = s.last_message
            ? s.last_message.substring(0, 45) + (s.last_message.length > 45 ? '…' : '')
            : 'Belum ada pesan';

        const div = document.createElement('div');
        div.className = 'session-item' + (isActive ? ' active' : '');
        div.dataset.sid    = s.id;
        div.dataset.status = s.status;
        div.innerHTML = `
            <div class="session-avatar">${initial}
                ${s.status==='open'?'<div class="session-online"></div>':''}
            </div>
            <div class="session-info">
                <div class="session-name">
                    <span>${esc(s.name)}</span>
                    <span class="session-time">${timeStr}</span>
                </div>
                <div class="session-preview">${esc(lastMsg)}</div>
                <div class="session-meta">
                    <span class="session-topic">${esc(s.topic)}</span>
                    <span class="s-status ${s.status}"></span>
                    <span style="font-size:.65rem;color:var(--gray2)">${s.status}</span>
                </div>
            </div>
            ${unread > 0 ? `<div class="s-unread">${unread}</div>` : ''}`;

        div.addEventListener('click', () => openSession(s));
        list.appendChild(div);
    });

    if (!shown) {
        list.innerHTML = `<div class="empty-sessions"><span class="ico">🔍</span>Tidak ada hasil</div>`;
    }
}

function filterSessions(search, status) {
    document.querySelectorAll('.session-item').forEach(item => {
        const name = item.querySelector('.session-name span')?.textContent.toLowerCase() || '';
        const topic = item.querySelector('.session-topic')?.textContent.toLowerCase() || '';
        const stat  = item.dataset.status;
        const matchSearch = !search || name.includes(search) || topic.includes(search);
        const matchStatus = status === 'all' || stat === status;
        item.style.display = matchSearch && matchStatus ? '' : 'none';
    });
}

// ── Open session ──────────────────────────────────────────────
function openSession(session) {
    activeSession = session;
    lastMsgId     = null;
    stopPolling();

    // Highlight in list
    document.querySelectorAll('.session-item').forEach(el => {
        el.classList.toggle('active', el.dataset.sid === session.id);
    });

    // Show chat panel
    document.getElementById('chatEmptyState').style.display  = 'none';
    const panel = document.getElementById('activeChatPanel');
    panel.style.display    = 'flex';
    panel.style.flex       = '1';

    // Header
    document.getElementById('cwAvatar').textContent = session.name.charAt(0).toUpperCase();
    document.getElementById('cwName').textContent   = session.name;
    document.getElementById('cwTopic').textContent  = '📌 ' + session.topic;

    updateSessionHeader(session.status);
    updateInfoPanel(session);

    // Load history
    document.getElementById('cwMessages').innerHTML = '';
    fetch(API + `?action=history&session_id=${session.id}&reader=admin`)
        .then(r => r.json())
        .then(d => {
            if (d.error) { showToast('⚠️ ' + d.error, 'error'); return; }
            const msgs = d.messages || [];
            let lastDate = '';
            msgs.forEach(m => {
                const msgDate = new Date(m.timestamp * 1000).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'});
                if (msgDate !== lastDate) {
                    appendDateDivider(msgDate);
                    lastDate = msgDate;
                }
                renderMsg(m);
            });
            if (msgs.length) lastMsgId = msgs[msgs.length - 1].id;
            scrollBottom();
            startPolling();
            // Clear unread in sidebar
            const item = document.querySelector(`.session-item[data-sid="${session.id}"] .s-unread`);
            if (item) item.remove();
        });
}

function updateSessionHeader(status) {
    const statusMap = { open: '🟢 Aktif', pending: '🟡 Pending', resolved: '⚫ Selesai' };
    const statusEl = document.getElementById('cwStatus');
    statusEl.textContent = statusMap[status] || status;

    const inputArea = document.getElementById('cwInputArea');
    const btnResolve = document.getElementById('btnResolve');
    const btnReopen  = document.getElementById('btnReopen');

    if (status === 'resolved') {
        inputArea.style.display  = 'none';
        btnResolve.style.display = 'none';
        btnReopen.style.display  = '';
    } else {
        inputArea.style.display  = 'flex';
        btnResolve.style.display = '';
        btnReopen.style.display  = 'none';
    }
}

function updateInfoPanel(session) {
    document.getElementById('infoContent').innerHTML = `
        <div class="info-row"><span class="info-label">Nama</span><span class="info-value">${esc(session.name)}</span></div>
        <div class="info-row"><span class="info-label">Email</span><span class="info-value">${esc(session.email||'-')}</span></div>
        <div class="info-row"><span class="info-label">Topik</span><span class="info-value">${esc(session.topic)}</span></div>
        <div class="info-row"><span class="info-label">Status</span><span class="info-value">${session.status}</span></div>
        <div class="info-row"><span class="info-label">Mulai</span><span class="info-value">${new Date(session.created_at*1000).toLocaleString('id-ID')}</span></div>
        <div class="info-row"><span class="info-label">ID Sesi</span><span class="info-value" style="font-size:.65rem;font-family:monospace">${session.id}</span></div>`;
}

// ── Polling ───────────────────────────────────────────────────
function startPolling() {
    stopPolling();
    pollTimer = setInterval(pollMessages, 2000);
}
function stopPolling() { if (pollTimer) clearInterval(pollTimer); }

function pollMessages() {
    if (!activeSession) return;
    const url = API + `?action=poll&session_id=${activeSession.id}&reader=admin` + (lastMsgId ? `&after_id=${lastMsgId}` : '');
    fetch(url).then(r => r.json()).then(d => {
        if (d.error) return;
        if (d.messages && d.messages.length) {
            d.messages.forEach(m => renderMsg(m));
            lastMsgId = d.messages[d.messages.length - 1].id;
            scrollBottom();
        }
        // Typing
        const typEl = document.getElementById('cwTyping');
        typEl.style.display = d.typing ? 'flex' : 'none';
        if (d.typing) scrollBottom();

        if (d.status !== activeSession.status) {
            activeSession.status = d.status;
            updateSessionHeader(d.status);
        }
    });
}

// ── Send message ──────────────────────────────────────────────
window.cwSend = function() {
    const inp  = document.getElementById('cwInput');
    const text = inp.value.trim();
    if (!text || !activeSession) return;
    if (activeSession.status === 'resolved') { showToast('⚠️ Sesi sudah ditutup', 'error'); return; }

    inp.value = ''; cwAutoResize(inp);
    document.getElementById('cwSendBtn').disabled = true;
    clearTimeout(typingTimer);
    sendTyping(false);

    const fd = new FormData();
    fd.append('session_id', activeSession.id);
    fd.append('sender', 'admin');
    fd.append('text', text);
    fetch(API + '?action=send', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            if (d.error) { showToast('⚠️ ' + d.error, 'error'); return; }
            renderMsg({ sender:'admin', sender_name:'Admin', text, timestamp: d.timestamp, id: d.msg_id });
            lastMsgId = d.msg_id;
            scrollBottom();
        });
};

// ── Typing ────────────────────────────────────────────────────
window.cwSendTyping = function() {
    const inp = document.getElementById('cwInput');
    document.getElementById('cwSendBtn').disabled = !inp.value.trim();
    if (!activeSession) return;
    clearTimeout(typingTimer);
    sendTyping(true);
    typingTimer = setTimeout(() => sendTyping(false), 2500);
};
function sendTyping(val) {
    if (!activeSession) return;
    const fd = new FormData();
    fd.append('session_id', activeSession.id);
    fd.append('who', 'admin');
    fd.append('typing', val ? '1' : '0');
    fetch(API + '?action=typing', { method:'POST', body:fd }).catch(()=>{});
}

window.cwKeyDown = function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); cwSend(); }
};
window.cwAutoResize = function(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
};

// ── Quick replies ─────────────────────────────────────────────
function buildQuickReplies() {
    const container = document.getElementById('quickReplies');
    QUICK.forEach(q => {
        const btn = document.createElement('button');
        btn.className = 'qr-btn';
        btn.textContent = q.label;
        btn.onclick = () => cwSetQuick(q.text);
        container.appendChild(btn);
    });
}
window.cwSetQuick = function(text) {
    const inp = document.getElementById('cwInput');
    inp.value = text;
    cwAutoResize(inp);
    document.getElementById('cwSendBtn').disabled = false;
    inp.focus();
};

// ── Resolve / Reopen ──────────────────────────────────────────
window.resolveSession = function() {
    if (!activeSession) return;
    if (!confirm('Tandai sesi ini sebagai selesai?')) return;
    const fd = new FormData();
    fd.append('session_id', activeSession.id);
    fd.append('status', 'resolved');
    fetch(API + '?action=resolve', { method:'POST', body:fd })
        .then(r => r.json()).then(d => {
            if (d.error) { showToast('⚠️ ' + d.error, 'error'); return; }
            activeSession.status = 'resolved';
            updateSessionHeader('resolved');
            showToast('✅ Sesi diselesaikan!');
            loadSessions();
            // Render system message
            renderMsg({ sender:'system', is_system:true, text:'✅ Sesi chat telah diselesaikan.', timestamp: Date.now()/1000 });
            scrollBottom();
        });
};
window.reopenSession = function() {
    if (!activeSession) return;
    const fd = new FormData();
    fd.append('session_id', activeSession.id);
    fd.append('status', 'open');
    fetch(API + '?action=resolve', { method:'POST', body:fd })
        .then(r => r.json()).then(d => {
            if (d.error) { showToast('⚠️ ' + d.error, 'error'); return; }
            activeSession.status = 'open';
            updateSessionHeader('open');
            showToast('🔄 Sesi dibuka kembali!');
            loadSessions();
        });
};

// ── Info panel ─────────────────────────────────────────────────
window.toggleInfoPanel = function() {
    infoPanelOpen = !infoPanelOpen;
    document.getElementById('cwInfoPanel').classList.toggle('show', infoPanelOpen);
};

// ── Render ─────────────────────────────────────────────────────
function renderMsg(m) {
    const box = document.getElementById('cwMessages');
    const cls = m.is_system ? 'system' : m.sender;
    const time = m.timestamp
        ? new Date(m.timestamp*1000).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})
        : '';
    const div = document.createElement('div');
    div.className = `cw-msg ${cls}`;
    div.dataset.id = m.id || '';
    const txt = (m.text||'').replace(/\*([^*]+)\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>');
    div.innerHTML = `
        ${cls==='customer'?`<div class="cw-sender-label">${esc(m.sender_name||'Customer')}</div>`:''}
        <div class="cw-bubble">${txt}</div>
        <div class="cw-meta">
            ${time}
            ${cls==='admin'?`<span class="read-tick">${m.read?'✓✓':'✓'}</span>`:''}
        </div>`;
    box.appendChild(div);
}

function appendDateDivider(label) {
    const box = document.getElementById('cwMessages');
    const div = document.createElement('div');
    div.className = 'date-divider';
    div.textContent = label;
    box.appendChild(div);
}

function scrollBottom() {
    const box = document.getElementById('cwMessages');
    box.scrollTop = box.scrollHeight;
}

// ── Utilities ──────────────────────────────────────────────────
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function timeAgo(ts) {
    const d = Date.now()/1000 - ts;
    if (d < 60)    return 'Baru saja';
    if (d < 3600)  return Math.floor(d/60) + ' mnt lalu';
    if (d < 86400) return Math.floor(d/3600) + ' jam lalu';
    return new Date(ts*1000).toLocaleDateString('id-ID',{day:'numeric',month:'short'});
}
function showToast(msg, type='success') {
    const t = document.getElementById('adminToast');
    t.textContent = msg;
    t.className = `toast ${type} show`;
    setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>
