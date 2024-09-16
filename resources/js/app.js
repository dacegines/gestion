import './bootstrap';

import axios from 'axios';

// Puedes configurar un valor base para tus peticiones de Axios si lo deseas:
axios.defaults.baseURL = 'http://127.0.0.1:8000'; // Cambia esto por la URL base de tu aplicación Laravel

// Puedes configurar headers de Axios como por ejemplo:
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';