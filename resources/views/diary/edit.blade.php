@extends('layouts.app')

@section('title', trans('common.diary_data'))

@section('styles')
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.fp.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-offset-1 col-md-offset-2 col-sm-10 col-md-8">
                <h1>@lang('common.update_diary_data')</h1>
                @if (empty($date) && empty($diary))
                    <h4>@lang('common.nothing_found')</h4>
                @else
                    <div class="row">
                        <div class="create-new">
                            {!! Form::open(['route' => array('diary.update', $diary->id), 'method' => 'PUT', 'files' => true]) !!}
                            <div class="col-sm-8">
                                <h4><b>@lang('common.last_update')</b> {{$diary->created_at}}</h4>
                                <div class="form-group">
                                    {!! Form::label('weight', trans('common.weight'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('weight',  old('weight', $diary->weight), ['class' => 'form-control create-new_input']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('waist', trans('common.waist'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('waist', old('waist', $diary->waist), ['class' => 'form-control create-new_input']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('upper_arm', trans('common.upper_arm'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('upper_arm', old('upper_arm', $diary->upper_arm), ['class' => 'form-control create-new_input']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('leg', trans('common.leg'), ['class' => 'create-new_label']) !!}
                                    {!! Form::text('leg', old('leg', $diary->leg), ['class' => 'form-control create-new_input']) !!}
                                </div>
                                @include('diary.fields.mood')
                                <div class="col-sm-4">
                                    {!! Form::submit(trans('common.save'), ['class' => 'btn btn-tiffany']) !!}
                                </div>

                                {!! Form::close() !!}

                                <div class="col-sm-4">
                                    <form action="{{ route('diary.destroy', $diary->id) }}" method="POST">
                                        {{ method_field('DELETE') }}
                                        {{ csrf_field() }}
                                        <button class="btn btn-pink-full">@lang('common.delete')</button>
                                    </form>
                                </div>
                                <div class="col-sm-4 ">
                                    <a href="{{ route('diary.statistics') }}"
                                       class="btn btn-gray">@lang('common.cancel')</a>
                                </div>
                            </div>
                        </div>
                    </div>
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
            let mood = $('.mood');
            mood.ionRangeSlider({
                min: 0,
                max: 10,
                step: 1,
                from: mood.attr('mood'),
                grid: true,
                grid_snap: true,
            });
        }

        function initCloseButton() {
            $('[data-dismiss="alert"]').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = '{{ route('posts.list') }}';
            });
        }
    </script>
@append