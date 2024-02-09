@extends('layout')

@section('title', 'Submission')

@section('content')
    <h1 class="success-title">Submission Successful!</h1>

    <div class="report-container">
        <h2>Summary</h2>

        <div>
            @if ($illRequest->fulfilled)
                <div class="field-header"><strong>Request was Fulfilled</strong></div>
            @else
                <div class="field-header"><strong>Request was not Fulfilled</strong></div>

                <div>
                    <div class="field-header"><strong>Reason why Request was Unfulfilled:</strong></div>
                    <div>{{ $illRequest->unfulfilled_reason }}</div>
                </div>
            @endif

            <div>
                <div class="field-header"><strong>Request Date:</strong></div>
                <div>{{ $illRequest->request_date }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Resource:</strong></div>
                <div>{{ $illRequest->resource }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Action:</strong></div>
                <div>{{ $illRequest->action }}</div>
            </div>

            @if (!is_null($libraryName))
                <div>
                    <div class="field-header"><strong>Library:</strong></div>
                    <div>{{ $libraryName }}</div>
                </div>
            @endif

            <div>
                <div class="field-header"><strong>Requestor Type:</strong></div>
                <div>{{ $illRequest->requestor_type }}</div>
            </div>

            @if (!is_null($illRequest->requestor_notes))
                <div>
                    <div class="field-header"><strong>Requestor Notes:</strong></div>
                    <div>{{ $illRequest->requestor_notes }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="main-buttons-container">
        <button onclick="window.location.href='/'">Submit Another ILL Request</button>
        <form action="/{{ $illRequest->id }}" method="POST">
            @csrf
            @method("DELETE")
            <button class="destructive-button">Delete Record</button>
        </form>
    </div>
@endsection
