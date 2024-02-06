<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
        <title>@yield('title')</title>
    </head>

    <body>
        @yield('content')
    </body>
</html>
