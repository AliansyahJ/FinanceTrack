const fs = require('fs');
const path = require('path');

const pagesDir = path.join(__dirname, 'pages');
const files = fs.readdirSync(pagesDir).filter(f => f.endsWith('.php'));

let changed = 0;
for (const file of files) {
    const filePath = path.join(pagesDir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    // Remove .app__header block
    // Pattern: <header class="app__header"> ... </header>
    const headerRegex = /<!-- Mobile header -->\s*<header class="app__header">[\s\S]*?<\/header>/g;
    const headerRegex2 = /<header class="app__header">[\s\S]*?<\/header>/g;
    content = content.replace(headerRegex, '');
    content = content.replace(headerRegex2, '');
    
    // Remove .sidebar__logout-mobile
    const logoutRegex = /<li class="sidebar__logout-mobile">[\s\S]*?<\/li>/g;
    content = content.replace(logoutRegex, '');

    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Processed ${file}`);
    changed++;
}
console.log(`Total changed: ${changed}`);
