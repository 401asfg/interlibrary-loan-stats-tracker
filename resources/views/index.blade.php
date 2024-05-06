{{-- Author: Michael Allan --}}

@extends('layout')

@section('title', 'ILL Statistics Form')

@section('content')

<h1 dusk="index_title">ILL Statistics Form</h1>

<div class="index-page-body centered-elements-container bottom-buttons-container">
    <button onclick="window.location.href='{{ url('/') }}/ill-requests/create'" dusk='submit' class="submit-button">Submit an ILL Request</button>
    <button onclick="window.location.href='{{ url('/') }}/ill-requests/records'" dusk='view_records'>View Records</button>
</div>

@endsection
