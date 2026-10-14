<?php
/**
 * FinanceTrack API — Current Authenticated User Info
 * GET /api/auth/me.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = getCurrentUserId();
if (!$userId) {
    sendJsonResponse([
        'authenticated' => false,
        'user' => null
    ], 200); // 200 OK so frontend can check session state smoothly
}

$user = getCurrentUser();

$avatarUrl = null;
$photoPath = '../../uploads/avatars/user_' . $userId . '.jpg';
if (file_exists(__DIR__ . '/' . $photoPath)) {
    $avatarUrl = '../uploads/avatars/user_' . $userId . '.jpg?t=' . filemtime(__DIR__ . '/' . $photoPath);
}

sendJsonResponse([
    'success' => true,
    'authenticated' => true,
    'data' => [
        'user' => [
            'id' => (int)$user['id'],
            'nama' => $user['name'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar_initials' => $user['avatar'] ?: 'FP',
            'avatar_url' => $avatarUrl
        ]
    ]
]);

