@extends('layout')

@section('title', 'Submission')

@section('content')
    <h1 class="success">Submission Successful!</h1>

    <div class="report-container">
        <h2>Summary</h2>

        <div>
            <div>
                <div class="field-header"><strong>Request Date:</strong></div>
                <div>{{ $illRequest->requestDate }}</div>
            </div>

            <div class="field-header"><strong>Request was Fulfilled</strong></div>

            <div class="field-header"><strong>Request was not Fulfilled</strong></div>

            <div>
                <div class="field-header"><strong>Reason why Request was Unfulfilled:</strong></div>
                <div>{{ $illRequest->unfulfilledReason }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Resource:</strong></div>
                <div>{{ $illRequest->resource }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Action:</strong></div>
                <div>{{ $illRequest->action }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Fulfilling Library:</strong></div>
                <div>{{ $illRequest->library }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Borrowing Library:</strong></div>
                <div>{{ $illRequest->library }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Requestor Type:</strong></div>
                <div>{{ $illRequest->requestorType }}</div>
            </div>

            <div>
                <div class="field-header"><strong>Requestor Notes:</strong></div>
                <div>{{ $illRequest->requestorNotes }}</div>
            </div>
        </div>
    </div>

    <div class="main-buttons-container">
        <button onclick="location.href='{{ url('/') }}'">Submit Another ILL Request</button>
        <button>View All Records</button>
        <button class="destructive-button">Delete Record</button>
    </div>
@endsection
