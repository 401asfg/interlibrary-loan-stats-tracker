<!-- Author: Michael Allan -->

<!-- FIXME: implement 2 way bindings for all fields to replace @input and :initSelection with v-model -->

<template>
    <div>
        <h2>Request Fulfilled</h2>
        <div>
            <div>
                <div class="field-header">Date</div>
                <input name="request_date" type="date" v-model="form.request_date" dusk="request_date" required>
            </div>

            <div>
                <div class="field-header">Fulfilled?</div>
                <input name="fulfilled" type="checkbox" v-model="form.fulfilled" dusk="fulfilled">
                <input type="hidden" name="fulfilled" v-model="form.fulfilled">
            </div>

            <div v-if="isUnfulfilled()">
                <div class="field-header">Reason</div>
                <DynamicSelectorWithOther :choices="unfulfilled_reasons" selectorName="unfulfilled_reason" @input="onUnfulfilledReasonInput" :initSelection="form.unfulfilled_reason" dusk="unfulfilled_reason" />
            </div>
        </div>
    </div>

    <div>
        <h2>Request Info</h2>
        <div>
            <div>
                <div class="field-header">Resource</div>
                <DynamicSelectorWithOther :choices="resources" selectorName="resource" @input="onResourceInput" :initSelection="form.resource" dusk="resource" />
            </div>

            <div>
                <div class="field-header">Action</div>
                <DynamicSelector :choices="actions" :hiddenSlugs="getHiddenActionSlugs()" selectorName="action" @input="onActionInput" :initSelection="form.action" dusk="action" />
            </div>
        </div>
    </div>

    <div>
        <h2>Parties Involved</h2>
        <div>
            <div>
                <div class="field-header">Requestor Number/Notes</div>
                <textarea name="requestor_notes" v-model="form.requestor_notes" placeholder="Notes..." dusk="requestor_notes"></textarea>
            </div>

            <div v-if="isLendingOrBorrowing()">
                <div class="field-header">{{ getLibraryHeader() }}</div>
                <SearchableSelect :databaseRoute="root_url + '/libraries'" @input="onLibraryInput" :initSelection="form.library" />
                <input v-if="hasLibrary()" type="hidden" name="library_id" v-model="form.library.id">
            </div>

            <div v-if="isBorrowingOrShipping()">
                <div class="field-header">VCC Borrower</div>
                <DynamicSelector :choices="getSelectableBorrowerTypes()" selectorName="vcc_borrower_type" @input="onBorrowerTypeInput" :initSelection="form.vcc_borrower_type" dusk="vcc_borrower_type" />
            </div>
        </div>
    </div>

    <input type="hidden" name="vcc_borrower_type" v-model="form.vcc_borrower_type">

    <div class="centered-elements-container bottom-buttons-container">
        <button type="submit" class="submit-button" dusk="submit">Submit</button>
        <button :onclick=goToRoot dusk='cancel' class="cancel-button">Cancel</button>
    </div>
</template>

<script>
    import DynamicSelector from './DynamicSelector.vue';
    import DynamicSelectorWithOther from './DynamicSelectorWithOther.vue';
    import SearchableSelect from './SearchableSelect.vue';

    export default {
        name: "ILLRequestFormFields",
        props: [
            'actions',
            'vcc_borrower_types',
            'unfulfilled_reasons',
            'resources',
            'ill_request',
            'library_name',
            'root_url'
        ],
        data() {
            if (this.ill_request) {
                const prevForm = { ...this.ill_request };

                if (this.library_name) {
                    const libraryId = this.ill_request.library_id;

                    prevForm.library = {
                        id: libraryId,
                        name: this.library_name
                    };
                } else {
                    prevForm.library = null;
                }

                delete prevForm.library_id;
                return { form: prevForm };
            }

            return {
                form: {
                    request_date: new Date().toISOString().split('T')[0],
                    fulfilled: true,
                    unfulfilled_reason: null,
                    resource: null,
                    action: null,
                    library: null,
                    vcc_borrower_type: this.vcc_borrower_types['library'],
                    requestor_notes: null,
                }
            }
        },
        methods: {
            goToRoot() {
                window.location.href = this.root_url
            },
            onActionInput(event) {
                this.form.action = event.target.value;
            },
            onBorrowerTypeInput(event) {
                this.form.vcc_borrower_type = event.target.value;
            },
            onUnfulfilledReasonInput(event) {
                this.form.unfulfilled_reason = event.target.value;
            },
            onResourceInput(event) {
                this.form.resource = event.target.value;
            },
            onLibraryInput(library) {
                this.form.library = library;
            },
            isUnfulfilled() {
                // FIXME: normalize true values for fulfilled
                const isFulfilled = this.form.fulfilled;
                if (isFulfilled === "true" && isFulfilled === true) this.form.unfulfilled_reason = null;
                return isFulfilled !== "true" && isFulfilled !== true;
            },
            isLendingOrBorrowing() {
                const neither = this.form.action !== this.actions['lend'] && this.form.action != this.actions['borrow'];
                if (neither) this.form.library = null;
                return !neither;
            },
            isBorrowingOrShipping() {
                const neither = this.form.action !== this.actions['borrow'] && this.form.action !== this.actions['ship-to-me'];
                if (neither) this.form.vcc_borrower_type = this.vcc_borrower_types['library'];
                return !neither;
            },
            getLibraryHeader() {
                return (this.form.action === this.actions['borrow'] ? "Lending" : "Borrowing") + " Library";
            },
            getHiddenActionSlugs() {
                return (this.form.resource !== this.resources['ea'] && this.form.resource !== this.resources['book-chapter']) ? [] : ['ship-to-me'];
            },
            getSelectableBorrowerTypes() {
                const {library, ...borrowerTypes} = this.vcc_borrower_types;
                return borrowerTypes;
            },
            hasLibrary() {
                return this.form.library !== null;
            }
        },
        components: {
            DynamicSelector,
            DynamicSelectorWithOther,
            SearchableSelect
        }
    }
</script>
