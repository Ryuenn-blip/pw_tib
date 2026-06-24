<?php
/**
 * GameStore Chat API
 * Semua request AJAX chat masuk ke sini
 * 
 * Actions:
 *   init          - Buat/ambil session customer
 *   send          - Kirim pesan
 *   poll          - Long-poll pesan baru
 *   typing        - Set status mengetik
 *   resolve       - Admin menutup sesi
 *   sessions      - Admin: daftar semua sesi
 *   history       - Ambil history pesan
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/chat_engine.php';

// ── Helpers ───────────────────────────────────────────────────
function resp($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function err($msg, $code = 400) { resp(['error' => $msg], $code); }

// ── CORS for same-origin ──────────────────────────────────────
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ── Admin auth check helper ───────────────────────────────────
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['admin_logged_in']);
}

// ── ROUTE ─────────────────────────────────────────────────────
switch ($action) {

    // ── Customer: init session ────────────────────────────────
    case 'init':
        if ($method !== 'POST') err('POST required');
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $topic = trim($_POST['topic'] ?? 'Umum');
        if (!$name) err('Nama wajib diisi');

        // Cek session cookie customer
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['chat_session_id'])) {
            $existing = chat_get_session($_SESSION['chat_session_id']);
            if ($existing && $existing['status'] !== 'resolved') {
                resp(['session_id' => $_SESSION['chat_session_id'], 'resumed' => true]);
            }
        }

        $sid = chat_create_session($name, $email, $topic);
        $_SESSION['chat_session_id']   = $sid;
        $_SESSION['chat_customer_name'] = $name;
        resp(['session_id' => $sid, 'resumed' => false]);
        break;

    // ── Send message ──────────────────────────────────────────
    case 'send':
        if ($method !== 'POST') err('POST required');
        $sid    = $_POST['session_id'] ?? '';
        $text   = trim($_POST['text']  ?? '');
        $sender = $_POST['sender']     ?? 'customer';

        if (!$sid)  err('session_id missing');
        if (!$text) err('Pesan tidak boleh kosong');
        if (mb_strlen($text) > 1000) err('Pesan terlalu panjang (max 1000 karakter)');

        // Auth check
        if ($sender === 'admin' && !isAdmin()) err('Unauthorized', 403);

        // Validate session
        $session = chat_get_session($sid);
        if (!$session) err('Sesi tidak ditemukan');
        if ($session['status'] === 'resolved') err('Sesi sudah ditutup');

        // Customer session validation
        if ($sender === 'customer') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (($_SESSION['chat_session_id'] ?? '') !== $sid) err('Session mismatch', 403);
            $sender_name = $_SESSION['chat_customer_name'] ?? $session['name'];
        } else {
            $sender_name = 'Admin GameStore';
        }

        // Stop typing
        chat_set_typing($sid, $sender, false);

        $msg_id = chat_send_message($sid, $sender, $sender_name, $text);
        resp(['success' => true, 'msg_id' => $msg_id, 'timestamp' => time()]);
        break;

    // ── Poll for new messages ─────────────────────────────────
    case 'poll':
        $sid      = $_GET['session_id'] ?? '';
        $after_id = $_GET['after_id']   ?? null;
        $reader   = $_GET['reader']     ?? 'customer';

        if (!$sid) err('session_id missing');

        $session = chat_get_session($sid);
        if (!$session) err('Sesi tidak ditemukan');

        // Auth
        if ($reader === 'admin' && !isAdmin()) err('Unauthorized', 403);
        if ($reader === 'customer') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (($_SESSION['chat_session_id'] ?? '') !== $sid) err('Session mismatch', 403);
        }

        $msgs = chat_get_messages($sid, $after_id);
        chat_mark_read($sid, $reader);

        // Check if other side is typing (expire after 5s)
        $typing_key    = $reader === 'admin' ? 'customer_typing' : 'admin_typing';
        $typing_active = !empty($session[$typing_key])
                      && (time() - ($session['typing_ts'] ?? 0)) < 5;

        resp([
            'messages'  => $msgs,
            'typing'    => $typing_active,
            'status'    => $session['status'],
            'timestamp' => time(),
        ]);
        break;

    // ── Typing indicator ──────────────────────────────────────
    case 'typing':
        if ($method !== 'POST') err('POST required');
        $sid    = $_POST['session_id'] ?? '';
        $who    = $_POST['who']        ?? 'customer';
        $typing = !empty($_POST['typing']);

        if (!$sid) err('session_id missing');
        if ($who === 'admin' && !isAdmin()) err('Unauthorized', 403);
        if ($who === 'customer') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (($_SESSION['chat_session_id'] ?? '') !== $sid) err('Session mismatch', 403);
        }

        chat_set_typing($sid, $who, $typing);
        resp(['success' => true]);
        break;

    // ── Admin: get all sessions ───────────────────────────────
    case 'sessions':
        if (!isAdmin()) err('Unauthorized', 403);
        $sessions = chat_get_all_sessions();
        // Sort by last_time desc
        usort($sessions, fn($a, $b) => $b['last_time'] - $a['last_time']);
        resp(['sessions' => $sessions, 'total_unread' => chat_total_unread_admin()]);
        break;

    // ── Admin: resolve/reopen session ─────────────────────────
    case 'resolve':
        if (!isAdmin()) err('Unauthorized', 403);
        if ($method !== 'POST') err('POST required');
        $sid    = $_POST['session_id'] ?? '';
        $status = $_POST['status']     ?? 'resolved';
        if (!$sid) err('session_id missing');

        chat_update_session($sid, ['status' => $status]);

        if ($status === 'resolved') {
            chat_send_message($sid, 'admin', 'Admin GameStore',
                '✅ Sesi chat telah diselesaikan. Terima kasih sudah menghubungi GameStore! Jika ada pertanyaan lain, silakan buka chat baru.', 'text', true);
        }
        resp(['success' => true]);
        break;

    // ── Get full history ──────────────────────────────────────
    case 'history':
        $sid    = $_GET['session_id'] ?? '';
        $reader = $_GET['reader']     ?? 'customer';
        if (!$sid) err('session_id missing');
        if ($reader === 'admin' && !isAdmin()) err('Unauthorized', 403);
        if ($reader === 'customer') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (($_SESSION['chat_session_id'] ?? '') !== $sid) err('Session mismatch', 403);
        }
        $msgs    = chat_get_messages($sid);
        $session = chat_get_session($sid);
        chat_mark_read($sid, $reader);
        resp(['messages' => $msgs, 'session' => $session]);
        break;

    // ── Check session (customer returning) ───────────────────
    case 'check':
        if (session_status() === PHP_SESSION_NONE) session_start();
        $sid = $_SESSION['chat_session_id'] ?? null;
        if (!$sid) { resp(['has_session' => false]); }
        $session = chat_get_session($sid);
        if (!$session || $session['status'] === 'resolved') {
            unset($_SESSION['chat_session_id']);
            resp(['has_session' => false]);
        }
        resp(['has_session' => true, 'session_id' => $sid, 'name' => $session['name'], 'unread' => $session['unread_customer']]);
        break;

    default:
        err('Action tidak dikenal');
}