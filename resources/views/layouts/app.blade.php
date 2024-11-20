<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @stack('preconnect')

    @if ($currentRoute->getName()=='login')
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <meta http-equiv="Expires" content="0"/>
    @endif

    {{-- @include('googletagmanager::head')--}}

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Foodpunk MeinPlan - @yield('title')</title>

    <!-- Bookmarks & Favicon icons-->
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('/images/favicons/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('/images/favicons/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('/images/favicons/favicon-16x16.png')}}">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="{{asset('/images/favicons/safari-pinned-tab.svg')}}" color="#fffdff">
    <meta name="msapplication-TileColor" content="#9f00a7">
    <meta name="theme-color" content="#ffffff">

    {{-- TODO: maybe make it extensible --}}
    @php $fonts = [['family'=>'Nunito Sans', 'wght' => [300,400,500,600,700,800]]];@endphp
    <x-googleFonts :fonts="$fonts"></x-googleFonts>

    <script type="text/javascript">
        window.foodPunk = {};
    </script>

    <!-- Scripts - Deferred -->
    <script src="{{ mix('js/navigation.js') }}" defer></script>
    <script src="{{ mix('js/dismissibleAlert.js') }}" defer></script>

    <!-- Styles -->
    <link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ mix('css/loader.css') }}">
    <link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ mix('css/app.css') }}">
    @yield('styles')
    {!! htmlScriptTagJsApi() !!}
</head>

<body data-is-app="{{ ($isApp || $hasAppCookie !== false) ? 'true' : 'false' }}">

{{--@include('googletagmanager::body')--}}
<div class="loading" id="loading">Loading&#8230;</div>

<div id="app">
    @php $limitedRoute = in_array($currentRoute->getName(),['layouts.choose_device', 'verification.verify', 'verification.resend']) @endphp
    @if (!$limitedRoute)
        @include('layouts.inc.site-navigation')
        @include('layouts.flash-message')
    @endif

    @yield('content')
</div>

@if(!$user && !$isApp && !$hasAppCookie && !$limitedRoute)
    @include('layouts.inc.footer')
@endif

<!-- Scripts -->
<script src="{{ mix('js/app.js') }}"></script>
@yield('scripts')
@yield('scripts_after')
</body>
</html>
