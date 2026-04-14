<template>
  <div id="app-container">
    <header class="app-header">
      <div class="header-left">
        <h1>GESTION D'ARRIVAGE</h1>
        <button class="import-btn glass-btn" @click="showImportModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Import Excel
        </button>
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

    <div class="table-container">
      <!-- The key change is REMOVING the :table-height prop -->
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
    </div>

    <!-- Loading overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <!-- Import Modal -->
    <ImportModal v-if="showImportModal" :show-modal="showImportModal" @close="showImportModal = false" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import ImportModal from './components/ImportModal.vue';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import axios from 'axios';

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
const searchValue = ref('');
const showImportModal = ref(false);

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

onMounted(() => {
  const saved = localStorage.getItem('darkMode');
  isDark.value = saved === 'true';
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  }
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
    const response = await axios.get('http://127.0.0.1:8000/api/products', {
      params: {
        page: serverOptions.value.page,
        rowsPerPage: serverOptions.value.rowsPerPage,
        sortBy: serverOptions.value.sortBy,
        sortType: serverOptions.value.sortType,
        searchValue: searchValue.value,
      }
    } );
    // Format date for display
    items.value = response.data.rows.map(item => ({
      ...item,
      date_display: formatDate(item.date)
    }));
    serverItemsLength.value = response.data.total;
  } catch (error) {
    console.error("Failed to fetch data:", error);
    alert("Failed to load data from the server. Check console for details.");
  }
  loading.value = false;
};

let debounceTimer;
watch([serverOptions, searchValue], () => {
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

/* --- FULL PAGE STYLES --- */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--color-background, var(--bg-light));
  color: var(--color-text, var(--text-primary));
  transition: background-color 0.3s, color 0.3s;
}

/* Ensure full space */
#app, main {
  height: 100%;
  width: 100%;
  padding: 0;
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

@media (min-width: 768px) {
  #app-container {
    padding: 1.5rem;
  }
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

.import-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 12px;
  padding: 0.75rem 1.5rem;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  transition: all 0.3s;
  backdrop-filter: blur(10px);
}

.import-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
}

.import-btn svg {
  width: 20px;
  height: 20px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.app-header h1 {
  font-size: 1.8rem;
  margin: 0;
  font-weight: 700;
}

.theme-toggle {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 44px;
  height: 44px;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.theme-toggle:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.icon {
  width: 20px;
  height: 20px;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 1rem;
  width: 20px;
  height: 20px;
  color: var(--text-secondary);
}

.search-wrapper input {
  padding: 0.75rem 1rem 0.75rem 3rem;
  border-radius: 12px;
  border: 2px solid var(--border-light);
  font-size: 1rem;
  width: 320px;
  background: var(--bg-card);
  color: var(--text-primary);
  transition: all 0.3s ease;
}

.search-wrapper input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 4px rgba(29, 99, 140, 0.1);
}

@media (max-width: 640px) {
  .search-wrapper input {
    width: 250px;
  }
}

.table-container {
  flex-grow: 1;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: var(--shadow);
  background: var(--bg-card);
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

.modern-table .modern-body {
  flex-grow: 1;
  overflow-y: auto;
}

/* Responsive table scroll */
@media (max-width: 768px) {
  .modern-table .modern-body {
    overflow-x: auto;
  }
}

/* Striped rows & hover */
.modern-table tbody tr:nth-child(even) {
  background: rgba(0, 0, 0, 0.02);
}

.dark .modern-table tbody tr:nth-child(even) {
  background: rgba(255, 255, 255, 0.05);
}

.modern-table tbody tr:hover {
  background: rgba(29, 99, 140, 0.1) !important;
}

.dark .modern-table tbody tr:hover {
  background: rgba(29, 99, 140, 0.2) !important;
}

.modern-header {
  font-weight: 600;
  color: var(--text-primary);
  border-bottom: 2px solid var(--border-light);
  flex-shrink: 0;
  background: var(--bg-card);
}

.vue3-easy-data-table__footer {
  border-top: 1px solid var(--border-light);
  padding-top: 1rem;
  flex-shrink: 0;
  background: var(--bg-card);
}

/* Loading overlay */
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
  backdrop-filter: blur(2px);
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
