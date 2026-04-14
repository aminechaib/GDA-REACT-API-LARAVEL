<template>
  <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
    <div class="modal glass animate-pop-in">
      <div class="modal-header">
        <h2>Import Excel Products</h2>
        <button class="close-btn" @click="closeModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Step 1: File Upload -->
      <div v-if="!excelData">
        <div class="upload-area glass hover-lift" @click="fileInput.click()">
          <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
          </svg>
          <p>Click to upload Excel file or drag & drop</p>
          <p class="file-types">.xlsx, .xls, .csv supported</p>
          <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" @change="handleFile" class="hidden">
        </div>
      </div>

      <!-- Step 2: Column Mapping -->
      <div v-else-if="excelData">
        <div class="step-info">
          <p>{{ excelData.length }} rows detected. Map columns:</p>
        </div>

        <div class="mapping-grid">
          <div class="mapping-field">
            <label>Reference</label>
            <select v-model="mapping.ref" class="glass-input">
              <option value="">Select column</option>
              <option v-for="(col, i) in columns" :key="'ref-'+i" :value="col">{{ col }}</option>
            </select>
          </div>
          <div class="mapping-field">
            <label>Désignation</label>
            <select v-model="mapping.designation" class="glass-input">
              <option value="">Select column</option>
              <option v-for="(col, i) in columns" :key="'des-'+i" :value="col">{{ col }}</option>
            </select>
          </div>
          <div class="mapping-field">
            <label>Marque</label>
            <select v-model="mapping.marque" class="glass-input">
              <option value="">Select column</option>
              <option v-for="(col, i) in columns" :key="'marque-'+i" :value="col">{{ col }}</option>
            </select>
          </div>
          <div class="mapping-field">
            <label>Prix</label>
            <select v-model="mapping.prix" class="glass-input">
              <option value="">Select column</option>
              <option v-for="(col, i) in columns" :key="'prix-'+i" :value="col">{{ col }}</option>
            </select>
          </div>
        </div>

        <!-- Fournisseur -->
        <div class="fournisseur-section">
          <label>Fournisseur
            <span v-if="loadingFournisseurs" class="loading">loading...</span>
            <span v-else-if="fournisseursError" class="error">{{ fournisseursError }}</span>
          </label>
          <div class="fournisseur-select">
            <select v-model="fournisseurId" class="glass-input flex-1" :disabled="loadingFournisseurs">
              <option value="">Select existing ({{ fournisseurs.length }})</option>
              <option v-for="sup in fournisseurs" :key="'sup-'+sup.id" :value="sup.id">{{ sup.nom }}</option>
            </select>
            <button v-if="!newFournisseur" class="btn-secondary" @click="newFournisseur = true" :disabled="loadingFournisseurs">+ New</button>
            <input v-else v-model="newFournisseurNom" placeholder="New fournisseur name" class="glass-input" @keyup.enter="createFournisseur">
          </div>
        </div>

        <!-- Date Picker -->
        <div class="date-section">
          <label>Date (for all rows)</label>
          <input type="date" v-model="importDate" class="glass-input">
        </div>

        <!-- Preview -->
        <div class="preview-section">
          <h3>Preview (first 5 rows)</h3>
          <div class="preview-table">
            <table>
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>Désignation</th>
                  <th>Marque</th>
                  <th>Prix</th>
                  <th>Fournisseur</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in previewRows" :key="index">
                  <td>{{ getMappedValue(row, 'ref') }}</td>
                  <td>{{ getMappedValue(row, 'designation') }}</td>
                  <td>{{ getMappedValue(row, 'marque') }}</td>
                  <td>{{ getMappedValue(row, 'prix') }}</td>
                  <td>{{ selectedFournisseur }}</td>
                  <td>{{ importDate || 'Today' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-secondary" @click="closeModal">Cancel</button>
          <button class="btn-primary" :disabled="!isValidMapping" @click="submitImport">
            Import {{ excelData.length }} Products
          </button>
        </div>
      </div>

      <!-- Success -->
      <div v-else>
        <div class="success-state">
          <svg class="success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <h3>Import Started!</h3>
          <p>Products are being processed. Check the table for updates or view import status.</p>
          <button class="btn-primary" @click="closeModal">Done</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import * as XLSX from 'xlsx';
import axios from 'axios';

import { ref, computed, onMounted } from 'vue';

const emit = defineEmits(['close']);
const props = defineProps({
  showModal: Boolean
});

const fileInput = ref();
const excelData = ref(null);
const columns = ref([]);
const mapping = ref({
  ref: '',
  designation: '',
  marque: '',
  prix: ''
});
const fournisseurs = ref([]);
const fournisseurId = ref('');
const newFournisseur = ref(false);
const newFournisseurNom = ref('');
const importDate = ref('');

const previewRows = computed(() => excelData.value ? excelData.value.slice(0, 5) : []);
const selectedFournisseur = computed(() => {
  if (newFournisseur.value && newFournisseurNom.value) return newFournisseurNom.value;
  return fournisseurs.value.find(f => f.id == fournisseurId.value)?.nom || '';
});

const isValidMapping = computed(() => {
  return mapping.value.ref && mapping.value.designation && mapping.value.marque && mapping.value.prix;
});

const handleFile = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: 'array' });
    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
    const jsonData = XLSX.utils.sheet_to_json(firstSheet);

    excelData.value = jsonData;
    columns.value = Object.keys(jsonData[0] || {});
  };
  reader.readAsArrayBuffer(file);
};

