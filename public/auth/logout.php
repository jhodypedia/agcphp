<?php
require_once __DIR__ . '/../../config.php';

$uid = current_user_id();
if ($uid) {
    log_activity($uid, 'logout', 'User logged out');
}

session_destroy();
header('Location: /login');
exit;
