import './bootstrap';

import { createApp } from 'vue';
import ILLRequestFormFields from './components/ILLRequestFormFields.vue';

const app = createApp({});
app.component('ill-request-form-fields', ILLRequestFormFields);
app.mount('#app');
