<!-- =====================================================
     GAMESTORE CHAT WIDGET (embed di footer semua halaman)
     ===================================================== -->
<style>
/* ── Chat bubble button ─────────────────────────────── */
#gchatBubble {
    position: fixed; bottom: 2rem; right: 2rem; z-index: 1000;
    width: 58px; height: 58px; border-radius: 50%;
    background: linear-gradient(135deg, #2563EB, #00D4FF);
    border: none; cursor: pointer;
    box-shadow: 0 4px 20px rgba(37,99,235,.5);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; transition: transform .25s, box-shadow .25s;
    animation: gchatPulse 3s infinite;
}
#gchatBubble:hover { transform: scale(1.1); box-shadow: 0 8px 30px rgba(37,99,235,.6); }
#gchatUnread {
    position: absolute; top: -4px; right: -4px;
    background: #EF4444; color: #fff;
    font-size: .65rem; font-weight: 800;
    min-width: 18px; height: 18px; border-radius: 9px;
    display: none; align-items: center; justify-content: center;
    padding: 0 4px; font-family: Inter, sans-serif;
}
@keyframes gchatPulse {
    0%,100% { box-shadow: 0 4px 20px rgba(37,99,235,.5); }
    50%      { box-shadow: 0 4px 30px rgba(37,99,235,.8), 0 0 0 8px rgba(37,99,235,.1); }
}

