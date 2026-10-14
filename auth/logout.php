<?php
/**
 * FinanceTrack — Logout Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
session_regenerate_id(true);
$_SESSION['auth_success'] = 'Anda telah keluar.';

header('Location: ../login.php');
exit;

