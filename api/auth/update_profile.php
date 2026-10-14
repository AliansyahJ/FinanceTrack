<?php
require_once __DIR__ . '/../../auth/middleware.php';
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user = getCurrentUser();
if (!$user) {
    sendJsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$name = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($name) || empty($email)) {
    sendJsonResponse(['success' => false, 'message' => 'Data tidak boleh kosong.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['success' => false, 'message' => 'Format email tidak valid.'], 400);
}

try {
    $pdo = getDbConnection();
    
    // Check if email already used by another user
    $check = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $check->execute([':email' => $email, ':id' => $user['id']]);
    if ($check->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Email sudah digunakan oleh pengguna lain.'], 400);
    }
    
    // Update avatar initials
    $words = explode(' ', $name);
    $avatar = mb_strtoupper(mb_substr($words[0] ?? 'F', 0, 1));
    if (isset($words[1]) && !empty($words[1])) {
        $avatar .= mb_strtoupper(mb_substr($words[1], 0, 1));
    } else {
        $avatar .= mb_strtoupper(mb_substr($words[0] ?? 'P', 1, 1));
    }

    // Update user
    $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, avatar = :avatar WHERE id = :id");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':avatar' => $avatar,
        ':id' => $user['id']
    ]);
    
    // Update session
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_avatar'] = $avatar;
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Profil berhasil diperbarui.',
        'data' => [
            'nama' => $name,
            'email' => $email,
            'role' => $user['role'] ?? 'user',
            'avatar_initials' => $avatar
        ]
    ]);
} catch (\Throwable $e) {
    error_log('Error update profile: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal memperbarui profil.'], 500);
}
