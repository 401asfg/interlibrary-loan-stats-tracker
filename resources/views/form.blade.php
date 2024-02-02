@use('Carbon\Carbon')

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
        <title>ILL Statistics Form</title>
    </head>

    <body>
        <h1>ILL Statistics Form</h1>

        <div>
            <h2>Request Fulfilled</h2>
            <div>
                <div>
                    <div class="field-header">Date</div>
                    <input type="date" value={{ Carbon::today() }}>
                </div>

                <div>
                    <div class="field-header">Fulfilled?</div>
                    <input type="checkbox" checked="checked">
                </div>

                <div>
                    <div class="field-header">Reason</div>
                    <x-dynamic-selector-with-other :set="$unfulfilledReasons" setName="unfulfilled-reason"></x-dynamic-selector-with-other>
                </div>
            </div>
        </div>

        <div>
            <h2>Request Info</h2>
            <div>
                <div>
                    <div class="field-header">Resource</div>
                    <x-dynamic-selector-with-other :set="$resources" setName="resource"></x-dynamic-selector-with-other>
                </div>

                <div>
                    <div class="field-header">Action</div>
                    <x-dynamic-selector :set="$actions" setName="action"></x-dynamic-selector>
                </div>
            </div>
        </div>

        <div>
            <h2>Parties Involved</h2>
            <div>
                <div>
                    <div class="field-header">Fulfilling Library</div>
                    <input type="textarea" id="fulfilling-library" name="fulfilling-library" placeholder="Name...">
                </div>

                <div>
                    <div class="field-header">Borrowing Library</div>
                    <input type="textarea" id="borrowing-library" name="borrowing-library" placeholder="Name...">
                </div>

                <div>
                    <div class="field-header">Requestor</div>

                    @php
                        unset($requestorTypes['library']);
                    @endphp
                    <x-dynamic-selector :set="$requestorTypes" setName="requestor-type"></x-dynamic-selector>
                    <input type="textarea" id="notes" name="notes" placeholder="Notes..." class="description-box">
                </div>
            </div>
        </div>

        <button class="submit-button">Submit</button>
    </body>
</html>
