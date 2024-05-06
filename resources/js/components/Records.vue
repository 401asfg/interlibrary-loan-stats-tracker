<template>
    <div class="horizontal-container">
        <div class="horizontal-container">
            <div>Multiple Dates?</div>
            <input name="fulfilled" type="checkbox" dusk="multiple_checkbox" :value="isDateRange" @input="onToggleMultiple">
        </div>

        <div class="horizontal-container">
            <div dusk="from_date_header">{{ isDateRange ? "From" : "Date" }}</div>
            <input name="from-date" type="date" dusk="from_date" :value="fromDate" @input="onFromDateSelection">
        </div>

        <div v-if="isDateRange" class="horizontal-container">
            <div dusk="to_date_header">To</div>
            <input name="to-date" type="date" dusk="to_date" :value="toDate" @input="onToDateSelection">
        </div>
    </div>

    <h3>Records Found: {{ numRecords() }}</h3>

    <div v-if="hasRecords()">
        <div class="record-table-container">
            <table dusk="records_table">
                <tr>
                    <th v-for="header in Object.keys(records[0])">{{ header }}</th>
                </tr>
                <tr v-for="[index, record] in records.entries()">
                    <td :dusk="'records_table_entry_' + key.split(' ').join('_') + '_' + index" v-for="[key, value] in Object.entries(record)">{{ value }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="centered-elements-container bottom-buttons-container">
        <button onclick="window.location.href='/'" dusk='back' class="cancel-button">Back</button>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'Records',
        data() {
            return {
                isDateRange: false,
                fromDate: '',
                toDate: '',
                records: []
            }
        },
        methods: {
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

                axios.get('/ill-requests', {
                    params: {
                        fromDate: this.fromDate,
                        toDate: toDate
                    }
                })
                    .then(response => this.records = response.data)
                    .catch(error => console.log(error));
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
            }
        }
    }
</script>
