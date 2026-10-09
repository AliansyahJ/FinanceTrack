<?php
/**
 * FinanceTrack — Database Migration & Seeder CLI Tool
 * Digunakan untuk menginisialisasi atau mereset database financetrack_db
 */

require_once __DIR__ . '/../config/database.php';

echo "=== FinanceTrack Database Migration ===\n";

try {
    // 1. Hubungkan ke MySQL server terlebih dahulu (tanpa memilih database)
    $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[OK] Terhubung ke MySQL server (" . DB_HOST . ":" . DB_PORT . ")\n";

    // 2. Baca file financetrack.sql
    $sqlFile = __DIR__ . '/financetrack.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("File financetrack.sql tidak ditemukan di: " . $sqlFile);
    }
    $sql = file_get_contents($sqlFile);

    // 3. Eksekusi query
    $pdo->exec($sql);
    echo "[OK] Schema & seed data berhasil diimpor ke database '" . DB_NAME . "'!\n";

    // 4. Verifikasi demo account
    $pdo->exec("USE `" . DB_NAME . "`");
    $stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE email = 'demo@financetrack.test'");
    $demoUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($demoUser) {
        echo "[OK] Akun demo terverifikasi: " . $demoUser['email'] . " (Role: " . $demoUser['role'] . ")\n";
    }

    $txCount = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    $bgCount = $pdo->query("SELECT COUNT(*) FROM budgets")->fetchColumn();
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

    echo "[INFO] Total Categories: {$catCount}\n";
    echo "[INFO] Total Transactions: {$txCount}\n";
    echo "[INFO] Total Budgets: {$bgCount}\n";
    echo "=== Migrasi Selesai dengan Sukses ===\n";

} catch (PDOException $e) {
    echo "[ERROR] Database Error: " . $e->getMessage() . "\n";
    echo "[TIPS] Pastikan MySQL di XAMPP sudah dinyalakan (Running).\n";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}

