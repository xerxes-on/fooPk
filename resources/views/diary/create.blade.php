@extends('layouts.app')

@section('title', trans('common.diary_data'))

@section('styles')
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.fp.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <br>
        <div class="row">
            <div class="col-sm-offset-1 col-md-offset-2 col-sm-10 col-md-8">
                <h1>@lang('common.diary_data')</h1>

                @if($diary->count() == 0)
                    <div class="row">
                        <div class="create-new">
                            {!! Form::open(['route' => 'diary.store', 'method' => 'POST', 'files' => true]) !!}

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="col-sm-8">

                                <div class="form-group">
                                    {!! Form::label('weight', trans('common.weight'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('weight', null, ['class' => 'form-control create-new_input']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('waist', trans('common.waist'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('waist', null, ['class' => 'form-control create-new_input']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('upper_arm', trans('common.upper_arm'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('upper_arm', null, ['class' => 'form-control create-new_input']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('leg', trans('common.leg'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('leg', null, ['class' => 'form-control create-new_input']) !!}
                                </div>

                                @include('diary.fields.mood')

                            </div>

                            {{-- <div class="col-sm-4">
                                <!-- load image blade -->
                                @include('diary.image', array('name' => 'image', 'label' => trans('common.post_photo'), 'required' => false))
                            </div> --}}

                            <div class="col-xs-6">
                                {!! Form::submit(trans('common.save'), ['class' => 'btn btn-tiffany']) !!}
                            </div>
                            <div class="col-xs-6">
                                <a href="{{ route('diary.statistics') }}"
                                   class="btn btn-gray">@lang('common.cancel')</a>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>

                @else
                    @php
                        $message = trans('common.existing_data');
                        $config = ['closable'  => true, 'type' => 'danger', 'rowWrapper' => false];
                    @endphp
                    <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            initIonRangeSlider();
            initCloseButton();
        });

        function initIonRangeSlider() {
            let mood = $('.mood'),
                moodAttr = mood.attr('mood');
            mood.ionRangeSlider({
                min: 0,
                max: 10,
                step: 1,
                from: moodAttr,
                grid: true,
                grid_snap: true,
            });
        }

        function initCloseButton() {
            $('[data-dismiss="alert"]').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = '{{ route('diary.statistics') }}';
            });
        }
    </script>
@append
