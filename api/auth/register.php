<?php
/**
 * FinanceTrack API — Register Endpoint
 * POST /api/auth/register.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Metode permintaan tidak diizinkan.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$name = trim($input['name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (empty($name) || empty($email) || empty($password)) {
    sendJsonResponse(['success' => false, 'message' => 'Semua kolom wajib diisi.'], 400);
}

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    sendJsonResponse(['success' => false, 'message' => 'Nama harus antara 2 hingga 100 karakter.'], 400);
}

if (!preg_match("/^[\p{L}\s\.,'-]+$/u", $name)) {
    sendJsonResponse(['success' => false, 'message' => 'Nama hanya boleh berisi huruf, spasi, titik, koma, tanda hubung, atau apostrof.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['success' => false, 'message' => 'Format email tidak valid.'], 400);
}

if (strlen($password) < 6) {
    sendJsonResponse(['success' => false, 'message' => 'Kata sandi minimal 6 karakter.'], 400);
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Email sudah terdaftar.'], 409);
    }

    $words = explode(' ', $name);
    $avatar = mb_strtoupper(mb_substr($words[0] ?? 'F', 0, 1));
    if (isset($words[1]) && !empty($words[1])) {
        $avatar .= mb_strtoupper(mb_substr($words[1], 0, 1));
    } else {
        $avatar .= mb_strtoupper(mb_substr($words[0] ?? 'P', 1, 1));
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $insert = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, avatar) VALUES (:name, :email, :hash, "user", :avatar)');
    $insert->execute([
        ':name' => $name,
        ':email' => $email,
        ':hash' => $hash,
        ':avatar' => $avatar
    ]);

    $newId = (int)$pdo->lastInsertId();

    session_regenerate_id(true);
    $_SESSION['user_id'] = $newId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_avatar'] = $avatar;

    sendJsonResponse([
        'success' => true,
        'message' => 'Pendaftaran akun berhasil.',
        'user' => [
            'id' => $newId,
            'name' => $name,
            'email' => $email,
            'role' => 'user',
            'avatar' => $avatar
        ]
    ], 201);
} catch (\Throwable $e) {
    error_log('API Register Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal mendaftarkan akun: ' . $e->getMessage()], 500);
}

