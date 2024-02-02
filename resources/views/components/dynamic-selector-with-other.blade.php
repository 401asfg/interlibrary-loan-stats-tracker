<div>
    @php
        $set['other'] = 'Other';
    @endphp
    <x-dynamic-selector :set="$set" :setName="$setName"></x-dynamic-selector>
    <input type="textarea" id="description" name="description" placeholder="Describe..." class="description-box">
</div>
