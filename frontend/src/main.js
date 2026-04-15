import './assets/new-ui.css'
import axios from 'axios'

axios.defaults.baseURL = 'http://127.0.0.1:8000/api'

// Request interceptor to add token
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

import { createApp } from 'vue'
import App from './App.vue'

createApp(App).mount('#app')
