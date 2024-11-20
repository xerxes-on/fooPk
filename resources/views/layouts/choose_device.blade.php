@extends("layouts.app")
@section("styles")
    <link rel="stylesheet" href="{{ mix('css/choose-device.css') }}">
@endsection
@section("content")
    <div class="container-fluid">
        <div class="row desktop-fullscreen">
            <div class="col-xs-12"><h1
                        class="page-title">{!! $message ?? trans('common.choose_device_page_title') !!}</h1></div>
            <div class="col-xs-8 col-sm-5 col-equal-height">
                <picture>
                    <source srcset="{{ asset("/images/choose_device/mealplan_{$language}_mobile.png") }}"
                            media="(max-width: 768px">
                    <source srcset="{{ asset("/images/choose_device/mealplan_{$language}.png") }}">
                    <img src="{{ asset("/images/choose_device/mealplan_{$language}.png") }}"
                         class="two-phones"
                         alt="Decor">
                </picture>
            </div>
            <div class="col-xs-12 col-sm-3 mobile-stick-to-bottom">
                <div class="stores-links col-equal-height">
                    <a class="stores-links-item"
                       aria-label="Get it on Google play"
                       target="_blank"
                       rel="nofollow noopener noindex"
                       href="{{ config('app.app_google_play_url') }}">
                        <img src="{{ asset("/images/choose_device/googleplay_{$language}.png") }}"
                             class="stores-links-image"
                             alt="Google store">
                    </a>
                    <a class="stores-links-item"
                       aria-label="Get it on Appstore"
                       target="_blank"
                       rel="nofollow noopener noindex"
                       href="{{ config('app.app_apple_store_url') }}">
                        <img src="{{ asset("/images/choose_device/appstore_{$language}.png") }}"
                             class="stores-links-image"
                             alt="Apple store">
                    </a>
                    @if(!isset($verify))
                        <a href="{{ route("recipes.list") }}" class="link-label">
                            <span class="link-label-text">{{ trans('common.choose_device_link_title') }}</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="col-xs-8 col-sm-4 col-equal-height mobile-stick-right">
                <div class="image-group">
                    <img src="{{ asset("/images/choose_device/green_logo.png") }}"
                         class="image-group-item image-group-mobile" alt="Foodpunk Logo">
                    <img src="{{ asset("/images/choose_device/choose_device_girl.png") }}"
                         class="image-group-item image-group-main" alt="Girl with food">
                </div>
            </div>
        </div>
        <span class="text-decor" aria-hidden="true">FOOD</span>
    </div>
@endsection
