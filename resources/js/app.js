import './bootstrap';

import { createApp } from 'vue';

import ILLRequestFormContent from './components/ILLRequestFormContent.vue';
import DynamicSelector from './components/DynamicSelector.vue';
import DynamicSelectorWithOther from './components/DynamicSelectorWithOther.vue';
import SearchableSelect from './components/SearchableSelect.vue';

const app = createApp({});

app.component('ill-request-form-content', ILLRequestFormContent);
app.component('dynamic-selector', DynamicSelector);
app.component('dynamic-selector-with-other', DynamicSelectorWithOther);
app.component('searchable-select', SearchableSelect);

app.mount('#app');
