<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';
if (!empty($_SESSION['admin_logged_in'])) {
    log_activity('logout', 'Admin logout: ' . ($_SESSION['admin_user'] ?? ''));
}
session_destroy();
header('Location: login.php');
exit;
