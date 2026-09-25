/* ==========================================================================
   FinanceTrack — Shared JavaScript (Milestone 2)
   --------------------------------------------------------------------------
   Semua interaksi dasar (sidebar, modal, toast, format angka, tabel, chart)
   dikumpulkan di satu file agar mudah dikembangkan pada Milestone 3.

   Struktur modul:
     1. Utils        — format Rupiah, tanggal, query helper
     2. Navigation   — sidebar toggle, submenu, active state, bottom nav
     3. UI Feedback  — modal konfirmasi, toast
     4. Table        — search, filter, sort, pagination (halaman Transaksi)
     5. Form         — format nominal, segmented type, preview (halaman Form)
     6. Budget       — status & progress bar (halaman Anggaran)
     7. Chart        — Chart.js inisialisasi (Dashboard & Laporan)
   ========================================================================== */

(function () {
  "use strict";

  /* =======================================================================
     1. UTILS
     ======================================================================= */
  const Utils = {
    /** 3450000 -> "Rp 3.450.000" */
    formatRupiah(value, withPrefix = true) {
      const n = Number(value) || 0;
      const formatted = n.toLocaleString("id-ID");
      return withPrefix ? "Rp " + formatted : formatted;
    },

    /** Versi ringkas untuk chart / badge: 2150000 -> "Rp 2,15 jt" */
    formatRupiahShort(value) {
      const n = Number(value) || 0;
      if (Math.abs(n) >= 1000000) {
        return "Rp " + (n / 1000000).toLocaleString("id-ID", {
          maximumFractionDigits: 2,
        }) + " jt";
      }
      if (Math.abs(n) >= 1000) {
        return "Rp " + Math.round(n / 1000) + " rb";
      }
      return "Rp " + n;
    },

    /** 3450000 -> "+Rp 3.450.000" / "-Rp 25.000" */
    formatSigned(value) {
      const n = Number(value) || 0;
      const sign = n > 0 ? "+" : n < 0 ? "-" : "";
      return sign + this.formatRupiah(Math.abs(n));
    },

    /** "2024-10-24" -> "24 Okt 2024" */
    formatTanggal(isoDate) {
      if (!isoDate) return "-";
      const bulan = [
        "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
        "Jul", "Agu", "Sep", "Okt", "Nov", "Des",
      ];
      const [y, m, d] = isoDate.split("-");
      return `${d} ${bulan[parseInt(m, 10) - 1]} ${y}`;
    },

    /** Parse input "25.000" -> 25000 */
    parseRupiah(str) {
      return parseInt(String(str).replace(/\D/g, ""), 10) || 0;
    },

    /** Format value input jadi ribuan: 25000 -> "25.000" */
    thousands(value) {
      return (Number(value) || 0).toLocaleString("id-ID");
    },

    /** Persentase aman (tidak NaN / Infinity) */
    percent(part, whole) {
      if (!whole) return 0;
      return Math.min(100, Math.round((part / whole) * 1000) / 10);
    },

    /** q / qsa helper */
    qs(selector, scope = document) {
      return scope.querySelector(selector);
    },
    qsa(selector, scope = document) {
      return Array.from(scope.querySelectorAll(selector));
    },

    /** Escaping teks sebelum masuk innerHTML (data mock tetap aman, tapi ini kebiasaan baik) */
    escapeHtml(str) {
      return String(str ?? "").replace(/[&<>"']/g, (c) => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
      })[c]);
    },
  };

  /* =======================================================================
     2. NAVIGATION — sidebar, submenu, active state
     ======================================================================= */
  const Navigation = {
    init() {
      this.setupSidebarToggle();
      this.setupSubmenu();
      this.setupActiveState();
      this.setupBottomNav();
    },

    setupSidebarToggle() {
      const toggle = Utils.qsa("[data-sidebar-toggle]");
      const close = Utils.qsa("[data-sidebar-close]");

      toggle.forEach((btn) => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          document.body.classList.toggle("sidebar-open");
        });
      });

      close.forEach((btn) => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          document.body.classList.remove("sidebar-open");
        });
      });

      // Tutup sidebar saat klik backdrop
      const backdrop = Utils.qs(".sidebar-backdrop");
      if (backdrop) {
        backdrop.addEventListener("click", () => {
          document.body.classList.remove("sidebar-open");
        });
      }

      // Tutup sidebar saat tekan Escape
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          document.body.classList.remove("sidebar-open");
          document.querySelectorAll(".modal.is-open").forEach((m) => m.classList.remove("is-open"));
        }
      });
    },

    setupSubmenu() {
      // Grup "Transaksi" bisa dibuka/tutup; default terbuka bila salah satu
      // submenu-nya aktif.
      const trigger = Utils.qsa("[data-submenu-trigger]");
      trigger.forEach((btn) => {
        const sublist = document.getElementById(btn.getAttribute("aria-controls"));
        if (!sublist) return;

        // Buka otomatis bila submenu aktif (halaman Transaksi / Tambah Transaksi)
        if (sublist.querySelector(".is-active")) sublist.classList.add("is-open");

        btn.addEventListener("click", () => {
          const isOpen = sublist.classList.toggle("is-open");
          btn.setAttribute("aria-expanded", String(isOpen));
        });
      });
    },

    setupActiveState() {
      // Fallback: bila markup lupa menandai .is-active, tandai via pathname.
      const path = window.location.pathname.split("/").pop() || "index.html";
      Utils.qsa(".nav-item, .nav-subitem, .bottom-nav__item").forEach((link) => {
        const href = link.getAttribute("href");
        if (href && href.endsWith(path) && !link.classList.contains("is-active")) {
          // Jangan aktifkan "Semua Transaksi" otomatis bila di halaman form,
          // kecuali memang tidak ada active.
          link.classList.add("is-active");
        }
      });
    },

    setupBottomNav() {
      // FAB "Tambah" tidak perlu status aktif.
      Utils.qsa(".bottom-nav__item--fab").forEach((el) => el.classList.remove("is-active"));
    },
  };

  /* =======================================================================
     3. UI FEEDBACK — modal konfirmasi & toast
     ======================================================================= */
  const Feedback = {
    modal: null,
    toast: null,
    toastTimer: null,

    init() {
      this.modal = Utils.qs("[data-modal]");
      this.toast = Utils.qs("[data-toast]");

      if (this.modal) {
        // Tutup modal saat klik backdrop / tombol batal / tombol close.
        this.modal.addEventListener("click", (e) => {
          if (e.target === this.modal) this.closeModal();
        });
        Utils.qsa("[data-modal-close]", this.modal).forEach((btn) => {
          btn.addEventListener("click", () => this.closeModal());
        });
      }
    },

    /**
     * Buka modal konfirmasi.
     * @param {object} options { title, message, confirmText, confirmClass, onConfirm }
     */
    openModal(options = {}) {
      if (!this.modal) return;
      const title = Utils.qs("[data-modal-title]", this.modal);
      const message = Utils.qs("[data-modal-message]", this.modal);
      const confirmBtn = Utils.qs("[data-modal-confirm]", this.modal);

      if (title) title.textContent = options.title || "Konfirmasi";
      if (message) message.textContent = options.message || "Anda yakin?";
      if (confirmBtn) {
        confirmBtn.textContent = options.confirmText || "Ya, lanjutkan";
        confirmBtn.className = "btn " + (options.confirmClass || "btn--danger");
        // Ganti listener lama dengan yang baru.
        confirmBtn.onclick = () => {
          if (typeof options.onConfirm === "function") options.onConfirm();
          this.closeModal();
        };
      }
      this.modal.classList.add("is-open");
      // Fokuskan tombol konfirmasi agar accessible via keyboard.
      if (confirmBtn) confirmBtn.focus();
    },

    closeModal() {
      if (this.modal) this.modal.classList.remove("is-open");
    },

    /**
     * Tampilkan toast.
     * @param {string} title  Judul toast
     * @param {string} message Pesan tambahan
     * @param {string} icon    Nama ikon Material Symbols
     */
    showToast(title, message = "", icon = "check_circle") {
      if (!this.toast) return;
      const t = Utils.qs("[data-toast-title]", this.toast);
      const m = Utils.qs("[data-toast-message]", this.toast);
      const i = Utils.qs("[data-toast-icon]", this.toast);

      if (t) t.textContent = title;
      if (m) m.textContent = message;
      if (i) i.textContent = icon;

      this.toast.classList.add("is-visible");
      clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => {
        this.toast.classList.remove("is-visible");
      }, 3200);
    },
  };

  /* =======================================================================
     4. TABLE — search, filter, sort, pagination
     ======================================================================= */
  const TransactionTable = {
    rows: [],
    feedItems: [],
    filtered: [],
    currentPage: 1,
    perPage: 8,
    _listenersBound: false,

    init() {
      const tbody = Utils.qs("[data-tx-tbody]");
      if (!tbody) return;

      this.readRows();

      this.perPage = parseInt(
        Utils.qs("[data-per-page]")?.value || "8",
        10
      );

      // Halaman me-render baris lewat inline script yang jalan SETELAH
      // DOMContentLoaded, jadi binding hanya boleh dilakukan sekali.
      if (!this._listenersBound) {
        this._listenersBound = true;
        this.bindSearch();
        this.bindFilters();
        this.bindSort();
        this.bindSelectAll();
        this.bindRowActions();
        this.bindPerPage();
      }

      this.applyFilters();
    },

    /**
     * Baca ulang elemen dari DOM lalu terapkan filter saat ini.
     * `rows`     -> <tr data-id>       (tampilan tabel, desktop)
     * `feedItems`-> [data-feed-id]     (tampilan kartu, mobile)
     */
    readRows() {
      const tbody = Utils.qs("[data-tx-tbody]");
      this.rows = tbody ? Array.from(tbody.querySelectorAll("tr[data-id]")) : [];
      this.feedItems = Array.from(
        document.querySelectorAll("[data-tx-feed] [data-feed-id]")
      );
      this.filtered = this.rows.slice();
    },

    /**
     * Dipanggil halaman setelah baris di-render dinamis.
     * Tidak melakukan binding ulang — hanya membaca elemen & render ulang.
     */
    refreshRows() {
      this.readRows();
      this.applyFilters();
    },

    bindSearch() {
      const input = Utils.qs("[data-search]");
      const clear = Utils.qs("[data-search-clear]");
      if (!input) return;

      input.addEventListener("input", (e) => {
        const term = e.target.value.toLowerCase().trim();
        if (clear) clear.classList.toggle("is-visible", term.length > 0);
        this.applyFilters();
      });
      if (clear) {
        clear.addEventListener("click", () => {
          input.value = "";
          clear.classList.remove("is-visible");
          this.applyFilters();
          input.focus();
        });
      }
    },

    bindFilters() {
      const typeSel = Utils.qs("[data-filter-type]");
      const catSel = Utils.qs("[data-filter-category]");
      const periodSel = Utils.qs("[data-filter-period]");
      const resetBtn = Utils.qs("[data-filter-reset]");

      [typeSel, catSel, periodSel].forEach((el) => {
        if (el) el.addEventListener("change", () => this.applyFilters());
      });

      if (resetBtn) {
        resetBtn.addEventListener("click", () => this.resetFilters());
      }
    },

    bindSort() {
      const sortSel = Utils.qs("[data-filter-sort]");
      if (!sortSel) return;
      sortSel.addEventListener("change", () => this.applyFilters(sortSel.value));
    },

    bindPerPage() {
      const perSel = Utils.qs("[data-per-page]");
      if (!perSel) return;
      perSel.addEventListener("change", (e) => {
        this.perPage = parseInt(e.target.value, 10);
        this.currentPage = 1;
        this.render();
      });
    },

    bindSelectAll() {
      const selectAll = Utils.qs("[data-select-all]");
      if (!selectAll) return;
      selectAll.addEventListener("change", () => {
        this.visibleCheckboxes().forEach((cb) => {
          cb.checked = selectAll.checked;
        });
      });
    },

    bindRowActions() {
      // Edit & Hapus memakai delegasi event karena baris di-render ulang.
      // Dua container: tbody (desktop) dan feed kartu (mobile).
      const onAction = (e) => {
        const editBtn = e.target.closest("[data-action='edit']");
        const deleteBtn = e.target.closest("[data-action='delete']");
        if (!editBtn && !deleteBtn) return;

        const host = editBtn ? editBtn.closest("[data-id], [data-feed-id]")
                             : deleteBtn.closest("[data-id], [data-feed-id]");
        if (!host) return;
        const id = host.dataset.id || host.dataset.feedId;

        if (editBtn) this.handleEdit(id);
        if (deleteBtn) this.handleDelete(id);
      };

      [Utils.qs("[data-tx-tbody]"), Utils.qs("[data-tx-feed]")]
        .filter(Boolean)
        .forEach((el) => el.addEventListener("click", onAction));
    },

    visibleCheckboxes() {
      return Array.from(
        document.querySelectorAll("[data-tx-tbody] .row-checkbox")
      ).filter((cb) => cb.closest("tr").style.display !== "none");
    },

    resetFilters() {
      const search = Utils.qs("[data-search]");
      const typeSel = Utils.qs("[data-filter-type]");
      const catSel = Utils.qs("[data-filter-category]");
      const periodSel = Utils.qs("[data-filter-period]");
      const sortSel = Utils.qs("[data-filter-sort]");

      if (search) search.value = "";
      if (typeSel) typeSel.value = "all";
      if (catSel) catSel.value = "all";
      if (periodSel) periodSel.value = "all";
      if (sortSel) sortSel.value = "newest";

      const clear = Utils.qs("[data-search-clear]");
      if (clear) clear.classList.remove("is-visible");

      const selectAll = Utils.qs("[data-select-all]");
      if (selectAll) selectAll.checked = false;

      this.applyFilters();
    },

    applyFilters(sortValue) {
      const search = Utils.qs("[data-search]");
      const typeSel = Utils.qs("[data-filter-type]");
      const catSel = Utils.qs("[data-filter-category]");
      const periodSel = Utils.qs("[data-filter-period]");
      const sortSel = Utils.qs("[data-filter-sort]");

      const term = (search?.value || "").toLowerCase().trim();
      const type = typeSel?.value || "all";
      const cat = catSel?.value || "all";
      const period = periodSel?.value || "all";
      const sort = sortValue || sortSel?.value || "newest";

      this.filtered = this.rows.filter((row) => {
        const text = row.dataset.search || row.textContent.toLowerCase();
        if (term && !text.includes(term)) return false;
        if (type !== "all" && row.dataset.type !== type) return false;
        if (cat !== "all" && row.dataset.kategori !== cat) return false;
        if (period !== "all" && row.dataset.period !== period) return false;
        return true;
      });

      // Sorting
      const sorted = this.filtered.slice();
      sorted.sort((a, b) => {
        const tglA = a.dataset.tanggal || "";
        const tglB = b.dataset.tanggal || "";
        const amtA = parseInt(a.dataset.jumlah || "0", 10);
        const amtB = parseInt(b.dataset.jumlah || "0", 10);

        if (sort === "oldest") return tglA.localeCompare(tglB);
        if (sort === "highest") return amtB - amtA;
        if (sort === "lowest") return amtA - amtB;
        return tglB.localeCompare(tglA); // default: terbaru
      });
      this.filtered = sorted;
      this.currentPage = 1;
      this.render();
    },

    render() {
      const total = this.filtered.length;
      const totalPages = Math.max(1, Math.ceil(total / this.perPage));

      if (this.currentPage > totalPages) this.currentPage = totalPages;

      const start = (this.currentPage - 1) * this.perPage;
      const end = Math.min(start + this.perPage, total);
      const pageRows = this.filtered.slice(start, end);
      const pageRowIds = new Set(pageRows.map((r) => r.dataset.id));

      // Tampilkan hanya baris di halaman ini (tabel desktop).
      this.rows.forEach((row) => {
        row.style.display = pageRowIds.has(row.dataset.id) ? "" : "none";
      });

      // Kartu mobile mengikuti halaman & filter yang sama.
      this.feedItems.forEach((item) => {
        item.style.display = pageRowIds.has(item.dataset.feedId) ? "" : "none";
      });

      // Sembunyikan blok pagination bila tidak ada hasil filter.
      const pagination = Utils.qs("[data-pagination]");
      if (pagination) pagination.classList.toggle("is-hidden", total === 0);

      this.updateInfo(start, end, total);
      this.renderPagination(totalPages);

      // Kosongkan checkbox "select all" saat ganti halaman.
      const selectAll = Utils.qs("[data-select-all]");
      if (selectAll) selectAll.checked = false;
    },

    updateInfo(start, end, total) {
      const info = Utils.qs("[data-tx-info]");
      if (info) {
        info.textContent =
          total === 0
            ? "Tidak ada transaksi"
            : `Menampilkan ${start + 1}-${end} dari ${total} transaksi`;
      }
      const empty = Utils.qs("[data-tx-empty]");
      if (empty) empty.classList.toggle("is-hidden", total > 0);
    },

    renderPagination(totalPages) {
      const nav = Utils.qs("[data-pagination]");
      if (!nav) return;

      const btn = (label, page, opts = {}) => {
        const b = document.createElement("button");
        b.className =
          "pagination__btn" +
          (opts.active ? " is-active" : "") +
          (opts.wide ? " pagination__btn--icon" : "");
        b.type = "button";
        b.textContent = label;
        if (opts.disabled) b.disabled = true;
        if (page) b.dataset.page = page;
        if (opts.label) b.setAttribute("aria-label", opts.label);
        return b;
      };

      nav.innerHTML = "";

      // Prev
      const prev = btn("‹", this.currentPage - 1, {
        disabled: this.currentPage === 1,
        label: "Halaman sebelumnya",
        wide: true,
      });
      prev.addEventListener("click", () => this.goTo(this.currentPage - 1));
      nav.appendChild(prev);

      // Nomor halaman (window 5 + ellipsis)
      const pages = [];
      const windowSize = 2;
      for (let p = 1; p <= totalPages; p++) {
        if (
          p === 1 ||
          p === totalPages ||
          (p >= this.currentPage - windowSize && p <= this.currentPage + windowSize)
        ) {
          pages.push(p);
        }
      }

      let prevNum = 0;
      pages.forEach((p) => {
        if (prevNum && p - prevNum > 1) {
          const ell = document.createElement("span");
          ell.className = "pagination__ellipsis";
          ell.textContent = "…";
          nav.appendChild(ell);
        }
        const b = btn(String(p), p, { active: p === this.currentPage });
        b.addEventListener("click", () => this.goTo(p));
        nav.appendChild(b);
        prevNum = p;
      });

      // Next
      const next = btn("›", this.currentPage + 1, {
        disabled: this.currentPage === totalPages,
        label: "Halaman berikutnya",
        wide: true,
      });
      next.addEventListener("click", () => this.goTo(this.currentPage + 1));
      nav.appendChild(next);
    },

    goTo(page) {
      const totalPages = Math.max(1, Math.ceil(this.filtered.length / this.perPage));
      if (page < 1 || page > totalPages) return;
      this.currentPage = page;
      this.render();
      // Gulir ke atas tabel saat ganti halaman.
      const wrap = Utils.qs("[data-tx-card]");
      if (wrap) wrap.scrollIntoView({ behavior: "smooth", block: "start" });
    },

    handleEdit(id) {
      // Milestone 2: belum ada storage, jadi arahkan ke form transaksi.
      Feedback.showToast(
        "Mode Edit",
        "Formulir edit akan diisi otomatis pada Milestone 3.",
        "edit"
      );
    },

    handleDelete(id) {
      const row = this.rows.find((r) => r.dataset.id === id);
      const desc = row?.dataset.descripsi || "transaksi ini";
      const jumlah = row?.dataset.jumlah || 0;

      Feedback.openModal({
        title: "Hapus transaksi?",
        message: `Transaksi "${desc}" sebesar ${Utils.formatRupiah(
          jumlah
        )} akan dihapus dari daftar. Tindakan ini tidak dapat dibatalkan.`,
        confirmText: "Ya, hapus",
        onConfirm: () => {
          // Milestone 2: hapus hanya dari tampilan (mock, tanpa storage).
          const feedItem = this.feedItems.find((i) => i.dataset.feedId === id);
          if (row) row.remove();
          if (feedItem) feedItem.remove();

          this.readRows();
          this.applyFilters();

          Feedback.showToast(
            "Transaksi dihapus",
            `"${desc}" telah dihapus dari daftar.`,
            "delete"
          );
        },
      });
    },
  };

  /* =======================================================================
     5. FORM — halaman Tambah Transaksi
     ======================================================================= */
  const TransactionForm = {
    currentType: "expense",

    init() {
      const form = Utils.qs("[data-tx-form]");
      if (!form) return;

      this.setupTypeSelector();
      this.applyTypeFromQuery();
      this.setupAmount();
      this.setupQuickAmounts();
      this.setupLivePreview();
      this.setupSubmit();
      this.setupCancel();
    },

    /**
     * Dukungan deep-link dari Dashboard: form-transaksi.html?jenis=income
     * (atau ?jenis=expense). Dijalkan di dalam init — bukan lewat .click() dari
     * inline script halaman — supaya tidak bergantung pada urutan
     * registrasi listener DOMContentLoaded.
     */
    applyTypeFromQuery() {
      const requested = new URLSearchParams(window.location.search).get("jenis");
      if (requested !== "income" && requested !== "expense") return;
      if (requested === this.currentType) return;

      this.setType(requested);
    },

    /** Aktifkan satu mode (expense/income) dan segarkan dropdown + preview. */
    setType(type) {
      this.currentType = type;

      Utils.qsa("[data-type-btn]").forEach((b) => {
        b.classList.toggle("is-active", b.dataset.typeBtn === type);
        b.setAttribute("aria-pressed", String(b.dataset.typeBtn === type));
      });

      this.updateCategoryOptions(type);
      this.updatePreview();
    },

    setupTypeSelector() {
      Utils.qsa("[data-type-btn]").forEach((btn) => {
        btn.addEventListener("click", () => this.setType(btn.dataset.typeBtn));
      });
    },

    updateCategoryOptions(type) {
      const catSel = Utils.qs("[data-field-category]");
      if (!catSel || typeof FinanceTrackData === "undefined") return;

      const list =
        type === "income"
          ? FinanceTrackData.incomeCategories()
          : FinanceTrackData.expenseCategories();

      const current = catSel.value;
      catSel.innerHTML = list
        .map(
          (c) =>
            `<option value="${c.id}">${Utils.escapeHtml(c.nama)}</option>`
        )
        .join("");

      // Pertahankan pilihan bila masih valid.
      if (list.some((c) => c.id === current)) catSel.value = current;
    },

    setupAmount() {
      const input = Utils.qs("[data-field-amount]");
      if (!input) return;

      input.addEventListener("input", (e) => {
        const digits = e.target.value.replace(/\D/g, "");
        e.target.value = digits ? Utils.thousands(digits) : "";
        this.updatePreview();
      });
    },

    setupQuickAmounts() {
      Utils.qsa("[data-quick-amount]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const input = Utils.qs("[data-field-amount]");
          if (!input) return;
          const current = Utils.parseRupiah(input.value);
          const add = parseInt(btn.dataset.quickAmount, 10);
          input.value = Utils.thousands(current + add);
          this.updatePreview();
        });
      });
    },

    setupLivePreview() {
      // Preview diperbarui saat field lain berubah.
      ["data-field-category", "data-field-date", "data-field-time", "data-field-desc", "data-field-method"]
        .map((sel) => Utils.qs("[" + sel + "]"))
        .filter(Boolean)
        .forEach((el) => {
          el.addEventListener("input", () => this.updatePreview());
          el.addEventListener("change", () => this.updatePreview());
        });
    },

    updatePreview() {
      if (typeof FinanceTrackData === "undefined") return;

      const amount = Utils.parseRupiah(Utils.qs("[data-field-amount]")?.value);
      const catId = Utils.qs("[data-field-category]")?.value;
      const methodId = Utils.qs("[data-field-method]")?.value;
      const date = Utils.qs("[data-field-date]")?.value;
      const time = Utils.qs("[data-field-time]")?.value;
      const desc = Utils.qs("[data-field-desc]")?.value;

      const cat = FinanceTrackData.getCategory(catId);
      const method = FinanceTrackData.getMethod(methodId);
      const isIncome = this.currentType === "income";

      // Nominal
      const amountEl = Utils.qs("[data-preview-amount]");
      if (amountEl) {
        amountEl.textContent =
          (isIncome ? "+ " : "- ") + Utils.formatRupiah(amount);
        amountEl.classList.toggle("text-income", isIncome);
        amountEl.classList.toggle("text-expense", !isIncome);
      }

      // Badge jenis
      const badgeEl = Utils.qs("[data-preview-badge]");
      if (badgeEl) {
        badgeEl.textContent = isIncome ? "Pemasukan" : "Pengeluaran";
        badgeEl.className =
          "badge " + (isIncome ? "badge--income" : "badge--expense");
      }

      // Kategori
      const catEl = Utils.qs("[data-preview-category]");
      if (catEl) catEl.textContent = cat ? cat.nama : "-";

      // Metode
      const methodEl = Utils.qs("[data-preview-method]");
      if (methodEl) methodEl.textContent = method ? method.nama : "-";

      // Tanggal
      const dateEl = Utils.qs("[data-preview-date]");
      if (dateEl) {
        dateEl.textContent = date
          ? Utils.formatTanggal(date) + (time ? " • " + time : "")
          : "-";
      }

      // Deskripsi
      const descEl = Utils.qs("[data-preview-desc]");
      if (descEl) descEl.textContent = desc || "-";
    },

    setupSubmit() {
      const form = Utils.qs("[data-tx-form]");
      if (!form) return;

      form.addEventListener("submit", (e) => {
        e.preventDefault();

        const amount = Utils.parseRupiah(Utils.qs("[data-field-amount]")?.value);
        const desc = (Utils.qs("[data-field-desc]")?.value || "").trim();

        let valid = true;
        const amountField = Utils.qs("[data-field-amount]")?.closest(".field");
        const descField = Utils.qs("[data-field-desc]")?.closest(".field");

        if (!amount || amount <= 0) {
          amountField?.classList.add("has-error");
          valid = false;
        } else {
          amountField?.classList.remove("has-error");
        }

        if (!desc) {
          descField?.classList.add("has-error");
          valid = false;
        } else {
          descField?.classList.remove("has-error");
        }

        if (!valid) {
          Feedback.showToast(
            "Form belum lengkap",
            "Isi nominal dan deskripsi transaksi terlebih dahulu.",
            "error"
          );
          return;
        }

        const isIncome = this.currentType === "income";
        Feedback.showToast(
          isIncome ? "Pemasukan tersimpan" : "Pengeluaran tersimpan",
          `${Utils.formatRupiah(amount)} untuk "${desc}" berhasil dicatat.`,
          "check_circle"
        );

        // Reset form (Milestone 2 belum menyimpan ke storage).
        form.reset();
        this.updateCategoryOptions(this.currentType);
        this.updatePreview();
      });
    },

    setupCancel() {
      const cancel = Utils.qs("[data-cancel]");
      if (!cancel) return;
      cancel.addEventListener("click", (e) => {
        e.preventDefault();
        Feedback.openModal({
          title: "Batalkan input?",
          message:
            "Data yang sudah kamu isi akan hilang. Yakin ingin membatalkan?",
          confirmText: "Ya, batalkan",
          confirmClass: "btn--secondary",
          onConfirm: () => {
            const form = Utils.qs("[data-tx-form]");
            if (form) form.reset();
            this.updateCategoryOptions(this.currentType);
            this.updatePreview();
            Feedback.showToast(
              "Form dibatalkan",
              "Semua kolom dikosongkan kembali.",
              "close"
            );
          },
        });
      });
    },
  };

  /* =======================================================================
     6. BUDGET — status & progress bar
     ======================================================================= */
  const BudgetUI = {
    /** Modifiers_progress bar yang bersifat eksklusif (hanya boleh satu aktif). */
    BAR_MODIFIERS: [
      "progress__bar--success",
      "progress__bar--warning",
      "progress__bar--danger",
    ],

    init() {
      if (typeof FinanceTrackData === "undefined") return;

      // Warnai setiap progress bar + tetapkan status berdasarkan persentase.
      Utils.qsa("[data-budget-card]").forEach((card) => {
        const spent = parseInt(card.dataset.budgetSpent || "0", 10);
        const limit = parseInt(card.dataset.budgetLimit || "0", 10);
        const pct = Utils.percent(spent, limit);

        const bar = Utils.qs("[data-budget-bar]", card);
        if (bar) {
          // Modikator bersifat eksklusif — buang yang lain agar tidak konflik
          // bila kartu sudah punya kelas dari renderer halaman.
          bar.classList.remove(...this.BAR_MODIFIERS);
          bar.classList.add(
            pct >= 100
              ? "progress__bar--danger"
              : pct >= 80
              ? "progress__bar--warning"
              : "progress__bar--success"
          );
          // Naikkan sedikit delay supaya animasi progress terlihat.
          setTimeout(() => (bar.style.width = pct + "%"), 120);
        }

        const pctEl = Utils.qs("[data-budget-pct]", card);
        if (pctEl) {
          pctEl.textContent = Utils.percent(spent, limit).toFixed(1) + "%";
        }

        const remainingEl = Utils.qs("[data-budget-remaining]", card);
        if (remainingEl) {
          const remaining = limit - spent;
          remainingEl.textContent =
            (remaining >= 0 ? "Sisa " : "Lebih ") + Utils.formatRupiah(Math.abs(remaining));
        }
      });
    },
  };

  /* =======================================================================
     7. CHART — Chart.js (dimuat via CDN di HTML)
     ======================================================================= */
  const Charts = {
    ready: false,
    chartRefs: {},

    init() {
      if (typeof Chart === "undefined") {
        // Chart.js gagal dimuat — biarkan halaman tetap berfungsi tanpa grafik.
        Utils.qsa("[data-chart-fallback]").forEach((el) =>
          el.classList.remove("is-hidden")
        );
        return;
      }
      this.ready = true;

      // Global default font agar konsisten dengan design system.
      Chart.defaults.font.family =
        '"Inter", system-ui, -apple-system, "Segoe UI", sans-serif';
      Chart.defaults.color = "#64748b";
      Chart.defaults.font.size = 12;

      this.renderTrend();
      this.renderExpenseDonut();
      this.renderMonthlyBar();
      this.renderDailyCashflow();
    },

    getTooltip() {
      return {
        backgroundColor: "#0f172a",
        padding: 10,
        cornerRadius: 6,
        titleFont: { size: 12, weight: "600" },
        bodyFont: { size: 12 },
        displayColors: true,
      };
    },

    renderTrend() {
      const ctx = Utils.qs("[data-chart-trend]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const t = FinanceTrackData.trend;
      this.chartRefs.trend = new Chart(ctx, {
        type: "line",
        data: {
          labels: t.labels,
          datasets: [
            {
              label: "Pemasukan",
              data: t.pemasukan,
              borderColor: "#2563eb",
              backgroundColor: "rgba(37,99,235,0.10)",
              borderWidth: 2,
              pointRadius: 3,
              pointBackgroundColor: "#2563eb",
              pointBorderColor: "#fff",
              pointBorderWidth: 2,
              tension: 0.35,
              fill: true,
            },
            {
              label: "Pengeluaran",
              data: t.pengeluaran,
              borderColor: "#e11d48",
              backgroundColor: "rgba(225,29,72,0.08)",
              borderWidth: 2,
              pointRadius: 3,
              pointBackgroundColor: "#e11d48",
              pointBorderColor: "#fff",
              pointBorderWidth: 2,
              tension: 0.35,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: "index", intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                label: (ctx) => ` ${ctx.dataset.label}: ${Utils.formatRupiah(ctx.parsed.y)}`,
              },
            },
          },
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              grid: { color: "#e2e8f0", drawTicks: false },
              border: { display: false },
              ticks: {
                callback: (v) => Utils.formatRupiahShort(v),
                padding: 8,
              },
            },
          },
        },
      });
    },

    renderExpenseDonut() {
      const ctx = Utils.qs("[data-chart-expense]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const items = FinanceTrackData.expenseComposition;
      const total = items.reduce((sum, i) => sum + i.nilai, 0);

      this.chartRefs.expense = new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: items.map((i) => i.nama),
          datasets: [
            {
              data: items.map((i) => i.nilai),
              backgroundColor: items.map((i) => i.warna),
              borderColor: "#fff",
              borderWidth: 2,
              hoverOffset: 6,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "68%",
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                label: (ctx) => {
                  const pct = Utils.percent(ctx.parsed, total);
                  return ` ${ctx.label}: ${Utils.formatRupiah(ctx.parsed)} (${pct}%)`;
                },
              },
            },
          },
        },
      });

      // Total di tengah donut.
      const centerEl = Utils.qs("[data-chart-expense-total]");
      if (centerEl) {
        centerEl.innerHTML =
          '<span class="text-secondary" style="font-size:var(--text-xs);text-transform:uppercase;font-weight:600">Total</span>' +
          '<span style="font-size:var(--text-lg);font-weight:700">' +
          Utils.formatRupiahShort(total) +
          "</span>";
      }

      // Legenda kustom (Chart.js legend dimatikan agar gaya konsisten dengan HTML).
      const legendEl = Utils.qs("[data-chart-expense-legend]");
      if (legendEl) {
        legendEl.innerHTML = items
          .map(
            (i) =>
              '<li class="legend-list__item">' +
              '<span class="legend-list__swatch" style="background:' + i.warna + '"></span>' +
              '<span class="legend-list__label">' + Utils.escapeHtml(i.nama) + "</span>" +
              '<span class="legend-list__value numeric">' +
              Utils.formatRupiah(i.nilai) +
              "</span>" +
              '<span class="legend-list__pct numeric">' +
              Utils.percent(i.nilai, total).toFixed(1) +
              "%</span></li>"
          )
          .join("");
      }
    },

    /**
     * Arus kas harian (halaman Laporan) — dihitung dari `transactions`,
     * bukan dari angka hardcode, sehingga konsisten dengan tabel rekap.
     */
    renderDailyCashflow() {
      const ctx = Utils.qs("[data-chart-cashflow]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const upto = FinanceTrackData.summary.tanggal;
      const labels = [];
      const income = [];
      const expense = [];

      for (let day = 1; day <= upto; day++) {
        const iso = "2024-10-" + String(day).padStart(2, "0");
        const sameDay = FinanceTrackData.transactions.filter((t) => t.tanggal === iso);

        labels.push(String(day));
        income.push(
          sameDay
            .filter((t) => t.tipe === "income")
            .reduce((s, t) => s + t.jumlah, 0)
        );
        expense.push(
          sameDay
            .filter((t) => t.tipe === "expense")
            .reduce((s, t) => s + t.jumlah, 0)
        );
      }

      this.chartRefs.cashflow = new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: "Pemasukan",
              data: income,
              backgroundColor: "#0d9488",
              borderRadius: 3,
              maxBarThickness: 14,
            },
            {
              label: "Pengeluaran",
              data: expense,
              backgroundColor: "rgba(225,29,72,0.75)",
              borderRadius: 3,
              maxBarThickness: 14,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: "index", intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                title: (items) => "Tanggal " + items[0].label + " Oktober 2024",
                label: (c) => ` ${c.dataset.label}: ${Utils.formatRupiah(c.parsed.y)}`,
              },
            },
          },
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              grid: { color: "#e2e8f0", drawTicks: false },
              border: { display: false },
              ticks: {
                callback: (v) => Utils.formatRupiahShort(v),
                padding: 8,
              },
            },
          },
        },
      });
    },

    renderMonthlyBar() {
      const ctx = Utils.qs("[data-chart-monthly]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const recap = FinanceTrackData.monthlyRecap.slice().reverse();

      this.chartRefs.monthly = new Chart(ctx, {
        type: "bar",
        data: {
          labels: recap.map((r) => r.bulan.split(" ")[0].slice(0, 3)),
          datasets: [
            {
              label: "Pemasukan",
              data: recap.map((r) => r.pemasukan),
              backgroundColor: "#2563eb",
              borderRadius: 4,
              maxBarThickness: 28,
            },
            {
              label: "Pengeluaran",
              data: recap.map((r) => r.pengeluaran),
              backgroundColor: "rgba(225,29,72,0.75)",
              borderRadius: 4,
              maxBarThickness: 28,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                label: (ctx) => ` ${ctx.dataset.label}: ${Utils.formatRupiah(ctx.parsed.y)}`,
              },
            },
          },
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              grid: { color: "#e2e8f0", drawTicks: false },
              border: { display: false },
              ticks: {
                callback: (v) => Utils.formatRupiahShort(v),
                padding: 8,
              },
            },
          },
        },
      });
    },
  };

  /* =======================================================================
     8. DASHBOARD — interaksi kecil
     ======================================================================= */
  const Dashboard = {
    init() {
      // Sembunyikan/tampilkan saldo (ikon mata).
      const toggle = Utils.qs("[data-balance-toggle]");
      const value = Utils.qs("[data-balance-value]");
      if (toggle && value) {
        let hidden = false;
        const real = value.textContent;
        toggle.addEventListener("click", () => {
          hidden = !hidden;
          value.textContent = hidden ? "Rp ••••••••" : real;
          const icon = Utils.qs(".material-symbols-outlined", toggle);
          if (icon) icon.textContent = hidden ? "visibility_off" : "visibility";
        });
      }

      // Filter periode chart (visual toggle saja di Milestone 2).
      const periodBtns = Utils.qsa("[data-chart-period]");
      periodBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
          periodBtns.forEach((b) => b.classList.remove("is-active"));
          btn.classList.add("is-active");
        });
      });
    },
  };

  /* =======================================================================
     BOOTSTRAP
     ======================================================================= */

  /* Namespace diekspos secara sinkron (bukan di dalam DOMContentLoaded)
     supaya inline script tiap halaman selalu bisa memanggil modul di bawah
     yang dijalankan setelah DOM siap. */
  window.FinanceTrack = {
    Utils,
    Navigation,
    Feedback,
    TransactionTable,
    TransactionForm,
    BudgetUI,
    Charts,
  };

  document.addEventListener("DOMContentLoaded", () => {
    Navigation.init();
    Feedback.init();
    TransactionTable.init();
    TransactionForm.init();
    BudgetUI.init();
    Dashboard.init();
    Charts.init();
  });
})();
