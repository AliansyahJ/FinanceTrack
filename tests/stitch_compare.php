<?php
$stitchPages = [
    'dashboard' => 'stitch_financetrack_app_wireframes/dashboard_financetrack/code.html',
    'transaksi' => 'stitch_financetrack_app_wireframes/semua_transaksi/code.html',
    'form-transaksi' => 'stitch_financetrack_app_wireframes/tambah_transaksi/code.html',
    'anggaran' => 'stitch_financetrack_app_wireframes/anggaran/code.html',
    'laporan' => 'stitch_financetrack_app_wireframes/laporan_keuangan/code.html',
];

foreach ($stitchPages as $key => $stitchPath) {
    echo "=== CHECKING STITCH VS PHP: {$key} ===\n";
    if (!file_exists($stitchPath)) {
        echo "Stitch path not found: {$stitchPath}\n";
        continue;
    }
    $stitchHtml = file_get_contents($stitchPath);
    $phpHtml = file_get_contents('pages/' . $key . '.php');

    // Extract headings from stitch
    preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $stitchHtml, $mStitchH);
    preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $phpHtml, $mPhpH);

    $stitchHeadings = array_values(array_filter(array_map(function($h) { return trim(preg_replace('/\s+/', ' ', strip_tags($h))); }, $mStitchH[1])));
    $phpHeadings = array_values(array_filter(array_map(function($h) { return trim(preg_replace('/\s+/', ' ', strip_tags($h))); }, $mPhpH[1])));

    echo "Stitch headings (" . count($stitchHeadings) . "):\n  " . implode(' | ', $stitchHeadings) . "\n";
    echo "PHP headings (" . count($phpHeadings) . "):\n  " . implode(' | ', $phpHeadings) . "\n\n";
}

