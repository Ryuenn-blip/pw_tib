<?php
/**
 * GameStore Chat Widget
 * Di-include dari includes/footer.php
 * Path API otomatis dari __CHAT_ROOT yang di-set footer
 */
?>
<style>
/* ── Bubble ── */
#gchatBubble{
    position:fixed;bottom:2rem;right:2rem;z-index:1000;
    width:58px;height:58px;border-radius:50%;
    background:linear-gradient(135deg,#2563EB,#00D4FF);
    border:none;cursor:pointer;
    box-shadow:0 4px 24px rgba(37,99,235,.55);
    display:flex;align-items:center;justify-content:center;
    font-size:1.5rem;transition:.25s;
    animation:gcPulse 3s infinite;
}
#gchatBubble:hover{transform:scale(1.1)}
#gchatUnread{
    position:absolute;top:-5px;right:-5px;
    background:#EF4444;color:#fff;
    font-size:.62rem;font-weight:800;
    min-width:18px;height:18px;border-radius:9px;
    display:none;align-items:center;justify-content:center;
    padding:0 4px;font-family:Inter,sans-serif;border:2px solid #0D1117;
}
@keyframes gcPulse{
    0%,100%{box-shadow:0 4px 24px rgba(37,99,235,.55)}
    50%{box-shadow:0 4px 32px rgba(37,99,235,.85),0 0 0 8px rgba(37,99,235,.1)}
}

/* ── Window ── */
#gchatWindow{
    position:fixed;bottom:6.5rem;right:2rem;z-index:1001;
    width:360px;height:530px;
    background:#161B22;border:1px solid #30363D;
    border-radius:18px;display:none;flex-direction:column;
    box-shadow:0 24px 64px rgba(0,0,0,.65);
    font-family:Inter,system-ui,sans-serif;
    overflow:hidden;
}
#gchatWindow.gc-open{display:flex;animation:gcSlideUp .28s cubic-bezier(.34,1.56,.64,1)}
@keyframes gcSlideUp{from{opacity:0;transform:translateY(18px) scale(.95)}to{opacity:1;transform:none}}

