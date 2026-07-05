<?php
/**
 * GameStore Chat Engine — DB Version
 * Kolom disesuaikan dengan database/gamestore.sql:
 *   chat_sessions: customer_name, customer_email, ...
 *   chat_messages: message (bukan text)
 * Fallback ke file-based jika tabel belum ada
 */

require_once dirname(__DIR__) . '/includes/db.php';

function _chat_db_available(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db_row("SELECT 1 FROM chat_sessions LIMIT 1");
        $ok = true;
    } catch (\Throwable $e) {
        $ok = false;
    }
    return $ok;
}

function generateId(string $prefix = ''): string {
    return $prefix . substr(md5(uniqid((string)rand(), true)), 0, 10);
}
function timeAgo(int $ts): string {
    $d = time() - $ts;
    if ($d < 60)    return 'Baru saja';
    if ($d < 3600)  return floor($d/60).' menit lalu';
    if ($d < 86400) return floor($d/3600).' jam lalu';
    return date('d/m H:i', $ts);
}

// ── Normalize row DB → format yang dipakai admin/chat.php & widget ──
function _normalize_session(array $r): array {
    return [
        'id'              => $r['id'],
        'name'            => $r['customer_name'] ?? $r['name'] ?? '',
        'email'           => $r['customer_email'] ?? $r['email'] ?? '',
        'topic'           => $r['topic'] ?? 'Umum',
        'status'          => $r['status'] ?? 'open',
        'unread_admin'    => (int)($r['unread_admin'] ?? 0),
        'unread_customer' => (int)($r['unread_customer'] ?? 0),
        'last_message'    => $r['last_message'] ?? '',
        'last_time'       => (int)($r['last_time'] ?? 0),
        'admin_typing'    => (bool)($r['admin_typing'] ?? 0),
        'customer_typing' => (bool)($r['customer_typing'] ?? 0),
        'typing_ts'       => (int)($r['typing_ts'] ?? 0),
        'created_at'      => is_numeric($r['created_at'] ?? null) ? (int)$r['created_at'] : strtotime($r['created_at'] ?? 'now'),
    ];
}
function _normalize_message(array $r): array {
    return [
        'id'          => $r['id'],
        'session_id'  => $r['session_id'],
        'sender'      => $r['sender'],
        'sender_name' => $r['sender_name'] ?? '',
        'text'        => $r['message'] ?? $r['text'] ?? '',
        'type'        => $r['type'] ?? 'text',
        'is_system'   => (bool)($r['is_system'] ?? 0),
        'read'        => (bool)($r['is_read'] ?? $r['read'] ?? 0),
        'timestamp'   => isset($r['created_at']) ? (is_numeric($r['created_at']) ? (int)$r['created_at'] : strtotime($r['created_at'])) : time(),
    ];
}

// ── Sessions ──────────────────────────────────────────────────
function chat_get_all_sessions(): array {
    if (!_chat_db_available()) return _file_get_all_sessions();
    $rows = db_rows("SELECT * FROM chat_sessions ORDER BY last_time DESC");
    return array_map('_normalize_session', $rows);
}

function chat_get_session(string $session_id): ?array {
    if (!_chat_db_available()) {
        $all = _file_get_all_sessions();
        return $all[$session_id] ?? null;
    }
    $r = db_row("SELECT * FROM chat_sessions WHERE id=?", [$session_id]);
    return $r ? _normalize_session($r) : null;
}

function chat_create_session(string $name, string $email='', string $topic='Umum'): string {
    $sid = generateId('cs_');
    if (_chat_db_available()) {
        db_exec("INSERT INTO chat_sessions
            (id, customer_name, customer_email, topic, status, unread_admin, unread_customer,
             last_message, last_time, admin_typing, customer_typing, created_at)
            VALUES (?,?,?,?,'open',0,0,'',UNIX_TIMESTAMP(),0,0,NOW())",
            [$sid, $name, $email, $topic]);
    } else {
        _file_create_session($sid, $name, $email, $topic);
    }
    chat_send_message($sid, 'admin', 'Admin GameStore',
        "Halo *{$name}*! 👋 Selamat datang di GameStore. Ada yang bisa saya bantu?",
        'text', true);
    return $sid;
}

