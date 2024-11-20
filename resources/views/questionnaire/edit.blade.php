@extends('layouts.app')

@section('title', trans('questionnaire.page_title'))
@push('preconnect')
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
@endpush
@section('styles')
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css"
          rel="stylesheet">
    <link href="//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1>@lang('questionnaire.page_title')</h1>
            </div>
        </div>

        <div class="formular_panel_edit">
            {{-- Show errors if any --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! Form::open(['route' => 'questionnaire.store', 'method' => 'POST', 'id' => 'questionnaireEdit', 'class' => 'needs-validation']) !!}
            <input type="hidden" name="client_id" value="{{auth()->id()}}">

            @foreach($questions as $question)
                @include("questionnaire.fields.{$question['type']}", ['question' => $question])
            @endforeach

            <div class="text-center">
                {!! Form::submit(trans('common.submit'), ['class' => 'btn btn-tiffany']) !!}
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('scripts')
    <script src="//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/i18n/{{auth()->user()->lang ?? 'de'}}.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.de.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ mix('js/questionnaireValidation.js') }}"></script>
@append
