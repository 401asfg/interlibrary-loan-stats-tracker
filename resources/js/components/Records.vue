<template>
    <div class="horizontal-container">
        <div class="horizontal-container">
            <label>Multiple Dates?</label>
            <input name="fulfilled" type="checkbox" dusk="multiple_checkbox" :value="isDateRange" @input="onToggleMultiple">
        </div>

        <div class="horizontal-container">
            <label dusk="from_date_header">{{ isDateRange ? "From" : "Date" }}</label>
            <input name="from-date" type="date" dusk="from_date" :value="fromDate" @input="onFromDateSelection">
        </div>

        <div v-if="isDateRange" class="horizontal-container">
            <label dusk="to_date_header">To</label>
            <input name="to-date" type="date" dusk="to_date" :value="toDate" @input="onToDateSelection">
        </div>
    </div>

    <h3>Records Found: {{ numRecords() }}</h3>

    <div v-if="hasRecords()" @mouseout="endHover()">
        <div class="record-table-container">
            <table dusk="records_table">
                <tr>
                    <th v-for="header in Object.keys(records[0])">{{ header }}</th>
                </tr>
                <tr v-for="[index, record] in records.entries()">
                    <td :dusk="'records_table_entry_' + key.split(' ').join('_') + '_' + index"
                        v-for="[key, value] in Object.entries(record)"
                        :class="getRowClass(index)"
                        @mouseenter="updateHover(index)"
                        @click="selectRecord(index)">
                        {{ value }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="centered-elements-container bottom-buttons-container">
        <button :onclick=goToRoot dusk='back' class="cancel-button">Back</button>
    </div>
</template>

<script>
    import axios from 'axios';

    const FOCUSED_ROW_CLASS = "focused-item";

    export default {
        name: 'Records',
        props: [
            'root_url',
            'ill_requests_url'
        ],
        data() {
            return {
                isDateRange: false,
                fromDate: '',
                toDate: '',
                records: [],
                ids: [],
                hoverIndex: null
            }
        },
        methods: {
            goToRoot() {
                window.location.href = this.root_url;
            },
            numRecords() {
                return this.records.length;
            },
            hasRecords() {
                return this.numRecords() !== 0;
            },
            onDateSelection() {
                if (this.fromDate === '') return;

                let toDate = this.toDate;
                if (!this.isDateRange) toDate = this.fromDate;

                if (toDate === '') return;

                axios.get(this.root_url + '/ill-requests', {
                    params: {
                        fromDate: this.fromDate,
                        toDate: toDate
                    }
                }).then(response => {
                    const data = response.data;
                    this.records = data.records;
                    this.ids = data.ids;
                }).catch(error => console.log(error));
            },
            onToggleMultiple(event) {
                this.isDateRange = event.target.checked;
                if (!this.isDateRange) this.toDate = '';
                this.onDateSelection();
            },
            onFromDateSelection(event) {
                this.fromDate = event.target.value;
                this.onDateSelection();
            },
            onToDateSelection(event) {
                this.toDate = event.target.value;
                this.onDateSelection();
            },
            getRowClass(index) {
                return index === this.hoverIndex ? FOCUSED_ROW_CLASS : '';
            },
            updateHover(index) {
                this.hoverIndex = index;
            },
            endHover() {
                this.hoverIndex = null;
            },
            selectRecord(index) {
                const selectedId = this.ids[index].id;
                window.location.href = `${this.ill_requests_url}/${selectedId}`;
            }
        }
    }
</script>
