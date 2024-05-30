{{-- Author: Michael Allan --}}

@extends('layout')

@section('title', 'Submission')

@section('content')
@if ($status)
<h1 class="success-banner" dusk="submission_title">{{ $status }}</h1>
@endif

<div class="report-container">
    <h2>Summary</h2>

    <div>
        {{-- FIXME: normalize true values for fulfilled --}}
        @if ($illRequest->fulfilled === "true" || $illRequest->fulfilled === true)
        <div class="field-header" dusk="fulfilled"><strong>Request was Fulfilled</strong></div>
        @else
        <div class="field-header" dusk="unfulfilled"><strong>Request was not Fulfilled</strong></div>

        <div>
            <div class="field-header" dusk="unfulfilled_reason"><strong>Reason why Request was Unfulfilled:</strong></div>
            <div>{{ $illRequest->unfulfilled_reason }}</div>
        </div>
        @endif

        <div>
            <div class="field-header" dusk="request_date"><strong>Request Date:</strong></div>
            <div>{{ $illRequest->request_date }}</div>
        </div>

        <div>
            <div class="field-header" dusk="resource"><strong>Resource:</strong></div>
            <div>{{ $illRequest->resource }}</div>
        </div>

        <div>
            <div class="field-header" dusk="action"><strong>Action:</strong></div>
            <div>{{ $illRequest->action }}</div>
        </div>

        @if (!is_null($libraryName))
        <div>
            <div class="field-header" dusk="library"><strong>Library:</strong></div>
            <div dusk="library_name">{{ $libraryName }}</div>
        </div>
        @endif

        <div>
            <div class="field-header" dusk="vcc_borrower_type"><strong>VCC Borrower Type:</strong></div>
            <div>{{ $illRequest->vcc_borrower_type }}</div>
        </div>

        @if (!is_null($illRequest->requestor_notes))
        <div>
            <div class="field-header" dusk="requestor_notes"><strong>Requestor Number/Notes:</strong></div>
            <div>{{ $illRequest->requestor_notes }}</div>
        </div>
        @endif
    </div>
</div>

<div class="centered-elements-container bottom-buttons-container">
    <button onclick="window.location.href='{{ url('/') }}/ill-requests/create'" dusk='submit' class="submit-button">Submit Another ILL Request</button>
    <button onclick="window.location.href='{{ url('/') }}/ill-requests/{{ $illRequest->id }}/edit'" dusk='edit'>Edit Record</button>
    <button onclick="window.location.href='{{ url('/') }}/ill-requests/records'" dusk='view_records'>View Records</button>

    <form action="{{ url('/') }}/ill-requests/{{ $illRequest->id }}" method="POST">
        @csrf
        @method("DELETE")
        <button class="cancel-button" dusk='delete'>Delete Record</button>
    </form>
</div>
@endsection
