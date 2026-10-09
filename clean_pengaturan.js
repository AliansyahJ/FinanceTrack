const fs = require('fs');

let php = fs.readFileSync('pages/pengaturan.php', 'utf8');

// Fix double PHP block
php = php.replace(/^<\?php\s+require_once __DIR__ \. '\/\.\.\/auth\/middleware\.php';\s+require_once __DIR__ \. '\/\.\.\/database\/bootstrap\.php';\s+requireAuth\(\);\s+\$currentUser = getCurrentUser\(\);\s+\$userName = htmlspecialchars\(\$currentUser\['nama'\] \?\? 'Fikri Juana'\);\s+\$userRole = htmlspecialchars\(\$currentUser\['role'\] \?\? 'Mahasiswa'\);\s+\$userAvatar = htmlspecialchars\(\$currentUser\['avatar_initials'\] \?\? 'FP'\);\s+\$userEmail = htmlspecialchars\(\$currentUser\['email'\] \?\? 'demo@financetrack\.test'\);\s+\$bootstrapData = getDatabaseBootstrapData\(\(int\)\$currentUser\['id'\]\);\s+\?>\n/m, '');

// Add created_at
php = php.replace(/\$userEmail = htmlspecialchars\(\$currentUser\['email'\] \?\? 'demo@financetrack\.test'\);/, `$userEmail = htmlspecialchars($currentUser['email'] ?? 'demo@financetrack.test');\n$userCreatedAt = htmlspecialchars($currentUser['created_at'] ?? '');`);

// Fix user name (it was looking for 'nama' but column is 'name')
php = php.replace(/\$userName = htmlspecialchars\(\$currentUser\['nama'\] \?\? 'Fikri Juana'\);/, `$userName = htmlspecialchars($currentUser['name'] ?? $currentUser['nama'] ?? 'Fikri Juana');`);

// Remove milestone text
php = php.replace(/<p class="page-subtitle" style="margin-bottom:var\(--space-lg\)">\s*Data ini hanya tersimpan di perangkatmu pada Milestone 2\.\s*<\/p>/, '');

// Update role field to non-editable label
php = php.replace(/<div class="field">\s*<label class="field__label" for="set-role">Peran<\/label>\s*<select class="select" id="set-role" name="peran" disabled>[\s\S]*?<\/select>\s*<\/div>/, 
`<div class="field">
                  <label class="field__label" for="set-role">Peran</label>
                  <input type="text" class="input" id="set-role" name="peran" value="<?= ucfirst($userRole) ?>" disabled style="background-color: var(--surface-alt); cursor: not-allowed; color: var(--text-secondary);">
                </div>
                <div class="field">
                  <label class="field__label" for="set-created">Tanggal Bergabung</label>
                  <input type="text" class="input" id="set-created" name="created" value="<?= $userCreatedAt ? date('d M Y', strtotime($userCreatedAt)) : 'Tidak diketahui' ?>" disabled style="background-color: var(--surface-alt); cursor: not-allowed; color: var(--text-secondary);">
                </div>`);

// Fix strange characters
php = php.replace(/IDR A\\ Rp/, 'IDR &mdash; Rp');
php = php.replace(/IDR A Rp/, 'IDR &mdash; Rp');

fs.writeFileSync('pages/pengaturan.php', php, 'utf8');
console.log('pengaturan.php updated');
