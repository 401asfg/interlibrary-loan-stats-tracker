<template>
    <div>
        <DynamicSelector :choices="setWithOther()" :selectorName="selectorName" :initSelection="getInitButtonSelection()" @input="onSelectorInput" />
        <!-- FIXME: bad practice to tie v-model to init value that will be outdated? -->
        <textarea v-if="otherSelected" :name="selectorName" placeholder="Describe..." @input="onTextInput" v-model="selection" required></textarea>
    </div>
</template>

<script>
    import DynamicSelector from './DynamicSelector.vue';

    const OTHER_DISPLAY_TEXT = 'Other';

    export default {
    name: "DynamicSelectorWithOther",
    props: {
        choices: {
            type: Object
        },
        selectorName: {
            type: String
        },
        initSelection: {
            type: String,
            default: null
        }
    },
    data() {
        return {
            otherSelected: this.isOtherSelectedInit(),
            selection: this.initSelection,
        }
    },
    methods: {
        isOtherSelectedInit() {
            return this.initSelection !== null && !Object.values(this.choices).includes(this.initSelection);
        },
        getInitButtonSelection() {
            return this.isOtherSelectedInit() ? OTHER_DISPLAY_TEXT : this.initSelection;
        },
        setWithOther() {
            const choicesWithOther = this.choices;
            choicesWithOther['other'] = OTHER_DISPLAY_TEXT;
            return choicesWithOther;
        },
        onSelectorInput(event) {
            this.otherSelected = event.target.value === OTHER_DISPLAY_TEXT;
            if (!this.otherSelected) this.$emit('input', event);
        },
        onTextInput(event) {
            this.$emit('input', event);
        },
    },
    components: { DynamicSelector }
}
</script>
