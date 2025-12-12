import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.baseURL = import.meta.env.VITE_API_URL ?? 'http://0.0.0.0:8080';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
