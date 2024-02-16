<template>
    <div v-for="[slug, value] of Object.entries(set)" class="radio-buttons-container">
        <input type="radio" :id="slug" :name="setName" :value="value" required>
        <label :for="slug">{{ value }}</label>
    </div>
</template>

<script>
    export default {
        name: "DynamicSelector",
        props: [
            'set',
            'setName'
        ]
    }
</script>
