<?php
/**
 * FinanceTrack — Authentication & Session Middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');
        session_start();
    } else {
        @session_start();
    }
}

require_once __DIR__ . '/../config/database.php';

/**
 * Mendapatkan ID user yang sedang login
 * @return int|null
 */
function getCurrentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Mendapatkan data user yang sedang login dari database
 * @return array|null
 */
function getCurrentUser(): ?array {
    $userId = getCurrentUserId();
    if (!$userId) {
        return null;
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT id, name, email, role, avatar, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        if ($user) {
            return $user;
        }
    } catch (\Throwable $e) {
        error_log('Error fetching current user: ' . $e->getMessage());
    }

    // Fallback jika database belum ada atau koneksi gagal tetapi session ada
    return [
        'id' => $userId,
        'name' => $_SESSION['user_name'] ?? 'Pengguna',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'avatar' => $_SESSION['user_avatar'] ?? 'FP',
        'created_at' => null
    ];
}

/**
 * Proteksi halaman web: alihkan ke login jika belum diautentikasi
 * @param string $loginUrl
 */
function requireAuth(string $loginUrl = '../login.php'): void {
    if (!getCurrentUserId()) {
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Proteksi API endpoint: kirim 401 Unauthorized jika belum diautentikasi
 */
function requireApiAuth(): int {
    $userId = getCurrentUserId();
    if (!$userId) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Sesi login telah berakhir atau Anda belum login.',
            'error_code' => 'UNAUTHENTICATED'
        ], 401);
        exit;
    }
    return $userId;
}

/**
 * Kirim respons JSON terstandarisasi dengan HTTP status code
 * @param mixed $data
 * @param int $statusCode
 */
function sendJsonResponse($data, int $statusCode = 200): void {
    // Bersihkan buffer output agar tidak ada notice/warning PHP yang merusak JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Mendapatkan atau membuat CSRF Token
 * @return string
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF Token
 * @param string|null $token
 * @return bool
 */
function verifyCsrfToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

