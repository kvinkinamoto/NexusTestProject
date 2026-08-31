import { createApp } from 'vue';
import axios from 'axios';
import AsyncTable from './components/AsyncTable.vue';
import DynamicForm from './components/DynamicForm.vue';
import DynamicSelect from './components/DynamicSelect.vue';

// Діагностичний лог
console.log('Nexus.js loaded, initializing Vue...');

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

const app = createApp({
    mounted() {
        console.log('Vue app mounted!');
    }
});
app.component('async-table', AsyncTable);
app.component('dynamic-form', DynamicForm);
app.component('dynamic-select', DynamicSelect);
app.mount('#app');
