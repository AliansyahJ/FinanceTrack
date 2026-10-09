<?php
$pages = [
    'dashboard.php' => 'dashboard_financetrack',
    'transaksi.php' => 'semua_transaksi',
    'form-transaksi.php' => 'tambah_transaksi',
    'anggaran.php' => 'anggaran',
    'laporan.php' => 'laporan_keuangan'
];

$base_dir = __DIR__ . '/..';
$pages_dir = $base_dir . '/pages';
$stitch_dir = $base_dir . '/stitch_financetrack_app_wireframes';

foreach ($pages as $php_file => $stitch_folder) {
    $php_path = $pages_dir . '/' . $php_file;
    $html_path = $stitch_dir . '/' . $stitch_folder . '/code.html';
    
    if (!file_exists($php_path) || !file_exists($html_path)) {
        echo "Skipping $php_file - files not found\n";
        continue;
    }

    $php_content = file_get_contents($php_path);
    
    // Extract PHP header
    if (preg_match('/^(<\?php.*?\?>)\s*<!DOCTYPE html>/s', $php_content, $matches)) {
        $php_header = $matches[1];
    } else {
        echo "No PHP header found in $php_file\n";
        continue;
    }

    $html_content = file_get_contents($html_path);

    // Replacements
    $html_content = str_replace('Budi Pratama', '<?= htmlspecialchars($userName) ?>', $html_content);
    $html_content = str_replace('Mahasiswa / Personal', '<?= htmlspecialchars($userRole) ?> / Personal', $html_content);
    $html_content = str_replace('Personal Pro', '<?= htmlspecialchars($userRole) ?> / Personal', $html_content);
    $html_content = str_replace('Mahasiswa', '<?= htmlspecialchars($userRole) ?>', $html_content);
    
    $html_content = str_replace('Rp 3.450.000', '<?= \'Rp \' . number_format($summary[\'saldo\'], 0, \',\', \'.\') ?>', $html_content);
    $html_content = str_replace('Rp 4.200.000', '<?= \'Rp \' . number_format($summary[\'pemasukan\'], 0, \',\', \'.\') ?>', $html_content);
    $html_content = str_replace('Rp 2.150.000', '<?= \'Rp \' . number_format($summary[\'pengeluaran\'], 0, \',\', \'.\') ?>', $html_content);
    $html_content = str_replace('Rp 1.050.000', '<?= \'Rp \' . number_format($summary[\'sisaAnggaran\'], 0, \',\', \'.\') ?>', $html_content);
    $html_content = str_replace('48%', '<?= (100 - $expensePct) ?>%', $html_content);

    $html_content = str_replace('href="#" data-path="dashboard"', 'href="dashboard.php"', $html_content);
    $html_content = str_replace('href="#" data-path="transaksi"', 'href="transaksi.php"', $html_content);
    $html_content = str_replace('href="#" data-path="tambah-transaksi"', 'href="form-transaksi.php"', $html_content);
    $html_content = str_replace('href="#" data-path="anggaran"', 'href="anggaran.php"', $html_content);
    $html_content = str_replace('href="#" data-path="laporan"', 'href="laporan.php"', $html_content);

    $html_content = str_replace('data-path="dashboard" href="#"', 'href="dashboard.php"', $html_content);
    $html_content = str_replace('data-path="transaksi" href="#"', 'href="transaksi.php"', $html_content);
    $html_content = str_replace('data-path="tambah-transaksi" href="#"', 'href="form-transaksi.php"', $html_content);
    $html_content = str_replace('data-path="anggaran" href="#"', 'href="anggaran.php"', $html_content);
    $html_content = str_replace('data-path="laporan" href="#"', 'href="laporan.php"', $html_content);
    
    // Inject PHP Header
    $final_content = $php_header . "\n" . $html_content;

    $temp_path = $pages_dir . '/' . $php_file;
    file_put_contents($temp_path, $final_content);
    
    echo "Generated $php_file\n";
}

