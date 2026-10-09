<?php
/**
 * FinanceTrack — Database Connection Configuration
 * Menggunakan PDO dengan Prepared Statements & Exception Mode
 */

// Konfigurasi Database (Default untuk XAMPP)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'financetrack_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

/**
 * Mendapatkan koneksi PDO ke database MySQL
 * @return PDO
 * @throws PDOException
 */
function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log pesan error teknis ke error_log, jangan tampilkan password ke browser
            error_log('Database Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }

    return $pdo;
}

/**
 * Helper untuk memeriksa apakah database sudah terhubung
 * @return bool
 */
function isDbConnected(): bool {
    try {
        getDbConnection();
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

