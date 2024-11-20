@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="col-sm-9 col-md-10 main">
        <div class="panel panel-default">
            <div class="panel-heading">{{trans('common.dashboard')}}</div>

            <div class="panel-body">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                {{ trans('common.log_in_dashboard_message') }}

            </div>
        </div>
    </div>
@endsection
