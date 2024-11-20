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

    <script type="text/javascript">
        $(document).ready(function () {
            let $form = $('#formularEdit');

            $form.validate({
                lang: 'de',
                ignore: [],
                focusInvalid: false,
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('help-block alert alert-danger');
                    error.insertAfter($(element).closest('.form-group'));
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass('has-error').removeClass('has-success');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass('has-success').removeClass('has-error');
                },
                invalidHandler: function (form, validator) {
                    if (!validator.numberOfInvalids()) return;

                    $('html, body').animate({
                        scrollTop: $(validator.errorList[0].element).offset().top - 150,
                    }, 1000);
                },
                groups: {
                    particularly_important: '14[answer][ketogenic] 14[answer][low_carb] 14[answer][moderate_carb] 14[answer][paleo] 14[answer][vegetarian] 14[answer][vegan] 14[answer][pescetarisch] 14[answer][aip] 14[answer][no_matter]',
                },
            });
        });
    </script>
@append