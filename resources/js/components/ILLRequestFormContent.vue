<template>
    <div>
        <h2>Request Fulfilled</h2>
        <div>
            <div>
                <div class="field-header">Date</div>
                <input type="date" :value="new Date().toISOString().split('T')[0]" name="request_date" required>
            </div>

            <div>
                <div class="field-header">Fulfilled?</div>
                <input type="checkbox" :checked="true" name="fulfilled">
            </div>

            <div>
                <div class="field-header">Reason</div>
                <dynamic-selector-with-other :set="unfulfilled_reasons" setName="unfulfilled_reason" />
            </div>
        </div>
    </div>

    <div>
        <h2>Request Info</h2>
        <div>
            <div>
                <div class="field-header">Resource</div>
                <dynamic-selector-with-other :set="resources" setName="resource" />
            </div>

            <div>
                <div class="field-header">Action</div>
                <dynamic-selector :set="actions" setName="action" />
            </div>
        </div>
    </div>

    <div>
        <h2>Parties Involved</h2>
        <div>
            <div>
                <!-- FIXME: Make the header display text based on the selected action -->
                <div class="field-header">{{ true ? "Lending" : "Borrowing" }} Library</div>
                <!-- FIXME: send selected library data in form request -->
                <searchable-select database_route="/libraries" @selection="selectLibrary" />
            </div>

            <div>
                <div class="field-header">VCC Borrower</div>

                <dynamic-selector :set="vcc_borrower_types" setName="vcc_borrower_type" />
                <textarea name="vcc_borrower_notes" placeholder="Notes..."></textarea>
            </div>
        </div>
    </div>

    <div class="main-buttons-container">
        <button type="submit" class="submit-button">Submit</button>
    </div>
</template>

<script>
    export default {
        name: "ILLRequestFormContent",
        props: [
            'actions',
            'vcc_borrower_types',
            'unfulfilled_reasons',
            'resources'
        ],
        data() {
            return {
                library_id: null,
                library_name: null
            }
        },
        methods: {
            selectLibrary: function(library) {
                this.library_id = library.id;
                this.library_name = library.name;
            }
        }
    }
</script>
