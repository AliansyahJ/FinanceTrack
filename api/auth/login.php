<?php
/**
 * FinanceTrack API — Login Endpoint
 * POST /api/auth/login.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Metode permintaan tidak diizinkan.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    sendJsonResponse(['success' => false, 'message' => 'Email dan kata sandi wajib diisi.'], 400);
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, avatar FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'] ?: 'FP';

        sendJsonResponse([
            'success' => true,
            'message' => 'Login berhasil.',
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => $user['avatar']
            ]
        ]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Email atau kata sandi tidak cocok.'], 401);
    }
} catch (\Throwable $e) {
    error_log('API Login Error: ' . $e->getMessage());
    // Fallback akun demo jika MySQL belum menyala
    if ($email === 'demo@financetrack.test' && $password === 'Demo123!') {
        session_regenerate_id(true);
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Fikri Juana';
        $_SESSION['user_email'] = 'demo@financetrack.test';
        $_SESSION['user_role'] = 'user';
        $_SESSION['user_avatar'] = 'FP';

        sendJsonResponse([
            'success' => true,
            'message' => 'Login berhasil (demo fallback mode).',
            'user' => [
                'id' => 1,
                'name' => 'Fikri Juana',
                'email' => 'demo@financetrack.test',
                'role' => 'user',
                'avatar' => 'FP'
            ]
        ]);
    }

    sendJsonResponse(['success' => false, 'message' => 'Koneksi database bermasalah: ' . $e->getMessage()], 500);
}