function chat_update_session(string $sid, array $data): bool {
    if (!_chat_db_available()) return _file_update_session($sid, $data);
    if (empty($data)) return true;

    // Map alias field lama ke kolom DB asli
    $colmap = ['name'=>'customer_name', 'email'=>'customer_email'];
    $mapped = [];
    foreach ($data as $k => $v) $mapped[$colmap[$k] ?? $k] = $v;

    $sets   = implode(', ', array_map(fn($k) => "`$k`=?", array_keys($mapped)));
    $values = array_values($mapped);
    $values[] = $sid;
    db_exec("UPDATE chat_sessions SET $sets WHERE id=?", $values);
    return true;
}

// ── Messages ──────────────────────────────────────────────────
function chat_get_messages(string $sid, ?string $after_id = null): array {
    if (!_chat_db_available()) return _file_get_messages($sid, $after_id);

    if ($after_id) {
        $after_ts = db_row("SELECT created_at FROM chat_messages WHERE id=?", [$after_id])['created_at'] ?? null;
        if ($after_ts) {
            $rows = db_rows("SELECT * FROM chat_messages WHERE session_id=? AND created_at>? ORDER BY created_at ASC",
                [$sid, $after_ts]);
            return array_map('_normalize_message', $rows);
        }
    }
    $rows = db_rows("SELECT * FROM chat_messages WHERE session_id=? ORDER BY created_at ASC", [$sid]);
    return array_map('_normalize_message', $rows);
}

