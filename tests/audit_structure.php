<?php
$pages = ['dashboard', 'transaksi', 'form-transaksi', 'anggaran', 'laporan', 'pengaturan'];
$hasErrors = false;

foreach ($pages as $p) {
    $htmlFile = __DIR__ . '/../pages/' . $p . '.html';
    $phpFile = __DIR__ . '/../pages/' . $p . '.php';
    if (!file_exists($htmlFile) || !file_exists($phpFile)) {
        echo "Missing file for {$p}\n";
        $hasErrors = true;
        continue;
    }
    $html = file_get_contents($htmlFile);
    $php = file_get_contents($phpFile);

    // Strip PHP code
    $phpClean = preg_replace('/<\?php.*?\?>/s', '', $php);
    $phpClean = preg_replace('/<\?=.*?\?>/s', '', $phpClean);

    // Compare IDs
    preg_match_all('/id="([^"]+)"/', $html, $mHtmlIds);
    preg_match_all('/id="([^"]+)"/', $phpClean, $mPhpIds);
    $missingIds = array_diff($mHtmlIds[1], $mPhpIds[1]);

    // Compare data-attributes
    preg_match_all('/data-[a-z0-9\-]+/i', $html, $mHtmlData);
    preg_match_all('/data-[a-z0-9\-]+/i', $phpClean, $mPhpData);
    $missingData = array_diff(array_unique($mHtmlData[0]), array_unique($mPhpData[0]));

    // Compare headings
    preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $html, $mHtmlHeadings);
    preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $phpClean, $mPhpHeadings);
    
    // Clean headings text
    $hHtmlTexts = array_map(function($h) { return trim(strip_tags($h)); }, $mHtmlHeadings[1]);
    $hPhpTexts = array_map(function($h) { return trim(strip_tags($h)); }, $mPhpHeadings[1]);
    $missingHeadings = array_diff($hHtmlTexts, $hPhpTexts);

    // Compare major sections / cards
    preg_match_all('/<section[^>]*>/i', $html, $mHtmlSec);
    preg_match_all('/<section[^>]*>/i', $phpClean, $mPhpSec);

    echo "=== PAGE: {$p} ===\n";
    echo "  HTML sections: " . count($mHtmlSec[0]) . " | PHP sections: " . count($mPhpSec[0]) . "\n";
    echo "  HTML IDs: " . count($mHtmlIds[1]) . " | PHP IDs: " . count($mPhpIds[1]) . "\n";
    if (!empty($missingIds)) {
        echo "  MISSING IDs in PHP: " . implode(', ', $missingIds) . "\n";
        $hasErrors = true;
    }
    if (!empty($missingData)) {
        echo "  MISSING data-attrs in PHP: " . implode(', ', $missingData) . "\n";
        $hasErrors = true;
    }
    if (!empty($missingHeadings)) {
        echo "  MISSING headings in PHP: " . implode(' | ', $missingHeadings) . "\n";
        $hasErrors = true;
    }
    if (empty($missingIds) && empty($missingData) && empty($missingHeadings)) {
        echo "  -> Structural match: 100% OK\n";
    }
    echo "\n";
}

if (!$hasErrors) {
    echo "ALL PAGES MATCH STRUCTURALLY!\n";
} else {
    echo "SOME DIFFERENCES DETECTED!\n";
}

