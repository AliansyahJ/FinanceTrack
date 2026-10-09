<?php
/**
 * FinanceTrack — Entrypoint Router
 */

require_once __DIR__ . '/auth/middleware.php';

if (getCurrentUserId()) {
    header('Location: pages/dashboard.php');
    exit;
}

// Jika belum login, tampilkan landing page
require __DIR__ . '/index.html';

