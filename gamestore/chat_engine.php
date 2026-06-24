<?php
/**
 * GameStore Chat Engine
 * File-based storage (no database needed)
 * Data disimpan di chat/data/ sebagai JSON
 */

define('CHAT_DATA_DIR', __DIR__ . '/data/');
define('SESSIONS_FILE', CHAT_DATA_DIR . 'sessions.json');
define('CHAT_MAX_MSG',  200);   // max pesan per sesi disimpan

// ── Helpers ───────────────────────────────────────────────────
function chat_read_json($file) {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    return json_decode($raw, true) ?: [];
}

function chat_write_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function chat_session_file($session_id) {
    $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '', $session_id);
    return CHAT_DATA_DIR . 'chat_' . $safe . '.json';
}

function generateId($prefix = '') {
    return $prefix . substr(md5(uniqid(rand(), true)), 0, 10);
}

function timeAgo($ts) {
    $diff = time() - $ts;
    if ($diff < 60)    return 'Baru saja';
    if ($diff < 3600)  return floor($diff/60) . ' menit lalu';
    if ($diff < 86400) return floor($diff/3600) . ' jam lalu';
    return date('d/m H:i', $ts);
}

// ── Session management ────────────────────────────────────────
function chat_get_all_sessions() {
    return chat_read_json(SESSIONS_FILE);
}

function chat_get_session($session_id) {
    $sessions = chat_get_all_sessions();
    return $sessions[$session_id] ?? null;
}

function chat_create_session($name, $email = '', $topic = 'Umum') {
    $sessions   = chat_get_all_sessions();
    $session_id = generateId('cs_');

    $sessions[$session_id] = [
        'id'         => $session_id,
        'name'       => $name,
        'email'      => $email,
        'topic'      => $topic,
        'status'     => 'open',        // open | resolved | pending
        'unread_admin'  => 0,
        'unread_customer' => 0,
        'created_at' => time(),
        'last_message'   => '',
        'last_time'  => time(),
        'admin_typing'    => false,
        'customer_typing' => false,
    ];

    chat_write_json(SESSIONS_FILE, $sessions);

    // Kirim pesan selamat datang otomatis
    chat_send_message($session_id, 'admin', 'Admin GameStore',
        "Halo *{$name}*! 👋 Selamat datang di GameStore. Saya admin yang bertugas, ada yang bisa saya bantu?",
        'text', true
    );

    return $session_id;
}

function chat_update_session($session_id, $data) {
    $sessions = chat_get_all_sessions();
    if (!isset($sessions[$session_id])) return false;
    $sessions[$session_id] = array_merge($sessions[$session_id], $data);
    chat_write_json(SESSIONS_FILE, $sessions);
    return true;
}

// ── Message management ────────────────────────────────────────
function chat_get_messages($session_id, $after_id = null) {
    $file = chat_session_file($session_id);
    $msgs = chat_read_json($file);
    if ($after_id === null) return $msgs;
    // return only messages after given ID
    $found = false;
    $result = [];
    foreach ($msgs as $m) {
        if ($found) $result[] = $m;
        if ($m['id'] === $after_id) $found = true;
    }
    // if after_id not found, return all
    return $found ? $result : $msgs;
}

function chat_send_message($session_id, $sender, $sender_name, $text, $type = 'text', $is_system = false) {
    if (!$session_id || !trim($text)) return false;

    $file = chat_session_file($session_id);
    $msgs = chat_read_json($file);

    $msg_id = generateId('msg_');
    $msg = [
        'id'          => $msg_id,
        'session_id'  => $session_id,
        'sender'      => $sender,       // 'admin' | 'customer'
        'sender_name' => $sender_name,
        'text'        => htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8'),
        'type'        => $type,         // text | image | file
        'is_system'   => $is_system,
        'timestamp'   => time(),
        'read'        => false,
    ];

    $msgs[] = $msg;

    // Trim to max
    if (count($msgs) > CHAT_MAX_MSG)
        $msgs = array_slice($msgs, -CHAT_MAX_MSG);

    chat_write_json($file, $msgs);

    // Update session metadata
    $sessions = chat_get_all_sessions();
    if (isset($sessions[$session_id])) {
        $sessions[$session_id]['last_message'] = mb_substr($text, 0, 60);
        $sessions[$session_id]['last_time']    = time();
        if ($sender === 'customer')
            $sessions[$session_id]['unread_admin']++;
        else
            $sessions[$session_id]['unread_customer']++;
        chat_write_json(SESSIONS_FILE, $sessions);
    }

    return $msg_id;
}

function chat_mark_read($session_id, $reader) {
    // Mark messages as read
    $file = chat_session_file($session_id);
    $msgs = chat_read_json($file);
    $changed = false;
    foreach ($msgs as &$m) {
        if ($m['sender'] !== $reader && !$m['read']) {
            $m['read'] = true;
            $changed = true;
        }
    }
    unset($m);
    if ($changed) chat_write_json($file, $msgs);

    // Reset unread counter
    $field = $reader === 'admin' ? 'unread_admin' : 'unread_customer';
    chat_update_session($session_id, [$field => 0]);
}

function chat_set_typing($session_id, $who, $typing) {
    $key = $who === 'admin' ? 'admin_typing' : 'customer_typing';
    chat_update_session($session_id, [$key => $typing, 'typing_ts' => time()]);
}

function chat_total_unread_admin() {
    $sessions = chat_get_all_sessions();
    return array_sum(array_column($sessions, 'unread_admin'));
}

// ── Quick replies (preset jawaban cepat) ──────────────────────
function chat_quick_replies() {
    return [
        ['label' => '✅ Proses Selesai',   'text' => 'Pesanan kamu sudah selesai diproses! Item sudah masuk ke akun game kamu. Terima kasih sudah belanja di GameStore! 😊'],
        ['label' => '⏳ Sedang Diproses',  'text' => 'Pesanan kamu sedang kami proses ya. Mohon tunggu sebentar, maksimal 5 menit item akan masuk. 🙏'],
        ['label' => '💳 Info Pembayaran',  'text' => 'Untuk pembayaran bisa melalui: DANA, OVO, GoPay, ShopeePay, Transfer BCA/Mandiri/BRI, atau QRIS. Setelah bayar, kirimkan bukti transfer ke sini ya!'],
        ['label' => '🆔 Minta User ID',    'text' => 'Boleh tolong kirimkan User ID game kamu? Untuk Mobile Legends format: 123456789 (1234). Pastikan sudah benar ya!'],
        ['label' => '🙏 Terima Kasih',     'text' => 'Terima kasih sudah berbelanja di GameStore! 🎮 Jangan lupa kasih bintang 5 ya kalau puas. Sampai jumpa lagi! ⭐'],
        ['label' => '❓ Butuh Bantuan',    'text' => 'Ada yang bisa saya bantu lagi? Jangan ragu untuk bertanya ya! Kami siap melayani kamu 24 jam. 😊'],
    ];
}