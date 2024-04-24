{{-- Author: Michael Allan --}}

@extends('layout')

@section('title', 'Records')

@section('content')

<h1 dusk="form_title">Records</h1>

<div class="centered-elements-container">
    <input name="date" type="date" dusk="date" required>
</div>

{{-- TODO: create page --}}

<div class="centered-elements-container bottom-buttons-container">
    <button onclick="window.location.href='/'" dusk='back' class="cancel-button">Back</button>
</div>

@endsection
