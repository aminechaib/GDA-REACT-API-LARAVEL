<template>
  <div id="app-container">
    <header class="app-header">
      <h1>Product Data Table</h1>
      <div class="search-wrapper">
        <input type="text" id="search" v-model="searchValue" placeholder="Search products...">
      </div>
    </header>

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
</template>

<script setup>
// The <script> section remains exactly the same as before.
import { ref, watch } from 'vue';
import EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import axios from 'axios';

const headers = [
  { text: "ID", value: "id", sortable: true, width: 80 },
  { text: "Reference", value: "ref", sortable: true },
  { text: "Designation", value: "designation", sortable: true, width: 300 },
  { text: "Marque", value: "marque", sortable: true },
  { text: "Prix", value: "prix", sortable: true },
  { text: "Fournisseur", value: "fournisseur", sortable: true },
];

const items = ref([]);
const loading = ref(true);
const serverItemsLength = ref(0);
const searchValue = ref('');

const serverOptions = ref({
  page: 1,
  rowsPerPage: 100,
  sortBy: 'id',
  sortType: 'asc',
});

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
    items.value = response.data.rows;
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
/* --- FULL PAGE STYLES --- */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  background-color: #f4f7fa;
}

/* --- START OF FIX --- */
/* Ensure the root #app element and our main tag take up full space */
#app, main {
  height: 100%;
  width: 100%;
  padding: 0; /* Remove default padding */
}
/* --- END OF FIX --- */

#app-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  padding: 1.5rem;
  box-sizing: border-box;
}

.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-shrink: 0;
}

.app-header h1 {
  color: #2c3e50;
  font-size: 1.8rem;
  margin: 0;
}

.search-wrapper input {
  padding: 0.6rem 1rem;
  border-radius: 6px;
  border: 1px solid #dde1e7;
  font-size: 1rem;
  width: 350px;
  transition: box-shadow 0.2s ease;
}
.search-wrapper input:focus {
  outline: none;
  border-color: #1d638c;
  box-shadow: 0 0 0 3px rgba(29, 99, 140, 0.2);
}

.modern-table {
  flex-grow: 1;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modern-header {
  background-color: #ffffff;
  font-weight: 600;
  color: #34495e;
  border-bottom: 2px solid #eef0f2;
  flex-shrink: 0;
}

.modern-body {
  flex-grow: 1;
  overflow-y: auto;
  height: 100%;
}

.vue3-easy-data-table__footer {
  border-top: 1px solid #eef0f2;
  padding-top: 1rem;
  flex-shrink: 0;
}
</style>
