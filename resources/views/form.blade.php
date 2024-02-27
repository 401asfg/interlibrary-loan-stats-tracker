@extends('layout')

@section('title', 'ILL Statistics Form')

@section('content')
    @if (session('status'))
        <div class="success-banner">{{ session('status') }}</div>
    @endif

    <h1>ILL Statistics Form</h1>

    <form action="/" method="POST">
        @csrf
        <ill-request-form-fields
            :actions='{{ json_encode($actions) }}'
            :vcc_borrower_types='{{ json_encode($vccBorrowerTypes) }}'
            :unfulfilled_reasons='{{ json_encode($unfulfilledReasons) }}'
            :resources='{{ json_encode($resources) }}' />
    </form>
@endsection
