# Rencana Refactoring FinanceTrack

## Analisis Project Saat Ini

### Aset yang Tersedia (dari Google Stitch Export)
- **10 file code.html** (5 halaman × desktop + mobile):
  - Dashboard (desktop & mobile)
  - Semua Transaksi (desktop & mobile)
  - Tambah Transaksi (desktop & mobile)
  - Anggaran (desktop & mobile)
  - Laporan (desktop & mobile)
- **Logo**: SVG dari `logo_financetrack/code.html`
- **Design System**: `modern_fintech_wireframe/DESIGN.md` - lengkap dengan warna, tipografi, spacing, komponen
- **Dokumen Perancangan**: `docs/PERANCANGAN_FinanceTrack.md`
- **Gambar Referensi**: 5 PNG di `assets/img/stitch/`

### Struktur Target
```
FinanceTrack/
├── index.html (redirect ke dashboard.html)
├── pages/
│   ├── dashboard.html
│   ├── transaksi.html
│   ├── form-transaksi.html
│   ├── anggaran.html
│   └── laporan.html
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── img/
│       ├── logo/
│       └── stitch/
├── docs/
│   └── PERANCANGAN.md
└── README.md
```

## Tahap Refactoring

### 1. Persiapan Struktur & Design System
- [x] Buat folder `pages/`, `assets/css/`, `assets/js/`, `assets/img/logo/`
- [x] Pindah logo SVG ke `assets/img/logo/`
- [x] Buat `assets/css/style.css` dengan CSS variables dari DESIGN.md
- [x] Buat `assets/js/script.js` untuk shared functionality

### 2. Base Layout & Navigation
- [x] Buat template HTML dasar dengan:
  - Sidebar desktop (fixed, 260px)
  - Header mobile (fixed top, dengan hamburger)
  - Bottom navigation mobile (fixed bottom)
  - Off-canvas sidebar untuk mobile
  - Active state navigation

### 3. Halaman-halaman (Prioritas: Desktop First, lalu Mobile Responsive)
1. **Dashboard** - Ringkasan saldo, grafik tren, transaksi terbaru, budget progress, kategori pengeluaran, tips
2. **Semua Transaksi** - Tabel dengan search, filter, pagination, aksi edit/hapus
3. **Tambah Transaksi** - Form lengkap dengan preview real-time
4. **Anggaran** - Total budget, per kategori dengan progress bar, status, form tambah budget
5. **Laporan** - Income vs expense chart, kategori, rasio tabungan, ringkasan bulanan

### 4. JavaScript Interactions
- Sidebar toggle (mobile hamburger + off-canvas)
- Active navigation highlighting
- Chart rendering (SVG-based, no Chart.js dependency untuk simplicity)
- Form interactions (tambah transaksi preview, quick amounts)
- Table filtering/search/pagination
- Modal confirmations

### 5. Quality Assurance
- [ ] Semua link antar halaman berfungsi
- [ ] Responsive di 3 breakpoint: mobile (<768px), tablet (768-1024px), desktop (>1024px)
- [ ] Tidak ada horizontal overflow
- [ ] CSS konsisten, tidak duplikat
- [ ] HTML semantic
- [ ] Path asset benar

## Design Tokens (dari DESIGN.md)

### Colors
```css
--primary: #2563EB;
--secondary: #0D9488;   /* Success/Income */
--tertiary: #E11D48;    /* Danger/Expense */
--background: #F8FAFC;
--surface: #FFFFFF;
--surface-container: #F1F5F9;
--surface-container-high: #E2E8F0;
--text: #0F172A;
--text-secondary: #64748B;
--outline: #E2E8F0;
--outline-variant: #CBD5E1;
```

### Spacing
```css
--space-xs: 0.25rem;
--space-sm: 0.5rem;
--space-md: 1rem;
--space-lg: 1.5rem;
--space-xl: 2rem;
--gutter: 1rem;
--gutter-desktop: 1.5rem;
--margin: 1rem;
--margin-tablet: 1.5rem;
--margin-desktop: 2rem;
```

### Border Radius
```css
--radius-sm: 0.25rem;
--radius-md: 0.375rem;
--radius-lg: 0.5rem;
--radius-xl: 0.75rem;
--radius-full: 9999px;
```

### Typography
- Font: Inter (Google Fonts)
- Display LG: 3rem/700
- Headline LG: 2rem/600
- Headline MD: 1.25rem/600
- Headline SM: 1rem/600
- Body MD: 0.875rem/400
- Body SM: 0.75rem/400
- Numeric LG: 1.75rem/600
- Numeric MD: 1rem/500
- Label MD: 0.875rem/500
- Label SM: 0.75rem/600 (uppercase, tracking-wide)

## Breakpoints
- Mobile: < 768px
- Tablet: 768px - 1023px
- Desktop: ≥ 1024px

## Catatan Penting
- Gunakan Tailwind CSS via CDN hanya untuk prototyping cepat, tapi target akhir: custom CSS
- Chart: gunakan SVG inline (seperti Stitch export) untuk menghindari dependency Chart.js
- Icons: Material Symbols Outlined (Google Fonts)
- Mock data: hardcode di HTML/JS, tidak pakai backend
- Mata uang: Rupiah (Rp) dengan format Indonesia (titik sebagai pemisah ribuan)

