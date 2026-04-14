<template>
  <div id="app-container">
    <header class="app-header">
      <div class="header-left">
        <button class="mobile-menu-btn" @click="showMobileMenu = !showMobileMenu" v-if="isMobile">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
        <h1>GESTION D'ARRIVAGE</h1>
        <button class="import-btn glass-btn" @click="showImportModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Import Excel
        </button>
        <button class="export-btn glass-btn" @click="exportToExcel">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Export Excel
        </button>

        <div class="filter-section">
          <button class="filter-btn glass-btn" @click="showFilters = !showFilters">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filters
          </button>
        </div>
        <button class="theme-toggle" @click="toggleDark" :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'">
          <svg v-if="isDark" class="icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.707 2.707a1 1 0 010 1.414L13.414 9a1 1 0 01-1.414 0L11 8.414l-1 1-2.293-2.293a1 1 0 011.414-1.414L10 7.586l2.293-2.293a1 1 0 011.414 0zM10 15a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-5.657-4.707a1 1 0 011.414 0l1.293 1.293a1 1 0 01-1.414 1.414L4.343 12.414a1 1 0 010-1.414zM10 11a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          <svg v-else class="icon" viewBox="0 0 20 20" fill="currentColor">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
          </svg>
        </button>
      </div>
      <div class="search-wrapper">
        <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
        </svg>
        <input type="text" id="search" v-model="searchValue" placeholder="Search products...">
      </div>
    </header>

    <!-- Advanced Filters -->
    <div v-if="showFilters" class="filters-panel glass-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Date From</label>
          <input type="date" v-model="filters.dateFrom" class="glass-input">
        </div>
        <div class="filter-group">
          <label>Date To</label>
          <input type="date" v-model="filters.dateTo" class="glass-input">
        </div>
        <div class="filter-group">
          <label>Fournisseur</label>
          <select v-model="filters.fournisseur" class="glass-input">
            <option value="">All Suppliers</option>
            <option v-for="sup in fournisseurs" :key="'filter-'+sup.id" :value="sup.nom">{{ sup.nom }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Min Price</label>
          <input type="number" v-model="filters.minPrice" placeholder="0" class="glass-input">
        </div>
        <div class="filter-group">
          <label>Max Price</label>
          <input type="number" v-model="filters.maxPrice" placeholder="999999" class="glass-input">
        </div>
        <div class="filter-actions">
          <button class="btn-secondary" @click="clearFilters">Clear</button>
          <button class="btn-primary" @click="applyFilters">Apply</button>
        </div>
      </div>
    </div>

    <!-- Quick Search Row -->
    <div class="quick-search-grid">
      <div class="search-group">
        <label>Reference</label>
        <input v-model="searchRef" placeholder="Search by ref..." class="search-input">
      </div>
      <div class="search-group">
        <label>Marque</label>
        <input v-model="searchMarque" placeholder="Search by marque..." class="search-input">
      </div>
      <div class="search-group">
        <label>Date</label>
        <input type="date" v-model="searchDate" class="search-input">
      </div>
      <div class="search-group">
        <label>Fournisseur</label>
        <input v-model="searchFournisseur" placeholder="Search by fournisseur..." class="search-input">
      </div>
      <div class="search-group">
        <label>Produit</label>
        <input v-model="searchDesignation" placeholder="Search by designation..." class="search-input">
      </div>
    </div>
    <EasyDataTable
      :headers="headers"
      :items="items"
      :loading="loading"
      :server-items-length="serverItemsLength"
      v-model:server-options="serverOptions"
      :rows-per-page-options="[10, 50, 100, 300, 1000]"
      theme-color="#1d638c"
      table-class-name="modern-table"
      header-class-name="modern-header"
      body-class-name="modern-body"
      buttons-pagination
      fixed-header
    />

    <!-- Loading overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <!-- Import Modal -->
    <ImportModal v-if="showImportModal" :show-modal="showImportModal" @close="showImportModal = false" />

    <!-- Toast Notifications -->
    <Toast
      :show="toast.show"
      :type="toast.type"
      :title="toast.title"
      :message="toast.message"
      @close="hideToast"
    />
  </div>
</template>

<script setup>
// [script content - unchanged from previous]
import { ref, watch, onMounted } from 'vue';
import ImportModal from './components/ImportModal.vue';
import './assets/new-ui.css';
import Toast from './components/Toast.vue';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import axios from 'axios';
import * as XLSX from 'xlsx';

const headers = [
  { text: "ID", value: "id", sortable: true, width: 80 },
  { text: "Reference", value: "ref", sortable: true },
  { text: "Designation", value: "designation", sortable: true, width: 300 },
  { text: "Marque", value: "marque", sortable: true },
  { text: "Prix", value: "prix", sortable: true },
  { text: "Date", value: "date_display", sortable: true, width: 140 },
  { text: "Fournisseur", value: "fournisseur", sortable: true },
];

const items = ref([]);
const loading = ref(true);
const serverItemsLength = ref(0);
const searchRef = ref('');
const searchMarque = ref('');
const searchDate = ref('');
const searchFournisseur = ref('');
const searchDesignation = ref('');
const showImportModal = ref(false);
const totalValue = ref(0);
const uniqueFournisseurs = ref(0);
const recentImports = ref(0);
const showFilters = ref(false);

// Filters
const filters = ref({
  dateFrom: '',
  dateTo: '',
  fournisseur: '',
  minPrice: '',
  maxPrice: ''
});

const fournisseurs = ref([]);
const showMobileMenu = ref(false);
const isMobile = ref(false);

// Toast system
const toast = ref({
  show: false,
  type: 'info',
  title: '',
  message: ''
});

const showToast = (type, title, message, duration = 5000) => {
  toast.value = { show: true, type, title, message };
  if (duration > 0) {
    setTimeout(() => {
      toast.value.show = false;
    }, duration);
  }
};

const hideToast = () => {
  toast.value.show = false;
};

const loadFournisseurs = async () => {
  try {
    const { data } = await axios.get('http://127.0.0.1:8000/api/fournisseurs');
    fournisseurs.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to load fournisseurs', error);
  }
};

const applyFilters = () => {
  loadFromServer();
  showToast('success', 'Filters Applied', 'Products filtered successfully');
};

const clearFilters = () => {
  filters.value = {
    dateFrom: '',
    dateTo: '',
    fournisseur: '',
    minPrice: '',
    maxPrice: ''
  };
  loadFromServer();
  showToast('info', 'Filters Cleared', 'All filters have been reset');
};

const serverOptions = ref({
  page: 1,
  rowsPerPage: 100,
  sortBy: 'id',
  sortType: 'asc',
});

const isDark = ref(false);

const toggleDark = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('darkMode', isDark.value);
  document.documentElement.classList.toggle('dark');
};