/* Header */
.gc-hdr{
    background:linear-gradient(135deg,#0f1f4a,#1a3080);
    padding:.875rem 1.125rem;
    display:flex;align-items:center;gap:.75rem;
    border-bottom:1px solid #30363D;flex-shrink:0;
}
.gc-av{
    width:38px;height:38px;border-radius:50%;flex-shrink:0;
    background:linear-gradient(135deg,#2563EB,#00D4FF);
    display:flex;align-items:center;justify-content:center;
    font-size:1.1rem;position:relative;
}
.gc-dot{
    position:absolute;bottom:1px;right:1px;
    width:10px;height:10px;border-radius:50%;
    background:#22C55E;border:2px solid #161B22;
}
.gc-hdr-info{flex:1}
.gc-hdr-name{font-size:.9rem;font-weight:700;color:#F0F6FF}
.gc-hdr-status{font-size:.72rem;color:#22C55E}
.gc-close{
    background:none;border:none;color:#8B949E;
    font-size:1.1rem;cursor:pointer;
    width:28px;height:28px;border-radius:6px;
    display:flex;align-items:center;justify-content:center;
    transition:.2s;
}
.gc-close:hover{background:rgba(255,255,255,.08);color:#F0F6FF}

/* Start form */
#gcForm{
    flex:1;display:flex;flex-direction:column;
    justify-content:center;padding:1.5rem 1.375rem;gap:.75rem;
}
.gc-welcome{text-align:center;margin-bottom:.25rem}
.gc-welcome .em{font-size:2.25rem;display:block;margin-bottom:.5rem}
.gc-welcome h3{font-size:.975rem;font-weight:800;color:#F0F6FF;margin-bottom:.25rem}
.gc-welcome p{font-size:.78rem;color:#8B949E;line-height:1.5}
.gc-inp{
    background:#1C2333;border:1.5px solid #30363D;
    border-radius:8px;padding:.6rem .875rem;
    color:#F0F6FF;font-size:.855rem;width:100%;
    outline:none;transition:.2s;font-family:inherit;
}
.gc-inp:focus{border-color:#2563EB;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
select.gc-inp{cursor:pointer}
select.gc-inp option{background:#161B22}
.gc-start{
    background:linear-gradient(135deg,#2563EB,#3B82F6);
    color:#fff;border:none;border-radius:8px;
    padding:.75rem;font-size:.88rem;font-weight:700;
    cursor:pointer;transition:.2s;font-family:inherit;
}
.gc-start:hover{opacity:.9;transform:translateY(-1px)}
.gc-start:disabled{opacity:.5;cursor:default;transform:none}

/* Messages */
#gcMsgs{
    flex:1;overflow-y:auto;padding:.875rem 1rem;
    display:none;flex-direction:column;gap:.5rem;
    scroll-behavior:smooth;
}
#gcMsgs::-webkit-scrollbar{width:4px}
#gcMsgs::-webkit-scrollbar-thumb{background:#30363D;border-radius:2px}

.gc-msg{display:flex;flex-direction:column;max-width:83%;animation:gcMsgIn .2s ease}
@keyframes gcMsgIn{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
.gc-msg.customer{align-self:flex-end;align-items:flex-end}
.gc-msg.admin   {align-self:flex-start;align-items:flex-start}
.gc-msg.system  {align-self:center;align-items:center;max-width:92%}

.gc-bubble{
    padding:.5rem .875rem;border-radius:14px;
    font-size:.845rem;line-height:1.55;color:#F0F6FF;word-break:break-word;
}
.gc-msg.customer .gc-bubble{
    background:linear-gradient(135deg,#2563EB,#1d4ed8);
    border-bottom-right-radius:4px;
}
.gc-msg.admin .gc-bubble{
    background:#1C2333;border:1px solid #30363D;
    border-bottom-left-radius:4px;
}
.gc-msg.system .gc-bubble{
    background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);
    font-size:.76rem;color:#8B949E;text-align:center;border-radius:8px;
}
.gc-meta{
    font-size:.63rem;color:#6E7681;margin-top:.18rem;
    display:flex;align-items:center;gap:.3rem;
}
.gc-read{color:#22C55E}

/* Typing */
#gcTyping{
    display:none;padding:.35rem 1rem;
    font-size:.74rem;color:#8B949E;
    align-items:center;gap:.4rem;flex-shrink:0;
}
.gc-tdots{display:flex;gap:3px}
.gc-tdots span{
    width:5px;height:5px;border-radius:50%;
    background:#8B949E;animation:gcDot 1.2s infinite;
}
.gc-tdots span:nth-child(2){animation-delay:.2s}
.gc-tdots span:nth-child(3){animation-delay:.4s}
@keyframes gcDot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-4px)}}

/* Input */
#gcInputArea{
    display:none;border-top:1px solid #30363D;flex-shrink:0;
}
.gc-input-row{
    display:flex;align-items:flex-end;gap:.5rem;
    padding:.6rem .875rem;
}
#gcInput{
    flex:1;background:#1C2333;border:1.5px solid #30363D;
    border-radius:10px;padding:.55rem .875rem;
    color:#F0F6FF;font-size:.855rem;
    outline:none;resize:none;max-height:96px;
    overflow-y:auto;transition:.2s;font-family:inherit;line-height:1.5;
}
#gcInput:focus{border-color:#2563EB}
.gc-send{
    width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,#2563EB,#3B82F6);
    border:none;color:#fff;font-size:1rem;
    cursor:pointer;display:flex;align-items:center;
    justify-content:center;transition:.2s;flex-shrink:0;
}
.gc-send:hover{transform:scale(1.1)}
.gc-send:disabled{opacity:.35;cursor:default;transform:none}

/* Resolved */
#gcResolved{
    display:none;padding:.875rem 1.125rem;
    background:rgba(34,197,94,.07);border-top:1px solid rgba(34,197,94,.18);
    font-size:.78rem;color:#22C55E;text-align:center;flex-shrink:0;
}
.gc-newchat{
    background:none;border:1px solid rgba(34,197,94,.3);color:#22C55E;
    padding:.3rem .875rem;border-radius:6px;font-size:.73rem;
    cursor:pointer;margin-top:.4rem;font-family:inherit;transition:.2s;
}
.gc-newchat:hover{background:rgba(34,197,94,.1)}

@media(max-width:480px){
    #gchatWindow{width:calc(100vw - 2rem);right:1rem;bottom:5.5rem;height:480px}
    #gchatBubble{bottom:1.25rem;right:1.25rem}
}
</style>

<!-- Bubble -->
<button id="gchatBubble" onclick="gcToggle()" title="Chat dengan Admin">
    <span id="gcBubbleIcon">💬</span>
    <span id="gchatUnread"></span>
</button>

<!-- Window -->
<div id="gchatWindow">
    <div class="gc-hdr">
        <div class="gc-av">🎮<div class="gc-dot"></div></div>
        <div class="gc-hdr-info">
            <div class="gc-hdr-name">Admin GameStore</div>
            <div class="gc-hdr-status" id="gcStatusText">● Online sekarang</div>
        </div>
        <button class="gc-close" onclick="gcToggle()">✕</button>
    </div>

    <!-- Form -->
    <div id="gcForm">
        <div class="gc-welcome">
            <span class="em">👋</span>
            <h3>Ada yang bisa kami bantu?</h3>
            <p>Isi form di bawah untuk chat langsung dengan admin kami yang siap membantu 24/7.</p>
        </div>
        <input  type="text"  id="gcName"  class="gc-inp" placeholder="Nama kamu *" maxlength="50">
        <input  type="email" id="gcEmail" class="gc-inp" placeholder="Email (opsional)">
        <select id="gcTopic" class="gc-inp">
            <option value="Tanya Produk">🎮 Tanya Produk / Harga</option>
            <option value="Konfirmasi Order">✅ Konfirmasi Order</option>
            <option value="Masalah Top Up">⚠️ Masalah Top Up</option>
            <option value="Promo">🔥 Promo &amp; Diskon</option>
            <option value="Lainnya">💬 Lainnya</option>
        </select>
        <button class="gc-start" id="gcStartBtn" onclick="gcStart()">Mulai Chat →</button>
    </div>

    <!-- Messages -->
    <div id="gcMsgs"></div>

    <!-- Typing -->
    <div id="gcTyping">
        <div class="gc-tdots"><span></span><span></span><span></span></div>
        Admin sedang mengetik...
    </div>

    <!-- Input -->
    <div id="gcInputArea">
        <div class="gc-input-row">
            <textarea id="gcInput" rows="1"
                placeholder="Ketik pesan... (Enter kirim)"
                oninput="gcResize(this);gcTypingSend()"
                onkeydown="gcKeyDown(event)"></textarea>
            <button class="gc-send" id="gcSendBtn" onclick="gcSend()" disabled>➤</button>
        </div>
    </div>

    <!-- Resolved -->
    <div id="gcResolved">
        ✅ Sesi chat telah diselesaikan.<br>
        <button class="gc-newchat" onclick="gcNewSession()">Buka Chat Baru</button>
    </div>
</div>

<script>
(function(){
    const API      = (window.__CHAT_ROOT||'') + 'chat/api.php';
    let sid        = null;
    let lastId     = null;
    let pollTmr    = null;
    let typTmr     = null;
    let isOpen     = false;
    let unreadCnt  = 0;

    /* ── Toggle ── */
    window.gcToggle = function(){
        isOpen = !isOpen;
        document.getElementById('gchatWindow').classList.toggle('gc-open', isOpen);
        document.getElementById('gcBubbleIcon').textContent = isOpen ? '✕' : '💬';
        if(isOpen){ setUnread(0); if(sid) markRead(); }
    };

    /* ── Check existing session ── */
    fetch(API+'?action=check').then(r=>r.json()).then(d=>{
        if(d.has_session){
            sid = d.session_id;
            showChatUI();
            loadHistory();
            if(d.unread>0) setUnread(d.unread);
        }
    }).catch(()=>{});

    /* ── Start ── */
    window.gcStart = function(){
        const name  = document.getElementById('gcName').value.trim();
        const email = document.getElementById('gcEmail').value.trim();
        const topic = document.getElementById('gcTopic').value;
        if(!name){
            const inp = document.getElementById('gcName');
            inp.style.borderColor='#EF4444';
            inp.focus();
            setTimeout(()=>inp.style.borderColor='',1500);
            return;
        }
        const btn = document.getElementById('gcStartBtn');
        btn.textContent='Menghubungkan...'; btn.disabled=true;

        const fd=new FormData();
        fd.append('name',name); fd.append('email',email); fd.append('topic',topic);
        fetch(API+'?action=init',{method:'POST',body:fd})
            .then(r=>r.json()).then(d=>{
                if(d.error){ alert(d.error); btn.textContent='Mulai Chat →'; btn.disabled=false; return; }
                sid=d.session_id;
                showChatUI();
                loadHistory();
            }).catch(()=>{ btn.textContent='Mulai Chat →'; btn.disabled=false; });
    };

    /* ── Load history ── */
    function loadHistory(){
        fetch(API+`?action=history&session_id=${sid}&reader=customer`)
            .then(r=>r.json()).then(d=>{
                if(d.error) return;
                const box=document.getElementById('gcMsgs');
                box.innerHTML='';
                (d.messages||[]).forEach(m=>renderMsg(m));
                if(d.messages&&d.messages.length)
                    lastId=d.messages[d.messages.length-1].id;
                scrollBot();
                if(d.session&&d.session.status==='resolved') showResolved();
                else startPoll();
            }).catch(()=>{});
    }

    /* ── Poll ── */
    function startPoll(){ stopPoll(); pollTmr=setInterval(doPoll,2500); }
    function stopPoll(){ if(pollTmr) clearInterval(pollTmr); pollTmr=null; }

    function doPoll(){
        if(!sid) return;
        const url=API+`?action=poll&session_id=${sid}&reader=customer`+(lastId?`&after_id=${lastId}`:'');
        fetch(url).then(r=>r.json()).then(d=>{
            if(d.error) return;
            if(d.messages&&d.messages.length){
                d.messages.forEach(m=>renderMsg(m));
                lastId=d.messages[d.messages.length-1].id;
                scrollBot();
                if(!isOpen){
                    const newAdmin=d.messages.filter(m=>m.sender==='admin').length;
                    if(newAdmin>0) setUnread(unreadCnt+newAdmin);
                }
            }
            const typEl=document.getElementById('gcTyping');
            typEl.style.display=d.typing?'flex':'none';
            if(d.typing) scrollBot();
            if(d.status==='resolved'){ stopPoll(); showResolved(); }
        }).catch(()=>{});
    }

    /* ── Send ── */
    window.gcSend = function(){
        const inp=document.getElementById('gcInput');
        const text=inp.value.trim();
        if(!text||!sid) return;
        inp.value=''; gcResize(inp);
        document.getElementById('gcSendBtn').disabled=true;
        clearTimeout(typTmr); sendTyping(false);

        const fd=new FormData();
        fd.append('session_id',sid); fd.append('sender','customer'); fd.append('text',text);
        fetch(API+'?action=send',{method:'POST',body:fd})
            .then(r=>r.json()).then(d=>{
                if(d.error){ renderSys('⚠️ Gagal: '+d.error); return; }
                renderMsg({sender:'customer',text,timestamp:d.timestamp,id:d.msg_id,read:false});
                lastId=d.msg_id; scrollBot();
            }).catch(()=>{ renderSys('⚠️ Koneksi bermasalah, coba lagi.'); });
    };

    /* ── Typing ── */
    window.gcTypingSend = function(){
        const inp=document.getElementById('gcInput');
        document.getElementById('gcSendBtn').disabled=!inp.value.trim();
        if(!sid) return;
        clearTimeout(typTmr);
        sendTyping(true);
        typTmr=setTimeout(()=>sendTyping(false),2500);
    };
    function sendTyping(val){
        if(!sid) return;
        const fd=new FormData();
        fd.append('session_id',sid); fd.append('who','customer'); fd.append('typing',val?'1':'0');
        fetch(API+'?action=typing',{method:'POST',body:fd}).catch(()=>{});
    }

    /* ── Render ── */
    function renderMsg(m){
        const box=document.getElementById('gcMsgs');
        const cls=m.is_system?'system':m.sender;
        const time=m.timestamp?new Date(m.timestamp*1000).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}):'';
        const txt=(m.text||'').replace(/\*([^*]+)\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>');
        const div=document.createElement('div');
        div.className='gc-msg '+cls;
        div.dataset.id=m.id||'';
        div.innerHTML=`<div class="gc-bubble">${txt}</div>
            <div class="gc-meta">${time}${cls==='customer'?` <span class="gc-read">${m.read?'✓✓':'✓'}</span>`:''}</div>`;
        box.appendChild(div);
    }
    function renderSys(text){ renderMsg({sender:'admin',is_system:true,text,timestamp:Date.now()/1000}); scrollBot(); }

    /* ── Helpers ── */
    function showChatUI(){
        document.getElementById('gcForm').style.display      ='none';
        document.getElementById('gcMsgs').style.display      ='flex';
        document.getElementById('gcInputArea').style.display ='flex';
    }
    function showResolved(){
        document.getElementById('gcInputArea').style.display='none';
        document.getElementById('gcResolved').style.display ='block';
        document.getElementById('gcStatusText').textContent ='● Sesi ditutup';
        document.getElementById('gcStatusText').style.color='#6E7681';
    }
    function scrollBot(){ const b=document.getElementById('gcMsgs'); b.scrollTop=b.scrollHeight; }
    function setUnread(n){
        unreadCnt=n;
        const el=document.getElementById('gchatUnread');
        if(!el) return;
        if(n>0){ el.textContent=n>9?'9+':n; el.style.display='flex'; }
        else el.style.display='none';
    }
    function markRead(){
        if(!sid) return;
        fetch(API+`?action=poll&session_id=${sid}&reader=customer`).catch(()=>{});
    }
    window.gcNewSession = function(){
        sid=null; lastId=null;
        document.getElementById('gcMsgs').innerHTML='';
        document.getElementById('gcMsgs').style.display      ='none';
        document.getElementById('gcInputArea').style.display ='none';
        document.getElementById('gcResolved').style.display  ='none';
        document.getElementById('gcForm').style.display      ='flex';
        document.getElementById('gcStatusText').textContent  ='● Online sekarang';
        document.getElementById('gcStatusText').style.color  ='';
        document.getElementById('gcStartBtn').textContent    ='Mulai Chat →';
        document.getElementById('gcStartBtn').disabled       =false;
    };
    window.gcResize = function(el){
        el.style.height='auto';
        el.style.height=Math.min(el.scrollHeight,96)+'px';
    };
    window.gcKeyDown = function(e){
        if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); gcSend(); }
    };
})();
</script>
