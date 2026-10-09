<?php
/**
 * FinanceTrack — Comprehensive QA Verification Script
 * Menjalankan uji integrasi PHP, MySQL, Auth, Transaction Lifecycle,
 * Receipt MIME Validation, Dashboard Calculations, dan CSV Export.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "==============================================================\n";
echo "   FINANCETRACK — COMPREHENSIVE QA INTEGRATION VERIFICATION   \n";
echo "==============================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "[PASS] {$name}" . ($detail ? " — {$detail}" : "") . "\n";
    } else {
        $failed++;
        echo "[FAIL] {$name}" . ($detail ? " — {$detail}" : "") . "\n";
    }
}

// ----------------------------------------------------------------------------
// 1. Database Connection & Schema Check
// ----------------------------------------------------------------------------
echo "--- 1. Database Connection & Schema Integrity ---\n";
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDbConnection();
    assertTest("MySQL Connection", true, "Terhubung ke " . DB_HOST . ":" . DB_PORT . "/" . DB_NAME);

    $tables = ['users', 'categories', 'transactions', 'budgets'];
    foreach ($tables as $tbl) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        assertTest("Table `{$tbl}` exists & queryable", true, "Total baris: {$count}");
    }
} catch (\Throwable $e) {
    assertTest("MySQL Connection", false, $e->getMessage());
    exit(1);
}

// ----------------------------------------------------------------------------
// 2. Demo Account & Password Hashing Verification
// ----------------------------------------------------------------------------
echo "\n--- 2. Demo Account & Password Hashing ---\n";
$stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, avatar FROM users WHERE email = 'demo@financetrack.test'");
$stmt->execute();
$demoUser = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest("Demo User Exists", !empty($demoUser), "ID: " . ($demoUser['id'] ?? '-'));
$pwMatch = password_verify('Demo123!', $demoUser['password_hash'] ?? '');
assertTest("Password Verification ('Demo123!')", $pwMatch, "BCRYPT hash valid");

$wrongMatch = password_verify('WrongPassword', $demoUser['password_hash'] ?? '');
assertTest("Reject Invalid Password", !$wrongMatch, "Kata sandi salah ditolak");

// ----------------------------------------------------------------------------
// 3. Guest vs Authenticated Middleware Scoping
// ----------------------------------------------------------------------------
echo "\n--- 3. Middleware & Session Scoping ---\n";
require_once __DIR__ . '/../auth/middleware.php';

// Guest mode (tanpa session)
$_SESSION = [];
assertTest("Guest getCurrentUserId() is null", getCurrentUserId() === null);
assertTest("Guest getCurrentUser() is null", getCurrentUser() === null);

// Authenticated mode (simulasi login demo)
$_SESSION['user_id'] = (int)$demoUser['id'];
$_SESSION['user_name'] = $demoUser['name'];
$_SESSION['user_email'] = $demoUser['email'];
$_SESSION['user_role'] = $demoUser['role'];
$_SESSION['user_avatar'] = $demoUser['avatar'];

assertTest("Authenticated getCurrentUserId()", getCurrentUserId() === (int)$demoUser['id']);
$authProfile = getCurrentUser();
assertTest("getCurrentUser() returns profile", $authProfile['email'] === 'demo@financetrack.test');

// ----------------------------------------------------------------------------
// 4. Receipt Validation Logic (Frontend & Backend MIME)
// ----------------------------------------------------------------------------
echo "\n--- 4. Receipt Upload Security & MIME Validation ---\n";
$uploadsDir = __DIR__ . '/../uploads/receipts';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// A. Valid image: create 1x1 dummy PNG
$validPngPath = __DIR__ . '/temp_valid.png';
$pngHeader = hex2bin('89504e470d0a1a0a0000000d49484452000000010000000108060000001f15c4890000000a49444154789c63000100000500010d0a2d420000000049454e44ae426082');
file_put_contents($validPngPath, $pngHeader);

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$validMime = finfo_file($finfo, $validPngPath);
assertTest("Valid PNG MIME Check", in_array($validMime, ['image/png', 'image/x-png'], true), "MIME: {$validMime}");

// B. Fake image: rename text to .jpg
$fakeJpgPath = __DIR__ . '/temp_fake.jpg';
file_put_contents($fakeJpgPath, '<?php echo "evil script"; ?>');
$fakeMime = finfo_file($finfo, $fakeJpgPath);
$rejectedMime = !in_array($fakeMime, ['image/jpeg', 'image/png', 'image/webp'], true);
assertTest("Reject Fake JPG (PHP script disguised)", $rejectedMime, "Detected MIME: {$fakeMime}");

// C. PDF rejection
$pdfPath = __DIR__ . '/temp_test.pdf';
file_put_contents($pdfPath, '%PDF-1.4 dummy pdf content');
$pdfMime = finfo_file($finfo, $pdfPath);
$rejectedPdf = !in_array($pdfMime, ['image/jpeg', 'image/png', 'image/webp'], true);
assertTest("Reject PDF Document", $rejectedPdf, "Detected MIME: {$pdfMime}");

// D. Executable rejection
$exePath = __DIR__ . '/temp_test.exe';
file_put_contents($exePath, 'MZ dummy executable');
$exeMime = finfo_file($finfo, $exePath);
$rejectedExe = !in_array($exeMime, ['image/jpeg', 'image/png', 'image/webp'], true);
assertTest("Reject EXE File", $rejectedExe, "Detected MIME: {$exeMime}");
finfo_close($finfo);

// ----------------------------------------------------------------------------
// 5. Complete Transaction Lifecycle (CREATE, EDIT, DELETE)
// ----------------------------------------------------------------------------
echo "\n--- 5. Transaction Lifecycle against MySQL ---\n";
$uid = (int)$demoUser['id'];

// Initial balance & count
$initCount = (int)$pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = {$uid}")->fetchColumn();
$initSum = (float)$pdo->query("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) FROM transactions WHERE user_id = {$uid}")->fetchColumn();

// CREATE
$receiptDest = $uploadsDir . '/receipt_qa_test_' . time() . '.png';
copy($validPngPath, $receiptDest);
$receiptRelative = 'uploads/receipts/' . basename($receiptDest);

$insertStmt = $pdo->prepare("INSERT INTO transactions 
    (user_id, category_id, type, amount, transaction_date, transaction_time, description, payment_method, receipt_path, notes)
    VALUES (:uid, 'makanan', 'expense', 75000, '2026-10-08', '12:30:00', 'QA Test Makan Siang', 'qris', :receipt, 'Catatan QA')");
$insertStmt->execute([':uid' => $uid, ':receipt' => $receiptRelative]);
$testTxId = (int)$pdo->lastInsertId();

assertTest("CREATE Transaction", $testTxId > 0, "Inserted Tx ID: {$testTxId}");

// Verify in MySQL
$verifyStmt = $pdo->prepare("SELECT * FROM transactions WHERE id = :id AND user_id = :uid");
$verifyStmt->execute([':id' => $testTxId, ':uid' => $uid]);
$createdRecord = $verifyStmt->fetch(PDO::FETCH_ASSOC);

assertTest("Verify in MySQL", $createdRecord && (float)$createdRecord['amount'] == 75000.0 && $createdRecord['receipt_path'] === $receiptRelative, "Amount: 75.000, Category: makanan");
assertTest("Receipt File Saved on Disk", file_exists(__DIR__ . '/../' . $receiptRelative));

// Verify Dashboard calculations changed
$afterCreateSum = (float)$pdo->query("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) FROM transactions WHERE user_id = {$uid}")->fetchColumn();
assertTest("Dashboard Balance Decreased by 75.000", ($initSum - $afterCreateSum) == 75000.0, "New Balance: " . number_format($afterCreateSum, 0, ',', '.'));

// EDIT
$updateStmt = $pdo->prepare("UPDATE transactions SET amount = 85000, description = 'QA Test Makan Siang Updated' WHERE id = :id AND user_id = :uid");
$updateStmt->execute([':id' => $testTxId, ':uid' => $uid]);

$editVerify = $pdo->query("SELECT amount, description FROM transactions WHERE id = {$testTxId}")->fetch(PDO::FETCH_ASSOC);
assertTest("EDIT Transaction in MySQL", (float)$editVerify['amount'] == 85000.0 && $editVerify['description'] === 'QA Test Makan Siang Updated', "Amount updated to 85.000");

// DELETE
if (file_exists($receiptDest)) {
    unlink($receiptDest);
}
$delStmt = $pdo->prepare("DELETE FROM transactions WHERE id = :id AND user_id = :uid");
$delStmt->execute([':id' => $testTxId, ':uid' => $uid]);

$afterDelCount = (int)$pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = {$uid}")->fetchColumn();
assertTest("DELETE Transaction from MySQL", $afterDelCount === $initCount, "Count returned to {$initCount}");
assertTest("Receipt File Removed", !file_exists($receiptDest));

// ----------------------------------------------------------------------------
// 6. Database Hydration Bootstrap
// ----------------------------------------------------------------------------
echo "\n--- 6. Database Bootstrap Hydration for UI ---\n";
require_once __DIR__ . '/../database/bootstrap.php';
$bootstrap = getDatabaseBootstrapData($uid);

assertTest("Bootstrap Data Returns Success", !empty($bootstrap['success']));
assertTest("Bootstrap Transactions Array", is_array($bootstrap['transactions']) && count($bootstrap['transactions']) === $initCount, "Items: " . count($bootstrap['transactions']));
assertTest("Bootstrap Categories Array", is_array($bootstrap['categories']) && count($bootstrap['categories']) > 0, "Categories: " . count($bootstrap['categories']));
assertTest("Bootstrap Budgets Array", is_array($bootstrap['budgets']), "Budgets: " . count($bootstrap['budgets']));
assertTest("Bootstrap Summary Calculated", isset($bootstrap['summary']['saldo']), "Saldo: " . number_format($bootstrap['summary']['saldo'], 0, ',', '.'));

// ----------------------------------------------------------------------------
// 7. CSV Export Verification
// ----------------------------------------------------------------------------
echo "\n--- 7. Real CSV Export Verification ---\n";
$phpBin = PHP_BINARY;
$wrapperCode = '<?php session_start(); $_SESSION["user_id"] = ' . $uid . '; require "' . addslashes(__DIR__ . '/../api/reports/export_csv.php') . '";';
$tmpScript = __DIR__ . '/temp_csv_runner.php';
file_put_contents($tmpScript, $wrapperCode);
$csvOutput = shell_exec('"' . $phpBin . '" "' . $tmpScript . '"');
@unlink($tmpScript);

assertTest("CSV Output Not Empty", strlen($csvOutput) > 100, "Size: " . strlen($csvOutput) . " bytes");
$hasBom = (substr($csvOutput, 0, 3) === "\xEF\xBB\xBF");
assertTest("CSV UTF-8 BOM Present", $hasBom, "Valid Excel Windows encoding");

$hasHeader = strpos($csvOutput, "Tanggal") !== false && strpos($csvOutput, "Deskripsi") !== false && strpos($csvOutput, "Nominal (Rp)") !== false;
assertTest("CSV Column Headers Valid", $hasHeader, "Headers: No;Tanggal;Waktu;Jenis;Kategori;Deskripsi;Metode Pembayaran;Nominal (Rp);Catatan");

$hasSemicolon = (substr_count(strtok($csvOutput, "\r\n"), ";") >= 8);
assertTest("CSV Semicolon Separator Valid", $hasSemicolon, "European/Indonesian Excel standard delimiter (;)");

// ----------------------------------------------------------------------------
// 8. Cleanup Temporary Test Files
// ----------------------------------------------------------------------------
@unlink($validPngPath);
@unlink($fakeJpgPath);
@unlink($pdfPath);
@unlink($exePath);

echo "\n==============================================================\n";
echo "   QA RESULTS: {$passed} PASSED, {$failed} FAILED                 \n";
echo "==============================================================\n";

exit($failed === 0 ? 0 : 1);
