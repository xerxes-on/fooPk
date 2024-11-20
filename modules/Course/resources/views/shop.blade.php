@extends('layouts.app')

@section('title', trans('course::common.more'))

@section('styles')
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.min.css"
          rel="stylesheet">
    <link href="{{ mix('css/course.css') }}" rel="stylesheet">
    <script type="text/javascript">
        window.foodPunk.course = {};
        window.foodPunk.course.i18n = {
            start_date: '{{ trans('course::common.select_start_date') }}',
            confirm: '{{trans('course::common.confirm')}}',
            yes: '{{trans('common.confirm')}}',
            cancel: '{{trans('common.cancel')}}',
            dateFormat: "@lang('survey_questions.date_format')",
        };
        window.foodPunk.course.routes = {
            buy: '{{ route('course.buying') }}',
            index: '{{ route('course.index') }}',
            buy_foodpoints: '{{config('adding-new-recipes.purchase_url')}}'
        };
    </script>
@endsection

@section('content')
    <div class="container">
        <h1>@lang('course::common.book_now')</h1>
        @if($courses->isEmpty())
            <p>@lang('course::common.no_new_courses')<br>
                <a href="{{ route('course.index') }}">@lang('course::common.all')</a>
            </p>
        @else
            <ul class="course-list">
                @foreach($courses as $course)
                    <x-course :$course :$userCoursesId></x-course>
                @endforeach
            </ul>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.de.min.js"></script>
    <script src="{{ mix('vendor/sweetalert/sweetalert.min.js')}}"></script>
    <script src="{{ mix('js/course.js') }}"></script>
@endsection
