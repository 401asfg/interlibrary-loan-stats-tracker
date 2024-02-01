{{-- TODO: setup ids and names --}}

@use('Carbon\Carbon')

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <h1>ILL Statistics Form</h1>

    <div>
        <h2>Request Fulfilled</h2>
        <div>
            <div>
                <div>Date</div>
                <input type="date" value={{ Carbon::today() }}>
            </div>

            <div>
                <div>Fulfilled?</div>
                <input type="checkbox" checked="checked">
            </div>

            <div>
                <div>Reason</div>
                <x-dynamic-selector-with-other :set="$unfulfilledReasons" setName="unfulfilled-reason"></x-dynamic-selector-with-other>
            </div>
        </div>
    </div>

    <div>
        <h2>Request Info</h2>
        <div>
            <div>
                <div>Resource</div>
                <x-dynamic-selector-with-other :set="$resources" setName="resource"></x-dynamic-selector-with-other>
            </div>

            <div>
                <div>Action</div>
                <x-dynamic-selector :set="$actions" setName="action"></x-dynamic-selector>
            </div>
        </div>
    </div>

    <div>
        <h2>Parties Involved</h2>
        <div>
            <div>
                <div>Fulfilling Library</div>
                <input type="textarea" id="fulfilling-library" name="fulfilling-library" placeholder="Name...">
            </div>

            <div>
                <div>Borrowing Library</div>
                <input type="textarea" id="borrowing-library" name="borrowing-library" placeholder="Name...">
            </div>

            <div>
                <div>Requestor</div>

                @php
                    unset($requestorTypes['library']);
                @endphp
                <x-dynamic-selector :set="$requestorTypes" setName="requestor-type"></x-dynamic-selector>
                <input type="textarea" id="notes" name="notes" placeholder="Notes...">
            </div>
        </div>
    </div>

    <button>Submit</button>
</html>
