@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-offset-1 col-sm-10">
                <div class="auth_panel">
                    <div class="auth_panel_title">
                        {{ trans('passwords.set_new_password') }}
                    </div>

                    <form class="form-horizontal" method="POST"
                          action="{{ url(config('app.url_meinplan').route('password.request', [], false)) }}">
                        {{ csrf_field() }}

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email"
                                   class="control-label auth_panel_label">@lang('passwords.e-mail_address')</label>

                            <input id="email" type="email" class="form-control auth_panel_input" name="email"
                                   value="{{ $email or old('email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password"
                                   class="control-label auth_panel_label">@lang('passwords.new_password')</label>

                            <input id="password" type="password" class="form-control auth_panel_input" name="password"
                                   required>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label for="password-confirm"
                                   class="control-label auth_panel_label">@lang('passwords.confirm_new_password')</label>
                            <input id="password-confirm" type="password" class="form-control auth_panel_input"
                                   name="password_confirmation" required>

                            @if ($errors->has('password_confirmation'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-tiffany">@lang('passwords.set_new_password')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
