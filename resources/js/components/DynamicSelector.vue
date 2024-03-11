<template>
    <div class="radio-buttons-container">
        <div v-for="[slug, displayName] of Object.entries(choices)">
            <div v-if="notHidden(slug)">
                <!-- FIXME: bad practice to tie v-model to init value that will be outdated? -->
                <input type="radio" :name="selectorName" :id="slug" :value="displayName" @input="onInput" v-model="selection" :dusk="selectorName + '_' + slug" required>
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
                type: Object
            },
            selectorName: {
                type: String
            },
            hiddenSlugs: {
                type: Array,
                default: []
            },
            initSelection: {
                type: String,
                default: null
            }
        },
        data() {
            return {
                selection: this.initSelection
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
