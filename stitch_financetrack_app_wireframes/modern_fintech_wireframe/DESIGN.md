---
name: Modern Fintech Wireframe
colors:
  surface: '#faf8ff'
  surface-dim: '#d2d9f4'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#eaedff'
  surface-container-high: '#e2e7ff'
  surface-container-highest: '#dae2fd'
  on-surface: '#131b2e'
  on-surface-variant: '#434655'
  inverse-surface: '#283044'
  inverse-on-surface: '#eef0ff'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#006a61'
  on-secondary: '#ffffff'
  secondary-container: '#86f2e4'
  on-secondary-container: '#006f66'
  tertiary: '#ac0031'
  on-tertiary: '#ffffff'
  tertiary-container: '#d71142'
  on-tertiary-container: '#ffecec'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#89f5e7'
  secondary-fixed-dim: '#6bd8cb'
  on-secondary-fixed: '#00201d'
  on-secondary-fixed-variant: '#005049'
  tertiary-fixed: '#ffdada'
  tertiary-fixed-dim: '#ffb3b6'
  on-tertiary-fixed: '#40000c'
  on-tertiary-fixed-variant: '#920028'
  background: '#faf8ff'
  on-background: '#131b2e'
  surface-variant: '#dae2fd'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 3rem
    fontWeight: '700'
    lineHeight: 3.5rem
    letterSpacing: -0.025em
  display-lg-mobile:
    fontFamily: Inter
    fontSize: 2.25rem
    fontWeight: '700'
    lineHeight: 2.75rem
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 2rem
    fontWeight: '600'
    lineHeight: 2.5rem
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 1.5rem
    fontWeight: '600'
    lineHeight: 2rem
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Inter
    fontSize: 1.25rem
    fontWeight: '600'
    lineHeight: 1.75rem
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Inter
    fontSize: 1rem
    fontWeight: '600'
    lineHeight: 1.5rem
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 1.125rem
    fontWeight: '400'
    lineHeight: 1.75rem
  body-md:
    fontFamily: Inter
    fontSize: 0.875rem
    fontWeight: '400'
    lineHeight: 1.375rem
  body-sm:
    fontFamily: Inter
    fontSize: 0.75rem
    fontWeight: '400'
    lineHeight: 1.125rem
  numeric-lg:
    fontFamily: Inter
    fontSize: 1.75rem
    fontWeight: '600'
    lineHeight: 2rem
    letterSpacing: -0.02em
  numeric-md:
    fontFamily: Inter
    fontSize: 1rem
    fontWeight: '500'
    lineHeight: 1.5rem
    letterSpacing: -0.01em
  label-md:
    fontFamily: Inter
    fontSize: 0.875rem
    fontWeight: '500'
    lineHeight: 1.25rem
  label-sm:
    fontFamily: Inter
    fontSize: 0.75rem
    fontWeight: '600'
    lineHeight: 1rem
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-desktop: 1.5rem
  margin: 1rem
  margin-tablet: 1.5rem
  margin-desktop: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

This design system targets modern retail investors, SMEs, and financial operators who require precision, clarity, and rapid cognitive scanning across complex balance sheets, cash flow models, and expense tracking. The aesthetic balances institutional reliability with contemporary SaaS agility: high-density information architecture paired with crisp, razor-thin borders, disciplined structural alignment, and subtle tonal separation.

The visual direction follows a **Modern Corporate & Clean Wireframe** aesthetic. It prioritizes structure over ornamentation, using purposeful contrast to establish hierarchy. The interface communicates institutional rigor, eliminating visual ambiguity through structured numeric displays and distinct semantic indicators for fiscal health.

## Colors

The color palette is built around high-contrast legibility and functional semantic signals:

- **Primary (`#2563EB`)**: Royal Blue serves as the main interactive anchor, driving key calls-to-action, active table filters, navigational active states, and focused interactive elements.
- **Secondary (`#0D9488`)**: Deep Teal serves as the affirmative indicator. It represents cash inflows, credit balances, budget surpluses, positive growth vectors, and successful audits.
- **Tertiary (`#E11D48`)**: Deep Rose represents outflows, debit variances, over-budget alerts, and critical anomalies. It maintains visual weight without producing visual fatigue.
- **Neutral (`#0F172A`)**: Deep Navy Slate establishes foundational typographic contrast, structural icons, and deep container accents.
- **Surfaces & Borders**: Primary canvas backgrounds default to `#F8FAFC`, stepping to `#FFFFFF` for data-bearing cards and modals, and `#F1F5F9` for secondary data wells or table headers. Structural separation relies on `#E2E8F0` for hair-thin containment grids.

## Typography

Inter provides crisp rendering across high-density layouts. Financial figures must strictly leverage tabular lining figures (`font-variant-numeric: tabular-nums` or `tnum`) to ensure vertical column alignment across ledgers, transaction tables, and metric arrays. 