const exportToExcel = async () => {
  try {
    showToast('info', 'Exporting...', 'Preparing your Excel file');

    const response = await axios.get('http://127.0.0.1:8000/api/products', {
      params: {
        page: 1,
        rowsPerPage: 10000,
        sortBy: 'id',
        sortType: 'asc',
      }
    });

    const exportData = response.data.rows.map(item => ({
      ID: item.id,
      Reference: item.ref,
      Designation: item.designation,
      Marque: item.marque,
      Prix: item.prix,
      Date: formatDate(item.date),
      Fournisseur: item.fournisseur
    }));

    const worksheet = XLSX.utils.json_to_sheet(exportData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Products');

    const fileName = `products_export_${new Date().toISOString().split('T')[0]}.xlsx`;
    XLSX.writeFile(workbook, fileName);

    showToast('success', 'Export Complete', `File saved as ${fileName}`);

  } catch (error) {
    console.error('Export failed:', error);
    showToast('error', 'Export Failed', 'Could not export data. Check console for details.');
  }
};

onMounted(() => {
  const saved = localStorage.getItem('darkMode');
  isDark.value = saved === 'true';
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  }
  loadFournisseurs();

  const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
  };
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'numeric',
    day: 'numeric',
    year: 'numeric'
  });
};

const loadFromServer = async () => {
  loading.value = true;
  try {
    const params = {
      page: serverOptions.value.page,
      rowsPerPage: serverOptions.value.rowsPerPage,
      sortBy: serverOptions.value.sortBy,
      sortType: serverOptions.value.sortType,
      ref: searchRef.value,
      marque: searchMarque.value,
      date: searchDate.value,
      fournisseur: searchFournisseur.value,
      designation: searchDesignation.value,
    };

    if (filters.value.dateFrom) params.dateFrom = filters.value.dateFrom;
    if (filters.value.dateTo) params.dateTo = filters.value.dateTo;
    if (filters.value.fournisseur) params.fournisseur = filters.value.fournisseur;
    if (filters.value.minPrice) params.minPrice = filters.value.minPrice;
    if (filters.value.maxPrice) params.maxPrice = filters.value.maxPrice;

    const response = await axios.get('http://127.0.0.1:8000/api/products', { params });
    items.value = response.data.rows.map(item => ({
      ...item,
      date_display: formatDate(item.date)
    }));
    serverItemsLength.value = response.data.total;

    const allItems = response.data.rows;
    totalValue.value = allItems.reduce((sum, item) => sum + (parseFloat(item.prix) || 0), 0);
    uniqueFournisseurs.value = new Set(allItems.map(item => item.fournisseur).filter(Boolean)).size;

    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
    recentImports.value = allItems.filter(item => {
      const itemDate = new Date(item.date);
      return itemDate >= sevenDaysAgo;
    }).length;

  } catch (error) {
    console.error("Failed to fetch data:", error);
    alert("Failed to load data from the server. Check console for details.");
  }
  loading.value = false;
};

let debounceTimer;
watch([serverOptions, searchRef, searchMarque, searchDate, searchFournisseur, searchDesignation], () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    loadFromServer();
  }, 300);
}, { deep: true });

loadFromServer();
</script>

<style>
:root {
  --primary-color: #1d638c;
  --primary-dark: #16547a;
  --bg-light: #f8f9ff;
  --bg-card: #ffffff;
  --text-primary: #2c3e50;
  --text-secondary: #64748b;
  --border-light: #e2e8f0;
  --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.dark {
  --bg-light: #0f172a;
  --bg-card: #1e293b;
  --text-primary: #f1f5f9;
  --text-secondary: #94a3b8;
  --border-light: #334155;
  --shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg-light);
  color: var(--text-primary);
  transition: background-color 0.3s, color 0.3s;
}

#app-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  padding: 1rem;
  background: var(--bg-light);
  transition: background 0.3s;
  box-sizing: border-box;
}

.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-shrink: 0;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  padding: 1.5rem 2rem;
  border-radius: 12px;
  color: white;
  box-shadow: var(--shadow);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-radius: 12px;
  background: var(--bg-card);
  box-shadow: var(--shadow);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-content h3 {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
}

.stat-content p {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.modern-table {
  flex-grow: 1;
  border: none;
  border-radius: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  height: 100%;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid var(--border-light);
  border-top: 4px solid var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
