const fs = require('fs');

let php = fs.readFileSync('pages/pengaturan.php', 'utf8');

php = php.replace(/<p class="callout__title">Milestone 2<\/p>/g, '<p class="callout__title">Penyimpanan Terpusat</p>');
php = php.replace(/<p class="callout__text">Penyimpanan permanen, sinkronisasi antar perangkat, dan budgeting kolaboratif akan hadir pada Milestone 3\.<\/p>/g, '<p class="callout__text">Semua data Anda tersimpan di server dengan aman dan dapat disinkronisasi.</p>');
php = php.replace(/<span class="detail-list__val">Milestone 2 — Frontend<\/span>/g, '<span class="detail-list__val">Versi 1.0 (Stabil)</span>');
php = php.replace(/<span class="detail-list__val">Milestone 2 \?\" Frontend<\/span>/g, '<span class="detail-list__val">Versi 1.0 (Stabil)</span>');

// Specifically replacing the corrupted character just in case
php = php.replace(/Milestone 2 .* Frontend/g, 'Versi 1.0 (Stabil)');

fs.writeFileSync('pages/pengaturan.php', php, 'utf8');
console.log('Fixed milestone in pengaturan.php');
