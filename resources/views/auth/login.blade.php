@extends('layouts.app')

@section('styles')
    <style>
        {{-- Tiny adjustment allowing to see the alert, as page scrolls us down to the bottom--}}
        .alert.alert-block.alert-dismissible {
            position: fixed;
            z-index: 99999;
            right: 50%;
            left: 50%;
            width: 60%;
            transform: translateX(-50%);
        }
    </style>
@endsection

@section('title', trans('auth.login'))
{{-- TODO: amend hardcoded stuff --}}
@section('content')
    <div class="container landing-bg">
        <div class="row">
            <div class="col-xs-12">
                <div class="landing-title">
                    <h1 class="text-center">@lang('auth.30_days_challenge')</h1>
                    <p class="lead text-center">@lang('auth.individual_nutrition_program')</p>
                </div>
            </div>
        </div>

        <div class="step step-one">
            <div class="step-one_bg movement-bg"></div>
            <div class="row">
                <div class="col-sm-4 col-md-offset-1 col-md-4 hidden-xs">
                    <img src="{{asset('/images/landing/speechbubble.svg')}}"
                         alt="{{trans('auth.first_block_title').' '.trans('auth.first_block_subtitle')}}"
                         class="img-responsive"/>
                </div>
                <div class="col-sm-8 col-md-7">
                    <div class="step_content">
                        <div class="step_content_num">1.</div>
                        <div class="step_content_text">
                            <div class="step_content_text_title">
                                <span>@lang('auth.first_block_title')</span>
                                @lang('auth.first_block_subtitle')
                            </div>
                            <p>@lang('auth.first_block_description')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="step step-two">
            <div class="step-two_bg movement-bg"></div>
            <div class="row">
                <div class="col-sm-8 col-md-7">
                    <div class="step_content">
                        <div class="step_content_num">2.</div>
                        <div class="step_content_text">
                            <div class="step_content_text_title">
                                <span>@lang('auth.second_block_title')</span>
                                @lang('auth.second_block_subtitle')
                            </div>
                            <p>@lang('auth.second_block_description')</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-md-offset-1 col-md-4 hidden-xs">
                    <img src="{{asset('/images/landing/chart.svg')}}"
                         class="img-responsive"
                         alt="{{trans('auth.second_block_title').' '.trans('auth.second_block_subtitle')}}"/>
                </div>
            </div>
        </div>

        <div class="step step-three">
            <div class="step-three_bg movement-bg"></div>
            <div class="row">
                <div class="col-sm-4 col-md-4 col-md-offset-1 hidden-xs">
                    <img src="{{asset('/images/landing/goal.svg')}}"
                         alt="{{trans('auth.third_block_title').' '.trans('auth.third_block_subtitle')}}"
                         class="img-responsive"/>
                </div>
                <div class="col-sm-8 col-md-7">
                    <div class="step_content">
                        <div class="step_content_num">3.</div>
                        <div class="step_content_text">
                            <div class="step_content_text_title">
                                <span>@lang('auth.third_block_title')</span>
                                @lang('auth.third_block_subtitle')
                            </div>
                            <p>@lang('auth.third_block_description')<br/>@lang('auth.third_block_description2')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="step step-four">
            <div class="step-four_bg movement-bg"></div>

            <div class="row">
                <div class="col-sm-offset-3 col-sm-6 col-sm-offset-4 col-md-4">
                    <div class="step_large-title">@lang('auth.just_start')</div>

                    @if(session('message'))
                        <span class="help-block"><strong>{{ session('message') }}</strong></span>
                    @endif
                    <ul id="tabs" class="tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#login_form" role="tab" data-toggle="tab">Login</a>
                        </li>
                        <li>
                            <a href="https://foodpunk.com/de/preise/">@lang('auth.book_now')</a>
                        </li>
                        <li><a href="https://foodpunk.com">Über Foodpunk</a></li>
                    </ul>

                    <div class="tab-content">

                        <div role="tabpanel" class="tab-pane fade in active"
                             id="login_form">
                            <div class="auth_panel">
                                <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                                    {{ csrf_field() }}

                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label for="email" class="control-label auth_panel_label">
                                            @lang('auth.email_address')
                                        </label>
                                        <input id="email" type="email"
                                               class="form-control auth_panel_input"
                                               name="email"
                                               value="{{ old('email') }}"
                                               required
                                               autofocus>

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label for="password" class="control-label auth_panel_label">
                                            @lang('auth.password')
                                        </label>

                                        <input id="password"
                                               type="password"
                                               class="form-control auth_panel_input"
                                               name="password"
                                               required>

                                        @if ($errors->has('password'))
                                            <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label class="auth_panel_label">
                                                <input type="checkbox"
                                                       name="remember"
                                                       checked="checked">
                                                @lang('auth.remember_me')
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group text-right">
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            @lang('auth.forgot_password')
                                        </a>
                                        <button type="submit" class="btn btn-tiffany">@lang('auth.login')</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var refreshDate = new Date;
        refreshDate.setHours(refreshDate.getHours() + 1);
        var refresPageTimer = setInterval(function () {

            currentDate = new Date();
            if (currentDate > refreshDate) {
                window.location.reload(true);
            }
            delete currentDate;

        }, 1000);

        $(document).ready(function () {
            var lFollowX = 0,
                lFollowY = 0,
                x = 0,
                y = 0,
                friction = 1 / 30;

            function moveBackground() {
                x += (lFollowX - x) * friction;
                y += (lFollowY - y) * friction;

                translate = 'translate(' + x + 'px, ' + y + 'px)';

                $('.movement-bg').css({
                    '-webit-transform': translate,
                    '-moz-transform': translate,
                    'transform': translate,
                });

                window.requestAnimationFrame(moveBackground);
            }

            $(window).on('mousemove click', function (e) {

                var lMouseX = Math.max(-100, Math.min(100, $(window).width() / 2 - e.clientX));
                var lMouseY = Math.max(-50, Math.min(50, $(window).height() / 2 - e.clientY));
                lFollowX = (20 * lMouseX) / 100; // 100 : 12 = lMouxeX : lFollow
                lFollowY = (10 * lMouseY) / 100;

            });

            moveBackground();

            var background_image_parallax = function ($object, multiplier) {
                multiplier = typeof multiplier !== 'undefined' ? multiplier : 0.5;
                multiplier = 1 - multiplier;
                var $doc = $(document);
                $object.css({'background-attatchment': 'fixed'});
                $(window).scroll(function () {
                    var from_top = $doc.scrollTop(),
                        bg_css = 'center ' + (multiplier * from_top) + 'px';
                    $object.css({'background-position': bg_css});
                });
            };

            background_image_parallax($('.landing-bg'));
        });
    </script>
@append
