{{-- Author: Michael Allan --}}

@extends('layout')

@section('title', 'ILL Statistics Form')

@section('content')

<h1 dusk="form_title">ILL Statistics Form</h1>

<div class="index-buttons-container">
    <button onclick="window.location.href='{{ url('/') }}/ill-requests/create'" dusk='submit' class="submit-button">Submit an ILL Request</button>
    <button onclick="window.location.href='{{ url('/') }}/ill-requests'" dusk='records'>View Records</button>
    <button onclick="window.location.href='{{ url('/') }}/ill-requests/totals'" dusk='totals'>View Totals</button>
</div>

@endsection
