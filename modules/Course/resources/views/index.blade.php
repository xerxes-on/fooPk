@extends('layouts.app')

@section('title', trans('course::common.all'))
@section('styles')
    <link href="{{ mix('css/course.css') }}" rel="stylesheet">

    <script type="text/javascript">
        window.foodPunk.course = {};
        window.foodPunk.course.i18n = {
            restart: {},
            changeDate: {},
            yes: '{{trans('common.confirm')}}',
            cancel: '{{trans('common.cancel')}}',
            dateValidationError: '{{trans('course::common.date_start_error')}}',
        };
        window.foodPunk.course.i18n.restart = {
            title: '{{trans('course::common.restart.title')}}',
            approve: '{{trans('course::common.restart.approve')}}',
        }
        window.foodPunk.course.i18n.changeDate = {
            title: '{{trans('course::common.change_date.title')}}',
            approve: '{{trans('course::common.change_date.approve')}}',
        }
        window.foodPunk.course.routes = {
            reschedule: '{{ route('course.reschedule') }}',
            index: '{{ route('course.index') }}',
        };
    </script>
@endsection
@section('content')
    <div class="container">
        <h1>@lang('course::common.all')</h1>
        @if(isset($courses) && $courses->count() > 0)
            <ul class="course-list">
                @foreach($courses as $course)
                    <x-course :$course :$userCoursesId></x-course>
                @endforeach
            </ul>
            <div style="clear:both"></div>
        @else
            <div class="row">
                <div class="col-md-12">
                    <p>@lang('course::common.no_courses')
                        <a href="{{ route('course.buy') }}" target="_blank">@lang('course::common.click')</a>
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('vendor/sweetalert/sweetalert.min.js')}}"></script>
    <script src="{{ mix('js/course.js') }}"></script>
@endsection