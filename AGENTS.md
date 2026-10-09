# FinanceTrack — Agent Instructions

## PROJECT
FinanceTrack is a university Web Programming project.

Stack:
- PHP
- MySQL
- HTML5
- CSS3
- Vanilla JavaScript
- Bootstrap 5
- Chart.js
- XAMPP

Environment:
- Source: O:\Pemrograman Web\FinanceTrack
- XAMPP: O:\xampp
- URL: http://localhost/FinanceTrack/

## CRITICAL RULES

1. DO NOT redesign the application unless explicitly requested.

2. Stitch/Figma is the visual source of truth.
   Existing UI must be preserved unless the user explicitly asks for a redesign.

3. NEVER delete, hide, simplify, or replace existing content just to fix a layout problem.

4. Before modifying UI:
   - inspect the current PHP/HTML
   - inspect the corresponding Stitch implementation
   - inspect relevant CSS
   - identify the exact difference
   - then make the smallest possible fix

5. Do not globally override Bootstrap components such as:
   - .row
   - .container
   - .card
   - .btn
   - .modal
   - .navbar

6. Prefer:
   - CSS Grid
   - Flexbox
   - existing project classes

   Avoid unnecessary:
   - absolute positioning
   - negative margins
   - overflow:hidden as a visual workaround

7. FinanceTrack uses LIGHT Glassmorphism.
   Do not turn the UI into a dark/neon dashboard.

8. Never hardcode dynamic:
   - balance
   - income
   - expense
   - transaction totals
   - dates
   - years
   - chart datasets

9. Data flow must remain:

   MySQL
      ↓
   PHP
      ↓
   bootstrap.php
      ↓
   window.__FT_BOOTSTRAP__
      ↓
   FinanceTrackData
      ↓
   DOM / Chart.js

10. Never silently modify, migrate, normalize, or delete database data unless explicitly requested.

11. Do not add test frameworks, package managers, build systems, or unnecessary dependencies.

12. Make SMALL, TARGETED changes.

13. Do not continue to another task after the requested task is complete.

## VERIFICATION

PHP syntax passing is NOT proof that a UI task is complete.

The following are NOT sufficient:
- PHP syntax check
- JS syntax check
- grep
- HTTP 200
- database query success

For visual tasks, rendered browser verification is required.

For data-sync tasks, verify:

MySQL → PHP → bootstrap → JavaScript → rendered UI

If a layer has not been verified, report:

NOT VERIFIED

Do not claim "fixed", "complete", or "identical to Stitch" without actual verification.

## RESPONSIVE TARGETS

Check when relevant:

- 1920px
- 1440px
- 1366px
- 1024px
- 768px
- 480px
- 375px
- 360px

## FINAL REPORT

Always report:

1. Root cause
2. Files changed
3. Exact fix
4. Verification performed
5. Remaining issues
6. What was NOT verified

Do not claim success beyond the evidence.