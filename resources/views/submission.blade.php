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
                    <div>{{ $illRequest->unfulfilledReason }}</div>
                </div>
            @endif

            <div>
                <div class="field-header"><strong>Request Date:</strong></div>
                <div>{{ $illRequest->requestDate }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Resource:</strong></div>
                <div>{{ $illRequest->resource }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Action:</strong></div>
                <div>{{ $illRequest->action }}</div>
            </div>

            @if (!is_null($illRequest->library))
                <div>
                    <div class="field-header"><strong>Library:</strong></div>
                    <div>{{ $illRequest->library }}</div>
                </div>
            @endif

            <div>
                <div class="field-header"><strong>Requestor Type:</strong></div>
                <div>{{ $illRequest->requestorType }}</div>
            </div>

            @if (!is_null($illRequest->requestorNotes))
                <div>
                    <div class="field-header"><strong>Requestor Notes:</strong></div>
                    <div>{{ $illRequest->requestorNotes }}</div>
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
