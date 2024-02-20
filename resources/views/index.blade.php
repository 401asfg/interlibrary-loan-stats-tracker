@extends('layout')

@section('scripts')
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endsection

@section('title', 'ILL Statistics Form')

@section('content')
    @if (session('status'))
        <div class="success-banner">{{ session('status') }}</div>
    @endif

    <h1>ILL Statistics Form</h1>

    <ill-request-form :actions='{{ json_encode($actions) }}'
                      :vcc_borrower_types='{{ json_encode($vccBorrowerTypes) }}'
                      :unfulfilled_reasons='{{ json_encode($unfulfilledReasons) }}'
                      :resources='{{ json_encode($resources) }}' />
@endsection
