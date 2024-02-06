{{-- FIXME: do we need to use layouts and sections? --}}

@use('Carbon\Carbon')

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
        <title>ILL Statistics Form</title>
    </head>

    <form action="/" method="POST">
        @csrf

        <h1>ILL Statistics Form</h1>

        <div>
            <h2>Request Fulfilled</h2>
            <div>
                <div>
                    <div class="field-header">Date</div>
                    <input type="date" value={{ Carbon::today() }} name="request_date">
                </div>

                <div>
                    <div class="field-header">Fulfilled?</div>
                    <input type="checkbox" checked="checked" name="fulfilled">
                </div>

                <div>
                    <div class="field-header">Reason</div>
                    <x-dynamic-selector-with-other :set="$unfulfilledReasons" setName="unfulfilled_reason"></x-dynamic-selector-with-other>
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
                    <input type="textarea" name="library" placeholder="Name...">
                </div>

                <div>
                    <div class="field-header">Borrowing Library</div>
                    <input type="textarea" name="library" placeholder="Name...">
                </div>

                <div>
                    <div class="field-header">Requestor</div>

                    @php
                        unset($requestorTypes['library']);
                    @endphp
                    <x-dynamic-selector :set="$requestorTypes" setName="requestor_type"></x-dynamic-selector>
                    <input type="textarea" name="requestor_notes" placeholder="Notes..." class="description-box">
                </div>
            </div>
        </div>

        <div class="submit-section">
            <input type="submit" value="Submit">
        </div>
    </form>
</html>
