<template>
    <div class="radio-buttons-container">
        <div v-for="[slug, displayName] of Object.entries(choices)">
            <div v-if="notHidden(slug)">
                <input type="radio" :name="selectorName" :id="slug" :value="displayName" @input="onInput" required>
                <label :for="slug">{{ displayName }}</label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "DynamicSelector",
        props: {
            choices: {
                type: Object,
                default: {}
            },
            selectorName: {
                type: String,
                default: ""
            },
            hiddenSlugs: {
                type: Array,
                default: []
            }
        },
        methods: {
            onInput(event) {
                this.$emit('input', event);
            },
            // FIXME: needs to wipe frontend and make sure form is in the proper state if this was hidden (may not be necessary)
            notHidden(slug) {
                return !this.hiddenSlugs.includes(slug);
            }
        }
    }
</script>
