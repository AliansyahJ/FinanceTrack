<?php
/**
 * FinanceTrack API — Delete Avatar
 * POST /api/auth/delete_avatar.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$photoPath = __DIR__ . '/../../uploads/avatars/user_' . $userId . '.jpg';
if (file_exists($photoPath)) {
    @unlink($photoPath);
}

$user = getCurrentUser();

sendJsonResponse([
    'success' => true,
    'message' => 'Foto profil berhasil dihapus.',
    'avatar_initials' => $user['avatar'] ?? 'FP'
]);
