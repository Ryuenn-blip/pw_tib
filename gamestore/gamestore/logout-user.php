<?php
session_start();
require_once 'includes/config.php';
user_logout();
session_destroy();
header('Location: login.php');
exit;
