{{-- Author: Michael Allan --}}

@php
    use Carbon\Carbon;
@endphp

@extends('layout')

@section('title', 'Records')

@section('content')

<h1 dusk="records_title">Records</h1>

<records root_url='{{ url("/") }}' />

@endsection