---

# Status Akhir (selesai)

## Struktur Realisasi
```
FinanceTrack/
├── index.html                 # landing page + kartu shortcuts
├── pages/
│   ├── dashboard.html         # ringkasan saldo, tren, komposisi, transaksi terbaru, budget
│   ├── transaksi.html         # tabel + card feed, search/filter/sort/pagination, edit/hapus
│   ├── form-transaksi.html    # form + live preview + batas anggaran
│   ├── anggaran.html          # total budget, kartu per kategori, form set batas
│   ├── laporan.html           # arus kas harian, donut, rekap 6 bulan, skor kesehatan
│   └── pengaturan.html        # profil, preferensi, kategori, metode, kelola data
├── assets/
│   ├── css/style.css          # 2392 baris, design tokens + komponen responsif
│   ├── js/data.js             # single source of truth mock data
│   ├── js/script.js           # Utils/Navigation/Feedback/TransactionTable/TransactionForm/BudgetUI/Charts
│   └── img/{logo,stitch}/
└── docs/PERANCANGAN_FinanceTrack.md
```

Semua file export Google Stitch di `stitch_financetrack_app_wireframes/` **dipertahankan** sebagai referensi.

## Keputusan yang Berbeda dari Rencana Awal
- **Chart memakai Chart.js 4.4.1 (CDN), bukan SVG inline.** Alasannya: 4 chart (tren line, donut, bar 6 bulan, arus kas harian) lebih konsisten &accessible dengan canvas, dan label/tooltip otomatis. Halaman tetap berfungsi bila CDN gagal (guard `typeof Chart === "undefined"`).
- **`index.html` adalah landing page,** bukan redirect, memakai komponen `.landing__*` yang sudah disediakan style.css.
- **`data.js` dipisah dari `script.js`** agar mock data jadi satu sumber kebenaran yang mudah diganti response API di Milestone 3.
- **`docs/PERANCANGAN.md` tidak dibuat terpisah**; dokumen sumber `docs/PERANCANGAN_FinanceTrack.md` dipertahankan apa adanya.

## Bug yang Ditemukan & Diperbaiki Saat QA
1. **Urutan inisialisasi tabel** — `script.js` mendaftarkan listener `DOMContentLoaded` lebih dulu, sehingga `TransactionTable.init()` berjalan *sebelum* inline script merender `<tr>`. Akibatnya `rows = []`: search/filter/pagination mati. Diperbaiki dengan mengekspos `window.FinanceTrack` secara sinkron + `refreshRows()` (idempoten, `_listenersBound`).
2. **Card feed mobile tidak ikut ter-filter** — kini `render()` berlaku untuk `<tr data-id>` dan `[data-feed-id]`, dan `handleDelete()` menghapus keduanya.
3. **Warna chart `var(--primary)`** — canvas tidak bisa resolve CSS custom property. Diganti hex di `data.js`.
4. **Data tidak konsisten** — `transactions` adalah *cuplikan* (total Okt 1.579.000) dan tidak sama dengan total bulanan (2.150.000) yang dipakai Dashboard/Anggaran/donut. Rekap Laporan kini diturunkan dari `budgets` lewat `monthlyCategoryReport()` sehingga semua halaman konsisten di 2.150.000.
5. **`?jenis=income` rapuh** — bergantung pada urutan registrasi listener lewat `.click()` sintetis. Sekarang `TransactionForm.applyTypeFromQuery()` menanganinya di dalam `init()`.
6. **Dashboard pakai JS `matchMedia` untuk layout** — diganti `.table--responsive` / `.table--responsive-mobile` (murni CSS, tetap benar tanpa JS).
7. **Kelas progress bar ganda** — `anggaran.html` dan `BudgetUI` sama-sama mengatur modifier. Sekarang `BudgetUI` satu-satunya pemilik (eksklusif via `classList.remove`).
8. **`data-chart-monthly` tidak terpakai** — sebelumnya dead code; kini dipakai chart Rekap 6 Bulan di Laporan.
9. Saldo di Laporan tertinggal `Rp 3.400.000`; dikoreksi ke `Rp 3.450.000` sesuai `summary.saldo`.

## Hasil QA
- `node --check` lulus untuk `data.js`, `script.js`, dan 6 inline script halaman.
- Kurung kurawal CSS seimbang (389/389).
- Semua `href`/`src` lokal resolve; 5 link nav + 1 active konsisten di semua halaman.
- Smoke test jsdom (transaksi, dashboard, laporan, anggaran, form, pengaturan): **0 error**, table/feed sinkron (38 baris, 8/halaman), search "kopi" → 5, 3 chart Laporan render, rekap = 2.150.000, `?jenis=income` → mode Pemasukan, validasi form tolak kosong & terima isi.
- Scan karakter non-Latin (CJK/Hangul/Cyrillic): bersih.

## Belum Dilakukan (di luar Milestone 2)
- Perenderan visual nyata di browser pada 3 breakpoint (butuh browser/Playwright).
- Penyimpanan permanen (localStorage/backend), ekspor PDF/XLSX nyata, unggah foto profil.
