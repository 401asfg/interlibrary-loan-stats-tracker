@extends('layout')

@section('title', 'ILL Statistics Form')

@section('content')
    @if (session('status'))
        <div class="success-banner">{{ session('status') }}</div>
    @endif

    <h1>ILL Statistics Form</h1>

    @if ($illRequest)
        <form action="/{{ $illRequest->id }}" method="POST">
            @method("PUT")
    @else
        <form action="/" method="POST">
    @endif
            @csrf
            <ill-request-form-fields
                :actions='{{ json_encode($actions) }}'
                :vcc_borrower_types='{{ json_encode($vccBorrowerTypes) }}'
                :unfulfilled_reasons='{{ json_encode($unfulfilledReasons) }}'
                :resources='{{ json_encode($resources) }}'
                :ill_request='{{ json_encode($illRequest) }}'
                :library_name='{{ json_encode($libraryName) }}'
                libraries_url='{{ url("/libraries") }}' />
        </form>
@endsection
