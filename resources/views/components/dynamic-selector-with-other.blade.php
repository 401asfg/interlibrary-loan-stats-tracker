<div>
    @php
        $set['other'] = 'Other';
    @endphp
    <x-dynamic-selector :set="$set" :setName="$setName"></x-dynamic-selector>
    <textarea name={{ $setName }} placeholder="Describe..." required></textarea>
</div>
