<template>
    <div>
        <dynamic-selector :set="setWithOther()" :setName="setName" />
        <textarea :name="setName" placeholder="Describe..." required></textarea>
    </div>
</template>

<script>
    export default {
        name: "DynamicSelectorWithOther",
        props: [
            'set',
            'setName'
        ],
        methods: {
            setWithOther: function() {
                const setWithOther = this.set;
                setWithOther['other'] = 'Other';
                return setWithOther;
            }
        }
    }
</script>
