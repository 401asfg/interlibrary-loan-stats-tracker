/*
* Author: Michael Allan
*/

import './bootstrap';

import { createApp } from 'vue';
import ILLRequestFormFields from './components/ILLRequestFormFields.vue';
import Records from './components/Records.vue';

const app = createApp({});
app.component('ill-request-form-fields', ILLRequestFormFields);
app.component('records', Records);
app.mount('#app');
