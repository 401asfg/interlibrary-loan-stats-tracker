{{-- Author: Michael Allan --}}

@php
    use Carbon\Carbon;
@endphp

@extends('layout')

@section('title', 'Records')

@section('content')

<h1 dusk="form_title">Records</h1>

<records />

@endsection