const getMappedValue = (row, field) => {
  const col = mapping.value[field];
  return row[col] || '';
};

const loadingFournisseurs = ref(false);
const fournisseursError = ref('');

const loadFournisseurs = async () => {
  loadingFournisseurs.value = true;
  try {
    const { data } = await axios.get('http://127.0.0.1:8000/api/fournisseurs');
    fournisseurs.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to load fournisseurs', error);
    fournisseursError.value = 'Failed to load suppliers. Use + New or seed DB.';
    fournisseurs.value = [];
  } finally {
    loadingFournisseurs.value = false;
  }
};

  const createFournisseur = async () => {
  if (!newFournisseurNom.value) return;
  try {
    const { data } = await axios.post('http://127.0.0.1:8000/api/fournisseurs', { nom: newFournisseurNom.value });
    fournisseurs.value.push(data);
    fournisseurId.value = data.id;
    newFournisseur.value = false;
    newFournisseurNom.value = '';
  } catch (error) {
    console.error('Failed to create fournisseur');
    alert('Create failed: ' + error.response?.data?.message || error.message);
  }
};

const submitImport = async () => {
  const payload = {
    data: excelData.value,
    mapping: mapping.value,
    fournisseur_id: fournisseurId.value || null,
    new_fournisseur: newFournisseur.value ? newFournisseurNom.value : null,
    date: importDate.value
  };

  try {
    await axios.post('http://127.0.0.1:8000/api/products/import-mapping', payload);
    emit('close');
  } catch (error) {
    console.error('Import failed', error);
    alert('Import failed: ' + error.response?.data?.message || error.message);
  }
};

const closeModal = () => emit('close');

onMounted(loadFournisseurs);
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: fadeIn 0.3s ease;
}

.modal {
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(20px);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
}

.modal-header {
  padding: 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.close-btn {
  background: none;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  cursor: pointer;
  color: var(--text-secondary);
  transition: all 0.3s;
}

.close-btn:hover {
  background: rgba(255, 0, 0, 0.1);
  color: #ef4444;
}

.upload-area {
  border: 2px dashed var(--border-light);
  border-radius: 20px;
  padding: 3rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  margin: 2rem 0;
}

.upload-area:hover {
  border-color: var(--primary-color);
  background: rgba(29, 99, 140, 0.05);
  transform: translateY(-2px);
}

.hover-lift:hover {
  transform: translateY(-4px);
}

.upload-icon {
  width: 64px;
  height: 64px;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.upload-area p {
  margin: 0 0 0.5rem 0;
  color: var(--text-primary);
}

.file-types {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.mapping-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin: 2rem 0;
}

.mapping-field label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--text-primary);
}

.glass-input {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: 12px;
  padding: 0.875rem 1rem;
  font-size: 1rem;
  width: 100%;
  transition: all 0.3s;
  backdrop-filter: blur(10px);
}

.glass-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(29, 99, 140, 0.2);
}

.fournisseur-section, .date-section {
  margin: 2rem 0;
}

.fournisseur-select {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn-secondary {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  color: var(--text-primary);
  padding: 0.875rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 500;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.5);
  transform: translateY(-1px);
}

.date-section input {
  width: 100%;
  max-width: 300px;
}

.preview-section {
  margin: 2rem 0;
}

.preview-section h3 {
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.preview-table table {
  width: 100%;
  border-collapse: collapse;
  background: var(--glass-bg);
  border-radius: 12px;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.preview-table th, .preview-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--glass-border);
}

.preview-table th {
  background: rgba(255, 255, 255, 0.3);
  font-weight: 600;
}

.preview-table tr:hover {
  background: rgba(255, 255, 255, 0.2);
}

.modal-actions {
  padding: 2rem;
  border-top: 1px solid var(--glass-border);
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  min-width: 200px;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(29, 99, 140, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.success-state {
  text-align: center;
  padding: 4rem 2rem;
}

.success-icon {
  width: 80px;
  height: 80px;
  color: #10b981;
  margin-bottom: 1.5rem;
}

.success-state h3 {
  font-size: 1.75rem;
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.success-state p {
  color: var(--text-secondary);
  margin-bottom: 2rem;
  line-height: 1.6;
}

.hidden {
  display: none;
}

@keyframes pop-in {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-pop-in {
  animation: pop-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@media (max-width: 768px) {
  .mapping-grid {
    grid-template-columns: 1fr;
  }

  .modal-actions {
    flex-direction: column;
  }

  .btn-primary {
    width: 100%;
  }
}
</style>