/* ── Chat window ────────────────────────────────────── */
#gchatWindow {
    position: fixed; bottom: 6.5rem; right: 2rem; z-index: 1001;
    width: 360px; height: 520px;
    background: #161B22; border: 1px solid #30363D;
    border-radius: 18px; display: none; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.6);
    font-family: Inter, system-ui, sans-serif;
    overflow: hidden;
    animation: gchatSlideUp .3s ease;
}
#gchatWindow.open { display: flex; }
@keyframes gchatSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Header */
.gch-header {
    background: linear-gradient(135deg, #1a2744, #0d2060);
    padding: 1rem 1.125rem;
    display: flex; align-items: center; gap: .75rem;
    border-bottom: 1px solid #30363D;
    flex-shrink: 0;
}
.gch-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg,#2563EB,#00D4FF);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0; position: relative;
}
.gch-online {
    position: absolute; bottom: 1px; right: 1px;
    width: 10px; height: 10px; border-radius: 50%;
    background: #22C55E; border: 2px solid #161B22;
}
.gch-info { flex: 1 }
.gch-name { font-size: .9rem; font-weight: 700; color: #F0F6FF; }
.gch-status { font-size: .72rem; color: #22C55E; }
.gch-close {
    background: none; border: none; color: #8B949E;
    font-size: 1.1rem; cursor: pointer; width: 28px; height: 28px;
    border-radius: 6px; display: flex; align-items: center; justify-content: center;
    transition: .2s;
}
.gch-close:hover { background: rgba(255,255,255,.08); color: #F0F6FF; }

/* Start form */
#gchatStartForm {
    flex: 1; display: flex; flex-direction: column;
    justify-content: center; padding: 1.5rem; gap: .875rem;
}
.gch-welcome { text-align: center; margin-bottom: .5rem; }
.gch-welcome .emoji { font-size: 2.5rem; display: block; margin-bottom: .5rem; }
.gch-welcome h3 { font-size: 1rem; font-weight: 800; color: #F0F6FF; margin-bottom: .25rem; }
.gch-welcome p { font-size: .8rem; color: #8B949E; line-height: 1.5; }
.gch-input {
    background: #1C2333; border: 1.5px solid #30363D;
    border-radius: 8px; padding: .65rem .875rem;
    color: #F0F6FF; font-size: .875rem; width: 100%;
    outline: none; transition: .2s; font-family: inherit;
}
.gch-input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
select.gch-input { cursor: pointer; }
select.gch-input option { background: #161B22; }
.gch-start-btn {
    background: linear-gradient(135deg, #2563EB, #3B82F6);
    color: #fff; border: none; border-radius: 8px;
    padding: .75rem; font-size: .9rem; font-weight: 700;
    cursor: pointer; transition: .2s; font-family: inherit;
}
.gch-start-btn:hover { opacity: .9; transform: translateY(-1px); }

/* Messages area */
#gchatMessages {
    flex: 1; overflow-y: auto; padding: 1rem;
    display: none; flex-direction: column; gap: .625rem;
    scroll-behavior: smooth;
}
#gchatMessages::-webkit-scrollbar { width: 4px; }
#gchatMessages::-webkit-scrollbar-thumb { background: #30363D; border-radius: 2px; }

.gch-msg {
    display: flex; flex-direction: column; max-width: 82%;
    animation: gchatMsgIn .2s ease;
}
@keyframes gchatMsgIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.gch-msg.customer { align-self: flex-end; align-items: flex-end; }
.gch-msg.admin    { align-self: flex-start; align-items: flex-start; }
.gch-msg.system   { align-self: center; align-items: center; max-width: 90%; }

.gch-bubble {
    padding: .55rem .875rem;
    border-radius: 14px;
    font-size: .85rem; line-height: 1.55;
    color: #F0F6FF; word-break: break-word;
}
.gch-msg.customer .gch-bubble {
    background: linear-gradient(135deg, #2563EB, #1d4ed8);
    border-bottom-right-radius: 4px;
}
.gch-msg.admin .gch-bubble {
    background: #1C2333; border: 1px solid #30363D;
    border-bottom-left-radius: 4px;
}
.gch-msg.system .gch-bubble {
    background: rgba(37,99,235,.1); border: 1px solid rgba(37,99,235,.2);
    font-size: .78rem; color: #8B949E; text-align: center;
    border-radius: 8px;
}
.gch-meta {
    font-size: .65rem; color: #6E7681; margin-top: .2rem;
    display: flex; align-items: center; gap: .3rem;
}
.gch-read { color: #22C55E; }

/* Typing indicator */
#gchatTyping {
    display: none; padding: .4rem 1rem;
    font-size: .75rem; color: #8B949E;
    align-items: center; gap: .4rem;
    flex-shrink: 0;
}
.typing-dots { display: flex; gap: 3px; }
.typing-dots span {
    width: 5px; height: 5px; border-radius: 50%;
    background: #8B949E; animation: gchatDot 1.2s infinite;
}
.typing-dots span:nth-child(2) { animation-delay: .2s; }
.typing-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes gchatDot {
    0%,60%,100% { transform: translateY(0); }
    30%          { transform: translateY(-4px); }
}

/* Input bar */
#gchatInputArea {
    display: none; flex-direction: column; gap: 0;
    border-top: 1px solid #30363D; flex-shrink: 0;
}
.gch-input-row {
    display: flex; align-items: flex-end; gap: .5rem;
    padding: .625rem .875rem;
}
#gchatInput {
    flex: 1; background: #1C2333;
    border: 1.5px solid #30363D; border-radius: 10px;
    padding: .55rem .875rem; color: #F0F6FF;
    font-size: .875rem; outline: none; resize: none;
    max-height: 100px; overflow-y: auto;
    transition: .2s; font-family: inherit; line-height: 1.5;
}
#gchatInput:focus { border-color: #2563EB; }
.gch-send-btn {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg,#2563EB,#3B82F6);
    border: none; color: #fff; font-size: 1rem;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; transition: .2s; flex-shrink: 0;
}
.gch-send-btn:hover { transform: scale(1.1); }
.gch-send-btn:disabled { opacity: .4; cursor: default; transform: none; }

/* Resolved state */
#gchatResolved {
    display: none; padding: 1rem 1.25rem;
    background: rgba(34,197,94,.08); border-top: 1px solid rgba(34,197,94,.2);
    font-size: .78rem; color: #22C55E; text-align: center; flex-shrink: 0;
}
.gch-new-chat {
    background: none; border: 1px solid rgba(34,197,94,.3); color: #22C55E;
    padding: .35rem .875rem; border-radius: 6px;
    font-size: .75rem; cursor: pointer; margin-top: .5rem;
    font-family: inherit; transition: .2s;
}
.gch-new-chat:hover { background: rgba(34,197,94,.1); }
</style>

<!-- Bubble Button -->
<button id="gchatBubble" onclick="gchatToggle()" title="Chat dengan Admin">
    💬
    <span id="gchatUnread"></span>
</button>

<!-- Chat Window -->
<div id="gchatWindow">
    <!-- Header -->
    <div class="gch-header">
        <div class="gch-avatar">
            🎮
            <div class="gch-online"></div>
        </div>
        <div class="gch-info">
            <div class="gch-name">Admin GameStore</div>
            <div class="gch-status" id="gchatStatusText">● Online sekarang</div>
        </div>
        <button class="gch-close" onclick="gchatToggle()">✕</button>
    </div>

    <!-- Start Form -->
    <div id="gchatStartForm">
        <div class="gch-welcome">
            <span class="emoji">👋</span>
            <h3>Halo! Ada yang bisa kami bantu?</h3>
            <p>Isi form di bawah untuk memulai chat dengan admin kami yang siap membantu 24/7.</p>
        </div>
        <input type="text" id="gchatName"  class="gch-input" placeholder="Nama kamu *" maxlength="50">
        <input type="email" id="gchatEmail" class="gch-input" placeholder="Email (opsional)">
        <select id="gchatTopic" class="gch-input">
            <option value="Tanya Produk">🎮 Tanya Produk / Harga</option>
            <option value="Konfirmasi Order">✅ Konfirmasi Order</option>
            <option value="Masalah Top Up">⚠️ Masalah Top Up</option>
            <option value="Promo">🔥 Promo & Diskon</option>
            <option value="Lainnya">💬 Lainnya</option>
        </select>
        <button class="gch-start-btn" onclick="gchatStart()">Mulai Chat →</button>
    </div>

    <!-- Messages -->
    <div id="gchatMessages"></div>

    <!-- Typing -->
    <div id="gchatTyping">
        <div class="typing-dots"><span></span><span></span><span></span></div>
        Admin sedang mengetik...
    </div>

    <!-- Input area -->
    <div id="gchatInputArea">
        <div class="gch-input-row">
            <textarea id="gchatInput" placeholder="Ketik pesan..." rows="1"
                      oninput="gchatAutoResize(this); gchatSendTyping()"
                      onkeydown="gchatKeyDown(event)"></textarea>
            <button class="gch-send-btn" id="gchatSendBtn" onclick="gchatSend()" disabled>➤</button>
        </div>
    </div>

    <!-- Resolved notice -->
    <div id="gchatResolved">
        ✅ Sesi chat telah diselesaikan.<br>
        <button class="gch-new-chat" onclick="gchatNewSession()">Buka Chat Baru</button>
    </div>
</div>

<script>
(function() {
    const API = '<?= rtrim(dirname($_SERVER['PHP_SELF']), '/') ?>/chat/api.php';
    let sessionId   = null;
    let lastMsgId   = null;
    let pollTimer   = null;
    let typingTimer = null;
    let isOpen      = false;

    // ── Toggle window ──────────────────────────────────────────
    window.gchatToggle = function() {
        isOpen = !isOpen;
        const win = document.getElementById('gchatWindow');
        win.classList.toggle('open', isOpen);
        document.getElementById('gchatBubble').innerHTML = isOpen
            ? '✕<span id="gchatUnread"></span>'
            : '💬<span id="gchatUnread"></span>';
        if (isOpen && sessionId) gchatMarkRead();
    };

    // ── Check existing session on load ─────────────────────────
    fetch(API + '?action=check').then(r => r.json()).then(d => {
        if (d.has_session) {
            sessionId = d.session_id;
            showChatUI();
            gchatLoadHistory();
            if (d.unread > 0) showUnread(d.unread);
        }
    });

    // ── Start chat ─────────────────────────────────────────────
    window.gchatStart = function() {
        const name  = document.getElementById('gchatName').value.trim();
        const email = document.getElementById('gchatEmail').value.trim();
        const topic = document.getElementById('gchatTopic').value;
        if (!name) { document.getElementById('gchatName').focus(); gchatShake('gchatName'); return; }

        const btn = document.querySelector('.gch-start-btn');
        btn.textContent = 'Menghubungkan...'; btn.disabled = true;

        const fd = new FormData();
        fd.append('name', name); fd.append('email', email); fd.append('topic', topic);
        fetch(API + '?action=init', { method: 'POST', body: fd })
            .then(r => r.json()).then(d => {
                if (d.error) { alert(d.error); btn.textContent = 'Mulai Chat →'; btn.disabled = false; return; }
                sessionId = d.session_id;
                showChatUI();
                gchatLoadHistory();
            });
    };

    // ── Load history ───────────────────────────────────────────
    window.gchatLoadHistory = function() {
        fetch(API + `?action=history&session_id=${sessionId}&reader=customer`)
            .then(r => r.json()).then(d => {
                if (d.error) return;
                const box = document.getElementById('gchatMessages');
                box.innerHTML = '';
                d.messages.forEach(m => renderMsg(m));
                scrollBottom();
                if (d.messages.length)
                    lastMsgId = d.messages[d.messages.length - 1].id;
                if (d.session?.status === 'resolved') showResolved();
                else startPolling();
            });
    };

    // ── Polling ────────────────────────────────────────────────
    function startPolling() {
        stopPolling();
        pollTimer = setInterval(gchatPoll, 2500);
    }
    function stopPolling() { if (pollTimer) clearInterval(pollTimer); }

    window.gchatPoll = function() {
        if (!sessionId) return;
        const url = API + `?action=poll&session_id=${sessionId}&reader=customer` + (lastMsgId ? `&after_id=${lastMsgId}` : '');
        fetch(url).then(r => r.json()).then(d => {
            if (d.error) return;
            if (d.messages && d.messages.length) {
                d.messages.forEach(m => renderMsg(m));
                lastMsgId = d.messages[d.messages.length - 1].id;
                scrollBottom();
                if (!isOpen) showUnread((parseInt(document.getElementById('gchatUnread')?.textContent||'0') + d.messages.filter(m=>m.sender==='admin').length));
            }
            // Typing
            const typingEl = document.getElementById('gchatTyping');
            typingEl.style.display = d.typing ? 'flex' : 'none';
            if (d.typing) scrollBottom();
            if (d.status === 'resolved') { stopPolling(); showResolved(); }
        });
    };

    // ── Send message ───────────────────────────────────────────
    window.gchatSend = function() {
        const inp = document.getElementById('gchatInput');
        const text = inp.value.trim();
        if (!text || !sessionId) return;
        inp.value = ''; gchatAutoResize(inp);
        document.getElementById('gchatSendBtn').disabled = true;

        const fd = new FormData();
        fd.append('session_id', sessionId);
        fd.append('sender', 'customer');
        fd.append('text', text);
        fetch(API + '?action=send', { method: 'POST', body: fd })
            .then(r => r.json()).then(d => {
                if (d.error) { renderSystemMsg('⚠️ Gagal mengirim: ' + d.error); return; }
                renderMsg({ sender:'customer', text, timestamp: d.timestamp, id: d.msg_id });
                lastMsgId = d.msg_id;
                scrollBottom();
            });
    };

    // ── Typing indicator ───────────────────────────────────────
    window.gchatSendTyping = function() {
        const inp = document.getElementById('gchatInput');
        document.getElementById('gchatSendBtn').disabled = !inp.value.trim();
        if (!sessionId) return;
        clearTimeout(typingTimer);
        sendTyping(true);
        typingTimer = setTimeout(() => sendTyping(false), 2500);
    };

    function sendTyping(val) {
        const fd = new FormData();
        fd.append('session_id', sessionId);
        fd.append('who', 'customer');
        fd.append('typing', val ? '1' : '0');
        fetch(API + '?action=typing', { method: 'POST', body: fd }).catch(()=>{});
    }

    // ── Render message ─────────────────────────────────────────
    function renderMsg(m) {
        const box = document.getElementById('gchatMessages');
        const cls = m.is_system ? 'system' : m.sender;
        const time = m.timestamp ? new Date(m.timestamp*1000).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}) : '';
        const div = document.createElement('div');
        div.className = `gch-msg ${cls}`;
        div.dataset.id = m.id || '';
        // Bold & line-break support
        const txt = (m.text||'').replace(/\*([^*]+)\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>');
        div.innerHTML = `
            <div class="gch-bubble">${txt}</div>
            <div class="gch-meta">${time}${m.sender==='customer'?` <span class="gch-read">${m.read?'✓✓':'✓'}</span>`:''}</div>`;
        box.appendChild(div);
    }
    function renderSystemMsg(text) {
        renderMsg({ sender:'admin', text, is_system:true, timestamp: Date.now()/1000 });
        scrollBottom();
    }

    // ── Helpers ────────────────────────────────────────────────
    function showChatUI() {
        document.getElementById('gchatStartForm').style.display = 'none';
        document.getElementById('gchatMessages').style.display  = 'flex';
        document.getElementById('gchatInputArea').style.display = 'flex';
    }
    function showResolved() {
        document.getElementById('gchatInputArea').style.display = 'none';
        document.getElementById('gchatResolved').style.display  = 'block';
        document.getElementById('gchatStatusText').textContent  = '● Sesi ditutup';
        document.getElementById('gchatStatusText').style.color  = '#8B949E';
    }
    function scrollBottom() {
        const box = document.getElementById('gchatMessages');
        box.scrollTop = box.scrollHeight;
    }
    function showUnread(n) {
        const el = document.getElementById('gchatUnread');
        if (!el) return;
        if (n > 0) { el.textContent = n > 9 ? '9+' : n; el.style.display = 'flex'; }
        else el.style.display = 'none';
    }
    function gchatMarkRead() {
        showUnread(0);
        if (!sessionId) return;
        fetch(API + `?action=poll&session_id=${sessionId}&reader=customer`);
    }
    window.gchatNewSession = function() {
        sessionId = null; lastMsgId = null;
        document.getElementById('gchatMessages').innerHTML = '';
        document.getElementById('gchatMessages').style.display  = 'none';
        document.getElementById('gchatInputArea').style.display = 'none';
        document.getElementById('gchatResolved').style.display  = 'none';
        document.getElementById('gchatStartForm').style.display = 'flex';
        document.getElementById('gchatStatusText').textContent  = '● Online sekarang';
        document.getElementById('gchatStatusText').style.color  = '';
    };
    function gchatShake(id) {
        const el = document.getElementById(id);
        el.style.animation = 'none';
        el.style.borderColor = '#EF4444';
        setTimeout(() => el.style.borderColor = '', 1500);
    }
    window.gchatAutoResize = function(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 100) + 'px';
    };
    window.gchatKeyDown = function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); gchatSend(); }
    };
})();
</script>