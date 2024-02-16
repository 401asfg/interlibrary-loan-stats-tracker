<template>
    <div>
        <input type="text" placeholder="Search..." v-model="query" @input="queryDatabase" @focus="startSearch" @blur="endSearch" required>
        <div class="options-dropdown" v-if="isSearching()">
            <ul v-if="hasResults()">
                <li v-for="(result, index) in results" :class="getDropdownItemClass(index)" @mouseenter="updateHover(index)" @mousedown="chooseSelection(index)">{{ result.name }}</li>
            </ul>
            <ul v-if="!hasResults()">
                <li>No results found</li>
            </ul>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    const FOCUSED_DROPDOWN_ITEM_CLASS = 'focused-dropdown-item';

    export default {
        name: 'SearchableSelect',
        props: [
            'database_route'
        ],
        data() {
            return {
                query: '',
                results: [],
                hoverIndex: 0,
                selection: null
            }
        },
        methods: {
            hasQuery: function() {
                return this.query !== '';
            },
            startSearch: function() {
                this.selection = null;
            },
            endSearch: function() {
                if (this.selection === null) this.query = '';
            },
            isSearching: function() {
                return this.hasQuery() && this.selection === null;
            },
            hasResults: function() {
                return this.results.length !== 0;
            },
            updateHover: function(index) {
                this.hoverIndex = index;
            },
            getDropdownItemClass: function(index) {
                return index === this.hoverIndex ? FOCUSED_DROPDOWN_ITEM_CLASS : '';
            },
            chooseSelection: function(index) {
                if (this.results.length === 0) return;

                this.selection = this.results[index];
                this.query = this.selection.name;

                this.$emit('selection', this.selection);
            },
            queryDatabase: function() {
                this.updateHover(0);

                if (!this.hasQuery()) return;

                axios.get(this.database_route, { params: { query: this.query } })
                     .then(response => {
                        this.results = response.data;
                     })
                     .catch(error => console.log(error));
            }
        }
    };
</script>

