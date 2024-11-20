<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @stack('preconnect')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Foodpunk') }}</title>

    @php $fonts = [['family'=>'Nunito Sans', 'wght' => [300,400,500,600,700,800]]];@endphp
    <x-googleFonts :fonts="$fonts"/>

    <!-- Styles -->
    <link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/app.css') }}">

    @yield('styles')
</head>
<body>
<div id="app">
    <div class="container">
        <div class="row">
            @include('layouts.flash-message')

            @yield('content')
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ mix('js/app.js') }}"></script>

@yield('scripts')
</body>
</html>