function chat_send_message(string $sid, string $sender, string $sender_name, string $text, string $type='text', bool $is_system=false): ?string {
    if (!$sid || !trim($text)) return null;
    $msg_id = generateId('msg_');
    $clean  = htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');

    if (_chat_db_available()) {
        db_exec("INSERT INTO chat_messages (id, session_id, sender, sender_name, message, type, is_system, is_read, created_at)
            VALUES (?,?,?,?,?,?,?,0,NOW())",
            [$msg_id, $sid, $sender, $sender_name, $clean, $type, $is_system ? 1 : 0]);

        $unread_col = $sender === 'customer' ? 'unread_admin' : 'unread_customer';
        db_exec("UPDATE chat_sessions SET
            last_message=?, last_time=UNIX_TIMESTAMP(), {$unread_col}={$unread_col}+1
            WHERE id=?", [mb_substr($clean, 0, 60), $sid]);
    } else {
        _file_send_message($sid, $msg_id, $sender, $sender_name, $clean, $type, $is_system);
    }
    return $msg_id;
}

function chat_mark_read(string $sid, string $reader): void {
    if (_chat_db_available()) {
        db_exec("UPDATE chat_messages SET is_read=1 WHERE session_id=? AND sender!=?", [$sid, $reader]);
        $col = $reader === 'admin' ? 'unread_admin' : 'unread_customer';
        db_exec("UPDATE chat_sessions SET {$col}=0 WHERE id=?", [$sid]);
    } else {
        _file_mark_read($sid, $reader);
    }
}

function chat_set_typing(string $sid, string $who, bool $typing): void {
    $col = $who === 'admin' ? 'admin_typing' : 'customer_typing';
    chat_update_session($sid, [$col => $typing ? 1 : 0, 'typing_ts' => time()]);
}

function chat_total_unread_admin(): int {
    if (_chat_db_available()) {
        return (int)(db_row("SELECT COALESCE(SUM(unread_admin),0) AS t FROM chat_sessions")['t'] ?? 0);
    }
    return array_sum(array_column(_file_get_all_sessions(), 'unread_admin'));
}

function chat_quick_replies(): array {
    return [
        ['label'=>'✅ Proses Selesai',   'text'=>'Pesanan kamu sudah selesai diproses! Item sudah masuk ke akun game kamu. Terima kasih sudah belanja di GameStore! 😊'],
        ['label'=>'⏳ Sedang Diproses',  'text'=>'Pesanan kamu sedang kami proses ya. Mohon tunggu sebentar, maksimal 5 menit item akan masuk. 🙏'],
        ['label'=>'💳 Info Pembayaran',  'text'=>'Untuk pembayaran bisa melalui: DANA, OVO, GoPay, ShopeePay, Transfer BCA/Mandiri/BRI, atau QRIS. Setelah bayar, kirimkan bukti ke sini ya!'],
        ['label'=>'🆔 Minta User ID',    'text'=>'Boleh tolong kirimkan User ID game kamu? Untuk Mobile Legends format: 123456789 (1234). Pastikan sudah benar ya!'],
        ['label'=>'🙏 Terima Kasih',     'text'=>'Terima kasih sudah berbelanja di GameStore! 🎮 Jangan lupa kasih bintang 5 ya kalau puas. Sampai jumpa lagi! ⭐'],
        ['label'=>'❓ Ada Bantuan Lagi', 'text'=>'Ada yang bisa saya bantu lagi? Jangan ragu untuk bertanya ya! Kami siap melayani 24 jam. 😊'],
    ];
}

// ── File-based fallback (jika tabel chat belum dibuat) ─────────
define('CHAT_DATA_DIR', __DIR__ . '/data/');
define('SESSIONS_FILE', CHAT_DATA_DIR . 'sessions.json');

function _file_read($f){ if(!file_exists($f))return []; $r=file_get_contents($f); return json_decode($r,true)?:[]; }
function _file_write($f,$d){ if(!is_dir(dirname($f)))@mkdir(dirname($f),0755,true); file_put_contents($f,json_encode($d,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX); }
function _file_session_file($sid){ $s=preg_replace('/[^a-zA-Z0-9_\-]/','', $sid); return CHAT_DATA_DIR.'chat_'.$s.'.json'; }

function _file_get_all_sessions(): array { return _file_read(SESSIONS_FILE); }

function _file_create_session($sid,$name,$email,$topic){
    $sessions = _file_read(SESSIONS_FILE);
    $sessions[$sid]=['id'=>$sid,'name'=>$name,'email'=>$email,'topic'=>$topic,'status'=>'open',
        'unread_admin'=>0,'unread_customer'=>0,'created_at'=>time(),'last_message'=>'','last_time'=>time(),
        'admin_typing'=>false,'customer_typing'=>false];
    _file_write(SESSIONS_FILE,$sessions);
}
function _file_update_session($sid,$data){
    $sessions=_file_read(SESSIONS_FILE);
    if(!isset($sessions[$sid]))return false;
    $sessions[$sid]=array_merge($sessions[$sid],$data);
    _file_write(SESSIONS_FILE,$sessions);
    return true;
}
function _file_get_messages($sid,$after_id=null){
    $msgs=_file_read(_file_session_file($sid));
    if(!$after_id)return $msgs;
    $found=false;$result=[];
    foreach($msgs as $m){if($found)$result[]=$m;if($m['id']===$after_id)$found=true;}
    return $found?$result:$msgs;
}
function _file_send_message($sid,$msg_id,$sender,$sender_name,$text,$type,$is_system){
    $f=_file_session_file($sid);
    $msgs=_file_read($f);
    $msgs[]=['id'=>$msg_id,'session_id'=>$sid,'sender'=>$sender,'sender_name'=>$sender_name,
        'text'=>$text,'type'=>$type,'is_system'=>$is_system,'read'=>false,'timestamp'=>time()];
    if(count($msgs)>200)$msgs=array_slice($msgs,-200);
    _file_write($f,$msgs);
    $sessions=_file_read(SESSIONS_FILE);
    if(isset($sessions[$sid])){
        $sessions[$sid]['last_message']=mb_substr($text,0,60);
        $sessions[$sid]['last_time']=time();
        if($sender==='customer')$sessions[$sid]['unread_admin']++;
        else $sessions[$sid]['unread_customer']++;
        _file_write(SESSIONS_FILE,$sessions);
    }
}
function _file_mark_read($sid,$reader){
    $f=_file_session_file($sid);$msgs=_file_read($f);$changed=false;
    foreach($msgs as &$m){if($m['sender']!==$reader && empty($m['read'])){$m['read']=true;$changed=true;}}
    unset($m);if($changed)_file_write($f,$msgs);
    _file_update_session($sid,[$reader==='admin'?'unread_admin':'unread_customer'=>0]);
}
