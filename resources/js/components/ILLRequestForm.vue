<!-- FIXME: implement 2 way bindings for all fields to replace @input and :initSelection with v-model -->

<template>
    <form @submit.prevent="submit">
        <div>
            <h2>Request Fulfilled</h2>
            <div>
                <div>
                    <div class="field-header">Date</div>
                    <input type="date" v-model="form.request_date" required>
                </div>

                <div>
                    <div class="field-header">Fulfilled?</div>
                    <input type="checkbox" :checked="true" v-model="form.fulfilled">
                </div>

                <div v-if="isUnfulfilled()">
                    <div class="field-header">Reason</div>
                    <DynamicSelectorWithOther :choices="unfulfilled_reasons" selectorName="Unfulfilled Reason" @input="onUnfulfilledReasonInput" :initSelection="form.unfulfilled_reason" />
                </div>
            </div>
        </div>

        <div>
            <h2>Request Info</h2>
            <div>
                <div>
                    <div class="field-header">Resource</div>
                    <DynamicSelectorWithOther :choices="resources" selectorName="Resource" @input="onResourceInput" :initSelection="form.resource" />
                </div>

                <div>
                    <div class="field-header">Action</div>
                    <DynamicSelector :choices="actions" :hiddenSlugs="getHiddenActionSlugs()" selectorName="Action" @input="onActionInput" :initSelection="form.action" />
                </div>
            </div>
        </div>

        <div v-if="hasAction()">
            <h2>Parties Involved</h2>
            <div>
                <div v-if="isLendingOrBorrowing()">
                    <div class="field-header">{{ getLibraryHeader() }}</div>
                    <SearchableSelect databaseRoute="/libraries" @input="onLibraryInput" :initSelection="form.library" />
                </div>

                <div v-if="isBorrowingOrShipping()">
                    <div class="field-header">VCC Borrower</div>

                    <DynamicSelector :choices="getSelectableBorrowerTypes()" selectorName="VCC Borrower Type" @input="onBorrowerTypeInput" :initSelection="form.vcc_borrower_type" />
                    <textarea v-model="form.vcc_borrower_notes" placeholder="Notes..."></textarea>
                </div>
            </div>
        </div>

        <div class="main-buttons-container">
            <button type="submit" class="submit-button">Submit</button>
        </div>
    </form>
</template>

<script>
    import DynamicSelector from './DynamicSelector.vue';
    import DynamicSelectorWithOther from './DynamicSelectorWithOther.vue';
    import SearchableSelect from './SearchableSelect.vue';

    export default {
        name: "ILLRequestForm",
        props: [
            'actions',
            'vcc_borrower_types',
            'unfulfilled_reasons',
            'resources'
        ],
        data() {
            return {
                form: {
                    request_date: new Date().toISOString().split('T')[0],
                    fulfilled: true,
                    unfulfilled_reason: null,
                    resource: null,
                    action: null,
                    library: null,
                    vcc_borrower_type: this.vcc_borrower_types['library'],
                    vcc_borrower_notes: null,
                },
            }
        },
        methods: {
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
                const isFulfilled = this.form.fulfilled;
                if (isFulfilled) this.form.unfulfilled_reason = null;
                return !isFulfilled;
            },
            isLendingOrBorrowing() {
                const neither = this.form.action !== this.actions['lend'] && this.form.action != this.actions['borrow'];

                if (neither) {
                    this.form.library = null;
                }

                return !neither;
            },
            isBorrowingOrShipping() {
                const neither = this.form.action !== this.actions['borrow'] && this.form.action !== this.actions['ship-to-me'];

                if (neither) {
                    this.form.vcc_borrower_type = this.vcc_borrower_types['library'];
                    this.form.vcc_borrower_notes = null;
                }

                return !neither;
            },
            hasAction() {
                return this.isLendingOrBorrowing() || this.isBorrowingOrShipping();
            },
            getLibraryHeader() {
                return (this.form.action === this.actions['borrow'] ? "Lending" : "Borrowing") + " Library";
            },
            getHiddenActionSlugs() {
                return (this.form.resource !== this.resources['ea'] && this.form.resource !== this.resources['book-chapter']) ? [] : ['ship-to-me'];
            },
            getSelectableBorrowerTypes() {
                let {library, ...borrowerTypes} = this.vcc_borrower_types;
                return borrowerTypes;
            },
            submit() {
                // FIXME: send post request to "/"
                console.log(this.form);
            }
        },
        components: {
            DynamicSelector,
            DynamicSelectorWithOther,
            SearchableSelect
        }
    }
</script>
