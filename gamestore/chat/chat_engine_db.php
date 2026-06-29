<?php
/**
 * GameStore Chat Engine (MySQL version)
 * Menggantikan chat_engine.php berbasis file JSON
 */

require_once dirname(__DIR__) . '/includes/db.php';

function generateId(string $prefix = ''): string {
    return $prefix . substr(md5(uniqid(rand(), true)), 0, 10);
}

function timeAgo(int $ts): string {
    $diff = time() - $ts;
    if ($diff < 60)    return 'Baru saja';
    if ($diff < 3600)  return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    return date('d/m H:i', $ts);
}

// ── Sessions ──────────────────────────────────────────────────
function chat_get_all_sessions(): array {
    return DB::rows("SELECT * FROM chat_sessions ORDER BY last_time DESC");
}

function chat_get_session(string $session_id): ?array {
    return DB::row("SELECT * FROM chat_sessions WHERE id = ?", [$session_id]);
}

function chat_create_session(string $name, string $email = '', string $topic = 'Umum'): string {
    $sid = generateId('cs_');
    DB::exec("
        INSERT INTO chat_sessions (id, name, email, topic, status, last_time)
        VALUES (?, ?, ?, ?, 'open', ?)
    ", [$sid, $name, $email, $topic, time()]);

    // Auto welcome message
    chat_send_message($sid, 'admin', 'Admin GameStore',
        "Halo *{$name}*! 👋 Selamat datang di GameStore. Saya admin yang bertugas, ada yang bisa saya bantu?",
        'text', true
    );
    return $sid;
}

function chat_update_session(string $session_id, array $data): bool {
    if (empty($data)) return false;
    $sets = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
    $vals = array_values($data);
    $vals[] = $session_id;
    return DB::exec("UPDATE chat_sessions SET $sets WHERE id = ?", $vals) > 0;
}

// ── Messages ──────────────────────────────────────────────────
function chat_get_messages(string $session_id, ?string $after_id = null): array {
    if ($after_id) {
        // Ambil timestamp pesan after_id, lalu ambil yang setelahnya
        $ts = DB::val("SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages WHERE id = ?", [$after_id]);
        if ($ts) {
            return DB::rows("
                SELECT *, UNIX_TIMESTAMP(created_at) AS timestamp
                FROM chat_messages
                WHERE session_id = ? AND created_at > FROM_UNIXTIME(?)
                ORDER BY created_at ASC
                LIMIT 50
            ", [$session_id, $ts]);
        }
    }
    return DB::rows("
        SELECT *, UNIX_TIMESTAMP(created_at) AS timestamp
        FROM chat_messages
        WHERE session_id = ?
        ORDER BY created_at ASC
        LIMIT 200
    ", [$session_id]);
}

function chat_send_message(string $session_id, string $sender, string $sender_name,
    string $text, string $type = 'text', bool $is_system = false): ?string
{
    if (!$session_id || !trim($text)) return null;

    $msg_id = generateId('msg_');
    $clean  = htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
    $preview = mb_substr($clean, 0, 60);

    DB::exec("
        INSERT INTO chat_messages (id, session_id, sender, sender_name, message, is_system)
        VALUES (?, ?, ?, ?, ?, ?)
    ", [$msg_id, $session_id, $sender, $sender_name, $clean, $is_system ? 1 : 0]);

    // Update session metadata
    $unread_field = $sender === 'customer' ? 'unread_admin' : 'unread_customer';
    DB::exec("
        UPDATE chat_sessions
        SET last_message = ?, last_time = ?, $unread_field = $unread_field + 1
        WHERE id = ?
    ", [$preview, time(), $session_id]);

    return $msg_id;
}

function chat_mark_read(string $session_id, string $reader): void {
    $sender = $reader === 'admin' ? 'customer' : 'admin';
    DB::exec("
        UPDATE chat_messages SET is_read = 1
        WHERE session_id = ? AND sender = ? AND is_read = 0
    ", [$session_id, $sender]);

    $field = $reader === 'admin' ? 'unread_admin' : 'unread_customer';
    DB::exec("UPDATE chat_sessions SET $field = 0 WHERE id = ?", [$session_id]);
}

function chat_set_typing(string $session_id, string $who, bool $typing): void {
    $key = $who === 'admin' ? 'admin_typing' : 'customer_typing';
    // Simpan di PHP session sementara (typing indicator tidak perlu persisten)
    $_SESSION['typing'][$session_id][$key] = $typing ? time() : 0;
}

function chat_is_typing(string $session_id, string $reader): bool {
    $key = $reader === 'admin' ? 'customer_typing' : 'admin_typing';
    $ts  = $_SESSION['typing'][$session_id][$key] ?? 0;
    return $ts > 0 && (time() - $ts) < 5;
}

function chat_total_unread_admin(): int {
    return (int)(DB::val("
        SELECT COALESCE(SUM(unread_admin), 0) FROM chat_sessions WHERE status != 'resolved'
    ") ?? 0);
}

function chat_quick_replies(): array {
    return [
        ['label' => '✅ Proses Selesai',  'text' => 'Pesanan kamu sudah selesai diproses! Item sudah masuk ke akun game kamu. Terima kasih sudah belanja di GameStore! 😊'],
        ['label' => '⏳ Sedang Diproses', 'text' => 'Pesanan kamu sedang kami proses ya. Mohon tunggu sebentar, maksimal 5 menit item akan masuk. 🙏'],
        ['label' => '💳 Info Pembayaran', 'text' => 'Untuk pembayaran bisa melalui: DANA, OVO, GoPay, ShopeePay, Transfer BCA/Mandiri/BRI, atau QRIS. Setelah bayar, kirimkan bukti transfer ke sini ya!'],
        ['label' => '🆔 Minta User ID',   'text' => 'Boleh tolong kirimkan User ID game kamu? Untuk Mobile Legends format: 123456789 (1234). Pastikan sudah benar ya!'],
        ['label' => '🙏 Terima Kasih',    'text' => 'Terima kasih sudah berbelanja di GameStore! 🎮 Jangan lupa kasih bintang 5 ya kalau puas. Sampai jumpa lagi! ⭐'],
        ['label' => '❓ Butuh Bantuan',   'text' => 'Ada yang bisa saya bantu lagi? Jangan ragu untuk bertanya ya! Kami siap melayani kamu 24 jam. 😊'],
    ];
}
