<?php
/**
 * FinanceTrack — Form Login Handler
 */

require_once __DIR__ . '/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']);

if (empty($email) || empty($password)) {
    $_SESSION['auth_error'] = 'Harap isi email dan kata sandi.';
    header('Location: ../login.php');
    exit;
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, avatar FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Login berhasil
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'] ?: 'FP';
        unset($_SESSION['auth_error']);

        header('Location: ../pages/dashboard.php');
        exit;
    } else {
        $_SESSION['auth_error'] = 'Email atau kata sandi tidak cocok.';
        header('Location: ../login.php');
        exit;
    }
} catch (\Throwable $e) {
    error_log('Login Error: ' . $e->getMessage());
    // Fallback untuk akun demo jika database belum sempat di-start/diimpor
    if ($email === 'demo@financetrack.test' && $password === 'Demo123!') {
        session_regenerate_id(true);
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Fikri Juana';
        $_SESSION['user_email'] = 'demo@financetrack.test';
        $_SESSION['user_role'] = 'user';
        $_SESSION['user_avatar'] = 'FP';
        unset($_SESSION['auth_error']);

        header('Location: ../pages/dashboard.php');
        exit;
    }

    $_SESSION['auth_error'] = 'Gagal terhubung ke database. Pastikan MySQL sudah menyala.';
    header('Location: ../login.php');
    exit;
}

