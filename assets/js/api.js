/**
 * FinanceTrack — Client-side API Service
 * Komunikasi sinkronisasi antara Frontend dan Backend PHP
 */

(function () {
  "use strict";

  const API_BASE = (function () {
    // Tentukan root path yang fleksibel baik saat di /pages/ maupun di root /
    const path = window.location.pathname;
    if (path.includes("/pages/")) {
      return "../api";
    }
    return "api";
  })();

  const ApiService = {
    /**
     * Helper fetch wrapper dengan penanganan JSON dan error terstandarisasi
     */
    async request(endpoint, options = {}) {
      const url = `${API_BASE}/${endpoint.replace(/^\//, "")}`;
      const defaultOptions = {
        headers: {},
        credentials: "same-origin",
      };

      const config = { ...defaultOptions, ...options };

      // Jangan set Content-Type jika body adalah FormData (biarkan browser set boundary)
      if (config.body && !(config.body instanceof FormData)) {
        if (!config.headers["Content-Type"]) {
          config.headers["Content-Type"] = "application/json";
        }
      }

      try {
        const response = await fetch(url, config);
        const data = await response.json().catch(() => null);

        if (!response.ok) {
          const message = data?.message || `Terjadi kesalahan (HTTP ${response.status})`;
          const error = new Error(message);
          error.status = response.status;
          error.data = data;
          throw error;
        }

        return data;
      } catch (err) {
        // Log untuk keperluan debugging
        console.warn(`[API] ${endpoint} request failed:`, err.message);
        throw err;
      }
    },

    /* --- Auth --- */
    async getMe() {
      return this.request("auth/me.php");
    },

    async getCurrentUser() {
      try {
        const res = await this.getMe();
        return res?.authenticated ? (res?.data?.user || res?.user) : null;
      } catch (e) {
        return null;
      }
    },

    async logout() {
      return this.request("auth/logout.php", { method: "POST" });
    },

    /* --- Transaksi --- */
    async getTransactions(params = {}) {
      const qs = new URLSearchParams(params).toString();
      return this.request(`transactions/list.php${qs ? "?" + qs : ""}`);
    },

    async createTransaction(formDataOrObj, file) {
      let body;
      const receiptFile = file || (formDataOrObj && typeof File !== "undefined" && formDataOrObj.receipt_file instanceof File ? formDataOrObj.receipt_file : null);
      if (typeof FormData !== "undefined" && formDataOrObj instanceof FormData) {
        body = formDataOrObj;
        if (receiptFile && !body.has("struk")) {
          body.append("struk", receiptFile);
        }
      } else if (receiptFile && typeof FormData !== "undefined") {
        body = new FormData();
        Object.keys(formDataOrObj).forEach((k) => {
          if (k !== "receipt_file" && formDataOrObj[k] !== undefined && formDataOrObj[k] !== null) {
            body.append(k, formDataOrObj[k]);
          }
        });
        body.append("struk", receiptFile);
      } else {
        body = JSON.stringify(formDataOrObj);
      }
      return this.request("transactions/create.php", {
        method: "POST",
        body,
      });
    },

    async updateTransaction(id, formDataOrObj, file) {
      let body;
      const receiptFile = file || (formDataOrObj && typeof File !== "undefined" && formDataOrObj.receipt_file instanceof File ? formDataOrObj.receipt_file : null);
      if (typeof FormData !== "undefined" && formDataOrObj instanceof FormData) {
        if (!formDataOrObj.has("id")) formDataOrObj.append("id", id);
        if (receiptFile && !formDataOrObj.has("struk")) formDataOrObj.append("struk", receiptFile);
        body = formDataOrObj;
      } else if (receiptFile && typeof FormData !== "undefined") {
        body = new FormData();
        body.append("id", id);
        Object.keys(formDataOrObj).forEach((k) => {
          if (k !== "receipt_file" && formDataOrObj[k] !== undefined && formDataOrObj[k] !== null) {
            body.append(k, formDataOrObj[k]);
          }
        });
        body.append("struk", receiptFile);
      } else {
        body = JSON.stringify({ ...formDataOrObj, id });
      }
      return this.request("transactions/update.php", {
        method: "POST",
        body,
      });
    },

    async deleteTransaction(id) {
      return this.request("transactions/delete.php", {
        method: "POST",
        body: JSON.stringify({ id }),
      });
    },

    /* --- Anggaran --- */
    async getBudgets(month, year) {
      const params = {};
      if (month) params.month = month;
      if (year) params.year = year;
      const qs = new URLSearchParams(params).toString();
      return this.request(`budgets/list.php${qs ? "?" + qs : ""}`);
    },

    async saveBudget(data) {
      return this.request("budgets/create.php", {
        method: "POST",
        body: JSON.stringify(data),
      });
    },

    async deleteBudget(categoryId) {
      return this.request("budgets/delete.php", {
        method: "POST",
        body: JSON.stringify({ category_id: categoryId }),
      });
    },

    /* --- Dashboard --- */
    async getDashboardSummary() {
      return this.request("dashboard/summary.php");
    },

    /* --- Laporan --- */
    async getReportSummary(params = {}) {
      const qs = new URLSearchParams(params).toString();
      return this.request(`reports/summary.php${qs ? "?" + qs : ""}`);
    },
  };

  window.FinanceTrackAPI = ApiService;
})();

