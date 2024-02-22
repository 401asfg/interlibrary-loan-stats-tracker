import './bootstrap';

import { createApp } from 'vue';
import ILLRequestForm from './components/ILLRequestForm.vue';

const app = createApp({});
app.component('ill-request-form', ILLRequestForm);
app.mount('#app');
