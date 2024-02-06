<div>
    @php
        $set['other'] = 'Other';
    @endphp
    <x-dynamic-selector :set="$set" :setName="$setName"></x-dynamic-selector>
    <input type="textarea" name={{ $setName }} placeholder="Describe..." class="description-box" required>
</div>
