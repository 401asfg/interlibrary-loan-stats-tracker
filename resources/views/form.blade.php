@use('Carbon\Carbon')

@extends('layout')

@section('scripts')
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endsection

@section('title', 'ILL Statistics Form')

@section('content')
    @if (session('status'))
        <div class="success-banner">{{ session('status') }}</div>
    @endif

    <h1>ILL Statistics Form</h1>

    <form action="/" method="POST">
        @csrf

        <div>
            <h2>Request Fulfilled</h2>
            <div>
                <div>
                    <div class="field-header">Date</div>
                    <input type="date" value={{ Carbon::today() }} name="request_date" required>
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
                    {{-- FIXME: Make the header display text based on the selected action --}}
                    <div class="field-header">{{ true ? "Fulfilling" : "Borrowing" }} Library</div>
                    <select class="form-control" id="library-search" name="library_data"></select>
                </div>

                <div>
                    <div class="field-header">Requestor</div>

                    @php
                        unset($requestorTypes['library']);
                    @endphp
                    <x-dynamic-selector :set="$requestorTypes" setName="requestor_type"></x-dynamic-selector>
                    <textarea name="requestor_notes" placeholder="Notes..."></textarea>
                </div>
            </div>
        </div>

        <div class="main-buttons-container">
            <button type="submit" class="submit-button">Submit</button>
        </div>
    </form>

    <script type="text/javascript">
        $('#library-search').select2({
            placeholder: 'Name...',
            ajax: {
                url: '/libraries',
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: $.map(data, function (library) {
                            return {
                                text: library.name,
                                id: [library.id, library.name]
                            }
                        })
                    };
                },
                cache: true
            }
      });
    </script>
@endsection
