@extends('layouts.app')
<style>
    #app {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
</style>
@section('title', trans('auth.login'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-offset-1 col-sm-10">
                <div class="auth_panel">
                    <div class="auth_panel_title">{{ trans('auth.login') }}</div>
                    @error('status')
                    <div class="alert-danger" style="border-radius:4px; padding: 15px">{{$message}}</div>
                    @enderror

                    <form class="form-horizontal" method="POST" action="{{ route('login.admin.submit') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email"
                                   class="control-label auth_panel_label">{{ trans('auth.email_address') }}</label>
                            <input id="email" type="email" class="form-control auth_panel_input" name="email"
                                   value="{{ old('email') }}" required autofocus>
                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
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

                        <div class="form-group">
                            <div class="checkbox">
                                <label class="auth_panel_label">
                                    <input type="checkbox"
                                           name="remember" {{ old('remember') ? 'checked' : '' }}> {{ trans('auth.remember_me') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                {{ trans('auth.forgot_password') }}
                            </a>
                            <button type="submit" class="btn btn-tiffany">
                                {{ trans('auth.login') }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
