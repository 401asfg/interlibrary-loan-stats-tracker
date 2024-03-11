<template>
    <div>
        <input type="text" placeholder="Search..." v-model="query" @input="queryDatabase" @focus="startSearch" @blur="endSearch" dusk="library" required>
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
        props: {
            databaseRoute: {
                type: String,
            },
            initSelection: {
                type: Object,
                default: null
            }
        },
        data() {
            return {
                query: this.getInitQuery(),
                results: [],
                hoverIndex: 0,
                selection: this.initSelection
            }
        },
        methods: {
            getInitQuery() {
                if (this.initSelection === null) return '';
                return this.initSelection.name;
            },
            hasQuery() {
                return this.query !== '';
            },
            startSearch() {
                this.selection = null;
            },
            endSearch() {
                if (this.selection === null) this.query = '';
            },
            isSearching() {
                return this.hasQuery() && this.selection === null;
            },
            hasResults() {
                return this.results.length !== 0;
            },
            updateHover(index) {
                this.hoverIndex = index;
            },
            getDropdownItemClass(index) {
                return index === this.hoverIndex ? FOCUSED_DROPDOWN_ITEM_CLASS : '';
            },
            chooseSelection(index) {
                if (this.results.length === 0) return;

                this.selection = this.results[index];
                this.query = this.selection.name;

                this.$emit('input', this.selection);
            },
            queryDatabase() {
                this.updateHover(0);

                if (!this.hasQuery()) return;

                axios.get(this.databaseRoute, { params: { query: this.query } })
                     .then(response => {
                        this.results = response.data;
                     })
                     .catch(error => console.log(error));
            }
        }
    };
</script>

