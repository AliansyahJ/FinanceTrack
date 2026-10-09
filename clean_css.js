const fs = require('fs');

let css = fs.readFileSync('assets/css/responsive.css', 'utf8');

css = css.replace(/\.sidebar__logout-mobile \{ display: none; \}/g, '');
css = css.replace(/@media \(max-width: 1024px\) \{\s*\.sidebar__logout-mobile \{ display: block; border-top: 1px solid var\(--surface-container\); margin-top: auto; padding-top: 0\.5rem; \}\s*\}/g, '');

css = css.replace(/\.app__header \{\s*display: flex !important;\s*\}/g, '');
css = css.replace(/\.app__topbar \{\s*display: none !important;\s*\}/g, `.app__topbar {
    padding: 0 var(--space-md);
    gap: var(--space-sm);
  }
  .app__topbar .input-group {
    flex: 0 1 100%;
  }
  .app__topbar .input-group input {
    font-size: 16px; /* prevent iOS zoom */
  }
  .app__topbar .topbar__user-menu span:not(.sidebar__avatar):not(.material-symbols-outlined) {
    display: none; /* hide username text on very small screens to save space */
  }
`);

css = css.replace(/@media \(min-width: 1025px\) \{\s*\.app__header \{\s*display: none !important;\s*\}/g, '@media (min-width: 1025px) {');

fs.writeFileSync('assets/css/responsive.css', css, 'utf8');
console.log("Updated responsive.css");
