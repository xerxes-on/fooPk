<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @php
        $fonts = [['family'=>'Nunito Sans', 'wght' => [300,400,500,600,700,800]]];
        $user = Auth::user();
        $isApp = request()->has('is_app') && Cookie::get('is_app');
        $aboChallengeIsNotOver = null;
    @endphp
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Foodpunk MeinPlan - @yield('title')</title>

    <!-- Bookmarks icons-->
    <link rel="apple-touch-icon" href="{{ asset('/images/icons/foodpunk-logo57.svg') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('/images/icons/foodpunk-logo57.svg') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('/images/icons/foodpunk-logo57.svg') }}">
    <link rel="apple-touch-icon" sizes="228x228" href="{{ asset('/images/icons/foodpunk-logo57.svg') }}">

    <x-googleFonts :fonts="$fonts"/>

    <!-- Styles -->
    <link href="{{ mix('css/loader.css') }}" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body>
<div class="container" id="app">
    @include('layouts.inc.site-navigation')
    <div class="header-spacer"></div>

    @include('layouts.flash-message')

    @yield('content')
</div>
@include('layouts.inc.footer')

<!-- Scripts -->
<script src="{{ mix('js/app.js') }}"></script>

@yield('scripts')
</body>
</html>
