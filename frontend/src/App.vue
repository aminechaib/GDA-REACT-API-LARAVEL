<template>
  <div id="app-container">
<header class="app-header">
      <div class="header-left">
        <button class="mobile-menu-btn" @click="showMobileMenu = !showMobileMenu" v-if="isMobile">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
        <div class="auth-section" v-if="!user">
          <button class="login-btn glass-btn" @click="showLogin = true">Login</button>
          <button class="register-btn glass-btn" @click="showRegister = true">Register</button>
        </div>
        <div class="user-section" v-else>
          <span class="user-name">{{ user.name }}</span>
          <button class="logout-btn glass-btn" @click="logout">Logout</button>
        </div>
        <h1 v-if="user">Welcome, {{ user.name }}!</h1>
        <h1 v-else>GESTION D'ARRIVAGE</h1>
        <button class="import-btn glass-btn" @click="showImportModal = true" v-if="user">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Import Excel
        </button>
        <div class="export-group">
          <button class="export-btn glass-btn" @click="exportToExcel(exportMode)" :title="exportMode === 'filtered' ? 'Export current filtered view' : 'Export all products'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
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

    <!-- Login Modal -->
    <div v-if="showLogin" class="modal-overlay" @click.self="showLogin = false">
      <div class="modal-card">
        <h2>Login</h2>
        <form @submit.prevent="login">
          <input v-model="loginForm.email" type="email" placeholder="Email" required>
          <input v-model="loginForm.password" type="password" placeholder="Password" required>
          <button type="submit" class="btn-primary">Login</button>
          <button type="button" @click="showLogin = false" class="btn-secondary">Cancel</button>
        </form>
      </div>
    </div>

    <!-- Register Modal -->
    <div v-if="showRegister" class="modal-overlay" @click.self="showRegister = false">
      <div class="modal-card">
        <h2>Register</h2>
        <form @submit.prevent="register">
          <input v-model="registerForm.name" type="text" placeholder="Name" required>
          <input v-model="registerForm.email" type="email" placeholder="Email" required>
          <input v-model="registerForm.password" type="password" placeholder="Password" required minlength="8">
          <input v-model="registerForm.password_confirmation" type="password" placeholder="Confirm Password" required>
          <button type="submit" class="btn-primary">Register</button>
          <button type="button" @click="showRegister = false" class="btn-secondary">Cancel</button>
        </form>
      </div>
    </div>

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


const user = ref(null);
const showLogin = ref(false);
const showRegister = ref(false);
const loginForm = ref({ email: '', password: '' });
const registerForm = ref({ name: '', email: '', password: '', password_confirmation: '' });

const login = async () => {
  try {
    const response = await axios.post('/login', loginForm.value);
    localStorage.setItem('auth_token', response.data.token);
    user.value = response.data.user;
    showLogin.value = false;
    showToast('success', 'Login Success', 'Welcome back!');
    loadFromServer();
    loadFournisseurs();
  } catch (error) {
    showToast('error', 'Login Failed', error.response?.data?.message || 'Invalid credentials');
  }
};

const register = async () => {
  try {
    const response = await axios.post('/register', registerForm.value);
    localStorage.setItem('auth_token', response.data.token);
    user.value = response.data.user;
    showRegister.value = false;
    showToast('success', 'Registration Success', 'Account created!');
    loadFromServer();
    loadFournisseurs();
  } catch (error) {
    showToast('error', 'Registration Failed', error.response?.data?.message || 'Registration failed');
  }
};

const logout = async () => {
  try {
    await axios.post('/logout');
  } catch (e) {}
  localStorage.removeItem('auth_token');
  user.value = null;
  showToast('info', 'Logged Out', 'Goodbye!');
  loadFromServer();
  loadFournisseurs();
};

onMounted(() => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    // Verify token by fetching /user
    axios.get('/user').then(response => {
      user.value = response.data;
    }).catch(() => {
      localStorage.removeItem('auth_token');
    });
  }
  // ... rest of onMounted
});

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
const searchValue = ref('');
const showImportModal = ref(false);
const totalValue = ref(0);
const uniqueFournisseurs = ref(0);
const recentImports = ref(0);
const showFilters = ref(false);
const exportMode = ref('filtered');

// Filters
const filters = ref({
  dateFrom: '',
  dateTo: '',
  fournisseur: '',
  minPrice: '',
  maxPrice: ''
});

// Shared debounce vars/functions - declare before watches
let debounceTimer;

const debounceHandler = (delay) => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => loadFromServer(), delay);
};

// Auto-reactivity for filters (500ms)
watch(filters, () => debounceHandler(500), { deep: true });

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

// In App.vue script setup

