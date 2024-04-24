<template>
    <div class="centered-elements-container">
        <input name="date" type="date" dusk="date" @input="onDateSelection" required>
    </div>

    <div v-if="hasRecords()" class="record-table-container">
        <table>
            <tr>
                <th v-for="header in Object.keys(records[0])">{{ header }}</th>
            </tr>
            <tr v-for="record in records">
                <td v-for="value in Object.values(record)">{{ value }}</td>
            </tr>
        </table>
    </div>

    <div v-else class="empty-table-notification">
        <h3>No records found</h3>
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
                records: []
            }
        },
        methods: {
            hasRecords() {
                return this.records.length !== 0;
            },
            onDateSelection(event) {
                axios.get('/ill-requests', { params: { date: event.target.value } })
                     .then(response => this.records = response.data)
                     .catch(error => console.log(error));
            }
        }
    }
</script>
