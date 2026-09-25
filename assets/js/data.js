/* ==========================================================================
   FinanceTrack — Mock Data
   --------------------------------------------------------------------------
   Single source of truth untuk seluruh data demo.
   Struktur ini sengaja dibuat mirrors ERD di docs/PERANCANGAN.md sehingga
   pada Milestone 3 data ini tinggal diganti dengan response API tanpa
   mengubah halaman HTML.

   ERD terkait:
     CATEGORIES  { id, nama, tipe, icon }
     TRANSACTIONS{ id, tanggal, jam, deskripsi, kategoriId, metode, tipe, jumlah }
     BUDGETS     { kategoriId, batas, periode }
   ========================================================================== */

const FinanceTrackData = (() => {
  "use strict";

  /* ----------------------------------------------------------------------
     Master data
     ---------------------------------------------------------------------- */

  const categories = [
    { id: "pemasukan-saku", nama: "Uang Saku", tipe: "income", icon: "payments" },
    { id: "pemasukan-freelance", nama: "Freelance", tipe: "income", icon: "work" },
    { id: "pemasukan-beasiswa", nama: "Beasiswa", tipe: "income", icon: "school" },
    { id: "makanan", nama: "Makanan", tipe: "expense", icon: "restaurant" },
    { id: "transportasi", nama: "Transportasi", tipe: "expense", icon: "two_wheeler" },
    { id: "internet", nama: "Internet", tipe: "expense", icon: "wifi" },
    { id: "listrik", nama: "Token Listrik", tipe: "expense", icon: "bolt" },
    { id: "kuliah", nama: "Kuliah & Skripsi", tipe: "expense", icon: "menu_book" },
    { id: "kopi", nama: "Kopi & Belajar", tipe: "expense", icon: "coffee" },
    { id: "alat-tulis", nama: "Alat Tulis", tipe: "expense", icon: "edit_note" },
    { id: "hiburan", nama: "Hiburan", tipe: "expense", icon: "movie" },
    { id: "tabungan", nama: "Tabungan", tipe: "income", icon: "savings" },
  ];

  const paymentMethods = [
    { id: "cash", nama: "Cash" },
    { id: "gopay", nama: "GoPay" },
    { id: "ovo", nama: "OVO" },
    { id: "dana", nama: "DANA" },
    { id: "tabungan", nama: "Tabungan" },
  ];

  /* ----------------------------------------------------------------------
     Ringkasan bulan berjalan (Oktober 2024)
     Nilai-nilai ini mengikuti spesifikasi Milestone 2.
     ---------------------------------------------------------------------- */
  const summary = {
    periode: "Oktober 2024",
    tanggal: 24,
    hariDalamBulan: 31,
    saldo: 3450000,
    pemasukan: 4200000,
    pengeluaran: 2150000,
    sisaAnggaran: 1050000,
    totalAnggaran: 3200000,
  };

  /* ----------------------------------------------------------------------
     Transaksi
     ---------------------------------------------------------------------- */
  const transactions = [
    {
      id: 1,
      tanggal: "2024-10-24",
      jam: "12:30",
      deskripsi: "Uang saku bulanan dari orang tua",
      kategoriId: "pemasukan-saku",
      metode: "tabungan",
      tipe: "income",
      jumlah: 1500000,
    },
    {
      id: 2,
      tanggal: "2024-10-24",
      jam: "08:15",
      deskripsi: "Makan siang warteg Mbak Sri",
      kategoriId: "makanan",
      metode: "cash",
      tipe: "expense",
      jumlah: 18000,
    },
    {
      id: 3,
      tanggal: "2024-10-23",
      jam: "19:30",
      deskripsi: "Kopi dan belajar di perpustakaan",
      kategoriId: "kopi",
      metode: "gopay",
      tipe: "expense",
      jumlah: 28000,
    },
    {
      id: 4,
      tanggal: "2024-10-23",
      jam: "10:05",
      deskripsi: "Fotokopi modul dan print skripsi",
      kategoriId: "kuliah",
      metode: "cash",
      tipe: "expense",
      jumlah: 75000,
    },
    {
      id: 5,
      tanggal: "2024-10-22",
      jam: "14:00",
      deskripsi: "Honor asisten lab (part-time)",
      kategoriId: "pemasukan-freelance",
      metode: "gopay",
      tipe: "income",
      jumlah: 1200000,
    },
    {
      id: 6,
      tanggal: "2024-10-22",
      jam: "07:40",
      deskripsi: "Bensin motor ke kampus",
      kategoriId: "transportasi",
      metode: "cash",
      tipe: "expense",
      jumlah: 35000,
    },
    {
      id: 7,
      tanggal: "2024-10-21",
      jam: "09:00",
      deskripsi: "Top up kuota internet 30 GB",
      kategoriId: "internet",
      metode: "dana",
      tipe: "expense",
      jumlah: 150000,
    },
    {
      id: 8,
      tanggal: "2024-10-20",
      jam: "16:20",
      deskripsi: "Token listrik kost bulan Oktober",
      kategoriId: "listrik",
      metode: "ovo",
      tipe: "expense",
      jumlah: 300000,
    },
    {
      id: 9,
      tanggal: "2024-10-19",
      jam: "11:25",
      deskripsi: "Alat tulis dan buku algoritma",
      kategoriId: "alat-tulis",
      metode: "gopay",
      tipe: "expense",
      jumlah: 125000,
    },
    {
      id: 10,
      tanggal: "2024-10-18",
      jam: "20:10",
      deskripsi: "Nonton bioskop sama teman",
      kategoriId: "hiburan",
      metode: "dana",
      tipe: "expense",
      jumlah: 80000,
    },
    {
      id: 11,
      tanggal: "2024-10-17",
      jam: "13:00",
      deskripsi: "Setoran tabungan bulanan",
      kategoriId: "tabungan",
      metode: "tabungan",
      tipe: "income",
      jumlah: 500000,
    },
    {
      id: 12,
      tanggal: "2024-10-16",
      jam: "12:00",
      deskripsi: "Beasiswa prestasi semester ganjil",
      kategoriId: "pemasukan-beasiswa",
      metode: "tabungan",
      tipe: "income",
      jumlah: 1000000,
    },
    {
      id: 13,
      tanggal: "2024-10-15",
      jam: "19:45",
      deskripsi: "Makan malam together (patungan)",
      kategoriId: "makanan",
      metode: "gopay",
      tipe: "expense",
      jumlah: 96000,
    },
    {
      id: 14,
      tanggal: "2024-10-14",
      jam: "07:50",
      deskripsi: "Bensin motor dan parkir kampus",
      kategoriId: "transportasi",
      metode: "cash",
      tipe: "expense",
      jumlah: 42000,
    },
    {
      id: 15,
      tanggal: "2024-10-13",
      jam: "15:30",
      deskripsi: "Print skripsi bab 2 dan bind",
      kategoriId: "kuliah",
      metode: "cash",
      tipe: "expense",
      jumlah: 165000,
    },
    {
      id: 16,
      tanggal: "2024-10-12",
      jam: "18:00",
      deskripsi: "Kopi belajar di kedai dekat kampus",
      kategoriId: "kopi",
      metode: "ovo",
      tipe: "expense",
      jumlah: 22000,
    },
    {
      id: 17,
      tanggal: "2024-10-11",
      jam: "09:30",
      deskripsi: "Beli bahan presentasi kelompok",
      kategoriId: "alat-tulis",
      metode: "dana",
      tipe: "expense",
      jumlah: 88000,
    },
    {
      id: 18,
      tanggal: "2024-10-10",
      jam: "08:20",
      deskripsi: "Makan pagi dan sarapan di kos",
      kategoriId: "makanan",
      metode: "cash",
      tipe: "expense",
      jumlah: 21000,
    },
    {
      id: 19,
      tanggal: "2024-10-09",
      jam: "10:00",
      deskripsi: "Top up pulsa dan paket data",
      kategoriId: "internet",
      metode: "gopay",
      tipe: "expense",
      jumlah: 100000,
    },
    {
      id: 20,
      tanggal: "2024-10-08",
      jam: "14:45",
      deskripsi: "Freelance desain poster organisasi",
      kategoriId: "pemasukan-freelance",
      metode: "gopay",
      tipe: "income",
      jumlah: 750000,
    },
    {
      id: 21,
      tanggal: "2024-10-07",
      jam: "12:10",
      deskripsi: "Makan siang kantin kampus",
      kategoriId: "makanan",
      metode: "gopay",
      tipe: "expense",
      jumlah: 24000,
    },
    {
      id: 22,
      tanggal: "2024-10-06",
      jam: "16:00",
      deskripsi: "Ojek online pulang ke kos",
      kategoriId: "transportasi",
      metode: "dana",
      tipe: "expense",
      jumlah: 27000,
    },
    {
      id: 23,
      tanggal: "2024-10-05",
      jam: "21:00",
      deskripsi: "Jajan malam di dekat kampus",
      kategoriId: "hiburan",
      tipe: "expense",
      metode: "cash",
      jumlah: 35000,
    },
    {
      id: 24,
      tanggal: "2024-10-04",
      jam: "15:30",
      deskripsi: "Uang saku bulanan dari orang tua",
      kategoriId: "pemasukan-saku",
      metode: "tabungan",
      tipe: "income",
      jumlah: 1500000,
    },
    {
      id: 25,
      tanggal: "2024-10-03",
      jam: "13:15",
      deskripsi: "Fotokopi buku referensi semester 5",
      kategoriId: "kuliah",
      metode: "cash",
      tipe: "expense",
      jumlah: 95000,
    },
    {
      id: 26,
      tanggal: "2024-10-02",
      jam: "08:45",
      deskripsi: "Sarapan dan makan pagi di kos",
      kategoriId: "makanan",
      metode: "cash",
      tipe: "expense",
      jumlah: 15000,
    },
    {
      id: 27,
      tanggal: "2024-10-01",
      jam: "07:30",
      deskripsi: "Bensin motor awal bulan",
      kategoriId: "transportasi",
      metode: "cash",
      tipe: "expense",
      jumlah: 38000,
    },
    {
      id: 28,
      tanggal: "2024-09-28",
      jam: "19:00",
      deskripsi: "Kopi dan belajar di kedai",
      kategoriId: "kopi",
      metode: "ovo",
      tipe: "expense",
      jumlah: 19000,
    },
    {
      id: 29,
      tanggal: "2024-09-27",
      jam: "11:00",
      deskripsi: "Setoran tabungan bulanan",
      kategoriId: "tabungan",
      metode: "tabungan",
      tipe: "income",
      jumlah: 400000,
    },
    {
      id: 30,
      tanggal: "2024-09-26",
      jam: "14:00",
      deskripsi: "Uang saku bulanan dari orang tua",
      kategoriId: "pemasukan-saku",
      metode: "tabungan",
      tipe: "income",
      jumlah: 1500000,
    },
    {
      id: 31,
      tanggal: "2024-09-25",
      jam: "12:30",
      deskripsi: "Makan siang dan es teh",
      kategoriId: "makanan",
      metode: "gopay",
      tipe: "expense",
      jumlah: 27000,
    },
    {
      id: 32,
      tanggal: "2024-09-24",
      jam: "16:30",
      deskripsi: "Token listrik kost September",
      kategoriId: "listrik",
      metode: "ovo",
      tipe: "expense",
      jumlah: 300000,
    },
    {
      id: 33,
      tanggal: "2024-09-23",
      jam: "10:00",
      deskripsi: "Top up kuota internet 30 GB",
      kategoriId: "internet",
      metode: "dana",
      tipe: "expense",
      jumlah: 150000,
    },
    {
      id: 34,
      tanggal: "2024-09-22",
      jam: "20:00",
      deskripsi: "Bioskop akhir pekan",
      kategoriId: "hiburan",
      metode: "dana",
      tipe: "expense",
      jumlah: 85000,
    },
    {
      id: 35,
      tanggal: "2024-09-21",
      jam: "08:00",
      deskripsi: "Bensin motor ke kampus",
      kategoriId: "transportasi",
      metode: "cash",
      tipe: "expense",
      jumlah: 33000,
    },
    {
      id: 36,
      tanggal: "2024-09-20",
      jam: "15:00",
      deskripsi: "Beli buku pemrograman web",
      kategoriId: "kuliah",
      metode: "gopay",
      tipe: "expense",
      jumlah: 180000,
    },
    {
      id: 37,
      tanggal: "2024-09-19",
      jam: "09:30",
      deskripsi: "Honor proyek tugas",
      kategoriId: "pemasukan-freelance",
      metode: "gopay",
      tipe: "income",
      jumlah: 950000,
    },
    {
      id: 38,
      tanggal: "2024-09-18",
      jam: "12:00",
      deskripsi: "Makan siang di kantin",
      kategoriId: "makanan",
      metode: "cash",
      tipe: "expense",
      jumlah: 23000,
    },
  ];

  /* ----------------------------------------------------------------------
     Anggaran bulanan
     Total batas 3.200.000 · terpakai 2.150.000 · sisa 1.050.000
     ---------------------------------------------------------------------- */
  const budgets = [
    {
      kategoriId: "makanan",
      batas: 1000000,
      terpakai: 750000,
      icon: "restaurant",
      catatan: "Makan & warteg",
    },
    {
      kategoriId: "transportasi",
      batas: 500000,
      terpakai: 350000,
      icon: "two_wheeler",
      catatan: "Bensin & ojek",
    },
    {
      kategoriId: "internet",
      batas: 200000,
      terpakai: 150000,
      icon: "wifi",
      catatan: "Kuota internet",
    },
    {
      kategoriId: "listrik",
      batas: 400000,
      terpakai: 300000,
      icon: "bolt",
      catatan: "Token listrik kost",
    },
    {
      kategoriId: "kuliah",
      batas: 600000,
      terpakai: 400000,
      icon: "menu_book",
      catatan: "Print & fotokopi skripsi",
    },
    {
      kategoriId: "kopi",
      batas: 300000,
      terpakai: 180000,
      icon: "coffee",
      catatan: "Kopi & belajar",
    },
    {
      kategoriId: "hiburan",
      batas: 200000,
      terpakai: 20000,
      icon: "movie",
      catatan: "Hiburan & nongkrong",
    },
  ];

  /* ----------------------------------------------------------------------
     Tren 6 bulan (dalam ribuan rupiah, untuk chart)
     ---------------------------------------------------------------------- */
  const trend = {
    labels: ["Mei", "Jun", "Jul", "Agu", "Sep", "Okt"],
    pemasukan: [3800000, 3900000, 3600000, 4100000, 4900000, 4200000],
    pengeluaran: [2100000, 2250000, 1980000, 2400000, 3240000, 2150000],
  };

  /* ----------------------------------------------------------------------
     Komposisi pengeluaran (untuk donut & composition bar)
     Warna ditulis sebagai hex (bukan var(--x)) karena dipakai langsung
     oleh canvas Chart.js yang tidak bisa resolve CSS custom property.
     ---------------------------------------------------------------------- */
  const expenseComposition = [
    { nama: "Makanan", nilai: 750000, warna: "#2563eb" },
    { nama: "Kuliah & Skripsi", nilai: 400000, warna: "#0d9488" },
    { nama: "Token Listrik", nilai: 300000, warna: "#f59e0b" },
    { nama: "Transportasi", nilai: 350000, warna: "#e11d48" },
    { nama: "Internet", nilai: 150000, warna: "#8b5cf6" },
    { nama: "Kopi & Belajar", nilai: 180000, warna: "#0ea5e9" },
    { nama: "Hiburan", nilai: 20000, warna: "#cbd5e1" },
  ];

  const incomeComposition = [
    { nama: "Uang Saku", nilai: 1500000, warna: "#2563eb" },
    { nama: "Freelance", nilai: 1200000, warna: "#0d9488" },
    { nama: "Beasiswa", nilai: 1000000, warna: "#8b5cf6" },
    { nama: "Tabungan", nilai: 500000, warna: "#f59e0b" },
  ];

  /* ----------------------------------------------------------------------
     Rekapitulasi bulanan (untuk halaman Laporan)
     ---------------------------------------------------------------------- */
  const monthlyRecap = [
    {
      bulan: "Oktober 2024",
      bulanKe: 10,
      pemasukan: 4200000,
      pengeluaran: 2150000,
      tabungan: 2050000,
      status: "Surplus Tinggi",
      badge: "badge--income",
    },
    {
      bulan: "September 2024",
      bulanKe: 9,
      pemasukan: 4900000,
      pengeluaran: 3240000,
      tabungan: 1660000,
      status: "Surplus Sehat",
      badge: "badge--income",
    },
    {
      bulan: "Agustus 2024",
      bulanKe: 8,
      pemasukan: 4100000,
      pengeluaran: 2400000,
      tabungan: 1700000,
      status: "Surplus Sehat",
      badge: "badge--income",
    },
    {
      bulan: "Juli 2024",
      bulanKe: 7,
      pemasukan: 3600000,
      pengeluaran: 1980000,
      tabungan: 1620000,
      status: "Surplus Sehat",
      badge: "badge--income",
    },
    {
      bulan: "Juni 2024",
      bulanKe: 6,
      pemasukan: 3900000,
      pengeluaran: 2250000,
      tabungan: 1650000,
      status: "Surplus Sehat",
      badge: "badge--income",
    },
    {
      bulan: "Mei 2024",
      bulanKe: 5,
      pemasukan: 3800000,
      pengeluaran: 2100000,
      tabungan: 1700000,
      status: "Surplus Sehat",
      badge: "badge--income",
    },
  ];

  /* ----------------------------------------------------------------------
     Tips keuangan (callout dashboard)
     ---------------------------------------------------------------------- */
  const tips = [
    "Kurangi jajan Rp 20.000 per hari — dalam sebulan bisa selisih Rp 600.000 untuk dana skripsi.",
    "Masak atau bring bekal 2× seminggu sudah menghemat sekitar Rp 150.000 per bulan.",
    "Pindahkan sisa uang saku ke tabungan setiap tanggal 25 agar tidak terbuang untuk belanja online.",
  ];

  /* ----------------------------------------------------------------------
     Helper functions (dipakai script.js)
     ---------------------------------------------------------------------- */
  const getCategory = (id) => categories.find((c) => c.id === id) || null;
  const getMethod = (id) => paymentMethods.find((m) => m.id === id) || null;

  /** Kategori expense saja (untuk form & filter) */
  const expenseCategories = () => categories.filter((c) => c.tipe === "expense");
  const incomeCategories = () => categories.filter((c) => c.tipe === "income");

  /**
   * Rekap pengeluaran per kategori untuk bulan berjalan.
   *
   * PENTING: array `transactions` di atas adalah CUPLIKAN (sample) transaksi,
   * bukan buku kas lengkap — jumlahnya sengaja tidak sama dengan total bulanan.
   * Sumber kebenaran untuk angka bulanan adalah `summary` (headline Dashboard),
   * `budgets[].terpakai` (kategori) dan `expenseComposition` (donut).
   * Ketiganya konsisten: 750rb + 350rb + 150rb + 300rb + 400rb + 180rb + 20rb
   * = Rp 2.150.000.
   *
   * Karena itu rekap Laporan diturunkan dari `budgets`, bukan dijumlahkan dari
   * `transactions`, supaya tidak muncul dua angka berbeda untuk hal yang sama.
   *
   * @returns {{kategoriId, nama, icon, catatan, total, batas, jumlah}[]}
   *          urut total descending
   */
  const monthlyCategoryReport = () => {
    return budgets
      .map((b) => {
        const cat = getCategory(b.kategoriId);
        return {
          kategoriId: b.kategoriId,
          nama: cat ? cat.nama : b.kategoriId,
          icon: b.icon,
          catatan: b.catatan,
          total: b.terpakai,
          batas: b.batas,
          jumlah: transactions.filter(
            (t) => t.tipe === "expense" && t.kategoriId === b.kategoriId
          ).length,
        };
      })
      .sort((a, b) => b.total - a.total);
  };

  return {
    categories,
    paymentMethods,
    expenseCategories,
    incomeCategories,
    getCategory,
    getMethod,
    summary,
    transactions,
    budgets,
    trend,
    expenseComposition,
    incomeComposition,
    monthlyRecap,
    monthlyCategoryReport,
    tips,
  };
})();
