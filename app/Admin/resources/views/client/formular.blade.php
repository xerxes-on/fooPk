@extends('layouts.admin-app')
{{--DEPRECATED--}}
@section('styles')
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.min.css"
          rel="stylesheet">
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.fp.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="col-sm-9 col-md-10 main">
        <h1>{{trans('common.formular.title')}}</h1>

        <div class="panel panel-default">
            <div class="panel-heading">{{ trans('common.enter_data') }}</div>

            <div class="panel-body">
                {!! Form::open(['route' => 'admin.clients.formular.store', 'method' => 'POST', 'files' => true, 'id' => 'formularEdit']) !!}
                <input type="hidden" name="client_id" value="{{$client->id}}">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @foreach($questions as $_question)
                    @if($_question->key_code === 'disease' || $_question->key_code === 'allergy')
                        @include('formular.fields.sickness')
                    @else
                        @include('formular.fields.' . $_question->type)
                    @endif

                @endforeach

                {!! Form::submit('Submit', ['class' => 'btn btn-info']) !!}

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.de.min.js"></script>
    <script src="{{mix('vendor/ion-rangeslider/ion.rangeSlider.min.js') }}"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/localization/messages_de.min.js"></script>

@append