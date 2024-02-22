<template>
    <div>
        <DynamicSelector :choices="setWithOther()" :selectorName="selectorName" @input="onSelectorInput" />
        <textarea v-if="otherSelected" :name="selectorName" placeholder="Describe..." @input="onTextInput" required></textarea>
    </div>
</template>

<script>
    import DynamicSelector from './DynamicSelector.vue';

    const OTHER_DISPLAY_TEXT = 'Other';

    export default {
    name: "DynamicSelectorWithOther",
    props: [
        'choices',
        'selectorName',
    ],
    data() {
        return {
            otherSelected: false,
        }
    },
    methods: {
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
        }
    },
    components: { DynamicSelector }
}
</script>
