<?php
/**
 * FinanceTrack — Form Registration Handler
 */

require_once __DIR__ . '/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php?action=register');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($name) || empty($email) || empty($password)) {
    $_SESSION['auth_error'] = 'Semua kolom wajib diisi.';
    header('Location: ../login.php?action=register');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['auth_error'] = 'Format email tidak valid.';
    header('Location: ../login.php?action=register');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['auth_error'] = 'Kata sandi minimal 6 karakter.';
    header('Location: ../login.php?action=register');
    exit;
}

if (!empty($confirmPassword) && $password !== $confirmPassword) {
    $_SESSION['auth_error'] = 'Konfirmasi kata sandi tidak cocok.';
    header('Location: ../login.php?action=register');
    exit;
}

try {
    $pdo = getDbConnection();

    // Periksa apakah email sudah terdaftar
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['auth_error'] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
        header('Location: ../login.php?action=register');
        exit;
    }

    // Inisial avatar (2 huruf depan nama)
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

    // Auto login
    session_regenerate_id(true);
    $_SESSION['user_id'] = $newId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_avatar'] = $avatar;
    unset($_SESSION['auth_error']);

    header('Location: ../pages/dashboard.php');
    exit;

} catch (\Throwable $e) {
    error_log('Registration Error: ' . $e->getMessage());
    $_SESSION['auth_error'] = 'Terjadi kesalahan sistem saat mendaftar: ' . $e->getMessage();
    header('Location: ../login.php?action=register');
    exit;
}