const exportToExcel = () => {
  try {
    showToast('info', `Exporting ${exportMode.value === 'filtered' ? 'filtered' : 'all'} products...`, 'Server is generating Excel file. Download will start shortly.');

    let params = {};

    if (exportMode.value === 'filtered') {
      // Build params from current filtered state
      params = {
        ref: searchRef.value,
        marque: searchMarque.value,
        date: searchDate.value,
        fournisseur: searchFournisseur.value,
        designation: searchDesignation.value,
        searchValue: searchValue.value,
        dateFrom: filters.value.dateFrom,
        dateTo: filters.value.dateTo,
        // Use the correct key for the advanced filter supplier
        fournisseur: filters.value.fournisseur || searchFournisseur.value,
        minPrice: filters.value.minPrice,
        maxPrice: filters.value.maxPrice,
      };
    }
    // For 'all' mode, params remains an empty object.

    // Filter out empty parameters to create a clean URL
    const filteredParams = Object.entries(params)
      .filter(([_, value]) => value !== '' && value !== null)
      .reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {});

    // Construct the URL with query parameters
    const queryString = new URLSearchParams(filteredParams).toString();
    const url = `http://127.0.0.1:8000/api/products/export?${queryString}`;

    // Use a hidden anchor tag to trigger the download
    const link = document.createElement('a' );
    link.href = url;
    // The 'download' attribute is not strictly necessary but good practice
    const modeText = exportMode.value === 'filtered' ? 'filtered' : 'all';
    link.setAttribute('download', `products_${modeText}_export_${new Date().toISOString().split('T')[0]}.xlsx`);
    link.style.display = 'none';
    document.body.appendChild(link);

    link.click(); // This directly triggers the browser's download mechanism

    document.body.removeChild(link);

    // Note: We can't easily show a "success" toast here because we are no longer
    // "awaiting" a response in JavaScript. The browser handles it in a separate process.
    // The initial "Exporting..." message is the best we can do.

  } catch (error) {
    // This catch block will now only catch errors from building the URL, not the download itself.
    console.error('Failed to initiate export:', error);
    showToast('error', 'Export Failed', 'Could not create the download link. Check console.');
  }
};


onMounted(() => {
  const saved = localStorage.getItem('darkMode');
  isDark.value = saved === 'true';
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  }

  const token = localStorage.getItem('auth_token');
  if (token) {
    axios.get('/user').then(response => {
      user.value = response.data;
    }).catch(() => {
      localStorage.removeItem('auth_token');
    });
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
      searchValue: searchValue.value,
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

    serverItemsLength.value = response.data.total;

    // Stats from current page (for dashboard, use full query if needed)
    const pageItems = response.data.rows;
    totalValue.value = pageItems.reduce((sum, item) => sum + (parseFloat(item.prix) || 0), 0);
    uniqueFournisseurs.value = new Set(pageItems.map(item => item.fournisseur).filter(Boolean)).size;

    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
    recentImports.value = pageItems.filter(item => {
      const itemDate = new Date(item.date);
      return itemDate >= sevenDaysAgo;
    }).length;

  } catch (error) {
    console.error("Failed to fetch data:", error);
    alert("Failed to load data from the server. Check console for details.");
  }
  loading.value = false;
};

// Quick search + pagination (300ms)
watch([serverOptions, searchRef, searchMarque, searchDate, searchFournisseur, searchDesignation, searchValue], () => debounceHandler(300), { deep: true });

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

.export-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.export-mode {
  background: rgba(255,255,255,0.2);
  border: 1px solid rgba(255,255,255,0.3);
  color: white;
  border-radius: 8px;
  min-width: 80px;
}

.dark .export-mode {
  background: rgba(0,0,0,0.3);
  border-color: rgba(255,255,255,0.2);
}

.auth-section, .user-section {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.login-btn, .register-btn, .logout-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.2s;
}

.login-btn {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.register-btn {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.logout-btn {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.user-name {
  font-weight: 500;
  color: white;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-card {
  background: var(--bg-card);
  padding: 2rem;
  border-radius: 12px;
  max-width: 400px;
  width: 90%;
  box-shadow: var(--shadow);
  max-height: 90vh;
  overflow-y: auto;
}

.modal-card h2 {
  margin-top: 0;
  color: var(--text-primary);
  text-align: center;
}

.modal-card input {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 1rem;
  border: 1px solid var(--border-light);
  border-radius: 6px;
  background: var(--bg-card);
  color: var(--text-primary);
  box-sizing: border-box;
}

.modal-card input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(29, 99, 140, 0.1);
}

.modal-card button {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background: var(--primary-dark);
}

.btn-secondary {
  background: var(--border-light);
  color: var(--text-primary);
}

.btn-secondary:hover {
  background: var(--text-secondary);
  color: white;
}

.dark .modal-card {
  background: var(--bg-card);
}

.dark .modal-card input {
  background: var(--bg-card);
  color: var(--text-primary);
  border-color: var(--border-light);
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
