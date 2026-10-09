<?php
$baseUrl = 'http://localhost/FinanceTrack';

// 1. Test guest access to protected dashboard
$ch = curl_init($baseUrl . '/pages/dashboard.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "1. Guest access to dashboard.php: HTTP {$httpCode}\n";
if ($httpCode === 302) {
    echo "   [OK] Redirected as expected.\n";
} else {
    echo "   [FAIL] Expected 302 redirect.\n";
}

// 2. Perform login request
$cookieFile = sys_get_temp_dir() . '/ft_cookie_' . uniqid() . '.txt';
$ch = curl_init($baseUrl . '/auth/login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'demo@financetrack.test',
    'password' => 'Demo123!'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
$response = curl_exec($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "2. Login submission: HTTP {$loginCode}\n";
if ($loginCode === 302) {
    echo "   [OK] Authenticated successfully.\n";
} else {
    echo "   [FAIL] Login returned {$loginCode}.\n";
}

// 3. Test access to all 6 pages with session cookie
$pages = ['dashboard.php', 'transaksi.php', 'form-transaksi.php', 'anggaran.php', 'laporan.php', 'pengaturan.php'];
foreach ($pages as $p) {
    $ch = curl_init($baseUrl . '/pages/' . $p);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $hasPhpError = (stripos($html, 'Fatal error') !== false || stripos($html, 'Parse error') !== false || stripos($html, 'Warning:') !== false);
    echo "3. Page {$p}: HTTP {$code}" . ($hasPhpError ? " [PHP ERROR DETECTED]" : " [OK Clean HTML, size " . strlen($html) . " bytes]") . "\n";
}

@unlink($cookieFile);
echo "\nAll HTTP tests completed.\n";