All category indicators, transaction states, and ledger metadata utilize uppercase styling for `label-sm` with widened letter-spacing to distinguish supporting metadata from core transactional figures.

## Layout & Spacing

The layout model employs a responsive, fluid 12-column grid system calibrated for data-heavy dashboard architectures:

- **Desktop (≥ 1280px)**: 12 columns with `margin-desktop` (2rem) and `gutter-desktop` (1.5rem). The navigation exists as a fixed 260px vertical sidebar, with remaining dashboard space fluidly distributing across grid panels.
- **Tablet (768px – 1279px)**: 8 columns with `margin-tablet` (1.5rem) and `gutter` (1rem). The sidebar collapses into an icon-rail (72px) or off-canvas drawer. Metric blocks fold from 4 units per row to 2 units per row.
- **Mobile (< 768px)**: 4 columns with `margin` (1rem) and `gutter` (1rem). Ledger tables transition to stacked record lists. Chart representations prioritize 100% container width with horizontal scroll affordances for extensive timelines.

## Elevation & Depth

This design system avoids heavy shadows, using a combination of **low-contrast outlines** and **subtle tonal layering** to maintain a crisp wireframe precision:

- **Flat Outlines**: Surfaces are delineated by single-pixel boundaries (`#E2E8F0`). Cards, modal dialogs, and table shells rely on physical perimeter lines rather than heavy drop-shadows.
- **Surface Layering**: The primary foundation rests at `#F8FAFC`. Elevated interactive panels and working data surfaces lift via pure white (`#FFFFFF`). Subordinate containers (e.g., metric sub-panels, ledger headers, and empty states) settle into `#F1F5F9`.
- **Soft Ambient Accents**: When elevation is required for floating navigation elements, dropdown panels, or contextual transaction menus, use a crisp ambient shadow: `0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.05)`. Floating modals step up slightly to `0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04)`.

## Shapes

The design system relies on a **Soft** geometry scale (`roundedness: 1`). This provides clean, architectural lines that maintain a wireframe-inspired aesthetic while avoiding harsh 90-degree corners.

- **Base Radius (`0.25rem` / 4px)**: Input controls, checkbox shells, secondary badges, and micro-metric indicators.
- **Large Radius (`0.5rem` / 8px)**: Cards, data tables, modals, action sheets, and chart frame containers.
- **Extra Large Radius (`0.75rem` / 12px)**: Outer structural viewports and full-screen modular envelopes.

## Components

### Buttons
- **Primary**: Solid `#2563EB` fill with `#FFFFFF` text. Focus state provides a 2px ring offset with `#2563EB` at 20% opacity. Corner radius is 4px.
- **Secondary / Outline**: Clean `#FFFFFF` surface with `#E2E8F0` border and `#0F172A` text. On hover, background shifts to `#F8FAFC` with border darkening to `#CBD5E1`.
- **Destructive**: Low-saturation rose background (`#FFE4E6`) with bold tertiary text (`#E11D48`). Active states transition to solid `#E11D48` with `#FFFFFF` text.

### Chips & Badges
- Used for transaction categorization (e.g., *Operasional*, *Pemasukan*, *Investasi*).
- Height fixed to 22px, padding `0.25rem 0.5rem`, `label-sm` font style.
- Positive status (*Selesai* / *Kredit*): `#F0FDFA` background, `#0D9488` text, `#CCFBF1` border.
- Expense/Alert status (*Tertunda* / *Debit*): `#FFF1F2` background, `#E11D48` text, `#FFE4E6` border.

### Input Fields & Controls
- Form inputs feature a 1px `#E2E8F0` border, `#FFFFFF` background, and 4px radius. Focus states transition the border directly to `#2563EB` with a crisp 1px ambient outline.
- Checkboxes and radios use a 1px slate frame with a `#2563EB` fill upon selection.
- Number inputs for Indonesian Rupiah formatting include a fixed, non-editable prefix (`Rp`) styled in `#64748B` with tabular alignment.

### Cards & Ledger Tables
- Cards use a `#FFFFFF` fill with a continuous 1px `#E2E8F0` border. Headers separate from card bodies using a 1px border line rather than empty space.
- Tables employ `#F1F5F9` row headers with uppercase `label-sm` text in `#475569`. Each transaction row uses a 1px `#F1F5F9` bottom divider, highlighting on hover with `#F8FAFC`. Numerical amounts are right-aligned using tabular figures.

### Progress Bars & Charts
- Budget progress tracks use a 6px height with `#E2E8F0` track fill and 4px radius. Fill colors shift semantically based on budget consumption: `#0D9488` under 80%, `#EAB308` between 80-99%, and `#E11D48` at 100% or greater.
- Charts feature minimal grid lines (`#E2E8F0` dashed), no background gradients, and precise 2px stroke lines using `#2563EB` and `#0D9488`.