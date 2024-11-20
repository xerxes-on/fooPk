@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-offset-1 col-sm-10">
                <div class="auth_panel">
                    <div class="auth_panel_title">
                        {{ trans('passwords.reset_password') }}
                    </div>
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email"
                                   class="control-label auth_panel_label">{{ trans('passwords.e-mail_address') }}</label>

                            <input id="email" type="email" class="form-control auth_panel_input" name="email"
                                   value="{{ old('email') }}" required>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                            @endif
                        </div>

                        <div class="form-group text-right">
                            <button type="submit"
                                    class="btn btn-tiffany">{{ trans('passwords.send_password_reset_link') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
