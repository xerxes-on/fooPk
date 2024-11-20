@extends('layouts.app')

@section('title', trans('auth.login'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-offset-1 col-sm-10">
                <div class="auth_panel">
                    <div class="auth_panel_title">
                        {{ trans('auth.register') }}
                    </div>
                    <form class="form-horizontal" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}

                        <div class="form-group required{{ $errors->has('first_name') ? ' has-error' : '' }}">
                            <label for="first_name"
                                   class="control-label auth_panel_label">{{ trans('auth.first_name') }}</label>
                            <input id="first_name" type="text" class="form-control auth_panel_input" name="first_name"
                                   value="{{ old('first_name') }}" required autofocus>

                            @if ($errors->has('first_name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('first_name') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group required{{ $errors->has('last_name') ? ' has-error' : '' }}">
                            <label for="last_name"
                                   class="control-label auth_panel_label">{{ trans('auth.last_name') }}</label>

                            <input id="last_name" type="text" class="form-control auth_panel_input" name="last_name"
                                   value="{{ old('last_name') }}" required autofocus>

                            @if ($errors->has('last_name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('last_name') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group required{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email"
                                   class="control-label auth_panel_label">{{ trans('auth.email_address') }}</label>
                            <input id="email" type="email" class="form-control auth_panel_input" name="email"
                                   value="{{ old('email') }}" required>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group required{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password"
                                   class="control-label auth_panel_label">{{ trans('auth.password') }}</label>

                            <input id="password" type="password" class="form-control auth_panel_input" name="password"
                                   required>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group required">
                            <label for="password-confirm"
                                   class="control-label auth_panel_label">{{ trans('auth.confirm_password') }}</label>
                            <input id="password-confirm" type="password" class="form-control auth_panel_input"
                                   name="password_confirmation" required>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-tiffany">{{ trans('auth.register') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
