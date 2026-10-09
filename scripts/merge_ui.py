import os
import re

# Mapping of page names to their stitch folders
pages = {
    'dashboard.php': 'dashboard_financetrack',
    'transaksi.php': 'semua_transaksi',
    'form-transaksi.php': 'tambah_transaksi',
    'anggaran.php': 'anggaran',
    'laporan.php': 'laporan_keuangan'
}

base_dir = r"O:\Pemrograman Web\FinanceTrack"
pages_dir = os.path.join(base_dir, 'pages')
stitch_dir = os.path.join(base_dir, 'stitch_financetrack_app_wireframes')

for php_file, stitch_folder in pages.items():
    php_path = os.path.join(pages_dir, php_file)
    html_path = os.path.join(stitch_dir, stitch_folder, 'code.html')
    
    if not os.path.exists(php_path) or not os.path.exists(html_path):
        print(f"Skipping {php_file} - files not found")
        continue

    # Extract PHP header block from original file
    with open(php_path, 'r', encoding='utf-8') as f:
        php_content = f.read()
    
    match = re.search(r'^(<\?php.*?\?>)\s*<!DOCTYPE html>', php_content, re.DOTALL)
    if match:
        php_header = match.group(1)
    else:
        print(f"No PHP header found in {php_file}")
        continue

    # Read Stitch HTML
    with open(html_path, 'r', encoding='utf-8') as f:
        html_content = f.read()

    # Replacements
    html_content = html_content.replace('Budi Pratama', '<?= htmlspecialchars($userName) ?>')
    html_content = html_content.replace('Mahasiswa / Personal', '<?= htmlspecialchars($userRole) ?> / Personal')
    html_content = html_content.replace('Personal Pro', '<?= htmlspecialchars($userRole) ?> / Personal')
    
    # Summary replacements
    html_content = html_content.replace('Rp 3.450.000', '<?= \'Rp \' . number_format($summary[\'saldo\'], 0, \',\', \'.\') ?>')
    html_content = html_content.replace('Rp 4.200.000', '<?= \'Rp \' . number_format($summary[\'pemasukan\'], 0, \',\', \'.\') ?>')
    html_content = html_content.replace('Rp 2.150.000', '<?= \'Rp \' . number_format($summary[\'pengeluaran\'], 0, \',\', \'.\') ?>')
    html_content = html_content.replace('Rp 1.050.000', '<?= \'Rp \' . number_format($summary[\'sisaAnggaran\'], 0, \',\', \'.\') ?>')
    html_content = html_content.replace('48%', '<?= (100 - $expensePct) ?>%')
    
    # Active menu paths fixing
    # Stitch uses data-path="dashboard" href="#"
    html_content = html_content.replace('href="#" data-path="dashboard"', 'href="dashboard.php"')
    html_content = html_content.replace('href="#" data-path="transaksi"', 'href="transaksi.php"')
    html_content = html_content.replace('href="#" data-path="tambah-transaksi"', 'href="form-transaksi.php"')
    html_content = html_content.replace('href="#" data-path="anggaran"', 'href="anggaran.php"')
    html_content = html_content.replace('href="#" data-path="laporan"', 'href="laporan.php"')
    html_content = html_content.replace('data-path="dashboard" href="#"', 'href="dashboard.php"')
    html_content = html_content.replace('data-path="transaksi" href="#"', 'href="transaksi.php"')
    html_content = html_content.replace('data-path="tambah-transaksi" href="#"', 'href="form-transaksi.php"')
    html_content = html_content.replace('data-path="anggaran" href="#"', 'href="anggaran.php"')
    html_content = html_content.replace('data-path="laporan" href="#"', 'href="laporan.php"')

    # Inject PHP Header
    final_content = php_header + "\n" + html_content

    # Write to a temp file for inspection
    temp_path = os.path.join(pages_dir, php_file + '.new')
    with open(temp_path, 'w', encoding='utf-8') as f:
        f.write(final_content)
    
    print(f"Generated {php_file}.new")

