{{-- TODO: PAGE IS NOT USED  --}}
@extends('layouts.app')

@section('title', $_page->title)

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
<link href="{{ mix('css/app.css') }}" rel="stylesheet"> {{--TODO: asset is loaded twice. first in layout.app--}}
<!-- Bootstrap CSS -->
<link rel="stylesheet"
      href="//cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
      integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
      crossorigin="anonymous">
<!-- Custom css -->
<link href="{{ mix('css/pricing-page.css') }}" rel="stylesheet">

@section('content')

    @php
        $urlParams = ['landing' => 'app'];
        if (Session::has('email')) $urlParams['email'] = urlencode(Session::get('email'));
        if (Session::has('firstname')) $urlParams['firstname'] = urlencode(Session::get('firstname'));
        if (Session::has('lastname')) $urlParams['lastname'] = urlencode(Session::get('lastname'));
    @endphp
    <div>
        {!! $_page->content !!}
    </div>

@endsection

@section('scripts')
    <!-- JS, Popper.js, and jQuery -->
    <script src="//cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
            crossorigin="anonymous"></script>
    <script src="{{ asset('pricingpage/js/script.js') }}"></script>
    <script src="//js.chargebee.com/v2/chargebee.js"
            data-cb-site="foodpunk"
            data-cb-gtm-enabled="true"
            data-cb-fbq-enabled="true"></script>
@endsection